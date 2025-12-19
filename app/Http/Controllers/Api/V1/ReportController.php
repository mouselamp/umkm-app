<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Production;
use App\Models\Purchase;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    /**
     * Sales report
     */
    public function sales(Request $request): JsonResponse
    {
        $request->validate([
            'date_from' => 'required|date',
            'date_to' => 'required|date|after_or_equal:date_from',
            'group_by' => 'in:day,week,month',
        ]);

        $dateFrom = $request->date_from;
        $dateTo = $request->date_to;
        $groupBy = $request->get('group_by', 'day');

        $dateFormat = match ($groupBy) {
            'week' => '%Y-%u',
            'month' => '%Y-%m',
            default => '%Y-%m-%d',
        };

        // Summary
        $summary = Order::whereBetween('order_date', [$dateFrom, $dateTo])
            ->where('status', '!=', 'cancelled')
            ->selectRaw('
                COUNT(*) as total_orders,
                SUM(subtotal) as gross_sales,
                SUM(discount) as total_discount,
                SUM(delivery_fee) as total_delivery_fee,
                SUM(total) as net_sales,
                SUM(CASE WHEN payment_status = "paid" THEN total ELSE 0 END) as paid_amount,
                SUM(CASE WHEN payment_status != "paid" THEN total ELSE 0 END) as unpaid_amount
            ')
            ->first();

        // Grouped data
        $groupedData = Order::whereBetween('order_date', [$dateFrom, $dateTo])
            ->where('status', '!=', 'cancelled')
            ->selectRaw("
                DATE_FORMAT(order_date, '{$dateFormat}') as period,
                COUNT(*) as orders,
                SUM(total) as total_sales
            ")
            ->groupBy('period')
            ->orderBy('period')
            ->get();

        // By payment status
        $byPaymentStatus = Order::whereBetween('order_date', [$dateFrom, $dateTo])
            ->where('status', '!=', 'cancelled')
            ->selectRaw('
                payment_status,
                COUNT(*) as count,
                SUM(total) as total
            ')
            ->groupBy('payment_status')
            ->get();

        // Top customers
        $topCustomers = Order::whereBetween('order_date', [$dateFrom, $dateTo])
            ->where('status', '!=', 'cancelled')
            ->whereNotNull('customer_id')
            ->join('customers', 'orders.customer_id', '=', 'customers.id')
            ->selectRaw('
                customers.id,
                customers.name,
                COUNT(*) as total_orders,
                SUM(orders.total) as total_purchases
            ')
            ->groupBy('customers.id', 'customers.name')
            ->orderByDesc('total_purchases')
            ->limit(10)
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'period' => [
                    'from' => $dateFrom,
                    'to' => $dateTo,
                ],
                'summary' => $summary,
                'chart_data' => $groupedData,
                'by_payment_status' => $byPaymentStatus,
                'top_customers' => $topCustomers,
            ],
        ]);
    }

    /**
     * Purchase report
     */
    public function purchases(Request $request): JsonResponse
    {
        $request->validate([
            'date_from' => 'required|date',
            'date_to' => 'required|date|after_or_equal:date_from',
        ]);

        $dateFrom = $request->date_from;
        $dateTo = $request->date_to;

        // Summary
        $summary = Purchase::whereBetween('purchase_date', [$dateFrom, $dateTo])
            ->selectRaw('
                COUNT(*) as total_purchases,
                SUM(total) as total_amount,
                SUM(CASE WHEN payment_type = "cash" THEN total ELSE 0 END) as cash_purchases,
                SUM(CASE WHEN payment_type = "credit" THEN total ELSE 0 END) as credit_purchases
            ')
            ->first();

        // By supplier
        $bySupplier = Purchase::whereBetween('purchase_date', [$dateFrom, $dateTo])
            ->join('suppliers', 'purchases.supplier_id', '=', 'suppliers.id')
            ->selectRaw('
                suppliers.id,
                suppliers.name,
                COUNT(*) as total_purchases,
                SUM(purchases.total) as total_amount
            ')
            ->groupBy('suppliers.id', 'suppliers.name')
            ->orderByDesc('total_amount')
            ->get();

        // By material
        $byMaterial = DB::table('purchase_items')
            ->join('purchases', 'purchase_items.purchase_id', '=', 'purchases.id')
            ->join('materials', 'purchase_items.material_id', '=', 'materials.id')
            ->whereBetween('purchases.purchase_date', [$dateFrom, $dateTo])
            ->selectRaw('
                materials.id,
                materials.name,
                SUM(purchase_items.quantity) as total_qty,
                SUM(purchase_items.subtotal) as total_amount,
                AVG(purchase_items.price) as avg_price
            ')
            ->groupBy('materials.id', 'materials.name')
            ->orderByDesc('total_amount')
            ->limit(20)
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'period' => [
                    'from' => $dateFrom,
                    'to' => $dateTo,
                ],
                'summary' => $summary,
                'by_supplier' => $bySupplier,
                'by_material' => $byMaterial,
            ],
        ]);
    }

    /**
     * Production report
     */
    public function productions(Request $request): JsonResponse
    {
        $request->validate([
            'date_from' => 'required|date',
            'date_to' => 'required|date|after_or_equal:date_from',
        ]);

        $dateFrom = $request->date_from;
        $dateTo = $request->date_to;

        // Summary by status
        $byStatus = Production::whereBetween('production_date', [$dateFrom, $dateTo])
            ->selectRaw('
                status,
                COUNT(*) as count
            ')
            ->groupBy('status')
            ->get();

        // Production output by product
        $byProduct = DB::table('production_items')
            ->join('productions', 'production_items.production_id', '=', 'productions.id')
            ->join('recipes', 'production_items.recipe_id', '=', 'recipes.id')
            ->join('products', 'recipes.product_id', '=', 'products.id')
            ->whereBetween('productions.production_date', [$dateFrom, $dateTo])
            ->where('productions.status', 'completed')
            ->selectRaw('
                products.id,
                products.name,
                SUM(production_items.quantity) as total_produced,
                SUM(production_items.cost) as total_cost
            ')
            ->groupBy('products.id', 'products.name')
            ->orderByDesc('total_produced')
            ->get();

        // Total output
        $totalOutput = DB::table('production_items')
            ->join('productions', 'production_items.production_id', '=', 'productions.id')
            ->whereBetween('productions.production_date', [$dateFrom, $dateTo])
            ->where('productions.status', 'completed')
            ->selectRaw('
                SUM(quantity) as total_produced,
                SUM(cost) as total_cost
            ')
            ->first();

        return response()->json([
            'success' => true,
            'data' => [
                'period' => [
                    'from' => $dateFrom,
                    'to' => $dateTo,
                ],
                'summary' => [
                    'total_produced' => $totalOutput->total_produced ?? 0,
                    'total_cost' => $totalOutput->total_cost ?? 0,
                ],
                'by_status' => $byStatus,
                'by_product' => $byProduct,
            ],
        ]);
    }

    /**
     * Inventory valuation report
     */
    public function inventory(): JsonResponse
    {
        // Product inventory
        $products = DB::table('product_stocks')
            ->join('products', 'product_stocks.product_id', '=', 'products.id')
            ->selectRaw('
                products.id,
                products.name,
                products.sku,
                product_stocks.quantity,
                product_stocks.avg_cost,
                (product_stocks.quantity * product_stocks.avg_cost) as value
            ')
            ->orderByDesc('value')
            ->get();

        $productTotal = $products->sum('value');

        // Material inventory
        $materials = DB::table('material_stocks')
            ->join('materials', 'material_stocks.material_id', '=', 'materials.id')
            ->join('units', 'materials.unit_id', '=', 'units.id')
            ->selectRaw('
                materials.id,
                materials.name,
                units.symbol as unit,
                materials.min_stock,
                material_stocks.quantity,
                material_stocks.avg_cost,
                (material_stocks.quantity * material_stocks.avg_cost) as value,
                (material_stocks.quantity <= materials.min_stock) as is_low_stock
            ')
            ->orderByDesc('value')
            ->get();

        $materialTotal = $materials->sum('value');

        return response()->json([
            'success' => true,
            'data' => [
                'products' => [
                    'items' => $products,
                    'total_value' => $productTotal,
                    'total_items' => $products->count(),
                ],
                'materials' => [
                    'items' => $materials,
                    'total_value' => $materialTotal,
                    'total_items' => $materials->count(),
                    'low_stock_count' => $materials->where('is_low_stock', 1)->count(),
                ],
                'total_inventory_value' => $productTotal + $materialTotal,
            ],
        ]);
    }

    /**
     * Profit/Loss report (simplified)
     */
    public function profitLoss(Request $request): JsonResponse
    {
        $request->validate([
            'date_from' => 'required|date',
            'date_to' => 'required|date|after_or_equal:date_from',
        ]);

        $dateFrom = $request->date_from;
        $dateTo = $request->date_to;

        // Revenue (from completed orders)
        $revenue = Order::whereBetween('order_date', [$dateFrom, $dateTo])
            ->where('status', 'completed')
            ->sum('total');

        // Cost of Goods Sold (from production cost)
        $cogs = DB::table('production_items')
            ->join('productions', 'production_items.production_id', '=', 'productions.id')
            ->whereBetween('productions.production_date', [$dateFrom, $dateTo])
            ->where('productions.status', 'completed')
            ->sum('production_items.cost');

        // Purchase cost (materials)
        $purchaseCost = Purchase::whereBetween('purchase_date', [$dateFrom, $dateTo])
            ->sum('total');

        $grossProfit = $revenue - $cogs;

        return response()->json([
            'success' => true,
            'data' => [
                'period' => [
                    'from' => $dateFrom,
                    'to' => $dateTo,
                ],
                'revenue' => $revenue,
                'cogs' => $cogs,
                'gross_profit' => $grossProfit,
                'gross_margin' => $revenue > 0 ? round(($grossProfit / $revenue) * 100, 2) : 0,
                'purchase_cost' => $purchaseCost,
            ],
        ]);
    }
}

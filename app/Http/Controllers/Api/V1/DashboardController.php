<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Material;
use App\Models\MaterialStock;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductStock;
use App\Models\Production;
use App\Models\Purchase;
use App\Models\Supplier;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Get dashboard summary statistics
     */
    public function index(Request $request): JsonResponse
    {
        $today = now()->toDateString();
        $startOfMonth = now()->startOfMonth()->toDateString();
        $endOfMonth = now()->endOfMonth()->toDateString();

        // Sales summary
        $salesToday = Order::whereDate('order_date', $today)
            ->where('status', '!=', 'cancelled')
            ->sum('total');

        $salesThisMonth = Order::whereBetween('order_date', [$startOfMonth, $endOfMonth])
            ->where('status', '!=', 'cancelled')
            ->sum('total');

        $ordersToday = Order::whereDate('order_date', $today)->count();
        $ordersThisMonth = Order::whereBetween('order_date', [$startOfMonth, $endOfMonth])->count();

        // Purchase summary
        $purchaseThisMonth = Purchase::whereBetween('purchase_date', [$startOfMonth, $endOfMonth])
            ->sum('total');

        // Production summary
        $productionThisMonth = Production::whereBetween('production_date', [$startOfMonth, $endOfMonth])
            ->where('status', 'completed')
            ->count();

        // Master data counts
        $totalProducts = Product::count();
        $totalMaterials = Material::count();
        $totalCustomers = Customer::count();
        $totalSuppliers = Supplier::count();

        // Low stock alerts
        $lowStockMaterials = Material::whereHas('stock', function ($q) {
            $q->whereRaw('quantity <= materials.min_stock');
        })->count();

        $lowStockProducts = ProductStock::where('quantity', '<=', 10)->count();

        // Pending orders
        $pendingOrders = Order::where('status', 'pending')->count();
        $unpaidOrders = Order::where('payment_status', 'unpaid')
            ->where('status', '!=', 'cancelled')
            ->count();

        return response()->json([
            'success' => true,
            'data' => [
                'sales' => [
                    'today' => $salesToday,
                    'this_month' => $salesThisMonth,
                    'orders_today' => $ordersToday,
                    'orders_this_month' => $ordersThisMonth,
                ],
                'purchase' => [
                    'this_month' => $purchaseThisMonth,
                ],
                'production' => [
                    'completed_this_month' => $productionThisMonth,
                ],
                'master_data' => [
                    'products' => $totalProducts,
                    'materials' => $totalMaterials,
                    'customers' => $totalCustomers,
                    'suppliers' => $totalSuppliers,
                ],
                'alerts' => [
                    'low_stock_materials' => $lowStockMaterials,
                    'low_stock_products' => $lowStockProducts,
                    'pending_orders' => $pendingOrders,
                    'unpaid_orders' => $unpaidOrders,
                ],
            ],
        ]);
    }

    /**
     * Get sales chart data (last 7 days or custom period)
     */
    public function salesChart(Request $request): JsonResponse
    {
        $days = $request->get('days', 7);
        $startDate = now()->subDays($days - 1)->toDateString();
        $endDate = now()->toDateString();

        $salesData = Order::select(
            DB::raw('DATE(order_date) as date'),
            DB::raw('SUM(total) as total'),
            DB::raw('COUNT(*) as count')
        )
            ->whereBetween('order_date', [$startDate, $endDate])
            ->where('status', '!=', 'cancelled')
            ->groupBy(DB::raw('DATE(order_date)'))
            ->orderBy('date')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $salesData,
        ]);
    }

    /**
     * Get top selling products
     */
    public function topProducts(Request $request): JsonResponse
    {
        $limit = $request->get('limit', 10);
        $startDate = $request->get('date_from', now()->startOfMonth()->toDateString());
        $endDate = $request->get('date_to', now()->toDateString());

        $topProducts = DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->select(
                'products.id',
                'products.name',
                DB::raw('SUM(order_items.qty) as total_qty'),
                DB::raw('SUM(order_items.subtotal) as total_sales')
            )
            ->whereBetween('orders.order_date', [$startDate, $endDate])
            ->where('orders.status', '!=', 'cancelled')
            ->groupBy('products.id', 'products.name')
            ->orderByDesc('total_qty')
            ->limit($limit)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $topProducts,
        ]);
    }

    /**
     * Get inventory status
     */
    public function inventoryStatus(): JsonResponse
    {
        $productStock = ProductStock::with('product:id,name,price')
            ->orderBy('quantity', 'asc')
            ->limit(20)
            ->get()
            ->map(fn($s) => [
                'id' => $s->product_id,
                'name' => $s->product?->name,
                'quantity' => $s->quantity,
                'avg_cost' => $s->avg_cost,
                'value' => $s->quantity * $s->avg_cost,
            ]);

        $materialStock = MaterialStock::with('material:id,name,min_stock,unit_id', 'material.unit:id,symbol')
            ->orderBy('quantity', 'asc')
            ->limit(20)
            ->get()
            ->map(fn($s) => [
                'id' => $s->material_id,
                'name' => $s->material?->name,
                'quantity' => $s->quantity,
                'min_stock' => $s->material?->min_stock,
                'unit' => $s->material?->unit?->symbol,
                'avg_cost' => $s->avg_cost,
                'value' => $s->quantity * $s->avg_cost,
                'is_low' => $s->quantity <= ($s->material?->min_stock ?? 0),
            ]);

        return response()->json([
            'success' => true,
            'data' => [
                'products' => $productStock,
                'materials' => $materialStock,
            ],
        ]);
    }
}

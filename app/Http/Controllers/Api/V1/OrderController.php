<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductStock;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Order::with(['customer', 'user', 'paymentMethod'])->withCount('items');

        if ($request->has('search')) {
            $query->where('order_number', 'like', '%' . $request->search . '%');
        }

        if ($request->has('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        if ($request->has('date_from')) {
            $query->whereDate('order_date', '>=', $request->date_from);
        }

        if ($request->has('date_to')) {
            $query->whereDate('order_date', '<=', $request->date_to);
        }

        $orders = $query->orderBy('order_date', 'desc')->paginate($request->get('per_page', 15));

        return OrderResource::collection($orders);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'customer_id' => 'nullable|exists:customers,id',
            'payment_method_id' => 'nullable|exists:payment_methods,id',
            'order_date' => 'required|date',
            'discount' => 'nullable|numeric|min:0',
            'delivery_fee' => 'nullable|numeric|min:0',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.qty' => 'required|integer|min:1',
            'items.*.price' => 'nullable|numeric|min:0',
            'items.*.discount' => 'nullable|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            // Generate order number
            $lastOrder = Order::whereDate('created_at', today())->latest()->first();
            $sequence = $lastOrder ? intval(substr($lastOrder->order_number, -4)) + 1 : 1;
            $orderNumber = 'ORD-' . date('Ymd') . '-' . str_pad($sequence, 4, '0', STR_PAD_LEFT);

            $subtotal = 0;
            $itemsData = [];

            foreach ($validated['items'] as $item) {
                $product = Product::find($item['product_id']);
                $price = $item['price'] ?? $product->price;
                $discount = $item['discount'] ?? 0;
                $itemSubtotal = ($price - $discount) * $item['qty'];
                $subtotal += $itemSubtotal;

                $itemsData[] = [
                    'product_id' => $item['product_id'],
                    'qty' => $item['qty'],
                    'price' => $price,
                    'discount' => $discount,
                    'subtotal' => $itemSubtotal,
                ];

                // Check stock
                $stock = ProductStock::where('product_id', $item['product_id'])->first();
                if (!$stock || $stock->quantity < $item['qty']) {
                    throw new \Exception("Stok produk {$product->name} tidak mencukupi");
                }
            }

            $orderDiscount = $validated['discount'] ?? 0;
            $deliveryFee = $validated['delivery_fee'] ?? 0;
            $total = $subtotal - $orderDiscount + $deliveryFee;

            $order = Order::create([
                'customer_id' => $validated['customer_id'] ?? null,
                'user_id' => $request->user()->id,
                'payment_method_id' => $validated['payment_method_id'] ?? null,
                'order_number' => $orderNumber,
                'order_date' => $validated['order_date'],
                'subtotal' => $subtotal,
                'discount' => $orderDiscount,
                'delivery_fee' => $deliveryFee,
                'total' => $total,
                'payment_status' => 'unpaid',
                'status' => 'pending',
            ]);

            foreach ($itemsData as $itemData) {
                OrderItem::create([
                    'order_id' => $order->id,
                    ...$itemData,
                ]);

                // Reduce stock
                ProductStock::where('product_id', $itemData['product_id'])
                    ->decrement('quantity', $itemData['qty']);
            }

            DB::commit();

            $order->load(['customer', 'user', 'paymentMethod', 'items.product']);

            return response()->json([
                'success' => true,
                'message' => 'Pesanan berhasil dibuat',
                'data' => new OrderResource($order),
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat pesanan: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function show(Order $order): JsonResponse
    {
        $order->load(['customer', 'user', 'paymentMethod', 'items.product']);

        return response()->json([
            'success' => true,
            'data' => new OrderResource($order),
        ]);
    }

    public function update(Request $request, Order $order): JsonResponse
    {
        $validated = $request->validate([
            'payment_method_id' => 'nullable|exists:payment_methods,id',
            'payment_status' => 'required|in:unpaid,partial,paid',
            'status' => 'required|in:pending,processing,completed,cancelled',
        ]);

        DB::beginTransaction();
        try {
            $oldStatus = $order->status;
            $newStatus = $validated['status'];

            // If cancelling order, restore stock
            if ($oldStatus !== 'cancelled' && $newStatus === 'cancelled') {
                foreach ($order->items as $item) {
                    ProductStock::where('product_id', $item->product_id)
                        ->increment('quantity', $item->qty);
                }
            }

            // If un-cancelling order, reduce stock again
            if ($oldStatus === 'cancelled' && $newStatus !== 'cancelled') {
                foreach ($order->items as $item) {
                    $stock = ProductStock::where('product_id', $item->product_id)->first();
                    if (!$stock || $stock->quantity < $item->qty) {
                        throw new \Exception("Stok produk tidak mencukupi");
                    }
                    $stock->decrement('quantity', $item->qty);
                }
            }

            $order->update($validated);

            DB::commit();

            $order->load(['customer', 'user', 'paymentMethod', 'items.product']);

            return response()->json([
                'success' => true,
                'message' => 'Pesanan berhasil diperbarui',
                'data' => new OrderResource($order),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui pesanan: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function destroy(Order $order): JsonResponse
    {
        if ($order->status === 'completed') {
            return response()->json([
                'success' => false,
                'message' => 'Pesanan yang sudah selesai tidak dapat dihapus',
            ], 422);
        }

        DB::beginTransaction();
        try {
            // Restore stock if not cancelled
            if ($order->status !== 'cancelled') {
                foreach ($order->items as $item) {
                    ProductStock::where('product_id', $item->product_id)
                        ->increment('quantity', $item->qty);
                }
            }

            $order->items()->delete();
            $order->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Pesanan berhasil dihapus',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus pesanan: ' . $e->getMessage(),
            ], 500);
        }
    }
}

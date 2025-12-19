<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\PurchaseResource;
use App\Models\MaterialStock;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;

class PurchaseController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Purchase::with(['supplier', 'user', 'paymentMethod'])->withCount('items');

        if ($request->has('search')) {
            $query->where('purchase_number', 'like', '%' . $request->search . '%');
        }

        if ($request->has('supplier_id')) {
            $query->where('supplier_id', $request->supplier_id);
        }

        if ($request->has('payment_type')) {
            $query->where('payment_type', $request->payment_type);
        }

        if ($request->has('date_from')) {
            $query->whereDate('purchase_date', '>=', $request->date_from);
        }

        if ($request->has('date_to')) {
            $query->whereDate('purchase_date', '<=', $request->date_to);
        }

        $purchases = $query->orderBy('purchase_date', 'desc')->paginate($request->get('per_page', 15));

        return PurchaseResource::collection($purchases);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'payment_method_id' => 'nullable|exists:payment_methods,id',
            'purchase_date' => 'required|date',
            'payment_type' => 'required|in:cash,credit',
            'items' => 'required|array|min:1',
            'items.*.material_id' => 'required|exists:materials,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.price' => 'required|numeric|min:0',
            'items.*.expiry_date' => 'nullable|date',
        ]);

        DB::beginTransaction();
        try {
            // Generate purchase number
            $lastPurchase = Purchase::whereDate('created_at', today())->latest()->first();
            $sequence = $lastPurchase ? intval(substr($lastPurchase->purchase_number, -4)) + 1 : 1;
            $purchaseNumber = 'PUR-' . date('Ymd') . '-' . str_pad($sequence, 4, '0', STR_PAD_LEFT);

            $total = 0;
            foreach ($validated['items'] as $item) {
                $total += $item['quantity'] * $item['price'];
            }

            $purchase = Purchase::create([
                'supplier_id' => $validated['supplier_id'],
                'user_id' => $request->user()->id,
                'payment_method_id' => $validated['payment_method_id'] ?? null,
                'purchase_number' => $purchaseNumber,
                'purchase_date' => $validated['purchase_date'],
                'total' => $total,
                'payment_type' => $validated['payment_type'],
            ]);

            foreach ($validated['items'] as $item) {
                $subtotal = $item['quantity'] * $item['price'];

                PurchaseItem::create([
                    'purchase_id' => $purchase->id,
                    'material_id' => $item['material_id'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'subtotal' => $subtotal,
                    'expiry_date' => $item['expiry_date'] ?? null,
                ]);

                // Update material stock
                $stock = MaterialStock::firstOrCreate(
                    ['material_id' => $item['material_id']],
                    ['quantity' => 0, 'avg_cost' => 0]
                );

                $newQty = $stock->quantity + $item['quantity'];
                $newAvgCost = $newQty > 0
                    ? (($stock->quantity * $stock->avg_cost) + $subtotal) / $newQty
                    : $item['price'];

                $stock->update([
                    'quantity' => $newQty,
                    'avg_cost' => $newAvgCost,
                ]);
            }

            DB::commit();

            $purchase->load(['supplier', 'user', 'paymentMethod', 'items.material']);

            return response()->json([
                'success' => true,
                'message' => 'Pembelian berhasil dibuat',
                'data' => new PurchaseResource($purchase),
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat pembelian: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function show(Purchase $purchase): JsonResponse
    {
        $purchase->load(['supplier', 'user', 'paymentMethod', 'items.material.unit']);

        return response()->json([
            'success' => true,
            'data' => new PurchaseResource($purchase),
        ]);
    }

    public function update(Request $request, Purchase $purchase): JsonResponse
    {
        $validated = $request->validate([
            'payment_method_id' => 'nullable|exists:payment_methods,id',
            'payment_type' => 'required|in:cash,credit',
        ]);

        DB::beginTransaction();
        try {
            $purchase->update($validated);

            DB::commit();

            $purchase->load(['supplier', 'user', 'paymentMethod', 'items.material']);

            return response()->json([
                'success' => true,
                'message' => 'Pembelian berhasil diperbarui',
                'data' => new PurchaseResource($purchase),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui pembelian: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function destroy(Purchase $purchase): JsonResponse
    {
        DB::beginTransaction();
        try {
            // Reverse stock changes before deleting
            foreach ($purchase->items as $item) {
                $stock = MaterialStock::where('material_id', $item->material_id)->first();
                if ($stock) {
                    $newQty = max(0, $stock->quantity - $item->quantity);
                    $stock->update(['quantity' => $newQty]);
                }
            }

            $purchase->items()->delete();
            $purchase->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Pembelian berhasil dihapus',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus pembelian: ' . $e->getMessage(),
            ], 500);
        }
    }
}

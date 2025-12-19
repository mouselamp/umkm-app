<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductionResource;
use App\Models\MaterialStock;
use App\Models\Production;
use App\Models\ProductionItem;
use App\Models\ProductStock;
use App\Models\Recipe;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;

class ProductionController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Production::with(['user'])->withCount('items');

        if ($request->has('search')) {
            $query->where('production_number', 'like', '%' . $request->search . '%');
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('date_from')) {
            $query->whereDate('production_date', '>=', $request->date_from);
        }

        if ($request->has('date_to')) {
            $query->whereDate('production_date', '<=', $request->date_to);
        }

        $productions = $query->orderBy('production_date', 'desc')->paginate($request->get('per_page', 15));

        return ProductionResource::collection($productions);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'production_date' => 'required|date',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.recipe_id' => 'required|exists:recipes,id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        DB::beginTransaction();
        try {
            // Generate production number
            $lastProd = Production::whereDate('created_at', today())->latest()->first();
            $sequence = $lastProd ? intval(substr($lastProd->production_number, -4)) + 1 : 1;
            $productionNumber = 'PRD-' . date('Ymd') . '-' . str_pad($sequence, 4, '0', STR_PAD_LEFT);

            $production = Production::create([
                'user_id' => $request->user()->id,
                'production_number' => $productionNumber,
                'production_date' => $validated['production_date'],
                'status' => 'draft',
                'notes' => $validated['notes'] ?? null,
            ]);

            foreach ($validated['items'] as $item) {
                $recipe = Recipe::with('items')->find($item['recipe_id']);
                $outputMultiplier = $item['quantity'] / $recipe->output_qty;

                // Calculate material cost
                $cost = 0;
                foreach ($recipe->items as $recipeItem) {
                    $materialStock = MaterialStock::where('material_id', $recipeItem->material_id)->first();
                    $cost += ($recipeItem->quantity * $outputMultiplier) * ($materialStock->avg_cost ?? 0);
                }

                ProductionItem::create([
                    'production_id' => $production->id,
                    'recipe_id' => $item['recipe_id'],
                    'quantity' => $item['quantity'],
                    'cost' => $cost,
                ]);
            }

            DB::commit();

            $production->load(['user', 'items.recipe.product']);

            return response()->json([
                'success' => true,
                'message' => 'Produksi berhasil dibuat (status: draft)',
                'data' => new ProductionResource($production),
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat produksi: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function show(Production $production): JsonResponse
    {
        $production->load(['user', 'items.recipe.product']);

        return response()->json([
            'success' => true,
            'data' => new ProductionResource($production),
        ]);
    }

    public function update(Request $request, Production $production): JsonResponse
    {
        if ($production->status === 'completed') {
            return response()->json([
                'success' => false,
                'message' => 'Produksi yang sudah selesai tidak dapat diubah',
            ], 422);
        }

        $validated = $request->validate([
            'status' => 'required|in:draft,in_progress,completed,cancelled',
            'notes' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $oldStatus = $production->status;
            $newStatus = $validated['status'];

            // If completing production, consume materials and add product stock
            if ($oldStatus !== 'completed' && $newStatus === 'completed') {
                foreach ($production->items as $item) {
                    $recipe = Recipe::with('items')->find($item->recipe_id);
                    $outputMultiplier = $item->quantity / $recipe->output_qty;

                    // Consume materials
                    foreach ($recipe->items as $recipeItem) {
                        $materialStock = MaterialStock::where('material_id', $recipeItem->material_id)->first();
                        if ($materialStock) {
                            $consumeQty = $recipeItem->quantity * $outputMultiplier;
                            $materialStock->update([
                                'quantity' => $materialStock->quantity - $consumeQty,
                            ]);
                        }
                    }

                    // Add product stock
                    $productStock = ProductStock::firstOrCreate(
                        ['product_id' => $recipe->product_id],
                        ['quantity' => 0, 'avg_cost' => 0]
                    );

                    $newQty = $productStock->quantity + $item->quantity;
                    $newAvgCost = $newQty > 0
                        ? (($productStock->quantity * $productStock->avg_cost) + $item->cost) / $newQty
                        : ($item->cost / $item->quantity);

                    $productStock->update([
                        'quantity' => $newQty,
                        'avg_cost' => $newAvgCost,
                    ]);
                }
            }

            $production->update($validated);

            DB::commit();

            $production->load(['user', 'items.recipe.product']);

            return response()->json([
                'success' => true,
                'message' => 'Produksi berhasil diperbarui',
                'data' => new ProductionResource($production),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui produksi: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function destroy(Production $production): JsonResponse
    {
        if ($production->status === 'completed') {
            return response()->json([
                'success' => false,
                'message' => 'Produksi yang sudah selesai tidak dapat dihapus',
            ], 422);
        }

        DB::beginTransaction();
        try {
            $production->items()->delete();
            $production->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Produksi berhasil dihapus',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus produksi: ' . $e->getMessage(),
            ], 500);
        }
    }
}

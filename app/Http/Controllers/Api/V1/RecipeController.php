<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\RecipeResource;
use App\Models\Recipe;
use App\Models\RecipeItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;

class RecipeController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Recipe::with(['product', 'items.material.unit'])->withCount('items');

        if ($request->has('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        if ($request->has('product_id')) {
            $query->where('product_id', $request->product_id);
        }

        $recipes = $query->orderBy('name')->paginate($request->get('per_page', 15));

        return RecipeResource::collection($recipes);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'name' => 'required|string|max:255',
            'output_qty' => 'required|integer|min:1',
            'items' => 'required|array|min:1',
            'items.*.material_id' => 'required|exists:materials,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
        ]);

        DB::beginTransaction();
        try {
            $recipe = Recipe::create([
                'product_id' => $validated['product_id'],
                'name' => $validated['name'],
                'output_qty' => $validated['output_qty'],
            ]);

            foreach ($validated['items'] as $item) {
                RecipeItem::create([
                    'recipe_id' => $recipe->id,
                    'material_id' => $item['material_id'],
                    'quantity' => $item['quantity'],
                ]);
            }

            DB::commit();

            $recipe->load(['product', 'items.material']);

            return response()->json([
                'success' => true,
                'message' => 'Resep berhasil dibuat',
                'data' => new RecipeResource($recipe),
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat resep: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function show(Recipe $recipe): JsonResponse
    {
        $recipe->load(['product', 'items.material.unit']);

        return response()->json([
            'success' => true,
            'data' => new RecipeResource($recipe),
        ]);
    }

    public function update(Request $request, Recipe $recipe): JsonResponse
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'name' => 'required|string|max:255',
            'output_qty' => 'required|integer|min:1',
            'items' => 'required|array|min:1',
            'items.*.material_id' => 'required|exists:materials,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
        ]);

        DB::beginTransaction();
        try {
            $recipe->update([
                'product_id' => $validated['product_id'],
                'name' => $validated['name'],
                'output_qty' => $validated['output_qty'],
            ]);

            // Delete existing items and recreate
            $recipe->items()->delete();

            foreach ($validated['items'] as $item) {
                RecipeItem::create([
                    'recipe_id' => $recipe->id,
                    'material_id' => $item['material_id'],
                    'quantity' => $item['quantity'],
                ]);
            }

            DB::commit();

            $recipe->load(['product', 'items.material']);

            return response()->json([
                'success' => true,
                'message' => 'Resep berhasil diperbarui',
                'data' => new RecipeResource($recipe),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui resep: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function destroy(Recipe $recipe): JsonResponse
    {
        if ($recipe->productionItems()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Resep tidak dapat dihapus karena sudah digunakan di produksi',
            ], 422);
        }

        DB::beginTransaction();
        try {
            $recipe->items()->delete();
            $recipe->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Resep berhasil dihapus',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus resep: ' . $e->getMessage(),
            ], 500);
        }
    }
}

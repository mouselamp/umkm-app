<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\MaterialResource;
use App\Models\Material;
use App\Models\MaterialStock;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;

class MaterialController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Material::with(['category', 'unit', 'stock']);

        if ($request->has('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->has('low_stock')) {
            $query->whereHas('stock', function ($q) {
                $q->whereRaw('quantity <= materials.min_stock');
            });
        }

        $materials = $query->orderBy('name')->paginate($request->get('per_page', 15));

        return MaterialResource::collection($materials);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:material_categories,id',
            'unit_id' => 'required|exists:units,id',
            'name' => 'required|string|max:255',
            'min_stock' => 'numeric|min:0',
            'expiry_date' => 'nullable|date',
        ]);

        DB::beginTransaction();
        try {
            $material = Material::create($validated);

            // Create initial stock record
            MaterialStock::create([
                'material_id' => $material->id,
                'quantity' => 0,
                'avg_cost' => 0,
            ]);

            DB::commit();

            $material->load(['category', 'unit', 'stock']);

            return response()->json([
                'success' => true,
                'message' => 'Bahan berhasil dibuat',
                'data' => new MaterialResource($material),
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat bahan: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function show(Material $material): JsonResponse
    {
        $material->load(['category', 'unit', 'stock']);

        return response()->json([
            'success' => true,
            'data' => new MaterialResource($material),
        ]);
    }

    public function update(Request $request, Material $material): JsonResponse
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:material_categories,id',
            'unit_id' => 'required|exists:units,id',
            'name' => 'required|string|max:255',
            'min_stock' => 'numeric|min:0',
            'expiry_date' => 'nullable|date',
        ]);

        DB::beginTransaction();
        try {
            $material->update($validated);

            DB::commit();

            $material->load(['category', 'unit', 'stock']);

            return response()->json([
                'success' => true,
                'message' => 'Bahan berhasil diperbarui',
                'data' => new MaterialResource($material),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui bahan: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function destroy(Material $material): JsonResponse
    {
        if ($material->purchaseItems()->exists() || $material->recipeItems()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Bahan tidak dapat dihapus karena sudah memiliki transaksi',
            ], 422);
        }

        DB::beginTransaction();
        try {
            $material->stock()->delete();
            $material->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Bahan berhasil dihapus',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus bahan: ' . $e->getMessage(),
            ], 500);
        }
    }
}

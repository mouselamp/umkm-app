<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\MaterialCategoryResource;
use App\Models\MaterialCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;

class MaterialCategoryController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = MaterialCategory::withCount('materials');

        if ($request->has('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $categories = $query->orderBy('name')->paginate($request->get('per_page', 15));

        return MaterialCategoryResource::collection($categories);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $category = MaterialCategory::create($validated);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Kategori bahan berhasil dibuat',
                'data' => new MaterialCategoryResource($category),
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat kategori bahan: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function show(MaterialCategory $materialCategory): JsonResponse
    {
        $materialCategory->loadCount('materials');

        return response()->json([
            'success' => true,
            'data' => new MaterialCategoryResource($materialCategory),
        ]);
    }

    public function update(Request $request, MaterialCategory $materialCategory): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $materialCategory->update($validated);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Kategori bahan berhasil diperbarui',
                'data' => new MaterialCategoryResource($materialCategory),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui kategori bahan: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function destroy(MaterialCategory $materialCategory): JsonResponse
    {
        if ($materialCategory->materials()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Kategori tidak dapat dihapus karena masih memiliki bahan',
            ], 422);
        }

        DB::beginTransaction();
        try {
            $materialCategory->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Kategori bahan berhasil dihapus',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus kategori bahan: ' . $e->getMessage(),
            ], 500);
        }
    }
}

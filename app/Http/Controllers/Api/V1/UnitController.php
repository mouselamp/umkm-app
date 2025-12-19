<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\UnitResource;
use App\Models\Unit;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;

class UnitController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Unit::query();

        if ($request->has('search')) {
            $query->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('symbol', 'like', '%' . $request->search . '%');
        }

        $units = $query->orderBy('name')->paginate($request->get('per_page', 15));

        return UnitResource::collection($units);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'symbol' => 'required|string|max:20|unique:units,symbol',
        ]);

        DB::beginTransaction();
        try {
            $unit = Unit::create($validated);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Satuan berhasil dibuat',
                'data' => new UnitResource($unit),
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat satuan: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function show(Unit $unit): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => new UnitResource($unit),
        ]);
    }

    public function update(Request $request, Unit $unit): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'symbol' => 'required|string|max:20|unique:units,symbol,' . $unit->id,
        ]);

        DB::beginTransaction();
        try {
            $unit->update($validated);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Satuan berhasil diperbarui',
                'data' => new UnitResource($unit),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui satuan: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function destroy(Unit $unit): JsonResponse
    {
        if ($unit->materials()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Satuan tidak dapat dihapus karena masih digunakan',
            ], 422);
        }

        DB::beginTransaction();
        try {
            $unit->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Satuan berhasil dihapus',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus satuan: ' . $e->getMessage(),
            ], 500);
        }
    }
}

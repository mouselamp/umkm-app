<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\SupplierResource;
use App\Models\Supplier;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;

class SupplierController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Supplier::withCount('purchases');

        if ($request->has('search')) {
            $query->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('phone', 'like', '%' . $request->search . '%');
        }

        $suppliers = $query->orderBy('name')->paginate($request->get('per_page', 15));

        return SupplierResource::collection($suppliers);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $supplier = Supplier::create($validated);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Supplier berhasil dibuat',
                'data' => new SupplierResource($supplier),
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat supplier: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function show(Supplier $supplier): JsonResponse
    {
        $supplier->loadCount('purchases');

        return response()->json([
            'success' => true,
            'data' => new SupplierResource($supplier),
        ]);
    }

    public function update(Request $request, Supplier $supplier): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $supplier->update($validated);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Supplier berhasil diperbarui',
                'data' => new SupplierResource($supplier),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui supplier: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function destroy(Supplier $supplier): JsonResponse
    {
        if ($supplier->purchases()->exists() || $supplier->debts()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Supplier tidak dapat dihapus karena sudah memiliki transaksi',
            ], 422);
        }

        DB::beginTransaction();
        try {
            $supplier->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Supplier berhasil dihapus',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus supplier: ' . $e->getMessage(),
            ], 500);
        }
    }
}

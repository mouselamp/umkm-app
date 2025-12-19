<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\PaymentMethodResource;
use App\Models\PaymentMethod;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;

class PaymentMethodController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = PaymentMethod::query();

        if ($request->has('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        $methods = $query->orderBy('name')->paginate($request->get('per_page', 15));

        return PaymentMethodResource::collection($methods);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:cash,transfer,ewallet',
            'is_active' => 'boolean',
        ]);

        DB::beginTransaction();
        try {
            $method = PaymentMethod::create($validated);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Metode pembayaran berhasil dibuat',
                'data' => new PaymentMethodResource($method),
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat metode pembayaran: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function show(PaymentMethod $paymentMethod): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => new PaymentMethodResource($paymentMethod),
        ]);
    }

    public function update(Request $request, PaymentMethod $paymentMethod): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:cash,transfer,ewallet',
            'is_active' => 'boolean',
        ]);

        DB::beginTransaction();
        try {
            $paymentMethod->update($validated);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Metode pembayaran berhasil diperbarui',
                'data' => new PaymentMethodResource($paymentMethod),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui metode pembayaran: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function destroy(PaymentMethod $paymentMethod): JsonResponse
    {
        DB::beginTransaction();
        try {
            $paymentMethod->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Metode pembayaran berhasil dihapus',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus metode pembayaran: ' . $e->getMessage(),
            ], 500);
        }
    }
}

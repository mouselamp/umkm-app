<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\DebtResource;
use App\Models\Debt;
use App\Models\DebtPayment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;

class DebtController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Debt::with(['supplier', 'payments']);

        if ($request->has('search')) {
            $query->whereHas('supplier', function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('debt_type')) {
            $query->where('debt_type', $request->debt_type);
        }

        $debts = $query->orderBy('debt_date', 'desc')->paginate($request->get('per_page', 15));

        return DebtResource::collection($debts);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'supplier_id' => 'nullable|exists:suppliers,id',
            'debt_type' => 'required|in:payable,receivable',
            'debt_date' => 'required|date',
            'amount' => 'required|numeric|min:0',
            'due_date' => 'nullable|date|after_or_equal:debt_date',
        ]);

        DB::beginTransaction();
        try {
            $debt = Debt::create([
                ...$validated,
                'paid_amount' => 0,
                'remaining_amount' => $validated['amount'],
                'status' => 'unpaid',
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Hutang/Piutang berhasil ditambahkan',
                'data' => new DebtResource($debt),
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal menambahkan hutang/piutang: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function show(Debt $debt): JsonResponse
    {
        $debt->load(['supplier', 'payments']);

        return response()->json([
            'success' => true,
            'data' => new DebtResource($debt),
        ]);
    }

    public function update(Request $request, Debt $debt): JsonResponse
    {
        $validated = $request->validate([
            'due_date' => 'nullable|date',
        ]);

        DB::beginTransaction();
        try {
            $debt->update($validated);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Hutang/Piutang berhasil diperbarui',
                'data' => new DebtResource($debt),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui hutang/piutang: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function destroy(Debt $debt): JsonResponse
    {
        if ($debt->paid_amount > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak dapat menghapus hutang yang sudah ada pembayaran',
            ], 422);
        }

        DB::beginTransaction();
        try {
            $debt->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Hutang/Piutang berhasil dihapus',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus hutang/piutang: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function addPayment(Request $request, Debt $debt): JsonResponse
    {
        $validated = $request->validate([
            'payment_date' => 'required|date',
            'amount' => 'required|numeric|min:0.01|max:' . $debt->remaining_amount,
            'payment_method_id' => 'nullable|exists:payment_methods,id',
            'notes' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            DebtPayment::create([
                'debt_id' => $debt->id,
                'user_id' => $request->user()->id,
                'payment_method_id' => $validated['payment_method_id'] ?? null,
                'payment_date' => $validated['payment_date'],
                'amount' => $validated['amount'],
                'notes' => $validated['notes'] ?? null,
            ]);

            $newPaid = $debt->paid_amount + $validated['amount'];
            $newRemaining = $debt->amount - $newPaid;
            $status = $newRemaining <= 0 ? 'paid' : 'partial';

            $debt->update([
                'paid_amount' => $newPaid,
                'remaining_amount' => max(0, $newRemaining),
                'status' => $status,
            ]);

            DB::commit();

            $debt->load(['supplier', 'payments']);

            return response()->json([
                'success' => true,
                'message' => 'Pembayaran berhasil ditambahkan',
                'data' => new DebtResource($debt),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal menambahkan pembayaran: ' . $e->getMessage(),
            ], 500);
        }
    }
}

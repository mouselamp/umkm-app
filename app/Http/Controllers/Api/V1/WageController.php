<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\WageResource;
use App\Models\Wage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;

class WageController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Wage::with(['employee', 'user', 'paymentMethod']);

        if ($request->has('search')) {
            $query->whereHas('employee', function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->has('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }

        if ($request->has('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        if ($request->has('date_from')) {
            $query->whereDate('wage_date', '>=', $request->date_from);
        }

        if ($request->has('date_to')) {
            $query->whereDate('wage_date', '<=', $request->date_to);
        }

        $wages = $query->orderBy('wage_date', 'desc')->paginate($request->get('per_page', 15));

        return WageResource::collection($wages);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'wage_date' => 'required|date',
            'amount' => 'required|numeric|min:0',
            'wage_type' => 'required|in:daily,weekly,monthly,bonus',
            'payment_method_id' => 'nullable|exists:payment_methods,id',
            'payment_status' => 'required|in:pending,paid',
            'notes' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $wage = Wage::create([
                ...$validated,
                'user_id' => $request->user()->id,
            ]);

            DB::commit();

            $wage->load(['employee', 'paymentMethod']);

            return response()->json([
                'success' => true,
                'message' => 'Gaji berhasil ditambahkan',
                'data' => new WageResource($wage),
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal menambahkan gaji: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function show(Wage $wage): JsonResponse
    {
        $wage->load(['employee', 'user', 'paymentMethod']);

        return response()->json([
            'success' => true,
            'data' => new WageResource($wage),
        ]);
    }

    public function update(Request $request, Wage $wage): JsonResponse
    {
        $validated = $request->validate([
            'wage_date' => 'required|date',
            'amount' => 'required|numeric|min:0',
            'wage_type' => 'required|in:daily,weekly,monthly,bonus',
            'payment_method_id' => 'nullable|exists:payment_methods,id',
            'payment_status' => 'required|in:pending,paid',
            'notes' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $wage->update($validated);

            DB::commit();

            $wage->load(['employee', 'paymentMethod']);

            return response()->json([
                'success' => true,
                'message' => 'Gaji berhasil diperbarui',
                'data' => new WageResource($wage),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui gaji: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function destroy(Wage $wage): JsonResponse
    {
        DB::beginTransaction();
        try {
            $wage->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Gaji berhasil dihapus',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus gaji: ' . $e->getMessage(),
            ], 500);
        }
    }
}

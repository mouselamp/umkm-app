<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\EmployeeResource;
use App\Models\Employee;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;

class EmployeeController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Employee::query();

        if ($request->has('search')) {
            $query->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('phone', 'like', '%' . $request->search . '%')
                  ->orWhere('position', 'like', '%' . $request->search . '%');
        }

        if ($request->has('position')) {
            $query->where('position', $request->position);
        }

        $employees = $query->orderBy('name')->paginate($request->get('per_page', 15));

        return EmployeeResource::collection($employees);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'position' => 'nullable|string|max:255',
        ]);

        DB::beginTransaction();
        try {
            $employee = Employee::create($validated);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Karyawan berhasil dibuat',
                'data' => new EmployeeResource($employee),
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat karyawan: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function show(Employee $employee): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => new EmployeeResource($employee),
        ]);
    }

    public function update(Request $request, Employee $employee): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'position' => 'nullable|string|max:255',
        ]);

        DB::beginTransaction();
        try {
            $employee->update($validated);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Karyawan berhasil diperbarui',
                'data' => new EmployeeResource($employee),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui karyawan: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function destroy(Employee $employee): JsonResponse
    {
        if ($employee->wages()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Karyawan tidak dapat dihapus karena sudah memiliki data gaji',
            ], 422);
        }

        DB::beginTransaction();
        try {
            $employee->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Karyawan berhasil dihapus',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus karyawan: ' . $e->getMessage(),
            ], 500);
        }
    }
}

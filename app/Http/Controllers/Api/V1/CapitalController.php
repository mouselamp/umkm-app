<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\CapitalResource;
use App\Models\Capital;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;

class CapitalController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Capital::with('user');

        if ($request->has('search')) {
            $query->where('source', 'like', '%' . $request->search . '%')
                  ->orWhere('description', 'like', '%' . $request->search . '%');
        }

        if ($request->has('date_from')) {
            $query->whereDate('capital_date', '>=', $request->date_from);
        }

        if ($request->has('date_to')) {
            $query->whereDate('capital_date', '<=', $request->date_to);
        }

        $capitals = $query->orderBy('capital_date', 'desc')->paginate($request->get('per_page', 15));

        return CapitalResource::collection($capitals);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'capital_date' => 'required|date',
            'amount' => 'required|numeric|min:0',
            'source' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $capital = Capital::create([
                ...$validated,
                'user_id' => $request->user()->id,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Modal berhasil ditambahkan',
                'data' => new CapitalResource($capital),
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal menambahkan modal: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function show(Capital $capital): JsonResponse
    {
        $capital->load('user');

        return response()->json([
            'success' => true,
            'data' => new CapitalResource($capital),
        ]);
    }

    public function update(Request $request, Capital $capital): JsonResponse
    {
        $validated = $request->validate([
            'capital_date' => 'required|date',
            'amount' => 'required|numeric|min:0',
            'source' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $capital->update($validated);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Modal berhasil diperbarui',
                'data' => new CapitalResource($capital),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui modal: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function destroy(Capital $capital): JsonResponse
    {
        DB::beginTransaction();
        try {
            $capital->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Modal berhasil dihapus',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus modal: ' . $e->getMessage(),
            ], 500);
        }
    }
}

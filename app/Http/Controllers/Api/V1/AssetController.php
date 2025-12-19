<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\AssetResource;
use App\Models\Asset;
use App\Models\Depreciation;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;

class AssetController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Asset::with(['user', 'depreciations']);

        if ($request->has('search')) {
            $query->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('asset_number', 'like', '%' . $request->search . '%');
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $assets = $query->orderBy('purchase_date', 'desc')->paginate($request->get('per_page', 15));

        return AssetResource::collection($assets);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'asset_number' => 'nullable|string|max:50|unique:assets,asset_number',
            'purchase_date' => 'required|date',
            'purchase_price' => 'required|numeric|min:0',
            'useful_life_month' => 'required|integer|min:1',
            'residual_value' => 'nullable|numeric|min:0',
            'payment_type' => 'required|in:cash,credit',
        ]);

        DB::beginTransaction();
        try {
            $assetNumber = $validated['asset_number'] ?? $this->generateAssetNumber();
            $residualValue = $validated['residual_value'] ?? 0;

            $asset = Asset::create([
                ...$validated,
                'asset_number' => $assetNumber,
                'residual_value' => $residualValue,
                'book_value' => $validated['purchase_price'],
                'user_id' => $request->user()->id,
                'status' => 'active',
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Aset berhasil ditambahkan',
                'data' => new AssetResource($asset),
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal menambahkan aset: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function show(Asset $asset): JsonResponse
    {
        $asset->load(['user', 'depreciations']);

        return response()->json([
            'success' => true,
            'data' => new AssetResource($asset),
        ]);
    }

    public function update(Request $request, Asset $asset): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'status' => 'sometimes|in:active,disposed,sold',
        ]);

        DB::beginTransaction();
        try {
            $asset->update($validated);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Aset berhasil diperbarui',
                'data' => new AssetResource($asset),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui aset: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function destroy(Asset $asset): JsonResponse
    {
        if ($asset->depreciations()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak dapat menghapus aset yang sudah ada depresiasi',
            ], 422);
        }

        DB::beginTransaction();
        try {
            $asset->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Aset berhasil dihapus',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus aset: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Calculate and record monthly depreciation
     */
    public function depreciate(Request $request, Asset $asset): JsonResponse
    {
        $validated = $request->validate([
            'period' => 'required|date_format:Y-m',
        ]);

        $period = Carbon::parse($validated['period'] . '-01');

        // Check if already depreciated this period
        $exists = Depreciation::where('asset_id', $asset->id)
            ->whereYear('period', $period->year)
            ->whereMonth('period', $period->month)
            ->exists();

        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'Depresiasi untuk periode ini sudah dicatat',
            ], 422);
        }

        // Calculate depreciation using straight-line method
        $depreciableValue = $asset->purchase_price - $asset->residual_value;
        $monthlyDepreciation = $depreciableValue / $asset->useful_life_month;

        // Get accumulated depreciation
        $accumulated = $asset->depreciations()->sum('amount');
        $newAccumulated = $accumulated + $monthlyDepreciation;

        // Calculate new book value
        $bookValueAfter = $asset->purchase_price - $newAccumulated;

        DB::beginTransaction();
        try {
            Depreciation::create([
                'asset_id' => $asset->id,
                'period' => $period,
                'amount' => $monthlyDepreciation,
                'accumulated' => $newAccumulated,
                'book_value_after' => max(0, $bookValueAfter),
            ]);

            $asset->update([
                'book_value' => max(0, $bookValueAfter),
            ]);

            DB::commit();

            $asset->load('depreciations');

            return response()->json([
                'success' => true,
                'message' => 'Depresiasi berhasil dicatat',
                'data' => new AssetResource($asset),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal mencatat depresiasi: ' . $e->getMessage(),
            ], 500);
        }
    }

    private function generateAssetNumber(): string
    {
        $date = now()->format('Ymd');
        $count = Asset::whereDate('created_at', today())->count() + 1;
        return "AST-{$date}-" . str_pad($count, 4, '0', STR_PAD_LEFT);
    }
}

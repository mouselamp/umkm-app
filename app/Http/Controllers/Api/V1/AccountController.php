<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\AccountResource;
use App\Models\Account;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;

class AccountController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Account::query();

        if ($request->has('search')) {
            $query->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('code', 'like', '%' . $request->search . '%');
        }

        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        $accounts = $query->orderBy('code')->paginate($request->get('per_page', 15));

        return AccountResource::collection($accounts);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'code' => 'required|string|max:20|unique:accounts,code',
            'name' => 'required|string|max:255',
            'type' => 'required|in:kas,bank,piutang,utang,modal,pendapatan,beban',
            'balance' => 'numeric|min:0',
            'is_active' => 'boolean',
        ]);

        DB::beginTransaction();
        try {
            $account = Account::create($validated);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Akun berhasil dibuat',
                'data' => new AccountResource($account),
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat akun: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function show(Account $account): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => new AccountResource($account),
        ]);
    }

    public function update(Request $request, Account $account): JsonResponse
    {
        $validated = $request->validate([
            'code' => 'required|string|max:20|unique:accounts,code,' . $account->id,
            'name' => 'required|string|max:255',
            'type' => 'required|in:kas,bank,piutang,utang,modal,pendapatan,beban',
            'is_active' => 'boolean',
        ]);

        DB::beginTransaction();
        try {
            $account->update($validated);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Akun berhasil diperbarui',
                'data' => new AccountResource($account),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui akun: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function destroy(Account $account): JsonResponse
    {
        if ($account->transactionLines()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Akun tidak dapat dihapus karena sudah memiliki transaksi',
            ], 422);
        }

        DB::beginTransaction();
        try {
            $account->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Akun berhasil dihapus',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus akun: ' . $e->getMessage(),
            ], 500);
        }
    }
}

<?php

namespace Database\Seeders;

use App\Models\Account;
use Illuminate\Database\Seeder;

class AccountSeeder extends Seeder
{
    public function run(): void
    {
        $accounts = [
            // Kas & Bank
            ['code' => '1-1001', 'name' => 'Kas Toko', 'type' => 'kas', 'balance' => 0, 'is_active' => true],
            ['code' => '1-1002', 'name' => 'Bank BCA', 'type' => 'bank', 'balance' => 0, 'is_active' => true],
            ['code' => '1-1003', 'name' => 'Bank BRI', 'type' => 'bank', 'balance' => 0, 'is_active' => true],

            // Piutang
            ['code' => '1-2001', 'name' => 'Piutang Usaha', 'type' => 'piutang', 'balance' => 0, 'is_active' => true],

            // Utang
            ['code' => '2-1001', 'name' => 'Utang Usaha', 'type' => 'utang', 'balance' => 0, 'is_active' => true],
            ['code' => '2-1002', 'name' => 'Utang Gaji', 'type' => 'utang', 'balance' => 0, 'is_active' => true],

            // Modal
            ['code' => '3-1001', 'name' => 'Modal Pemilik', 'type' => 'modal', 'balance' => 0, 'is_active' => true],
            ['code' => '3-1002', 'name' => 'Laba Ditahan', 'type' => 'modal', 'balance' => 0, 'is_active' => true],

            // Pendapatan
            ['code' => '4-1001', 'name' => 'Pendapatan Penjualan', 'type' => 'pendapatan', 'balance' => 0, 'is_active' => true],
            ['code' => '4-1002', 'name' => 'Pendapatan Lain-lain', 'type' => 'pendapatan', 'balance' => 0, 'is_active' => true],

            // Beban
            ['code' => '5-1001', 'name' => 'Beban Bahan Baku', 'type' => 'beban', 'balance' => 0, 'is_active' => true],
            ['code' => '5-1002', 'name' => 'Beban Gaji', 'type' => 'beban', 'balance' => 0, 'is_active' => true],
            ['code' => '5-1003', 'name' => 'Beban Penyusutan', 'type' => 'beban', 'balance' => 0, 'is_active' => true],
            ['code' => '5-1004', 'name' => 'Beban Operasional', 'type' => 'beban', 'balance' => 0, 'is_active' => true],
            ['code' => '5-1005', 'name' => 'Beban Lain-lain', 'type' => 'beban', 'balance' => 0, 'is_active' => true],
        ];

        foreach ($accounts as $account) {
            Account::firstOrCreate(['code' => $account['code']], $account);
        }
    }
}

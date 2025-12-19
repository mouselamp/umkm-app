<?php

namespace Database\Seeders;

use App\Models\PaymentMethod;
use Illuminate\Database\Seeder;

class PaymentMethodSeeder extends Seeder
{
    public function run(): void
    {
        $methods = [
            ['name' => 'Cash', 'type' => 'cash', 'is_active' => true],
            ['name' => 'Transfer Bank', 'type' => 'transfer', 'is_active' => true],
            ['name' => 'GoPay', 'type' => 'ewallet', 'is_active' => true],
            ['name' => 'OVO', 'type' => 'ewallet', 'is_active' => true],
            ['name' => 'DANA', 'type' => 'ewallet', 'is_active' => true],
            ['name' => 'ShopeePay', 'type' => 'ewallet', 'is_active' => true],
        ];

        foreach ($methods as $method) {
            PaymentMethod::firstOrCreate(['name' => $method['name']], $method);
        }
    }
}

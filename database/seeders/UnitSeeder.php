<?php

namespace Database\Seeders;

use App\Models\Unit;
use Illuminate\Database\Seeder;

class UnitSeeder extends Seeder
{
    public function run(): void
    {
        $units = [
            ['name' => 'Kilogram', 'symbol' => 'kg'],
            ['name' => 'Gram', 'symbol' => 'g'],
            ['name' => 'Liter', 'symbol' => 'L'],
            ['name' => 'Mililiter', 'symbol' => 'mL'],
            ['name' => 'Pieces', 'symbol' => 'pcs'],
            ['name' => 'Pack', 'symbol' => 'pack'],
            ['name' => 'Box', 'symbol' => 'box'],
            ['name' => 'Lusin', 'symbol' => 'doz'],
            ['name' => 'Sendok Makan', 'symbol' => 'sdm'],
            ['name' => 'Sendok Teh', 'symbol' => 'sdt'],
        ];

        foreach ($units as $unit) {
            Unit::firstOrCreate(['symbol' => $unit['symbol']], $unit);
        }
    }
}

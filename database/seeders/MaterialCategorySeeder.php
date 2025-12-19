<?php

namespace Database\Seeders;

use App\Models\MaterialCategory;
use Illuminate\Database\Seeder;

class MaterialCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Daging', 'description' => 'Bahan daging seperti ayam, sapi, ikan'],
            ['name' => 'Sayuran', 'description' => 'Sayuran segar'],
            ['name' => 'Bumbu', 'description' => 'Bumbu dan rempah-rempah'],
            ['name' => 'Tepung', 'description' => 'Berbagai jenis tepung'],
            ['name' => 'Minyak', 'description' => 'Minyak goreng dan minyak lainnya'],
            ['name' => 'Kemasan', 'description' => 'Material kemasan dan packaging'],
            ['name' => 'Lain-lain', 'description' => 'Bahan lainnya'],
        ];

        foreach ($categories as $category) {
            MaterialCategory::firstOrCreate(['name' => $category['name']], $category);
        }
    }
}

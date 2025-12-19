<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create admin user
        User::firstOrCreate(
            ['email' => 'admin@umkm.local'],
            [
                'name' => 'Admin UMKM',
                'password' => Hash::make('password'),
            ]
        );

        // Run master data seeders
        $this->call([
            PaymentMethodSeeder::class,
            UnitSeeder::class,
            AccountSeeder::class,
            MaterialCategorySeeder::class,
        ]);
    }
}

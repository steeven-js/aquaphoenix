<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Models\Shop\OrderItem;
use Illuminate\Database\Seeder;
use App\Http\Controllers\MonthController;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            CustomerSeeder::class,
            OrderProductsSeeder::class,
            OrderSeeder::class,
            OrderItemSeeder::class,
        ]);

        // Mise à jour du mois
        $updateMonth = new MonthController;
        $updateMonth->month();
    }
}

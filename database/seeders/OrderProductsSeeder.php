<?php

namespace Database\Seeders;

use App\Models\Shop\Product;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class OrderProductsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        $order_products = new Product;
        $order_products->name = 'Laver sécher ';
        $order_products->slug = 'laver-secher';
        $order_products->description = NULL;
        $order_products->is_visible = 1;
        $order_products->timestamps = true;
        $order_products->save();
    }
}

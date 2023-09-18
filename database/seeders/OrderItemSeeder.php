<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OrderItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $orderItems = [
            [
                'id' => 1,
                'sort' => 0,
                'order_id' => 1,
                'product_id' => 1,
                'qty' => 29,
                'created_at' => '2023-08-03 05:37:11',
                'updated_at' => '2023-08-03 05:37:11',
            ],
            [
                'id' => 2,
                'sort' => 0,
                'order_id' => 2,
                'product_id' => 1,
                'qty' => 33,
                'created_at' => '2023-08-06 18:49:43',
                'updated_at' => '2023-08-06 18:49:43',
            ],
            [
                'id' => 3,
                'sort' => 0,
                'order_id' => 3,
                'product_id' => 1,
                'qty' => 45,
                'created_at' => '2023-08-08 19:54:38',
                'updated_at' => '2023-08-08 19:54:38',
            ],
            [
                'id' => 4,
                'sort' => 0,
                'order_id' => 4,
                'product_id' => 1,
                'qty' => 35,
                'created_at' => '2023-08-10 16:47:02',
                'updated_at' => '2023-08-10 16:47:02',
            ],
            [
                'id' => 5,
                'sort' => 0,
                'order_id' => 5,
                'product_id' => 1,
                'qty' => 35,
                'created_at' => '2023-08-11 20:01:02',
                'updated_at' => '2023-08-11 20:01:02',
            ],
            [
                'id' => 6,
                'sort' => 0,
                'order_id' => 6,
                'product_id' => 1,
                'qty' => 45,
                'created_at' => '2023-08-14 18:13:21',
                'updated_at' => '2023-08-14 18:13:21',
            ],
            [
                'id' => 7,
                'sort' => 0,
                'order_id' => 7,
                'product_id' => 1,
                'qty' => 40,
                'created_at' => '2023-08-17 14:59:32',
                'updated_at' => '2023-08-17 14:59:32',
            ],
            [
                'id' => 8,
                'sort' => 0,
                'order_id' => 8,
                'product_id' => 1,
                'qty' => 40,
                'created_at' => '2023-08-18 19:54:27',
                'updated_at' => '2023-08-18 19:54:27',
            ],
            [
                'id' => 9,
                'sort' => 0,
                'order_id' => 9,
                'product_id' => 1,
                'qty' => 56,
                'created_at' => '2023-08-21 16:26:25',
                'updated_at' => '2023-08-21 16:26:25',
            ],
            [
                'id' => 10,
                'sort' => 0,
                'order_id' => 10,
                'product_id' => 1,
                'qty' => 36,
                'created_at' => '2023-08-23 19:17:32',
                'updated_at' => '2023-08-23 19:17:32',
            ],
            [
                'id' => 11,
                'sort' => 0,
                'order_id' => 11,
                'product_id' => 1,
                'qty' => 36,
                'created_at' => '2023-08-25 18:43:54',
                'updated_at' => '2023-08-25 18:43:54',
            ],
            [
                'id' => 12,
                'sort' => 0,
                'order_id' => 12,
                'product_id' => 1,
                'qty' => 62,
                'created_at' => '2023-08-29 18:52:55',
                'updated_at' => '2023-08-29 18:52:55',
            ],
            [
                'id' => 13,
                'sort' => 0,
                'order_id' => 13,
                'product_id' => 1,
                'qty' => 43,
                'created_at' => '2023-08-31 15:31:16',
                'updated_at' => '2023-08-31 15:31:16',
            ],
            [
                'id' => 14,
                'sort' => 0,
                'order_id' => 14,
                'product_id' => 1,
                'qty' => 40,
                'created_at' => '2023-09-02 13:41:11',
                'updated_at' => '2023-09-02 13:41:11',
            ],
            [
                'id' => 15,
                'sort' => 0,
                'order_id' => 15,
                'product_id' => 1,
                'qty' => 46,
                'created_at' => '2023-09-05 18:16:20',
                'updated_at' => '2023-09-05 18:16:20',
            ],
            [
                'id' => 16,
                'sort' => 0,
                'order_id' => 16,
                'product_id' => 1,
                'qty' => 38,
                'created_at' => '2023-09-12 13:09:39',
                'updated_at' => '2023-09-12 13:09:39',
            ],
            [
                'id' => 17,
                'sort' => 0,
                'order_id' => 17,
                'product_id' => 1,
                'qty' => 31,
                'created_at' => '2023-09-12 13:10:17',
                'updated_at' => '2023-09-12 13:10:17',
            ],
            [
                'id' => 18,
                'sort' => 0,
                'order_id' => 18,
                'product_id' => 1,
                'qty' => 39,
                'created_at' => '2023-09-12 13:18:05',
                'updated_at' => '2023-09-12 13:18:05',
            ],
            [
                'id' => 19,
                'sort' => 0,
                'order_id' => 19,
                'product_id' => 1,
                'qty' => 43,
                'created_at' => '2023-09-14 20:37:01',
                'updated_at' => '2023-09-14 20:37:01',
            ],
            [
                'id' => 20,
                'sort' => 0,
                'order_id' => 20,
                'product_id' => 1,
                'qty' => 22,
                'created_at' => '2023-09-15 15:01:41',
                'updated_at' => '2023-09-15 15:01:41',
            ],
        ];

        DB::table('order_items')->insert($orderItems);
    }
}

<?php

namespace Database\Seeders;


use App\Models\Shop\Customer;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class CustomerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

    $customer = new Customer;
    $customer->name = 'EHPAD Résidence Sainte-Hildegarde';
    $customer->email = 'ehpadstehildegarde@gmail.com';
    $customer->address = 'Croix Odilon';
    $customer->photo = NULL;
    $customer->phone1 = '0596765122';
    $customer->phone2 = NULL;
    $customer->code = '97213';
    $customer->commune = 'Gros Morne';
    $customer->timestamps = true;
    $customer->save();
    }

}

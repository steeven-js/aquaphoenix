<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $names = ['Admin Test', 'User Test', 'Liana Jacques', 'Steeven Jacques'];
        $emails = ['admin@test.com', 'user@test.com', 'liana.jacques@aquaphoenix.fr', 'jacques.steeven@gmail.com'];
        $commonPassword = '85245600';

        foreach ($names as $index => $name) {
            User::create([
                'name' => $name,
                'email' => $emails[$index],
                'password' => Hash::make($commonPassword),
                'email_verified_at' => now(),
            ]);
        }
    }
}

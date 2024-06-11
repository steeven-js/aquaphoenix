<?php

namespace Database\Seeders;


use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $names = ['Admin Test', 'User Test', 'Liana Jacques', 'Steeven Jacques'];
        $emails = ['admin@test.com', 'user@test.com', 'liana.jacques@aquaphoenix.fr', 'jacques.steeven@gmail.com'];
        $commonPassword = '85245600';

        $count = count($names);

        for ($i = 0; $i < $count; $i++) {
            // Vérifiez si l'email existe déjà dans la base de données
            $existingUser = User::where('email', $emails[$i])->first();

            // Si l'utilisateur n'existe pas, créez une nouvelle entrée
            if (!$existingUser) {
                $user = new User();
                $user->name = $names[$i];
                $user->email = $emails[$i];
                $user->password = Hash::make($commonPassword);
                $user->save();
            }
        }
    }
}

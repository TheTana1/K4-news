<?php

namespace Database\Seeders;

use App\Models\Phone;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::withTrashed()->where(['telegram_username' => 'admin', 'role_id' => '1', 'gender' => '1']);
        if($user) {
            $user->update([
                'name' => 'admin',
                'email' => 'admin@mail.ru',
                'password' => Hash::make('password'),
                'telegram_username' => 'admin',
                'role_id' => 1,
                'gender' => null,
            ]);
        }
        else{
            User::create([
                'name' => 'admin',
                'email' => 'admin@mail.ru',
                'password' => Hash::make('password'),
                'telegram_username' => 'admin',
                'role_id' => 1,
                'gender' => null,
            ]);
        }


    }
}

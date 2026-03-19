<?php

namespace Database\Seeders;

use App\Models\Phone;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::factory(23)
            ->has(Phone::factory()->count(rand(1,3)))
            ->create();

        User::factory()
            ->has(Phone::factory()->count(1))
            ->create([
                'name' => 'Admin',
                'email' => 'admin@mail.ru',
            ]);





    }
}

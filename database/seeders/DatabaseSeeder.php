<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();
        $this->call([
            RoleSeeder::class,
            UserSeeder::class
        ]);
        User::factory()->create([
            'name' => 'admin@mail.ru',
            'email' => 'admin@mail.ru',
            'password' => 'password',
            'role_id' =>1,
            'gender' =>1,

        ]);


    }
}

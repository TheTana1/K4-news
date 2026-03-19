<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('roles')->insert([
            [
                'slug' => 'admin',
                'label' => 'Администратор',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'slug' => 'moderator',
                'label' => 'Модератор',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'slug' => 'user',
                'label' => 'Пользователь',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}

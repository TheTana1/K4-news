<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('roles')->insert([
            //1
            [
                'slug' => 'admin',
                'label' => 'Администратор',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            //2
            [
                'slug' => 'moderator',
                'label' => 'Менеджер',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            //3
            [
                'slug' => 'Kitchen_Staff',
                'label' => 'Сотрудник Кухни',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            //4
            [
                'slug' => 'Service_Staff',
                'label' => 'Сотрудник Зала',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            //5
            [
                'slug' => 'Bar_Staff',
                'label' => 'Сотрудник бара',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            //6
            [
                'slug' => 'Cleaning_Staff',
                'label' => 'Сотрудник клининга',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            //7
            [
                'slug' => 'Tech_Service',
                'label'=> 'Техслужба',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            //8
            [
                'slug' => 'Everyone',
                'label' => 'Всем',
                'created_at' => now(),
                'updated_at' => now(),
            ],

        ]);
    }
}

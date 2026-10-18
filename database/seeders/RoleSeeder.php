<?php
// database/seeders/RoleSeeder.php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            ['slug' => 'admin',          'label' => 'Администратор'],
            ['slug' => 'moderator',      'label' => 'Менеджер'],
            ['slug' => 'Kitchen_Staff',  'label' => 'Сотрудник кухни'],
            ['slug' => 'Service_Staff',  'label' => 'Сотрудник зала'],
            ['slug' => 'Bar_Staff',      'label' => 'Сотрудник бара'],
            ['slug' => 'Cleaning_Staff', 'label' => 'Сотрудник клининга'],
            ['slug' => 'Tech_Service',   'label' => 'Техслужба'],
        ];

        foreach ($roles as $role) {
            Role::updateOrCreate(['slug' => $role['slug']], $role);
        }
    }
}

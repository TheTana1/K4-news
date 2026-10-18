<?php
// database/seeders/UserSeeder.php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $adminRole = Role::where('slug', 'admin')->first();
        $staffRoles = Role::whereIn('slug', [
            'Kitchen_Staff', 'Service_Staff', 'Bar_Staff', 'Cleaning_Staff', 'Tech_Service',
        ])->pluck('id')->all();

        // Админ
        User::updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name'              => 'Администратор',
                'password'          => Hash::make('password'),
                'telegram_username' => 'admin',
                'telegram_id'       => '100000000',
                'role_id'           => $adminRole?->id,
                'is_active_in_group'=> true,
                'gender'            => false,
            ]
        );

        // 100 сотрудников
        User::factory()
            ->count(100)
            ->state(function () use ($staffRoles) {
                return [
                    'role_id'           => fake()->randomElement($staffRoles),
                    'is_active_in_group'=> fake()->boolean(80),
                    'telegram_username' => fake()->slug(),
                    'gender'            => fake()->boolean(),
                    'birthday'          => fake()->dateTimeBetween('-50 years', '-18 years')->format('Y-m-d'),
                ];
            })
            ->create();
    }
}

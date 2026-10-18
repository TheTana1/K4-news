<?php
// database/seeders/ContentSeeder.php

namespace Database\Seeders;

use App\Models\Advertisement;
use App\Models\News;
use App\Models\Review;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class ContentSeeder extends Seeder
{
    public function run(): void
    {
        $roles = Role::pluck('id')->all();
        $users = User::whereNotNull('role_id')->get();

        if ($users->isEmpty()) {
            $this->command->warn('Нет пользователей — пропуск ContentSeeder');
            return;
        }

        // --- 60 объявлений ---
        Advertisement::factory()
            ->count(60)
            ->state(fn() => [
                'user_id'      => $users->random()->id,
                'role_id'      => fake()->randomElement([null, ...$roles]),
                'status'       => fake()->randomElement(['active', 'active', 'inactive']),
                'published_at' => now()->subDays(rand(0, 30)),
            ])
            ->create();

        // --- 60 новостей ---
        News::factory()
            ->count(60)
            ->state(fn() => [
                'user_id'      => $users->random()->id,
                'role_id'      => fake()->randomElement([null, ...$roles]),
                'status'       => fake()->randomElement(['active', 'active', 'inactive']),
                'published_at' => now()->subDays(rand(0, 30)),
            ])
            ->create();

        // --- 60 отзывов ---
        Review::factory()
            ->count(60)
            ->state(fn() => [
                'user_id'      => $users->random()->id,
                'rating'       => rand(1, 5),
                'status'       => 'active',
                'published_at' => now()->subDays(rand(0, 30)),
            ])
            ->create();
    }
}

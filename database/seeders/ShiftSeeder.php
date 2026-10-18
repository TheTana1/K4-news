<?php
// database/seeders/ShiftSeeder.php

namespace Database\Seeders;

use App\Models\Shift;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class ShiftSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::whereNotNull('role_id')->pluck('id');
        $start = Carbon::now()->startOfMonth();
        $end   = Carbon::now()->endOfMonth();

        $rows = [];

        foreach ($users as $userId) {
            $current = $start->copy();

            while ($current <= $end) {
                $type = match (true) {
                    $current->isWeekend() => 'off',
                    default => fake()->randomElement(['full', 'full', 'full', 'half']),
                };

                $status = $type === 'off'
                    ? 'draft'
                    : fake()->randomElement(['draft', 'pending', 'pending', 'approved']);

                $rows[] = [
                    'user_id'    => $userId,
                    'date'       => $current->format('Y-m-d'),
                    'type'       => $type,
                    'status'     => $status,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                $current->addDay();
            }
        }

        foreach (array_chunk($rows, 500) as $chunk) {
            Shift::insert($chunk);
        }
    }
}

<?php
// database/seeders/EngagementSeeder.php

namespace Database\Seeders;

use App\Models\Advertisement;
use App\Models\Comment;
use App\Models\News;
use App\Models\Review;
use App\Models\User;
use App\Models\View;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class EngagementSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::pluck('id')->all();
        $userCount = count($users);

        if ($userCount < 2) {
            $this->command->warn('Мало пользователей — пропуск EngagementSeeder');
            return;
        }

        foreach ([
                     [Advertisement::class, Advertisement::all()],
                     [News::class,          News::all()],
                     [Review::class,        Review::all()],
                 ] as [$type, $models]) {
            foreach ($models as $model) {
                // 10–100 просмотров на запись
                $viewersCount = rand(10, min(100, $userCount));
                $viewerIds = collect($users)->shuffle()->take($viewersCount);

                $views = [];
                foreach ($viewerIds as $uid) {
                    $views[] = [
                        'user_id'       => $uid,
                        'viewable_type' => $type,
                        'viewable_id'   => $model->id,
                        'created_at'    => Carbon::now()->subMinutes(rand(0, 10080)),
                        'updated_at'    => Carbon::now(),
                    ];
                }

                // Вставка пачкой — быстрее, чем по одной
                foreach (array_chunk($views, 100) as $chunk) {
                    View::insert($chunk);
                }

                // 0–10 комментариев
                $commentsCount = rand(0, 10);
                if ($commentsCount === 0) continue;

                $commenterIds = collect($users)->shuffle()->take(min($commentsCount, $userCount));

                foreach ($commenterIds as $uid) {
                    Comment::create([
                        'user_id'          => $uid,
                        'comment'          => fake()->realText(rand(50, 300)),
                        'commentable_type' => $type,
                        'commentable_id'   => $model->id,
                        'created_at'       => Carbon::now()->subMinutes(rand(0, 10080)),
                        'updated_at'       => Carbon::now(),
                    ]);
                }
            }
        }
    }
}

<?php

namespace App\Services;

use App\Models\Review;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use WeStacks\TeleBot\Laravel\TeleBot;

class ReviewParseService
{
    public function __construct()
    {
    }
    public static function parse($chatId, $count){

        $stars = str_repeat('★', $count);
        return TeleBot::sendMessage([
            'chat_id' => $chatId,
            'text' => "✅ Отзыв сохранён!\n\n" .
                "⭐ Рейтинг: {$stars} ({$count}/5)\n" .
                "📅 Дата: " . now()->format('d.m.Y H:i'),
        ]);



    }

    public static function reviewCreate($text, $count, $author)
    {
        DB::beginTransaction();
        try {
            Review::create([
                'content' => $text,
                'rating' => $count,
                'telegram_author_name' => $author,
                'published_at' => now(),
            ]);
            DB::commit();

            Cache::tags(['reviews-index'])->flush();
            Cache::tags(['dashboard'])->flush();

            return true;
        } catch (\Exception $e) {
            DB::rollBack();

            \Log::error("Ошибка создания отзыва: " . $e->getMessage());
            return false;
        }
    }


}

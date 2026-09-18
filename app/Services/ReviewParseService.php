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




}

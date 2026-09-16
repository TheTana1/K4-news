<?php

namespace App\Services;

use App\Models\Review;
use App\Telegram\Handlers\NewNewsHandler;
use App\Telegram\Handlers\NewUserHandler;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use WeStacks\TeleBot\Laravel\TeleBot;
use App\Telegram\Handlers\StartHandler;
use App\Telegram\Handlers\NewAdHandler;
use App\Telegram\Handlers\ReviewHandler;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class TelegramBotService
{
    public function __construct(
        readonly UserRegistrationService $userRegistrationService
    )
    {
    }

    public function handleUpdate($update)
    {
        $message = $update->message ?? null;
        if (!$message) {
            Log::debug('Сообщение не имеет значения');
            return false;
        }
        $chatId = $message->chat->id ?? null;
        $text = $message->text ?? '';

        if ($chatId && $this->isInSession($chatId, 'user')) {
            return (new NewUserHandler($this->userRegistrationService))->handleMessage($message);
        }
        // Регистрируем пользователя
        $user = $this->userRegistrationService->registerFromTelegram($message->from);

        // === Обработка команд ===
        if ($text === '/start' || $text === '❌ Отмена' || $text === '🏠 На главную' || $text === '❌ Сначала') {
            if ($user) {
                return (new StartHandler($this->userRegistrationService))->handle($update);
            }
            return (new NewUserHandler($this->userRegistrationService))->handle($update);
        }

        if ($text === '/new_ad' || $text === '📝 Новое объявление') {
            return (new NewAdHandler($this->userRegistrationService))->handle($update);
        }

        if ($text === '/new_news' || $text === '📝 Новая новость') {
            return (new NewNewsHandler($this->userRegistrationService))->handle($update);
        }

        if ($text === '/help' || $text === '❓ Помощь') {
            return $this->sendHelp($chatId);
        }

        // === Пошаговые обработчики (состояния) ===

        // 1. Проверяем активную сессию объявления
        if ($chatId && $this->isInSession($chatId, 'ad')) {
            return (new NewAdHandler($this->userRegistrationService))->handleMessage($message);
        }

        // 2. Проверяем активную сессию новости
        if ($chatId && $this->isInSession($chatId, 'news')) {
            return (new NewNewsHandler($this->userRegistrationService))->handleMessage($message);
        }

        // === Обработка отзывов (проверяем наличие звёзд в тексте) ===
        if (!empty($text)) {
            $count =mb_substr_count($text, '★', 'UTF-8');
            $stars = str_repeat('★', $count);
            TeleBot::sendMessage([
                'chat_id' => $chatId,
                'text' => "✅ Отзыв сохранён!\n\n" .
                    "⭐ Рейтинг: {$stars} ({$count}/5)\n" .
                    "📅 Дата: " . now()->format('d.m.Y H:i'),
            ]);
            Review::create([
                'content' => $text,
                'rating' => $count,
                'telegram_author_name' => $message->from->first_name,
                'published_at' => now(),
            ]);

            Cache::tags(['reviews'])->flush();
            Cache::tags(['dashboard'])->flush();
            return true;

        }

        // === Ответ по умолчанию ===
        return $this->sendDefaultMessage($chatId);
    }


    /**
     * Проверка наличия активной сессии
     */
    private function isInSession($chatId, $type): bool
    {
        return session()->has("{$type}_{$chatId}");
    }

    /**
     * Отправка справки
     */
    private function sendHelp($chatId)
    {
        if (!$chatId) return;

        $text = "📖 Помощь по боту:\n\n";
        $text .= "📝 /new_ad - Создать объявление\n";
        $text .= "📝 /new_news - Создать новость\n";
        $text .= "❓ /help - Эта справка\n\n";
        $text .= "⭐ Отправьте сообщение со звёздами (★) для создания отзыва\n\n";
        $text .= "Также вы можете использовать кнопки в меню.";

        return TeleBot::sendMessage([
            'chat_id' => $chatId,
            'text' => $text,
            'reply_markup' => [
                'keyboard' => [
                    [['text' => '📝 Новое объявление']],
                    [['text' => '📝 Новая новость']],
                    [['text' => '❓ Помощь']],
                ],
                'resize_keyboard' => true,
            ],
        ]);
    }

    /**
     * Сообщение по умолчанию
     */
    private function sendDefaultMessage($chatId)
    {
        if (!$chatId) return;

        return TeleBot::sendMessage([
            'chat_id' => $chatId,
            'text' => "👋 Используйте /start для начала работы или /help для помощи.\n\n" .
                "⭐ Отправьте сообщение со звёздами (★) для создания отзыва",
        ]);
    }
}

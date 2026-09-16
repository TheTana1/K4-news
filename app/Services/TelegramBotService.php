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
        if (!empty($text) && preg_match('/★/u', $text)) {
            $count =mb_substr_count($text, '★', 'UTF-8');

            ReviewParseService::parse($chatId, $count);
            ReviewParseService::reviewCreate($text, $count, $message->from->first_name);

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
        if (!$chatId) return false;

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
        if (!$chatId) return false;

        return TeleBot::sendMessage([
            'chat_id' => $chatId,
            'text' => "👋 Используйте /start для начала работы или /help для помощи.\n\n" .
                "⭐ Отправьте сообщение со звёздами (★) для создания отзыва",
        ]);
    }
}

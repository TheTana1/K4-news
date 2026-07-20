<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use WeStacks\TeleBot\Laravel\TeleBot;
use App\Telegram\Handlers\StartHandler;
use App\Telegram\Handlers\NewAdHandler;
use App\Telegram\Handlers\NewReviewHandler;
use App\Models\User;
use App\Models\Advertisement;

class TelegramBotService
{
    public function handleUpdate($update)
    {
        $chatId = $update->message->chat->id ?? null;
        $message = $update->message ?? null;
        $text = $message->text ?? '';

        // Регистрируем пользователя
        if ($message) {
            $this->registerUser($update);
        }

        // === Обработка команд ===
        if ($text === '/start') {
            return (new StartHandler($this))->handle($update);
        }

        if ($text === '/new_ad' || $text === '📝 Создать объявление') {
            return (new NewAdHandler($this))->handle($update);
        }

        if ($text === '/new_review' || $text === '⭐ Оставить отзыв') {
            return (new NewReviewHandler($this))->handle($update);
        }

        if ($text === '/help' || $text === '❓ Помощь') {
            return $this->sendHelp($chatId);
        }

        if ($text === '📋 Мои объявления') {
            return $this->showMyAds($chatId);
        }

        // === Пошаговые обработчики ===
        if ($chatId && $this->isInSession($chatId, 'ad')) {
            return (new NewAdHandler($this))->handleMessage($update, $message);
        }

        if ($chatId && $this->isInSession($chatId, 'review')) {
            return (new NewReviewHandler($this))->handleMessage($update, $message);
        }

        // === Обработка файлов ===
        if ($message && ($message->photo ?? false || $message->document ?? false)) {
            return $this->handleFileUpload($chatId);
        }

        // === Ответ по умолчанию ===
        return $this->sendDefaultMessage($chatId);
    }

    public function registerUser($update)
    {
        DB::beginTransaction();
        try {
            $telegramUser = $update->message->from ?? null;

            if ($telegramUser && isset($telegramUser->id)) {
                User::updateOrCreate(
                    ['telegram_id' => $telegramUser->id],
                    [
                        'name' => trim(($telegramUser->first_name ?? $telegramUser->id) . ' ' . ($telegramUser->last_name ?? '')),
                        'email' => trim(($telegramUser->first_name ?? $telegramUser->id) . ($telegramUser->last_name ?? '')) . '@email.com',
                        'telegram_username' => $telegramUser->username,
                        'is_active_in_group' => true,
                        'password' => Hash::make($telegramUser->username ?? $telegramUser->id),
                        'role_id' => 3,
                    ]
                );
                DB::commit();
                Log::info('User created successfully', ['telegramUser_id' => $telegramUser->id]);
                return true;
            }
        } catch (\Exception $exception) {
            DB::rollBack();
            Log::critical('Failed to create user: ' . $exception->getMessage(), [
                'telegramUser_id' => $telegramUser->id ?? null,
                'trace' => $exception->getTraceAsString()
            ]);
            return false;
        }
        return false;
    }

    public function showMainMenu($chatId)
    {
        return (new StartHandler($this))->showMainMenu($chatId);
    }

    private function isInSession($chatId, $type)
    {
        return session()->has("{$type}_{$chatId}");
    }

    private function sendHelp($chatId)
    {
        if (!$chatId) return;

        $text = "📖 Помощь по боту:\n\n";
        $text .= "📝 /new_ad - Создать объявление\n";
        $text .= "⭐ /new_review - Оставить отзыв\n";
        $text .= "📋 Мои объявления - Просмотр моих объявлений\n";
        $text .= "❓ /help - Эта справка\n\n";
        $text .= "Также вы можете использовать кнопки в меню.";

        return TeleBot::sendMessage([
            'chat_id' => $chatId,
            'text' => $text,
            'reply_markup' => [
                'keyboard' => [
                    [['text' => '📝 Создать объявление']],
                    [['text' => '⭐ Оставить отзыв']],
                    [['text' => '❓ Помощь']],
                    [['text' => '📋 Мои объявления']],
                ],
                'resize_keyboard' => true,
            ],
        ]);
    }

    private function sendDefaultMessage($chatId)
    {
        if (!$chatId) return;

        return TeleBot::sendMessage([
            'chat_id' => $chatId,
            'text' => "👋 Используйте /start для начала работы или /help для помощи.",
        ]);
    }

    private function handleFileUpload($chatId)
    {
        if (!$chatId) return;

        return TeleBot::sendMessage([
            'chat_id' => $chatId,
            'text' => "📎 Файл получен!\n\n" .
                "Используйте /new_ad для создания объявления с файлом.",
        ]);
    }

    private function showMyAds($chatId)
    {
        if (!$chatId) return;

        $user = User::where('telegram_id', $chatId)->first();
        if (!$user) {
            return TeleBot::sendMessage([
                'chat_id' => $chatId,
                'text' => "⚠️ Пользователь не найден. Используйте /start для регистрации.",
            ]);
        }

        $ads = Advertisement::where('telegram_author_name', $user->name)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        if ($ads->isEmpty()) {
            return TeleBot::sendMessage([
                'chat_id' => $chatId,
                'text' => "📋 У вас пока нет объявлений.\n\n" .
                    "Создайте первое объявление через /new_ad",
                'reply_markup' => [
                    'keyboard' => [
                        [['text' => '📝 Создать объявление']],
                        [['text' => '⭐ Оставить отзыв']],
                        [['text' => '❓ Помощь']],
                    ],
                    'resize_keyboard' => true,
                ],
            ]);
        }

        $text = "📋 Ваши объявления (последние 10):\n\n";
        foreach ($ads as $index => $ad) {
            $text .= ($index + 1) . ". " . mb_substr($ad->content, 0, 50) . "...\n";
            $text .= "   ID: {$ad->id} | Просмотров: {$ad->views}\n";
            $text .= "   Создано: " . $ad->created_at->format('d.m.Y H:i') . "\n\n";
        }

        return TeleBot::sendMessage([
            'chat_id' => $chatId,
            'text' => $text,
            'reply_markup' => [
                'keyboard' => [
                    [['text' => '📝 Создать объявление']],
                    [['text' => '⭐ Оставить отзыв']],
                    [['text' => '❓ Помощь']],
                ],
                'resize_keyboard' => true,
            ],
        ]);
    }
}

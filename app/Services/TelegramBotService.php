<?php

namespace App\Services;

use App\Repositories\ReviewRepository;
use App\Telegram\Handlers\NewAdHandler;
use App\Telegram\Handlers\NewNewsHandler;
use App\Telegram\Handlers\NewUserHandler;
use App\Telegram\Handlers\StartHandler;
use Illuminate\Support\Facades\Log;

readonly class TelegramBotService
{

    public function __construct(
        public ReviewParseService      $reviewParseService,
        public TelegramService         $telegramService,
        public UserRegistrationService $userRegistrationService,
    ) {
    }

    public function handleUpdate($update): bool
    {
        $message = $update->message ?? null;
        if (!$message) {
            Log::debug('Сообщение не имеет значения');
            return false;
        }

        $chatId = $message->chat->id ?? null;
        if (empty($chatId)) {
            return false;
        }

        $text = $message->text ?? '';
        $from = $message->from ?? null;
        $published_at = $message->forward_origin->date ?? now()->timestamp;

        if ($this->isInSession($chatId, 'user')) {
            return app(NewUserHandler::class)->handleMessage($chatId, $from, $text);
        }

        $user = $this->userRegistrationService->registerFromTelegram($from);
        if (!$user) {
            return app(NewUserHandler::class)->handle($update);
        }

        if ($user->deleted_at) {
            return $this->telegramService->sendMessage(
                (string) $chatId,
                "❌ Пользователь не активен, обратитесь к руководству.\n\n"
            );
        }

        if (in_array($text, ['/start', '❌ Отмена', '🏠 На главную', '❌ Сначала'], true)) {
            return app(StartHandler::class)->handle($chatId);
        }

        if ($text === '/new_ad' || $text === '📝 Новое объявление') {
            return app(NewAdHandler::class)->handle($chatId, $user);
        }

        if ($text === '/new_news' || $text === '📝 Новая новость') {
            return app(NewNewsHandler::class)->handle($chatId, $user);
        }

        if ($text === '/help' || $text === '❓ Помощь') {
            return $this->sendHelp($chatId);
        }

        if ($this->isInSession($chatId, 'ad')) {
            return app(NewAdHandler::class)->handleMessage($message);
        }

        if ($this->isInSession($chatId, 'news')) {
            return app(NewNewsHandler::class)->handleMessage($message);
        }

        if (!empty($text) && preg_match('/★/u', $text)) {
            $count = $this->reviewParseService->parseRating($text);

            if ($count === null) {
                return $this->telegramService->sendMessage(
                    (string) $chatId,
                    '❌ Не удалось определить рейтинг. Отправьте сообщение со звёздами (★).'
                );
            }

            $result = app(ReviewRepository::class)->store($text, $count, $from, $published_at);

            if ($result) {
                $stars = str_repeat('★', $count);
                $this->telegramService->sendMessage(
                    (string) $chatId,
                    "✅ Отзыв сохранён!\n\n" .
                    "⭐ Рейтинг: {$stars} ({$count}/5)\n" .
                    "📅 Дата: " . local_date(now())
                );
            }

            return $result;
        }

        return $this->telegramService->sendMessage(
            (string) $chatId,
            "👋 Используйте /start для начала работы или /help для помощи.\n\n" .
            "⭐ Отправьте сообщение со звёздами (★) для создания отзыва"
        );
    }

    private function isInSession($chatId, string $type): bool
    {
        return session()->has("{$type}_{$chatId}");
    }

    private function sendHelp($chatId): bool
    {
        if (!$chatId) return false;

        $text = "<b>📖 Помощь по боту:</b>\n\n";
        $text .= "📝 /new_ad - Создать объявление\n";
        $text .= "📝 /new_news - Создать новость\n";
        $text .= "❓ /help - Эта справка\n\n";
        $text .= "⭐ Отправьте сообщение со звёздами (★) для создания отзыва\n\n";
        $text .= "Также вы можете использовать кнопки в меню.";

        return $this->telegramService->sendHtmlWithKeyboard(
            $chatId,
            $text,
            [
                [['text' => '📝 Новое объявление']],
                [['text' => '📝 Новая новость']],
                [['text' => '❓ Помощь']],
            ]
        );
    }
}

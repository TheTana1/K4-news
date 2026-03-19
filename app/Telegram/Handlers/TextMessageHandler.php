<?php

namespace App\Telegram\Handlers;

use App\Models\Advertisement;
use App\Models\News;
use App\Models\Review;
use App\Models\User;
use App\Services\PostClassifier;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class TextMessageHandler
{
    protected PostClassifier $classifier;
    protected ?Command $command = null;

    public function __construct(PostClassifier $classifier)
    {
        $this->classifier = $classifier;
    }

    /**
     * Устанавливаем команду для вывода в консоль
     */
    public function setCommand(?Command $command): void
    {
        $this->command = $command;
    }

    /**
     * Вывод в консоль (если команда доступна)
     */
    protected function output(string $message, string $type = 'info'): void
    {
        if ($this->command) {
            switch ($type) {
                case 'info':
                    $this->command->info($message);
                    break;
                case 'warn':
                    $this->command->warn($message);
                    break;
                case 'error':
                    $this->command->error($message);
                    break;
                default:
                    $this->command->line($message);
            }
        } else {
            // Если команды нет, просто логируем
            Log::info($message);
        }
    }

    public function handle(array $message): void
    {
        $text = $message['text'] ?? '';
        $from = $message['from'] ?? [];
        $chat = $message['chat'] ?? [];
        $messageId = $message['message_id'] ?? null;
        //print_r($message);
        if (empty($text)) return;

        // Определяем тип чата
        $chatType = $chat['type'] ?? 'unknown';
        $userName = ($from['first_name'] ?? '') . ' ' . ($from['last_name'] ?? '');

        // Выводим в консоль
        $this->output(sprintf(
            "[%s] %s: %s",
            $chatType === 'supergroup' ? '👥 ГРУППА' : '👤 ЛИЧКА',
            trim($userName) ?: ($from['username'] ?? 'unknown'),
            $text
        ));

        // Сохраняем пользователя, если его нет
        $user = $this->getOrCreateUser($from);

        // Классифицируем и сохраняем
        $type = $this->classifier->classify($text);

        $data = [
            'telegram_message_id' => $messageId,
            'telegram_chat_id' => $chat['id'] ?? null,
            'telegram_author_id' => $from['id'] ?? null,
            'telegram_author_name' => $userName,
            'content' => $text,
            'published_at' => now(),
        ];

        switch ($type) {
            case 'advertisements':
                Advertisement::create(array_merge($data, [
                    'title' => $this->extractTitle($text),
                ]));
                $this->output("   ➡️ Сохранено как объявление", 'info');
                break;
            case 'news':
                News::create(array_merge($data, [
                    'title' => $this->extractTitle($text),
                ]));
                $this->output("   ➡️ Сохранено как новость", 'info');
                break;
            case 'reviews':
                Review::create(array_merge($data, [
                    'user_id' => $user?->id,
                    'rating' => $this->extractRating($text),
                ]));
                $this->output("   ➡️ Сохранено как отзыв", 'info');
                break;

        }

        // Обновляем время последнего поста у пользователя
        if ($user) {
            $user->update(['last_post_at' => now()]);
        }
    }

    protected function getOrCreateUser(array $from): ?User
    {
        $telegramId = $from['id'] ?? null;
        if (!$telegramId) return null;

        return User::firstOrCreate(
            ['telegram_id' => $telegramId],
            [
                'name' => ($from['first_name'] ?? '') . ' ' . ($from['last_name'] ?? ''),
                'telegram_username' => $from['username'] ?? null,
                'email' => $telegramId . '@telegram.user',
                'password' => bcrypt(uniqid()),
            ]
        );
    }

    protected function extractTitle(string $text): string
    {
        $lines = explode("\n", trim($text));
        $firstLine = $lines[0] ?? $text;
        return strlen($firstLine) > 100 ? substr($firstLine, 0, 97) . '...' : $firstLine;
    }

    protected function extractRating(string $text): ?int
    {
        preg_match('/(\d+)[\/\s]*(?:из|\/)\s*5/ui', $text, $matches);
        return isset($matches[1]) ? min((int) $matches[1], 5) : null;
    }
}

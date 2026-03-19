<?php

namespace App\Telegram\Handlers;

use App\Models\Advertisement;
use App\Models\News;
use App\Models\Review;
use App\Models\User;
use App\Services\PostClassifier;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

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
            Log::info($message);
        }
    }

    public function handle(array $message): void
    {
        $text = $message['text'] ?? '';
        $from = $message['from'] ?? [];
        $chat = $message['chat'] ?? [];
        $messageId = $message['message_id'] ?? null;

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

        // Классифицируем по первому символу
        $type = $this->classifier->classify($text);

        // Базовая структура данных
        $data = [
            'telegram_message_id' => $messageId,
            'telegram_chat_id' => $chat['id'] ?? null,
            'telegram_author_id' => $from['id'] ?? null,
            'telegram_author_name' => $userName,
            'published_at' => now(),
        ];

        switch ($type) {
            case 'advertisements':
                Advertisement::create(array_merge($data, [
                    'content' => $text,
                ]));
                $this->output("   ➡️ Сохранено как объявление", 'info');
                break;

            case 'news':
                News::create(array_merge($data, [
                    'content' => $text,
                ]));
                $this->output("   ➡️ Сохранено как новость", 'info');
                break;

            case 'reviews':
                $reviewData = $this->parseReview($text);

                Review::create(array_merge($data, [
                    'rating' => $reviewData['rating'],
                    'author' => $reviewData['author'],
                    'phone_number' => $reviewData['phone_number'],
                    'content' => $reviewData['content'],
                    'slug' => Str::slug($reviewData['author'] . '-' . now()->format('Y-m-d-H-i')) . '-' . uniqid(),
                ]));

                $this->output("   ➡️ Сохранено как отзыв", 'info');
                $this->output("      📊 Рейтинг: " . str_repeat('★', $reviewData['rating']) . str_repeat('☆', 5 - $reviewData['rating']), 'info');
                $this->output("      👤 Автор: " . $reviewData['author'], 'info');
                if ($reviewData['phone_number']) {
                    $this->output("      📞 Телефон: " . $reviewData['phone_number'], 'info');
                }
                break;
        }

        // Обновляем время последнего поста у пользователя
        if ($user) {
            $user->update(['last_post_at' => now()]);
        }
    }

    /**
     * Парсинг отзыва по вашему шаблону
     * ★★★★★
     * Товеко QR-код, 18/03/2026 21:00
     * Кружка на Невского, г Мурманск, ул Александра Невского, д 81
     * Автор: Владимир
     * Контакты:
     * Всё чудесно ❤️
     */
    protected function parseReview(string $text): array
    {
        $lines = explode("\n", trim($text));

        $result = [
            'rating' => 5, // по умолчанию
            'author' => null,
            'phone_number' => null,
            'content' => $text,
        ];

        // Определяем рейтинг по звездочкам в первой строке
        if (!empty($lines[0])) {
            $firstLine = trim($lines[0]);
            // Считаем количество ★
            $rating = substr_count($firstLine, '★');
            if ($rating > 0 && $rating <= 5) {
                $result['rating'] = $rating;
            }
        }

        // Ищем строку с "Автор:"
        foreach ($lines as $line) {
            $line = trim($line);
            if (str_starts_with($line, 'Автор:')) {
                $result['author'] = trim(str_replace('Автор:', '', $line));
            }

            // Ищем телефон (можно расширить поиск)
            if (preg_match('/(\+7|8)[\s\-]?\(?\d{3}\)?[\s\-]?\d{3}[\s\-]?\d{2}[\s\-]?\d{2}/', $line, $matches)) {
                $result['phone_number'] = $matches[0];
            }

            // Если есть строка "Контакты:", следующий элемент может быть телефоном
            if (str_contains($line, 'Контакты:') && isset($lines[array_search($line, $lines) + 1])) {
                $nextLine = trim($lines[array_search($line, $lines) + 1]);
                if (!empty($nextLine) && !str_contains($nextLine, 'Автор:')) {
                    $result['phone_number'] = $nextLine;
                }
            }
        }

        // Если автор не найден, используем имя отправителя
        if (!$result['author']) {
            $result['author'] = 'Гость';
        }

        return $result;
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

        // Убираем спецсимволы из начала
        $firstLine = preg_replace('/^[★!⭐✨❤👍🔥\s]+/', '', $firstLine);

        return strlen($firstLine) > 100 ? substr($firstLine, 0, 97) . '...' : $firstLine;
    }
}

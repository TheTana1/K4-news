<?php

namespace App\Services;

class PostClassifier
{
    /**
     * Минимальная длина сообщения для сохранения
     */
    protected int $minLength = 3;

    /**
     * Классификация сообщения только по первому символу
     */
    public function classify(string $text): ?string
    {
        $text = trim($text);

        // Игнорируем пустые или слишком короткие сообщения
        if (empty($text) || mb_strlen($text) < $this->minLength) {
            return null;
        }

        // Получаем первый символ
        $firstChar = mb_substr($text, 0, 1);

        // 1. Отзывы - начинаются со звездочки ★
        if (in_array($firstChar, ['★', '⭐', '✨', '❤', '👍', '🔥'])) {
            return 'reviews';
        }

        // 2. Новости - начинаются с !
        if ($firstChar === '!') {
            return 'news';
        }

        // 3. Объявления - начинаются с "Внимание" (проверяем первые 8 символов)
        $firstWord = mb_substr($text, 0, 8);
        if (mb_strtolower($firstWord) === 'внимание') {
            return 'advertisements';
        }

        // Если нет специального символа - не сохраняем
        return null;
    }

    /**
     * Проверка, нужно ли сохранять сообщение
     */
    public function shouldSave(string $text): bool
    {
        return $this->classify($text) !== null;
    }

    /**
     * Сохранение поста
     */
    public function save(?string $type, array $data): bool
    {
        if (!$type) {
            \Log::info("⏭️ Message ignored - no special symbol", [
                'content' => mb_substr($data['content'] ?? '', 0, 50)
            ]);
            return false;
        }

        $text = $data['content'] ?? '';

        // Удаляем спецсимвол из начала для чистого контента
        $cleanText = $this->removeSpecialChar($text);
        $data['content'] = $cleanText;

        switch ($type) {
            case 'advertisements':
                $data['title'] = $this->extractTitle($cleanText);
                $data['price'] = $this->extractPrice($text);
                $data['city'] = $this->extractCity($text);
                \Log::info("✅ Saving as advertisement", [
                    'title' => $data['title'],
                    'original' => mb_substr($text, 0, 30)
                ]);
                // Advertisement::create($data);
                break;

            case 'news':
                $data['title'] = $this->extractTitle($cleanText);
                \Log::info("✅ Saving as news", [
                    'title' => $data['title'],
                    'original' => mb_substr($text, 0, 30)
                ]);
                // News::create($data);
                break;

            case 'reviews':
                $data['rating'] = $this->extractRating($text);
                \Log::info("✅ Saving as review", [
                    'rating' => $data['rating'],
                    'original' => mb_substr($text, 0, 30)
                ]);
                // Review::create($data);
                break;
        }

        return true;
    }

    /**
     * Удаляет спецсимвол из начала текста
     */
    protected function removeSpecialChar(string $text): string
    {
        $text = trim($text);
        $firstChar = mb_substr($text, 0, 1);

        // Если первый символ - спецсимвол, удаляем его
        if (in_array($firstChar, ['★', '⭐', '✨', '❤', '👍', '🔥', '!'])) {
            return trim(mb_substr($text, 1));
        }

        // Если начинается с "Внимание", удаляем это слово
        if (mb_strtolower(mb_substr($text, 0, 8)) === 'внимание') {
            return trim(mb_substr($text, 8));
        }

        return $text;
    }

    /**
     * Извлекает заголовок (первую строку или первые 100 символов)
     */
    protected function extractTitle(string $text): string
    {
        $lines = explode("\n", trim($text));
        $firstLine = $lines[0] ?? $text;
        $firstLine = mb_substr($firstLine, 0, 100);
        return $firstLine . (mb_strlen($firstLine) >= 100 ? '...' : '');
    }

    /**
     * Извлекает цену из текста (для объявлений)
     */
    protected function extractPrice(string $text): ?int
    {
        preg_match('/(\d+[\s]?\d*)[\s]?(?:руб|р|₽)/ui', $text, $matches);
        return isset($matches[1]) ? (int) preg_replace('/\s/', '', $matches[1]) : null;
    }

    /**
     * Извлекает город из текста (для объявлений)
     */
    protected function extractCity(string $text): ?string
    {
        $cities = ['Москва', 'Питер', 'СПБ', 'Казань', 'Новосибирск', 'Екатеринбург'];
        foreach ($cities as $city) {
            if (str_contains($text, $city)) {
                return $city;
            }
        }
        return null;
    }

    /**
     * Извлекает рейтинг из текста (для отзывов)
     */
    protected function extractRating(string $text): ?int
    {
        preg_match('/(\d+)[\/\s]*(?:из|\/)\s*5/ui', $text, $matches);
        if (isset($matches[1])) {
            return min((int) $matches[1], 5);
        }

        // Если есть звездочка, но нет цифр - ставим 5
        if (preg_match('/[★⭐✨❤👍🔥]/u', $text)) {
            return 5;
        }

        return null;
    }
}

<?php
// app/Services/ReviewParserService.php

namespace App\Services;

use App\Models\Review;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class ReviewParserService
{
    /**
     * Парсинг и сохранение отзыва (основной метод)
     */
    public function parseAndSave($message, $telegramUser): ?Review
    {
        try {
            $text = $message->text ?? '';
            $chatId = $message->chat->id ?? null;

            Log::info('📝 ReviewParser: START', [
                'chat_id' => $chatId,
                'text_length' => strlen($text),
                'text_preview' => mb_substr($text, 0, 100)
            ]);

            if (empty($text) || !$chatId) {
                Log::warning('Empty message or chat_id');
                return null;
            }

            // Извлекаем рейтинг из звёзд
            $rating = $this->extractRating($text);

            Log::info('⭐ Rating extraction result', [
                'rating' => $rating,
                'text_preview' => mb_substr($text, 0, 50)
            ]);

            if ($rating === null) {
                Log::info('❌ Rating not found');
                return null;
            }

            // Очищаем текст
            $cleanText = $this->cleanText($text);

            Log::info('📝 Clean text result', [
                'original_length' => strlen($text),
                'clean_length' => strlen($cleanText),
                'clean_preview' => mb_substr($cleanText, 0, 100)
            ]);

            // Проверяем дубликаты
            $existingReview = Review::where('content', $cleanText)
                ->where('telegram_chat_id', $chatId)
                ->first();

            if ($existingReview) {
                Log::info('⚠️ Review already exists', ['review_id' => $existingReview->id]);
                return $existingReview;
            }

            // Создаем отзыв
            $telegramAuthorId = $telegramUser->id ?? null;
            $telegramAuthorName = trim(
                ($telegramUser->first_name ?? '') . ' ' . ($telegramUser->last_name ?? '')
            );

            $reviewData = [
                'content' => $cleanText,
                'rating' => $rating,
                'telegram_author_name' => $telegramAuthorName,
                'published_at' => now(),
            ];

            Log::info('💾 Saving review', $reviewData);

            $review = Review::create($reviewData);

            Log::info('✅ Review saved successfully', [
                'review_id' => $review->id,
                'rating' => $review->rating,
                'chat_id' => $chatId
            ]);

            return $review;

        } catch (\Exception $e) {
            Log::error('❌ Error parsing review: ' . $e->getMessage(), [
                'text' => $text ?? null,
                'chat_id' => $chatId ?? null,
                'trace' => $e->getTraceAsString()
            ]);
            return null;
        }
    }


    /**
     * Извлечение рейтинга из текста (только звёзды)
     */
    public function extractRating($text): ?int
    {
        Log::debug('🔍 Extracting rating from text', [
            'text_preview' => mb_substr($text, 0, 50)
        ]);

        // 1. Ищем звёзды ★★★★★ (5 подряд)
        if (preg_match('/[★☆⭐]{5}/', $text)) {
            Log::debug('Found 5 stars');
            return 5;
        }

        // 2. Ищем 4 звёзды
        if (preg_match('/[★☆⭐]{4}/', $text)) {
            Log::debug('Found 4 stars');
            return 4;
        }

        // 3. Ищем 3 звёзды
        if (preg_match('/[★☆⭐]{3}/', $text)) {
            Log::debug('Found 3 stars');
            return 3;
        }

        // 4. Ищем 2 звёзды
        if (preg_match('/[★☆⭐]{2}/', $text)) {
            Log::debug('Found 2 stars');
            return 2;
        }

        // 5. Ищем 1 звезду
        if (preg_match('/[★☆⭐]{1}/', $text)) {
            Log::debug('Found 1 star');
            return 1;
        }

        // 6. Ищем звёзды с пробелами ★ ★ ★ ★ ★
        if (preg_match_all('/[★☆⭐]/', $text, $matches)) {
            $count = count($matches[0]);
            if ($count >= 1 && $count <= 5) {
                Log::debug('Found stars with spaces', ['count' => $count]);
                return $count;
            }
        }

        Log::debug('No stars found');
        return null;
    }

    /**
     * Очистка текста от звёзд
     */
    public function cleanText($text): string
    {
        // Удаляем все звёзды
        $text = preg_replace('/[\x{2605}\x{2606}]/u', '', $text);

        $text = preg_replace('/\s+/', ' ', $text);
        $text = trim($text);

        return $text;
    }

    /**
     * Проверка наличия звёзд в тексте
     */
    public function hasStars($text): bool
    {
        return preg_match('/[★☆⭐]/', $text);
    }
}

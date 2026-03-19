<?php

namespace App\Services\Telegram;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramBotService
{
    protected string $token;
    protected int $timeout;
    protected string $apiUrl;

    public function __construct(string $token, int $timeout = 30)
    {
        $this->token = $token;
        $this->timeout = $timeout;
        $this->apiUrl = "https://api.telegram.org/bot{$token}";
    }

    /**
     * Получить обновления (polling)
     */
    public function getUpdates(int $offset = 0): array
    {
        $url = "{$this->apiUrl}/getUpdates?offset={$offset}&timeout={$this->timeout}";

        try {
            $response = Http::timeout($this->timeout + 5)
                ->withOptions(['verify' => false])
                ->get($url);

            if ($response->successful()) {
                return $response->json()['result'] ?? [];
            }
        } catch (\Exception $e) {
            Log::error('Telegram API error: ' . $e->getMessage());
        }

        return [];
    }

    /**
     * Отправить сообщение
     */
    public function sendMessage(int $chatId, string $text, array $options = []): bool
    {
        $url = "{$this->apiUrl}/sendMessage";

        $payload = array_merge([
            'chat_id' => $chatId,
            'text' => $text,
            'parse_mode' => 'HTML',
        ], $options);

        try {
            $response = Http::post($url, $payload);
            return $response->successful();
        } catch (\Exception $e) {
            Log::error('Telegram send error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Получить информацию о боте
     */
    public function getMe(): ?array
    {
        $url = "{$this->apiUrl}/getMe";

        try {
            $response = Http::get($url);
            return $response->successful() ? $response->json()['result'] : null;
        } catch (\Exception $e) {
            Log::error('Telegram getMe error: ' . $e->getMessage());
            return null;
        }
    }
}

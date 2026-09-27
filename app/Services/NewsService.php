<?php

namespace App\Services;

use App\Jobs\SendNewsTelegram;
use App\Models\News;
use Illuminate\Support\Facades\Log;

readonly class NewsService
{
    public function __construct(public TelegramService $telegramService)
    {
    }

    /**
     * Отправка в Telegram через очередь (с указанием chatId, которого исключить).
     */
    public final function sendNewsMessageTG(
        ?string $chatId,
        News $news,
        string $header = '❗ <b>Новая новость</b>'
    ): bool {
        SendNewsTelegram::dispatch($news, $header, $chatId);

        Log::info('Рассылка новости поставлена в очередь', [
            'news_id' => $news->id,
            'exclude_chat_id' => $chatId,
            'header' => $header,
        ]);

        return true;
    }

    /**
     * Отправка в Telegram через очередь (для веб-создания).
     */
    public final function sendNewsMessage(
        News $news,
        string $header = '❗ <b>Новая новость</b>'
    ): bool {
        SendNewsTelegram::dispatch($news, $header);

        Log::info('Рассылка новости поставлена в очередь', [
            'news_id' => $news->id,
            'header' => $header,
        ]);

        return true;
    }
}

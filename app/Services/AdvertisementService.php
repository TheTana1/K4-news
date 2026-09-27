<?php

namespace App\Services;

use App\Jobs\SendAdvertisementTelegram;
use App\Models\Advertisement;
use Illuminate\Support\Facades\Log;

readonly class AdvertisementService
{
    public function __construct(public TelegramService $telegramService)
    {
    }

    /**
     * Отправка в Telegram через очередь (с указанием chatId, которого исключить).
     */
    public final function sendAdvertisementMessageTG(
        ?string       $chatId,
        Advertisement $advertisement,
        string        $header = '‼ <b>Новое объявление</b>'
    ): bool
    {
        SendAdvertisementTelegram::dispatch($advertisement, $header, $chatId);

        Log::info('Рассылка Telegram поставлена в очередь', [
            'advertisement_id' => $advertisement->id,
            'exclude_chat_id' => $chatId,
            'header' => $header,
        ]);

        return true;
    }

    /**
     * Отправка в Telegram через очередь (для веб-создания).
     */
    public final function sendAdvertisementMessage(
        Advertisement $advertisement,
        string        $header = '‼ <b>Новое объявление</b>'
    ): bool
    {
        SendAdvertisementTelegram::dispatch($advertisement, $header);

        Log::info('Рассылка Telegram поставлена в очередь', [
            'advertisement_id' => $advertisement->id,
            'header' => $header,
        ]);

        return true;
    }
}

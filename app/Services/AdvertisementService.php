<?php

namespace App\Services;

use App\Models\Advertisement;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

readonly class AdvertisementService
{
    public function __construct(public TelegramService $telegramService)
    {
    }

    public final function sendAdvertisementMessageTG(?string $chatId, Advertisement $advertisement): bool
    {


        if ($advertisement->role_id == 2) {
            $users = User::where('telegram_id', '!=', $chatId)->
            whereNotNull('telegram_id')->get();
        } else {
            $users = User::where('role_id', '=', $advertisement->role_id)->
            where('telegram_id', '!=', $chatId)->
            whereNotNull('telegram_id')->get();
        }
        if ($users->isEmpty()) {
            Log::info('Рассылка объявления прервана: нет пользователей с telegram_id', [
                'advertisement_id' => $advertisement->id,
            ]);
            return false;
        }
        $roleLabels = match ($advertisement->role_id) {
            3 => 'сотрудникам кухни',
            4 => 'сотрудникам зала',
            default => 'всем'
        };
        $date = local_date(now());
        $text =
            "‼ <b>Новое объявление</b>" .
            "🆔 ID: {$advertisement->id}\n" .
            "👥 Отправлено: " . ($roleLabels) . "\n" .
            "📅 Дата: " . $date . "\n" .
            "✏️ Текст: " . $advertisement->content;

        return $this->resendToUsers($users, $text, $advertisement);
    }

    public final function sendAdvertisementMessage(Advertisement $advertisement, string $header): bool
    {


        if ($advertisement->role_id == 2) {
            $users = User::whereNotNull('telegram_id')->get();
        } else {
            $users = User::where('role_id', '=', $advertisement->role_id)
                ->whereNotNull('telegram_id')->get();
        }


        if ($users->isEmpty()) {
            Log::info('Рассылка объявления прервана: нет пользователей с telegram_id', [
                'advertisement_id' => $advertisement->id,
            ]);
            return false;
        }
        $roleLabels = match ($advertisement->role_id) {
            3 => 'сотрудникам кухни',
            4 => 'сотрудникам зала',
            default => 'всем'
        };
        $date = local_date(now());
        $text =
            "$header\n\n" .
            "🆔 ID: {$advertisement->id}\n" .
            "👥 Отправлено: " . ($roleLabels) . "\n" .
            "📅 Дата: " . $date . "\n" .
            "✏️ Текст: " . $advertisement->content;

        return $this->resendToUsers($users, $text, $advertisement);
    }

    /**
     * @param \Illuminate\Database\Eloquent\Collection $users
     * @param string $text
     * @param Advertisement $advertisement
     * @return bool
     */
    private function resendToUsers(\Illuminate\Database\Eloquent\Collection $users, string $text, Advertisement $advertisement): bool
    {
        foreach ($users as $user) {

            if (!$this->telegramService->sendHtmlWithRemoveKeyboard($user->telegram_id, $text)) {
                Log::warning('Рассылка: не удалось отправить', [
                    'advertisement_id' => $advertisement->id,
                    'user_id' => $user->id,
                ]);
            }
        }
        if ($advertisement->files()->count() > 0) {

            foreach ($users as $user) {
                foreach ($advertisement->files as $file) {
                    if (!file_exists(Storage::disk('public')->path($file->file_path))) {
                        Log::error('Telegram send failed: файла не существует', [
                            'chat_id' => $user->telegram_id,
                            'file_path' => $file->file_path,
                        ]);
                        return false;
                    }
                    if (str_starts_with($file->mime_type, 'image/')) {
                        $this->telegramService->sendPhoto($file->file_path, $user->telegram_id);
                    } else {
                        $this->telegramService->sendDocument($file->file_path, $user->telegram_id);
                    }

                }

            }
        }
        return true;
    }
}

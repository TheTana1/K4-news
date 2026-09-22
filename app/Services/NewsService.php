<?php

namespace App\Services;

use App\Models\News;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

readonly class NewsService
{
    public function __construct(public TelegramService $telegramService)
    {
    }
    public final function sendNewsMessageTG(?string $chatId, News $news): bool
    {


        if ($news->role_id == 2) {
            $users = User::where('telegram_id', '!=', $chatId)->
            whereNotNull('telegram_id')->get();
        } else {
            $users = User::where('role_id', '=', $news->role_id)->
            where('telegram_id', '!=', $chatId)->
            whereNotNull('telegram_id')->get();
        }
        if ($users->isEmpty()) {
            Log::info('Рассылка новости прервана: нет пользователей с telegram_id', [
                'advertisement_id' => $news->id,
            ]);
            return false;
        }

        $roleLabels = match ($news->role_id) {
            3 => 'сотрудникам кухни',
            4 => 'сотрудникам зала',
            default => 'всем'
        };
        $date = local_date(now());
        $text =
            "❗ <b>Новая новость</b>" .
            "🆔 ID: {$news->id}\n" .
            "👥 Отправлено: " . $roleLabels . "\n" .
            "📅 Дата: " . $date . "\n" .
            "✏️ Текст: " . $news->content;

        return $this->resendToUsers($users, $text, $news);
    }
    public final function sendNewsMessage(News $news, string $header): bool
    {


        if ($news->role_id == 2) {
            $users = User::whereNotNull('telegram_id')->get();
        } else {
            $users = User::where('role_id', '=', $news->role_id)
                ->whereNotNull('telegram_id')->get();
        }


        if ($users->isEmpty()) {
            Log::info('Рассылка новости прервана: нет пользователей с telegram_id', [
                'news_id' => $news->id,
            ]);
            return false;
        }
        $roleLabels = match ($news->role_id) {
            3 => 'сотрудникам кухни',
            4 => 'сотрудникам зала',
            default => 'всем'
        };
        $date = local_date(now());
        $text =
            "$header\n\n" .
            "🆔 ID: {$news->id}\n" .
            "👥 Отправлено: " . $roleLabels . "\n" .
            "📅 Дата: " . $date . "\n" .
            "✏️ Текст: " . $news->content;
        $this->resendToUsers($users, $text, $news);
        return true;

    }

    /**
     * @param \Illuminate\Database\Eloquent\Collection $users
     * @param string $text
     * @param News $news
     * @return bool
     */
    private function resendToUsers(\Illuminate\Database\Eloquent\Collection $users, string $text, News $news) :bool
    {
        foreach ($users as $user) {
            if (!$this->telegramService->sendHtmlWithRemoveKeyboard($user->telegram_id, $text)) {
                Log::warning('Рассылка: не удалось отправить', [
                    'news_id' => $news->id,
                    'user_id' => $user->id,
                ]);
            }
        }
        if ($news->files()->count() > 0) {

            foreach ($users as $user) {
                foreach ($news->files as $file) {
                    if (!file_exists(Storage::disk('public')->path($file->file_path))) {
                        Log::error('Telegram send failed: файла не существует', [
                            'chat_id' => $user->telegram_id,
                            'file_path' => $file->file_path,
                        ]);
                    }
                    $this->telegramService->sendPhoto($file->file_path, $user->telegram_id);


                }

            }
        }
        return true;
    }
}



<?php

namespace App\Jobs;

use App\Models\News;
use App\Services\TelegramService;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class SendNewsTelegram implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 300;

    public function __construct(
        public News $news,
        public string $header = '❗ <b>Новая новость</b>',
        public ?string $excludeChatId = null,
    ) {}

    public function handle(TelegramService $telegramService): void
    {
        $roleId = $this->news->role_id;

        $usersQuery = User::whereNotNull('telegram_id')
            ->when($this->excludeChatId, fn ($q) => $q->where('telegram_id', '!=', $this->excludeChatId))
            ->when($roleId !== null, fn ($q) => $q->where('role_id', $roleId));

        $users = $usersQuery->get();

        if ($users->isEmpty()) {
            Log::info('Рассылка прервана: нет получателей', [
                'news_id' => $this->news->id,
            ]);
            return;
        }

        $roleLabels = match ((int) $roleId) {
            1 => 'администраторам',
            2 => 'менеджерам',
            3 => 'сотрудникам кухни',
            4 => 'сотрудникам зала',
            5 => 'сотрудникам бара',
            6 => 'сотрудникам клининга',
            7 => 'сотрудникам техслужб',
            default => 'всем',
        };

        $text = "{$this->header}\n\n" .
            "🆔 ID: {$this->news->id}\n" .
            "👥 Отправлено: {$roleLabels}\n" .
            "📅 Дата: " . local_date(now()) . "\n" .
            "✏️ Текст: " . e($this->news->content);

        $sent = 0;

        foreach ($users as $user) {
            $chatId = (string) $user->telegram_id;

            if ($telegramService->sendHtmlWithRemoveKeyboard($chatId, $text)) {
                $sent++;
            } else {
                Log::warning('Рассылка: не удалось отправить текст', [
                    'news_id' => $this->news->id,
                    'user_id' => $user->id,
                ]);
            }

            // Лимит Telegram: 30 сообщений/сек. 50 мс = 20/сек — безопасно.
            usleep(50000);
        }

        // Файлы
        $files = $this->news->files;

        if ($files->isNotEmpty()) {
            foreach ($users as $user) {
                $chatId = (string) $user->telegram_id;

                foreach ($files as $file) {
                    if (!Storage::disk('public')->exists($file->file_path)) {
                        Log::error('Файл не найден', [
                            'news_id' => $this->news->id,
                            'file_path' => $file->file_path,
                        ]);
                        continue;
                    }

                    $telegramService->sendPhoto($file->file_path, $chatId);

                    usleep(50000);
                }
            }
        }

        Log::info('Рассылка завершена', [
            'news_id' => $this->news->id,
            'total' => $users->count(),
            'sent' => $sent,
            'files' => $files->count(),
        ]);
    }
}

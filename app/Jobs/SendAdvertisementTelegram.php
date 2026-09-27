<?php

namespace App\Jobs;

use App\Models\Advertisement;
use App\Services\TelegramService;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class SendAdvertisementTelegram implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 300;

    public function __construct(
        public Advertisement $advertisement,
        public string $header = '‼ <b>Новое объявление</b>',
        public ?string $excludeChatId = null,
    ) {}

    public function handle(TelegramService $telegramService): void
    {
        $roleId = $this->advertisement->role_id;

        $usersQuery = User::whereNotNull('telegram_id')
            ->when($this->excludeChatId, fn ($q) => $q->where('telegram_id', '!=', $this->excludeChatId))
            ->when($roleId !== null, fn ($q) => $q->where('role_id', $roleId));

        $users = $usersQuery->get();

        if ($users->isEmpty()) {
            Log::info('Рассылка прервана: нет получателей', [
                'advertisement_id' => $this->advertisement->id,
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
            "🆔 ID: {$this->advertisement->id}\n" .
            "👥 Отправлено: {$roleLabels}\n" .
            "📅 Дата: " . local_date(now()) . "\n" .
            "✏️ Текст: " . e($this->advertisement->content);

        $sent = 0;

        foreach ($users as $user) {
            $chatId = (string) $user->telegram_id;

            if ($telegramService->sendHtmlWithRemoveKeyboard($chatId, $text)) {
                $sent++;
            } else {
                Log::warning('Рассылка: не удалось отправить текст', [
                    'advertisement_id' => $this->advertisement->id,
                    'user_id' => $user->id,
                ]);
            }

            usleep(50000);
        }

        $files = $this->advertisement->files;

        if ($files->isNotEmpty()) {
            foreach ($users as $user) {
                $chatId = (string) $user->telegram_id;

                foreach ($files as $file) {
                    if (!Storage::disk('public')->exists($file->file_path)) {
                        Log::error('Файл не найден', [
                            'advertisement_id' => $this->advertisement->id,
                            'file_path' => $file->file_path,
                        ]);
                        continue;
                    }

                    if (str_starts_with($file->mime_type, 'image/')) {
                        $telegramService->sendPhoto($file->file_path, $chatId);
                    } else {
                        $telegramService->sendDocument($file->file_path, $chatId);
                    }

                    usleep(50000);
                }
            }
        }

        Log::info('Рассылка завершена', [
            'advertisement_id' => $this->advertisement->id,
            'total' => $users->count(),
            'sent' => $sent,
            'files' => $files->count(),
        ]);
    }
}

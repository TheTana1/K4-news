<?php

namespace App\Telegram\Handlers;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class MemberLeftHandler
{
    protected ?Command $command = null;

    public function setCommand(?Command $command): void
    {
        $this->command = $command;
    }

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

    public function handle(array $member): void
    {
        $telegramId = $member['id'] ?? null;
        $firstName = $member['first_name'] ?? '';
        $lastName = $member['last_name'] ?? '';
        $username = $member['username'] ?? null;

        $this->output("❌ Участник покинул группу: {$firstName} {$lastName}", 'warn');

        if ($telegramId) {
            $user = User::where('telegram_id', $telegramId)->first();

            if ($user) {
                $user->update([
                    'is_active_in_group' => false,
                    'left_at' => now(),
                ]);
                $this->output("   ➡️ Пользователь {$user->name} деактивирован", 'info');
            } else {
                // Если пользователя нет в БД, создаём запись о выходе
                User::create([
                    'telegram_id' => $telegramId,
                    'name' => $firstName . ' ' . $lastName,
                    'telegram_username' => $username,
                    'email' => $telegramId . '@telegram.user',
                    'password' => bcrypt(uniqid()),
                    'is_active_in_group' => false,
                    'left_at' => now(),
                ]);
                $this->output("   ➡️ Создана запись о выходе", 'info');
            }
        }
    }
}

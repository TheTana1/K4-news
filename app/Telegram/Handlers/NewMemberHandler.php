<?php

namespace App\Telegram\Handlers;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class NewMemberHandler
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

        $this->output("✅ Новый участник: {$firstName} {$lastName}", 'info');

        if (!$telegramId) {
            return;
        }

        // Ищем пользователя по telegram_id
        $user = User::where('telegram_id', $telegramId)->first();

        if ($user) {
            // Если пользователь уже есть в БД - активируем
            $user->update([
                'is_active_in_group' => true,
                'joined_at' => now(),
                'left_at' => null,
                'name' => $firstName . ' ' . $lastName,
                'telegram_username' => $username,
            ]);

            $this->output("   ➡️ Пользователь активирован: {$user->name}", 'info');
        } else {
            // Если нет - создаём нового пользователя
            $user = User::create([
                'telegram_id' => $telegramId,
                'name' => $firstName . ' ' . $lastName,
                'telegram_username' => $username,
                'email' => $telegramId . '@telegram.user',
                'password' => bcrypt(uniqid()),
                'is_active_in_group' => true,
                'joined_at' => now(),
            ]);

            $this->output("   ➡️ Новый пользователь создан: {$user->name}", 'info');
        }
    }
}

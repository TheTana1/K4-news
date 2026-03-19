<?php

namespace App\Telegram\Commands;

interface CommandInterface
{
    public function handle(array $update, array $chat, array $from, string $text): ?string;
}

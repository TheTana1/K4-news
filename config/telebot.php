<?php

return [
    // Имя бота по умолчанию (строка!)
    'default' => env('TELEGRAM_BOT_NAME', 'default'),

    // Конфигурации ботов
    'bots' => [
        'default' => [
            'token' => env('TELEGRAM_BOT_TOKEN'),
            'name' => env('TELEGRAM_BOT_NAME', 'default'),
            'timeout' => env('TELEGRAM_BOT_TIMEOUT', 30),
        ],
    ],
];

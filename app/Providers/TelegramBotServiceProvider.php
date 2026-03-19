<?php

namespace App\Providers;

use App\Services\Telegram\TelegramBotService;
use Illuminate\Support\ServiceProvider;

class TelegramBotServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(TelegramBotService::class, function ($app) {
            return new TelegramBotService(
                config('services.telegram.token'),
                config('services.telegram.polling_timeout', 30)
            );
        });

        $this->app->alias(TelegramBotService::class, 'telegram.bot');
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}

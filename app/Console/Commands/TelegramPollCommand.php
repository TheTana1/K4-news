<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Telegram\TelegramBotService;
use App\Telegram\TelegramRouter;
use Illuminate\Support\Facades\Log;

class TelegramPollCommand extends Command
{
    protected $signature = 'telegram:poll
                            {--timeout=30 : Long polling timeout}
                            {--limit=100 : Max updates per request}';

    protected $description = 'Poll Telegram for new messages and events';

    protected TelegramBotService $bot;
    protected TelegramRouter $router;

    public function __construct(TelegramBotService $bot, TelegramRouter $router)
    {
        parent::__construct();
        $this->bot = $bot;
        $this->router = $router;
    }

    public function handle()
    {
        $token = config('services.telegram.token');

        if (!$token) {
            $this->error('❌ Telegram token not configured!');
            return 1;
        }

        $this->info('🚀 Starting Telegram long polling...');
        $this->line("Token: {$token}");
        $this->line('Waiting for messages... (Ctrl+C to exit)');

        $offset = 0;

        while (true) {
            try {
                $updates = $this->bot->getUpdates($offset);

                foreach ($updates as $update) {
                    $offset = $update['update_id'] + 1;

                    // Передаём $this в роутер, чтобы можно было использовать методы вывода
                    $this->router->setCommand($this);
                    $this->router->route($update);
                }

                // Небольшая пауза, чтобы не нагружать процессор
                usleep(600000);

            } catch (\Exception $e) {
                $this->error('❌ Error: ' . $e->getMessage());
                Log::error('Telegram poll error: ' . $e->getMessage());
                sleep(5);
            }
        }
    }
}

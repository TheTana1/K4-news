<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use WeStacks\TeleBot\Laravel\TeleBot;
use App\Services\TelegramBotService;

class TelegramPolling extends Command
{
    protected $signature = 'telegram:poll';
    protected $description = 'Run Telegram bot polling';

    protected $botService;

    public function __construct(TelegramBotService $botService)
    {
        parent::__construct();
        $this->botService = $botService;
    }

    public function handle()
    {
        $this->info('🚀 Starting Telegram polling...');
        $this->info('Press Ctrl+C to stop');

        $lastUpdateId = 0;

        while (true) {
            try {
                $updates = TeleBot::getUpdates([
                    'offset' => $lastUpdateId + 1,
                    'timeout' => 30,
                ]);

                foreach ($updates as $update) {
                    $lastUpdateId = $update->update_id;
                    $this->info("📨 Update ID: {$update->update_id}");

                    $this->botService->handleUpdate($update);
                }

                if (empty($updates)) {
                    usleep(10000);//200000
                }
            } catch (\Exception $e) {
                $this->error('❌ Error: ' . $e->getMessage());
                $this->error('File: ' . $e->getFile() . ':' . $e->getLine());
                sleep(5);
            }
        }
    }
}

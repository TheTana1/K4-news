<?php

namespace App\Telegram;

use App\Telegram\Handlers\TextMessageHandler;
use App\Telegram\Handlers\MemberLeftHandler;
use App\Telegram\Handlers\NewMemberHandler;
use Illuminate\Console\Command;

class TelegramRouter
{
    protected TextMessageHandler $textHandler;
    protected MemberLeftHandler $leftHandler;
    protected NewMemberHandler $newMemberHandler;
    protected ?Command $command = null;

    public function __construct(
        TextMessageHandler $textHandler,
        MemberLeftHandler $leftHandler,
        NewMemberHandler $newMemberHandler
    ) {
        $this->textHandler = $textHandler;
        $this->leftHandler = $leftHandler;
        $this->newMemberHandler = $newMemberHandler;
    }

    /**
     * Устанавливаем команду для вывода в консоль
     */
    public function setCommand(Command $command): void
    {
        $this->command = $command;
    }

    public function route(array $update): void
    {
        // 1. Текстовые сообщения
        if (isset($update['message']['text'])) {
            $this->textHandler->setCommand($this->command);
            $this->textHandler->handle($update['message']);
        }

        // 2. Участник покинул группу
        if (isset($update['message']['left_chat_member'])) {
            $this->leftHandler->setCommand($this->command);
            $this->leftHandler->handle($update['message']['left_chat_member']);
        }

        // 3. Новый участник
        if (isset($update['message']['new_chat_members'])) {
            $this->newMemberHandler->setCommand($this->command);
            foreach ($update['message']['new_chat_members'] as $member) {
                $this->newMemberHandler->handle($member);
            }
        }
    }
}

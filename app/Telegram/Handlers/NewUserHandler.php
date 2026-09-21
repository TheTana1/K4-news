<?php

namespace App\Telegram\Handlers;

use App\Services\TelegramService;
use App\Services\UserRegistrationService;
use Illuminate\Support\Str;
use WeStacks\TeleBot\Objects\Message;

class NewUserHandler
{
    public function __construct(readonly TelegramService $telegramService)
    {
    }

    public function handle($update)
    {
        $chatId = $update->message->chat->id ?? null;
        if (!$chatId) return false;

        $sessionKey = "user_{$chatId}";

        if (session()->has($sessionKey)) {
            $data = session($sessionKey);
            return $this->showCurrentStep($chatId, $data);
        }

        session([$sessionKey => ['step' => 1]]);

        return $this->telegramService->sendMessage(
            $chatId,
            "📱 Давайте зарегистрируемся.\n\nНапишите ваш телефон"
        );
    }

    public function handleMessage($chatId, $from, $text)
    {
        if (!$chatId) return false;
        $sessionKey = "user_{$chatId}";
        $data = session($sessionKey, ['step' => 1]);

        $newPassword = Str::random(12);
        if ($text === '✅ Отправить') {
            $data = array_merge($data, [
                'id' => $from->id,
                'first_name' => $from->first_name ?? null,
                'last_name' => $from->last_name ?? null,
                'username' => $from->username ?? null,
                'password' => $newPassword
            ]);

            app(UserRegistrationService::class)->createUser($data);
            session()->forget($sessionKey);

            $text = "✅ <b>Регистрация завершена!</b>\n\n";
            $text .= "Вам выдан пароль <code>{$newPassword}</code>\n";
            $text .= "Рекомендуем сменить его после входа.";
            return $this->telegramService->sendHtmlMessage(
                $chatId,
                $text,
                [
                    'reply_markup' =>
                        [
                            'keyboard' =>
                                [
                                    [['text' => '🏠 На главную']],
                                ],
                            'resize_keyboard' => true,
                        ]
                ]
            );
        }

        if ($text === '⏮ Назад') {
            $step = $data['step'] - 1;
            if ($step < 1) $step = 1;

            $data['step'] = $step;
            session([$sessionKey => $data]);

            return $this->showCurrentStep($chatId, $data);
        }

        if ($text === '❌ Сначала' || $text === '/cancel') {
            session()->forget($sessionKey);

            return $this->telegramService->sendWithRemoveKeyboard(
                $chatId,
                '❌ Регистрация отменена.'
            );
        }

        $step = $data['step'] ?? 1;

        return match ($step) {
            1 => $this->processPhone($chatId, $text, $data),
            2 => $this->processEmail($chatId, $text, $data),
//            3 => $this->processPassword($chatId, $text, $data),
//            4 => $this->processPasswordConfirm($chatId, $text, $data),
            5 => $this->processRole($chatId, $text, $data),
            default => $this->handleCancel($chatId),
        };
    }

    private function processPhone($chatId, $text, $data)
    {
        if (empty($text)) {
            return $this->telegramService->sendMessage($chatId, '❌ Номер не может быть пустым. Попробуйте снова.');
        }

        if (!$this->checkNumber($text)) {
            return $this->telegramService->sendMessage($chatId, '❌ Некорректный номер. Попробуйте снова.');
        }

        $formatNumber = $this->normalizePhone($text);
        if ($formatNumber) {
            $data['phone'] = $formatNumber;
            $data['step'] = 2;
            session(["user_{$chatId}" => $data]);

            return $this->askForEmail($chatId);
        }
        return false;
    }

    private function processEmail($chatId, $text, $data)
    {
        if (empty($text)) {
            return $this->telegramService->sendMessage($chatId, '❌ Email не может быть пустым. Попробуйте снова.');
        }

        if (!$this->checkEmail($text)) {
            return $this->telegramService->sendMessage($chatId, '❌ Некорректный email. Попробуйте снова.');
        }

        $data['email'] = $text;
//      $data['step'] = 3;
        $data['step'] = 5;
        session(["user_{$chatId}" => $data]);

//        return $this->askForPassword($chatId);
        return $this->askForRole($chatId);
    }


//    private function processPassword($chatId, $text, $data)
//    {
//        // Валидация
//        if (empty($text)) {
//            return \TeleBot::sendMessage([
//                'chat_id' => $chatId,
//                'text' => '❌ Пароль не может быть пустым. Попробуйте снова.',
//            ]);
//        }
//
//        if (!$this->checkPassword($text)) {
//            return \TeleBot::sendMessage([
//                'chat_id' => $chatId,
//                'text' => "❌ Некорректный пароль. Попробуйте снова.\n" .
//                    "Минимум 8 символов, хотя бы одна буква и одна цифра.",
//            ]);
//        }
//
//        // Сохраняем и переходим на следующий шаг
//        $data['password'] = $text;
//        $data['step'] = 4;
//        session(["user_{$chatId}" => $data]);
//
//        // ПОСЛЕ СОХРАНЕНИЯ ВЫХОДИМ И ЖДЕМ СЛЕДУЮЩЕЕ СООБЩЕНИЕ
//        return $this->askForPasswordConfirm($chatId);
//    }
//
//    private function processPasswordConfirm($chatId, $text, $data)
//    {
//        // Проверяем, совпадает ли пароль
//        if ($text !== ($data['password'] ?? null)) {
//            return \TeleBot::sendMessage([
//                'chat_id' => $chatId,
//                'text' => '❌ Пароли не совпадают. Попробуйте снова.',
//            ]);
//        }
//
//        // Переходим на следующий шаг
//        $data['step'] = 5;
//        session(["user_{$chatId}" => $data]);
//
//        // ПОСЛЕ СОХРАНЕНИЯ ВЫХОДИМ И ЖДЕМ СЛЕДУЮЩЕЕ СООБЩЕНИЕ
//        return $this->askForRole($chatId);
//    }

    private function processRole($chatId, $text, $data)
    {
        $role = $this->checkRole($text);
        if (!$role) {
            return $this->telegramService->sendWithKeyboard(
                $chatId,
                '❌ Пожалуйста, выберите роль из кнопок ниже.',
                [
                    [['text' => '👨‍🍳 Сотрудник кухни']],
                    [['text' => '🤵 Сотрудник зала']],
                    [['text' => '⏮ Назад']],
                ]
            );
        }

        $data['role'] = $role;
        $data['step'] = 6;
        session(["user_{$chatId}" => $data]);

        return $this->confirmUser($chatId, $data);
    }

    private function showCurrentStep($chatId, $data)
    {
        $step = $data['step'] ?? 1;

        return match ($step) {
            1 => $this->telegramService->sendMessage($chatId, "📱 Напишите ваш телефон"),
            2 => $this->askForEmail($chatId),
//            3 => $this->askForPassword($chatId),
//            4 => $this->askForPasswordConfirm($chatId),
            5 => $this->askForRole($chatId),
            6 => $this->confirmUser($chatId, $data),
            default => $this->telegramService->sendMessage($chatId, 'Продолжите регистрацию'),
        };
    }

    private function askForEmail($chatId): bool
    {
        return $this->telegramService->sendWithKeyboard(
            $chatId,
            "🗄 Отлично!\n\nНапишите ваш email",
            [[['text' => '⏮ Назад']]]
        );
    }

//    private function askForPassword($chatId): bool
//    {
//        return $this->telegramService->sendWithKeyboard(
//            $chatId,
//            "😉 Мы на полпути!\n\nПридумайте пароль",
//            [[['text' => '⏮ Назад']]]
//        );
//    }

    private function askForPasswordConfirm($chatId): bool
    {
        return $this->telegramService->sendWithKeyboard(
            $chatId,
            "🔑 Повторите пароль\n\n",
            [[['text' => '⏮ Назад']]]
        );
    }

    private function askForRole($chatId): bool
    {
        return $this->telegramService->sendWithKeyboard(
            $chatId,
            "😁 Почти у цели.\n\nСкажите, вы сотрудник какого отдела.",
            [
                [['text' => '👨‍🍳 Сотрудник кухни']],
                [['text' => '🤵 Сотрудник зала']],
                [['text' => '⏮ Назад']],
            ]
        );
    }

    private function confirmUser($chatId, $data): bool
    {
        $text = "✅ Проверьте данные:\n" .
            "Номер: {$data['phone']}\n" .
            "Email: {$data['email']}\n" .
            "Вы: {$this->formatRole($data['role'])}\n\n" .
            "Все верно?";

        return $this->telegramService->sendWithKeyboard(
            $chatId,
            $text,
            [
                [['text' => '✅ Отправить']],
                [['text' => '⏮ Назад'], ['text' => '❌ Сначала']],
            ]
        );
    }

    // ==========================================
    // 5. ВСПОМОГАТЕЛЬНЫЕ МЕТОДЫ
    // ==========================================

    private function normalizePhone(string $phone): string|null
    {
        $clean = preg_replace('/[^0-9]/', '', $phone);

        if (strlen($clean) === 11 && $clean[0] === '8') {
            $clean = '7' . substr($clean, 1);
        }

        if (strlen($clean) === 10 && $clean[0] === '9') {
            $clean = '7' . $clean;
        }

        if (strlen($clean) === 11 && $clean[0] === '7') {
            return '+' . $clean[0] . ' (' . substr($clean, 1, 3) . ') '
                . substr($clean, 4, 3) . '-' . substr($clean, 7, 2) . '-' . substr($clean, 9, 2);
        }
        return null;
    }

    private function checkNumber(string $phone): bool
    {
        $patterns = [
            '/^\+7\d{10}$/',
            '/^8\d{10}$/',
            '/^9\d{9}$/',
            '/^\+7\s\(\d{3}\)\s\d{3}-\d{2}-\d{2}$/',
            '/^8\s\(\d{3}\)\s\d{3}-\d{2}-\d{2}$/',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $phone)) {
                return true;
            }
        }
        return false;
    }

    private function checkEmail(string $text): bool
    {
        $pattern = '/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Zа-яА-Я]{2,}$/u';
        return preg_match($pattern, $text) === 1;
    }

//    private function checkPassword(string $text): bool
//    {
//        $pattern = '/^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d!@#$%^&*()\-_=+{};:,<.>]{8,20}$/';
//        return preg_match($pattern, $text) === 1;
//    }

    private function checkRole(string $text): int|null
    {
        if ($text === '👨‍🍳 Сотрудник кухни') return 3;
        if ($text === '🤵 Сотрудник зала') return 4;
        return null;
    }

    private function formatRole(int $role): string
    {
        switch ($role) {
            case 3:
                return '👨‍🍳 Сотрудник кухни';
            case 4:
                return '🤵 Сотрудник зала';
            default:
                return 'Сотрудник';
        }
    }

    private function handleCancel($chatId): bool
    {
        session()->forget("user_{$chatId}");

        return $this->telegramService->sendWithRemoveKeyboard(
            $chatId,
            '❌ Регистрация отменена.'
        );
    }
}

<?php

namespace App\Telegram\Handlers;

use App\Services\UserRegistrationService;

class NewUserHandler
{
    public function __construct(readonly UserRegistrationService $userRegistrationService)
    {
    }

    public function handle($update)
    {
        $chatId = $update->message->chat->id ?? null;
        if (!$chatId) return;

        $sessionKey = "user_{$chatId}";

        // Проверяем, есть ли активная сессия
        if (session()->has($sessionKey)) {
            $data = session($sessionKey);
            // Если есть сессия, показываем текущий шаг
            return $this->showCurrentStep($chatId, $data);
        }

        // Начинаем новую регистрацию
        session([$sessionKey => ['step' => 1]]);

        return \TeleBot::sendMessage([
            'chat_id' => $chatId,
            'text' => "📱 Давайте зарегистрируемся.\n\nНапишите ваш телефон",
        ]);
    }

    public function handleMessage($message)
    {
        $chatId = $message->chat->id ?? null;
        if (!$chatId) return;

        $text = $message->text;
        $sessionKey = "user_{$chatId}";
        $data = session($sessionKey, ['step' => 1]);

        // ==========================================
        // 1. ОБРАБОТКА КНОПОК (всегда в первую очередь)
        // ==========================================

        if ($text === '✅ Отправить') {
            $data = array_merge($data, [
                'id' => $message->from->id,
                'first_name' => $message->from->first_name ?? null,
                'last_name' => $message->from->last_name ?? null,
                'username' => $message->from->username ?? null,
            ]);

            $this->userRegistrationService->createUser($data);
            session()->forget($sessionKey);

            return \TeleBot::sendMessage([
                'chat_id' => $chatId,
                'text' => "✅ Регистрация завершена!",
                'reply_markup' => ['remove_keyboard' => true],
            ]);
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

            return \TeleBot::sendMessage([
                'chat_id' => $chatId,
                'text' => '❌ Регистрация отменена.',
                'reply_markup' => ['remove_keyboard' => true],
            ]);
        }

        // ==========================================
        // 2. ОБРАБОТКА ШАГОВ
        // ==========================================

        $step = $data['step'] ?? 1;

        switch ($step) {
            case 1:
                return $this->processPhone($chatId, $text, $data);
            case 2:
                return $this->processEmail($chatId, $text, $data);
            case 3:
                return $this->processPassword($chatId, $text, $data);
            case 4:
                return $this->processPasswordConfirm($chatId, $text, $data);
            case 5:
                return $this->processRole($chatId, $text, $data);
            default:
                return $this->handleCancel($chatId);
        }
    }

    // ==========================================
    // 3. МЕТОДЫ ОБРАБОТКИ ШАГОВ
    // ==========================================

    private function processPhone($chatId, $text, $data)
    {
        // Валидация
        if (empty($text)) {
            return \TeleBot::sendMessage([
                'chat_id' => $chatId,
                'text' => '❌ Номер не может быть пустым. Попробуйте снова.',
            ]);
        }

        if (!$this->checkNumber($text)) {
            return \TeleBot::sendMessage([
                'chat_id' => $chatId,
                'text' => '❌ Некорректный номер. Попробуйте снова.',
            ]);
        }

        // Сохраняем и переходим на следующий шаг
        $formatNumber = $this->normalizePhone($text);
        if ($formatNumber) {
            $data['phone'] = $formatNumber;
            $data['step'] = 2;
            session(["user_{$chatId}" => $data]);

            // ПОСЛЕ СОХРАНЕНИЯ ВЫХОДИМ И ЖДЕМ СЛЕДУЮЩЕЕ СООБЩЕНИЕ
            return $this->askForEmail($chatId);
        }
    }

    private function processEmail($chatId, $text, $data)
    {
        // Валидация
        if (empty($text)) {
            return \TeleBot::sendMessage([
                'chat_id' => $chatId,
                'text' => '❌ Email не может быть пустым. Попробуйте снова.',
            ]);
        }

        if (!$this->checkEmail($text)) {
            return \TeleBot::sendMessage([
                'chat_id' => $chatId,
                'text' => '❌ Некорректный email. Попробуйте снова.',
            ]);
        }

        // Сохраняем и переходим на следующий шаг
        $data['email'] = $text;
        $data['step'] = 3;
        session(["user_{$chatId}" => $data]);

        // ПОСЛЕ СОХРАНЕНИЯ ВЫХОДИМ И ЖДЕМ СЛЕДУЮЩЕЕ СООБЩЕНИЕ
        return $this->askForPassword($chatId);
    }

    private function processPassword($chatId, $text, $data)
    {
        // Валидация
        if (empty($text)) {
            return \TeleBot::sendMessage([
                'chat_id' => $chatId,
                'text' => '❌ Пароль не может быть пустым. Попробуйте снова.',
            ]);
        }

        if (!$this->checkPassword($text)) {
            return \TeleBot::sendMessage([
                'chat_id' => $chatId,
                'text' => "❌ Некорректный пароль. Попробуйте снова.\n" .
                    "Минимум 8 символов, хотя бы одна буква и одна цифра.",
            ]);
        }

        // Сохраняем и переходим на следующий шаг
        $data['password'] = $text;
        $data['step'] = 4;
        session(["user_{$chatId}" => $data]);

        // ПОСЛЕ СОХРАНЕНИЯ ВЫХОДИМ И ЖДЕМ СЛЕДУЮЩЕЕ СООБЩЕНИЕ
        return $this->askForPasswordConfirm($chatId);
    }

    private function processPasswordConfirm($chatId, $text, $data)
    {
        // Проверяем, совпадает ли пароль
        if ($text !== ($data['password'] ?? null)) {
            return \TeleBot::sendMessage([
                'chat_id' => $chatId,
                'text' => '❌ Пароли не совпадают. Попробуйте снова.',
            ]);
        }

        // Переходим на следующий шаг
        $data['step'] = 5;
        session(["user_{$chatId}" => $data]);

        // ПОСЛЕ СОХРАНЕНИЯ ВЫХОДИМ И ЖДЕМ СЛЕДУЮЩЕЕ СООБЩЕНИЕ
        return $this->askForRole($chatId);
    }

    private function processRole($chatId, $text, $data)
    {
        // Проверяем роль
        $role = $this->checkRole($text);
        if (!$role) {
            return \TeleBot::sendMessage([
                'chat_id' => $chatId,
                'text' => '❌ Пожалуйста, выберите роль из кнопок ниже.',
                'reply_markup' => [
                    'keyboard' => [
                        [['text' => '👨‍🍳 Сотрудник кухни']],
                        [['text' => '🤵 Сотрудник зала']],
                        [['text' => '⏮ Назад']],
                    ],
                    'resize_keyboard' => true,
                ]
            ]);
        }

        // Сохраняем и показываем подтверждение
        $data['role'] = $role;
        $data['step'] = 6; // финальный шаг
        session(["user_{$chatId}" => $data]);

        // Показываем подтверждение
        return $this->confirmUser($chatId, $data);
    }

    // ==========================================
    // 4. МЕТОДЫ ДЛЯ ПОКАЗА ШАГОВ
    // ==========================================

    private function showCurrentStep($chatId, $data)
    {
        $step = $data['step'] ?? 1;

        switch ($step) {
            case 1:
                return \TeleBot::sendMessage([
                    'chat_id' => $chatId,
                    'text' => "📱 Напишите ваш телефон",
                ]);
            case 2:
                return $this->askForEmail($chatId);
            case 3:
                return $this->askForPassword($chatId);
            case 4:
                return $this->askForPasswordConfirm($chatId);
            case 5:
                return $this->askForRole($chatId);
            case 6:
                return $this->confirmUser($chatId, $data);
            default:
                return \TeleBot::sendMessage([
                    'chat_id' => $chatId,
                    'text' => 'Продолжите регистрацию',
                ]);
        }
    }

    private function askForEmail($chatId)
    {
        return \TeleBot::sendMessage([
            'chat_id' => $chatId,
            'text' => "🗄 Отлично!\n\nНапишите ваш email",
            'reply_markup' => [
                'keyboard' => [
                    [['text' => '⏮ Назад']],
                ],
                'resize_keyboard' => true,
            ]
        ]);
    }

    private function askForPassword($chatId)
    {
        return \TeleBot::sendMessage([
            'chat_id' => $chatId,
            'text' => "😉 Мы на полпути!\n\nПридумайте пароль",
            'reply_markup' => [
                'keyboard' => [
                    [['text' => '⏮ Назад']],
                ],
                'resize_keyboard' => true,
            ]
        ]);
    }

    private function askForPasswordConfirm($chatId)
    {
        return \TeleBot::sendMessage([
            'chat_id' => $chatId,
            'text' => "🔑 Повторите пароль\n\n",
            'reply_markup' => [
                'keyboard' => [
                    [['text' => '⏮ Назад']],
                ],
                'resize_keyboard' => true,
            ]
        ]);
    }

    private function askForRole($chatId)
    {
        return \TeleBot::sendMessage([
            'chat_id' => $chatId,
            'text' => "😁 Почти у цели.\n\nСкажите, вы сотрудник какого отдела.",
            'reply_markup' => [
                'keyboard' => [
                    [['text' => '👨‍🍳 Сотрудник кухни']],
                    [['text' => '🤵 Сотрудник зала']],
                    [['text' => '⏮ Назад']],
                ],
                'resize_keyboard' => true,
            ]
        ]);
    }

    private function confirmUser($chatId, $data)
    {
        $text = "✅ Проверьте данные:\n" .
            "Номер: {$data['phone']}\n" .
            "Email: {$data['email']}\n" .
            "Пароль: {$data['password']}\n" .
            "Вы: {$this->formatRole($data['role'])}\n\n" .
            "Все верно?";

        return \TeleBot::sendMessage([
            'chat_id' => $chatId,
            'text' => $text,
            'reply_markup' => [
                'keyboard' => [
                    [
                        ['text' => '✅ Отправить']
                    ],
                    [
                        ['text' => '⏮ Назад'],
                        ['text' => '❌ Сначала']
                    ]
                ],
                'resize_keyboard' => true,
            ],
        ]);
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

    private function checkPassword(string $text): bool
    {
        $pattern = '/^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d]{8,20}$/';
        return preg_match($pattern, $text) === 1;
    }

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

    private function handleCancel($chatId)
    {
        session()->forget("user_{$chatId}");

        return \TeleBot::sendMessage([
            'chat_id' => $chatId,
            'text' => '❌ Регистрация отменена.',
            'reply_markup' => ['remove_keyboard' => true],
        ]);
    }
}

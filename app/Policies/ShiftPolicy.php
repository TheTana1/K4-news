<?php

namespace App\Policies;

use App\Models\Shift;
use App\Models\User;

class ShiftPolicy
{
    /**
     * Видеть таблицу смен — все авторизованные.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Согласовывать смены — только админ/модератор.
     */
    public function approve(User $user): bool
    {
        return $user->isAdmin() || $user->isModerator();
    }

    /**
     * Сохранять смены (approved) — только админ/модератор.
     */
    public function saveAll(User $user): bool
    {
        return $user->isAdmin() || $user->isModerator();
    }

    /**
     * Отправить свою строку на проверку — любой авторизованный для себя.
     */
    public function submit(User $user, int $targetUserId): bool
    {
        return $user->id === $targetUserId;
    }
}

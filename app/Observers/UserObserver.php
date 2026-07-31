<?php

namespace App\Observers;

use App\Models\User;

class UserObserver
{
    /**
     * Handle the User "created" event.
     */
    public function created(User $user): void
    {
        info('Пользователь успешно создан: ', ['user_id' => $user->id, 'email' => $user->email]);
    }

    /**
     * Handle the User "updated" event.
     */
    public function updated(User $user): void
    {
        info('Пользователь успешно обновлён: ', ['user_id' => $user->id]);

    }

    /**
     * Handle the User "deleted" event.
     */
    public function deleted(User $user): void
    {
        info('Пользователь успешно удалён: ', ['user_id' => $user->id]);

    }

    /**
     * Handle the User "restored" event.
     */
    public function restored(User $user): void
    {
        //
    }

    /**
     * Handle the User "force deleted" event.
     */
    public function forceDeleted(User $user): void
    {
        //
    }
}

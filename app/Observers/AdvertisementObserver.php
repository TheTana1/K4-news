<?php

namespace App\Observers;

use App\Models\Advertisement;

class AdvertisementObserver
{
    /**
     * Handle the Advertisement "created" event.
     */
    public function created(Advertisement $advertisement): void
    {
        info('Объявление успешно создано: ', [
            'advertisement_id' => $advertisement->id,
            'user_id' => auth()->id()
        ]);

    }

    /**
     * Handle the Advertisement "updated" event.
     */
    public function updated(Advertisement $advertisement): void
    {
        info('Объявление успешно обновлено:', [
            'advertisement_id' => $advertisement->id,
            'user_id' => auth()->id()
        ]);
    }

    /**
     * Handle the Advertisement "deleted" event.
     */
    public function deleted(Advertisement $advertisement): void
    {
        info('Объявление успешно удалено:', [
            'advertisement_id' => $advertisement->id
        ]);
    }

    /**
     * Handle the Advertisement "restored" event.
     */
    public function restored(Advertisement $advertisement): void
    {
        //
    }

    /**
     * Handle the Advertisement "force deleted" event.
     */
    public function forceDeleted(Advertisement $advertisement): void
    {
        //
    }
}

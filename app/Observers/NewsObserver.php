<?php

namespace App\Observers;

use App\Models\News;

class NewsObserver
{
    /**
     * Handle the News "created" event.
     */
    public function created(News $news): void
    {
        info('Новость успешно создана: ', [
            'news_id' => $news->id,
            'content' => $news->content
        ]);
    }

    /**
     * Handle the News "updated" event.
     */
    public function updated(News $news): void
    {
        info('Новость успешно обновлена: ', [
            'news_id' => $news->id,
            'user_id' => auth()->id()
        ]);
    }

    /**
     * Handle the News "deleted" event.
     */
    public function deleted(News $news): void
    {
        info('Новость успешно удалена: ', [
            'news_id' => $news->id
        ]);
    }

    /**
     * Handle the News "restored" event.
     */
    public function restored(News $news): void
    {
        //
    }

    /**
     * Handle the News "force deleted" event.
     */
    public function forceDeleted(News $news): void
    {
        //
    }
}

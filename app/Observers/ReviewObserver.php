<?php

namespace App\Observers;

use App\Models\Review;

class ReviewObserver
{
    /**
     * Handle the Review "created" event.
     */
    public function created(Review $review): void
    {
        info('Отзыв успешно сохранён: ', [
            'review_id' => $review->id,
            'rating' => $review->rating,
            'content' => $review->content,
        ]);
    }

    /**
     * Handle the Review "updated" event.
     */
    public function updated(Review $review): void
    {

    }

    /**
     * Handle the Review "deleted" event.
     */
    public function deleted(Review $review): void
    {
        info('Отзыв успешно удалён: ', [
            'review_id' => $review->id,
            'rating' => $review->rating,
            'content' => $review->content,
        ]);
    }

    /**
     * Handle the Review "restored" event.
     */
    public function restored(Review $review): void
    {
        //
    }

    /**
     * Handle the Review "force deleted" event.
     */
    public function forceDeleted(Review $review): void
    {
        //
    }
}

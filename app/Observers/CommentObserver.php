<?php

namespace App\Observers;

use App\Models\Comment;

class CommentObserver
{
    /**
     * Handle the Comment "created" event.
     */
    public function created(Comment $comment): void
    {
        info('Комментарий успешно создан: ', [
            'comment_id' => $comment->id,
            'user_id' => $comment->user_id,
            'comment' => $comment->comment,
            'commentable_id' => $comment->commentable_id,
            'commentable_type' => $comment->commentable_type,


        ]);
    }

    /**
     * Handle the Comment "updated" event.
     */
    public function updated(Comment $comment): void
    {
        info('Комментарий успешно обновлён: ', [
            'comment_id' => $comment->id,
            'user_id' => $comment->user_id,
            'comment' => $comment->comment,
            'commentable_id' => $comment->commentable_id,
            'commentable_type' => $comment->commentable_type,
        ]);
    }

    /**
     * Handle the Comment "deleted" event.
     */
    public function deleted(Comment $comment): void
    {
        info('Комментарий успешно удалён: ', [
            'news_id' => $comment->id,
            'user_id' => $comment->user_id,
        ]);
    }

    /**
     * Handle the Comment "restored" event.
     */
    public function restored(Comment $comment): void
    {
        //
    }

    /**
     * Handle the Comment "force deleted" event.
     */
    public function forceDeleted(Comment $comment): void
    {
        //
    }
}

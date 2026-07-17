<?php

namespace App\Repositories;

use App\Models\Comment;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CommentRepository
{

    public function create(array $data, $parent): Comment
    {
        DB::beginTransaction();

        try {
            $comment = new Comment();
            $comment->comment = $data['comment'];
            $comment->user_id = Auth::id();
            $comment->commentable()->associate($parent);
            $comment->save();

            DB::commit();

            Log::info('Comment created successfully', [
                'comment_id' => $comment->id,
                'user_id' => $comment->user_id
            ]);

            return $comment;

        } catch (\Exception $exception) {
            DB::rollBack();
            Log::critical('Failed to create comment: ' . $exception->getMessage(), [
                'trace' => $exception->getTraceAsString()
            ]);
            throw $exception;
        }
    }

    public function update(Comment $comment, array $data): bool
    {
        DB::beginTransaction();

        try {
            $result = $comment->update($data);

            DB::commit();

            Log::info('Comment updated successfully', [
                'comment_id' => $comment->id,
                'user_id' => $comment->user_id
            ]);

            return $result;

        } catch (\Exception $exception) {
            DB::rollBack();
            Log::critical('Failed to update comment: ' . $exception->getMessage(), [
                'comment_id' => $comment->id,
                'user_id' => $comment->user_id,
                'trace' => $exception->getTraceAsString()
            ]);
            throw $exception;
        }
    }

    public function delete(Comment $comment): bool
    {
        DB::beginTransaction();

        try {
            $result = $comment->delete();

            DB::commit();

            Log::info('Comment deleted successfully', [
                'comment_id' => $comment->id,
                'user_id' => $comment->user_id
            ]);

            return $result;

        } catch (\Exception $exception) {
            DB::rollBack();
            Log::critical('Failed to delete comment: ' . $exception->getMessage(), [
                'comment_id' => $comment->id,
                'user_id' => $comment->user_id,
                'trace' => $exception->getTraceAsString()
            ]);
            throw $exception;
        }
    }
}

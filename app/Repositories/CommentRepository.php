<?php

namespace App\Repositories;

use App\Http\Requests\CommentRequest;

use App\Models\Comment;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CommentRepository
{

    public function store(CommentRequest $request)
    {

        $validated = $request->validated();

        $model = match ($request->commentable_type) {
            'advertisement' => \App\Models\Advertisement::findOrfail($request->commentable_id),
            'news' => \App\Models\News::findOrfail($request->commentable_id),
            'review' => \App\Models\Review::findOrFail($request->commentable_id),
            default => null
        };
        $comment = Comment::query()->create([
            'user_id' => Auth::id(),
            'comment' => $validated['comment'],
            'commentable_id' => $validated['commentable_id'],
            'commentable_type' => $validated['commentable_type'],
        ]);
        $model->comments()->save($comment);
        return $comment;
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

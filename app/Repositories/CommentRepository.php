<?php

namespace App\Repositories;

use App\Http\Requests\CommentRequest;

use App\Models\Comment;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class CommentRepository
{

    public function store(CommentRequest $request)
    {
        DB::beginTransaction();
        try {
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
            DB::commit();

            return $comment;
        } catch (\Exception $exception) {
            DB::rollBack();
            Log::critical('Ошибка при создании комментария: '.$exception->getMessage(),[
                'comment_id' => $comment->id,
                'user_id' => $comment->user_id,
                'comment' => $comment->comment,
                'commentable_id' => $comment->commentable_id,
                'commentable_type' => $comment->commentable_type,
            ]);
            throw new BadRequestHttpException('Ошибка при создании комментария: ' . $exception->getMessage());
        }
    }

    public function update(Comment $comment, array $data): bool
    {
        DB::beginTransaction();

        try {
            $result = $comment->update($data);

            DB::commit();

            return $result;

        } catch (\Exception $exception) {
            DB::rollBack();
            Log::critical('Ошибка при обновлении комментария: ' . $exception->getMessage(), [
                'comment_id' => $comment->id,
                'user_id' => $comment->user_id,
                'comment' => $comment->comment,
                'commentable_id' => $comment->commentable_id,
                'commentable_type' => $comment->commentable_type,
            ]);
            throw new BadRequestHttpException('Ошибка при обновлении комментария: ' . $exception->getMessage());
        }
    }

    public function delete(Comment $comment): bool
    {
        DB::beginTransaction();

        try {
            $result = $comment->delete();

            DB::commit();

            return $result;

        } catch (\Exception $exception) {
            DB::rollBack();
            Log::critical('Ошибка при удалении комментария: ' . $exception->getMessage(), [
                'comment_id' => $comment->id,
                'user_id' => $comment->user_id,
                'comment' => $comment->comment,
                'commentable_id' => $comment->commentable_id,
                'commentable_type' => $comment->commentable_type,
            ]);
            throw new BadRequestHttpException('Ошибка при удалении комментария: ' . $exception->getMessage());

        }
    }
}

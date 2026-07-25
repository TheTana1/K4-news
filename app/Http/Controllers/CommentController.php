<?php

namespace App\Http\Controllers;

use App\Http\Requests\CommentRequest;
use App\Models\Comment;
use App\Repositories\CommentRepository;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;

class CommentController extends Controller
{
    public function __construct(
        readonly CommentRepository $commentRepository
    ) {}

    public function show(Comment $comment)
    {
        $model = $comment->commentable_type;
        $responseClass= strtolower(class_basename($model));
        $object =  $model::find($comment->commentable_id);
        if($responseClass === 'news')
            return view($responseClass.'.show', [$responseClass => $object]);
        return view($responseClass.'s.show', [$responseClass => $object]);
    }
    public function store(CommentRequest $request)
    {
        try {

            $newComment = $this->commentRepository->store($request);

            return redirect()->route("{$newComment->commentable_type}.show", $newComment->commentable_id)
                ->with('success', 'Комментарий добавлен.');

        } catch (\Exception $e) {
            Log::error('Comment creation error: ' . $e->getMessage());
            return back()->with('error', 'Ошибка при создании комментария.');
        }
    }

    public function update(CommentRequest $request, Comment $comment)
    {
        // Проверка прав CommentPolicy
        if (!Gate::allows('update', $comment)) {
            abort(403, 'У вас нет прав на редактирование этого комментария.');
        }

        try {
            $result = $this->commentRepository->update(
                $comment,
                $request->validated()
            );

            if (!$result) {
                return back()->with('error', 'Не удалось обновить комментарий.');
            }

            return back()->with('success', 'Комментарий обновлён.');

        } catch (\Exception $e) {
            return back()->with('error', 'Ошибка при обновлении комментария.');
        }
    }

    public function destroy(Comment $comment)
    {
        // Проверка прав CommentPolicy
        if (!Gate::allows('delete', $comment)) {
            abort(403, 'У вас нет прав на удаление этого комментария.');
        }

        try {
            $result = $this->commentRepository->delete($comment);

            if (!$result) {
                return back()->with('error', 'Не удалось удалить комментарий.');
            }

            return back()->with('success', 'Комментарий удалён.');

        } catch (\Exception $e) {
            return back()->with('error', 'Ошибка при удалении комментария.');
        }
    }
}

@extends('layouts.app')

@section('title', 'Отзыв')

@section('content')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Главная</a></li>
            <li class="breadcrumb-item"><a href="{{ route('reviews.index') }}">Отзывы</a></li>
            <li class="breadcrumb-item active" aria-current="page">Отзыв #{{ $review->id }}</li>
        </ol>
    </nav>

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Отзыв</h5>
            @auth
                <div>
                    <a href="{{ route('reviews.edit', $review) }}" class="btn btn-sm btn-success">Редактировать</a>
                    <form action="{{ route('reviews.destroy', $review) }}" method="POST" class="d-inline">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Удалить отзыв?')">Удалить</button>
                    </form>
                </div>
            @endauth
        </div>
        <div class="card-body">
            <!-- Информация об авторе -->
            <div class="d-flex flex-wrap align-items-start mb-4">
                <div class="flex-grow-1">
                    <h6 class="mb-0">
                        {{ $review->user?->name ?? $review->author_name ?? 'Гость' }}
                        @if($review->user && $review->user->is_active_in_group)
                            <span class="badge bg-success ms-2">В группе</span>
                        @endif
                    </h6>
                    <small class="text-muted">{{ $review->created_at->format('d.m.Y H:i') }}</small>
                </div>
                @if($review->rating)
                    <div class="text-warning" style="font-size: 1.5rem;">
                        @for($i = 1; $i <= 5; $i++)
                            <span>{{ $i <= $review->rating ? '★' : '☆' }}</span>
                        @endfor
                    </div>
                @endif
            </div>

            <!-- Текст отзыва -->
            <div class="mb-4" style="white-space: pre-line;">
                {{ $review->content }}
            </div>

            <!-- Комментарии -->
            <div class="mt-4 pt-3 border-top">
                <h6 class="mb-3">Комментарии</h6>

                @auth
                    <form action="{{ route('comments.store') }}" method="POST" class="mb-4">
                        @csrf
                        <input type="hidden" name="commentable_id" value="{{ $review->id }}">
                        <input type="hidden" name="commentable_type" value="review">
                        <div class="mb-2">
                            <textarea name="comment" rows="3" class="form-control" placeholder="Ваш комментарий..."></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary btn-sm">Отправить</button>
                    </form>
                @endauth

                @forelse($review->comments as $comment)
                    <div class="border rounded p-3 mb-2">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <strong>{{ $comment->user?->name ?? 'Гость' }}</strong>
                                <small class="text-muted ms-2">{{ $comment->created_at->diffForHumans() }}</small>
                            </div>
                            <div>
                                @can('update', $comment)
                                    <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editCommentModal{{ $comment->id }}">
                                        Ред.
                                    </button>
                                @endcan
                                @can('delete', $comment)
                                    <form action="{{ route('comments.destroy', $comment) }}" method="POST" class="d-inline">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Удалить комментарий?')">Удалить</button>
                                    </form>
                                @endcan
                            </div>
                        </div>
                        <p class="mt-1 mb-0">{{ $comment->comment }}</p>
                    </div>

                    <!-- Модальное окно для редактирования комментария -->
                    <div class="modal fade" id="editCommentModal{{ $comment->id }}" tabindex="-1" aria-labelledby="editCommentModalLabel{{ $comment->id }}" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="editCommentModalLabel{{ $comment->id }}">Редактировать комментарий</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <form action="{{ route('comments.update', $comment) }}" method="POST">
                                    @csrf @method('PUT')
                                    <div class="modal-body">
                                        <div class="mb-3">
                                            <label for="comment-{{ $comment->id }}" class="form-label">Текст комментария</label>
                                            <textarea name="comment" id="comment-{{ $comment->id }}" rows="4"
                                                      class="form-control @error('comment') is-invalid @enderror">{{ old('comment', $comment->comment) }}</textarea>
                                            @error('comment') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                                        <button type="submit" class="btn btn-primary">Сохранить</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                @empty
                    <p class="text-muted">Комментариев пока нет</p>
                @endforelse
            </div>
        </div>
    </div>
@endsection

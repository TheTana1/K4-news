@extends('layouts.app')

@section('title', Str::limit($advertisement->title, 30))

@section('content')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Главная</a></li>
            <li class="breadcrumb-item"><a href="{{ route('advertisements.index') }}">Объявления</a></li>
            <li class="breadcrumb-item active" aria-current="page">{{ Str::limit($advertisement->title, 20) }}</li>
        </ol>
    </nav>

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">{{ $advertisement->title }}</h5>
            <div>
                <a href="{{ route('advertisements.edit', $advertisement) }}" class="btn btn-sm btn-success">Редактировать</a>
                <form action="{{ route('advertisements.destroy', $advertisement) }}" method="POST" class="d-inline">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Удалить?')">Удалить</button>
                </form>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-8">
                    <p class="card-text" style="white-space: pre-line;">{{ $advertisement->content }}</p>
                    @if($advertisement->price)
                        <div class="alert alert-info mt-3">
                            <strong>Цена:</strong> {{ number_format($advertisement->price, 0, ',', ' ') }} ₽
                        </div>
                    @endif
                </div>
                <div class="col-md-4">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between">
                            <span class="text-muted">Автор:</span>
                            <span>{{ $advertisement->author?->name ?? $advertisement->telegram_author_name ?? 'Неизвестно' }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <span class="text-muted">Статус:</span>
                            @if($advertisement->status === 'active')
                                <span class="badge bg-success">Активно</span>
                            @else
                                <span class="badge bg-secondary">Не активно</span>
                            @endif
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <span class="text-muted">Просмотров:</span>
                            <span>{{ $advertisement->views ?? 0 }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <span class="text-muted">Создано:</span>
                            <span>{{ $advertisement->created_at->format('d.m.Y H:i') }}</span>
                        </li>
                        @if($advertisement->published_at)
                            <li class="list-group-item d-flex justify-content-between">
                                <span class="text-muted">Опубликовано:</span>
                                <span>{{ $advertisement->published_at->format('d.m.Y H:i') }}</span>
                            </li>
                        @endif
                        @if($advertisement->city)
                            <li class="list-group-item d-flex justify-content-between">
                                <span class="text-muted">Город:</span>
                                <span>{{ $advertisement->city }}</span>
                            </li>
                        @endif
                    </ul>
                </div>
            </div>

            <!-- Комментарии -->
            <div class="mt-4 pt-3 border-top">
                <h6 class="mb-3">Комментарии</h6>

                @auth
                    <form action="{{ route('comments.store') }}" method="POST" class="mb-4">
                        @csrf
                        <input type="hidden" name="commentable_id" value="{{ $advertisement->id }}">
                        <input type="hidden" name="commentable_type" value="advertisement">
                        <div class="mb-2">
                            <textarea name="comment" rows="3" class="form-control" placeholder="Ваш комментарий..."></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary btn-sm">Отправить</button>
                    </form>
                @endauth

                @forelse($advertisement->comments as $comment)
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

@extends('layouts.app')

@section('title', Str::limit($news->title ?? 'Новость', 30))

@section('content')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Главная</a></li>
            <li class="breadcrumb-item"><a href="{{ route('news.index') }}">Новости</a></li>
            <li class="breadcrumb-item active" aria-current="page">{{ Str::limit($news->content ?? 'Новость', 10) }}</li>
        </ol>
    </nav>

    <div class="row g-4">
        <!-- Основная информация -->
        <div class="col-lg-12">
            <div class="row g-4">
                <!-- Колонка 1: Информация о новости -->
                <div class="col-md-4">
                    <div class="card h-100">
                        <div class="card-header">
                            <i class="bi bi-info-circle me-1"></i> Информация
                        </div>
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <span class="text-muted">
                                    <i class="bi bi-person me-1"></i> Автор
                                </span>
                                <span>{{ $news->author?->name ?? $news->telegram_author_name ?? 'Неизвестно' }}</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <span class="text-muted">
                                    <i class="bi bi-clock-history me-1"></i> Создано
                                </span>
                                <span>{{ $news->created_at->format('d.m.Y H:i') }}</span>
                            </li>
                            @if($news->updated_at && $news->updated_at != $news->created_at)
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <span class="text-muted">
                                        <i class="bi bi-pencil-square me-1"></i> Обновлено
                                    </span>
                                    <span>{{ $news->updated_at->format('d.m.Y H:i') }}</span>
                                </li>
                            @endif
                            @if($news->published_at)
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <span class="text-muted">
                                        <i class="bi bi-calendar-event me-1"></i> Дата публикации
                                    </span>
                                    <span>{{ $news->published_at->format('d.m.Y H:i') }}</span>
                                </li>
                            @endif
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <span class="text-muted">
                                    <i class="bi bi-eye me-1"></i> Просмотры
                                </span>
                                <span>{{ $news->views ?? 0 }}</span>
                            </li>
                            @if($news->telegram_message_id)
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <span class="text-muted">
                                        <i class="bi bi-telegram me-1"></i> ID сообщения
                                    </span>
                                    <span>{{ $news->telegram_message_id }}</span>
                                </li>
                            @endif
                            @if($news->author)
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <span class="text-muted">
                                        <i class="bi bi-envelope me-1"></i> Контакт
                                    </span>
                                    <a href="mailto:{{ $news->author->email }}">{{ $news->author->email }}</a>
                                </li>
                            @endif
                        </ul>
                    </div>
                </div>

                <!-- Колонка 2: Содержание -->
                <div class="col-md-8">
                    <div class="card h-100">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <span><i class="bi bi-file-text me-1"></i> Содержание</span>
                            <div>
                                <a href="{{ route('news.edit', $news) }}" class="btn btn-sm btn-success">
                                    <i class="bi bi-pencil me-1"></i> Редактировать
                                </a>
                                <form action="{{ route('news.destroy', $news) }}" method="POST" class="d-inline">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Удалить новость?')">
                                        <i class="bi bi-trash me-1"></i> Удалить
                                    </button>
                                </form>
                            </div>
                        </div>
                        <div class="card-body">
                            @if($news->file_path && $news->file_name)
                                @php
                                    $fileUrl = Storage::disk($news->disk ?? 'public')->url($news->file_path);
                                    $isImage = str_starts_with($news->mime_type ?? '', 'image/');
                                @endphp
                                @if($isImage)
                                    <div class="mb-4 text-center">
                                        <img src="{{ $fileUrl }}"
                                             alt="{{ $news->file_name }}"
                                             class="img-fluid rounded"
                                             style="max-height: 400px; width: auto; object-fit: contain;">
                                    </div>
                                @endif
                            @endif

                            <p class="card-text" style="white-space: pre-line;">{{ $news->content ?? 'Содержание отсутствует' }}</p>

                            @if($news->file_path && $news->file_name)
                                @php
                                    $fileUrl = Storage::disk($news->disk ?? 'public')->url($news->file_path);
                                    $isImage = str_starts_with($news->mime_type ?? '', 'image/');
                                    $fileSize = isset($news->file_size) ? number_format($news->file_size / 1024, 1) . ' KB' : 'Н/Д';
                                @endphp
                                @if(!$isImage)
                                    <div class="mt-3">
                                        <a href="{{ $fileUrl }}" target="_blank" class="btn btn-outline-primary btn-sm">
                                            <i class="bi bi-download me-1"></i> Скачать файл: {{ $news->file_name }}
                                            @if(isset($news->file_size))
                                                <span class="badge bg-secondary ms-1">{{ $fileSize }}</span>
                                            @endif
                                        </a>
                                    </div>
                                @endif
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Дополнительные файлы (если есть связь с файлами) -->
        @if($news->files && $news->files->count() > 0)
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div>
                            <i class="bi bi-paperclip me-1"></i> Прикрепленные файлы
                        </div>
                        <span class="badge bg-primary rounded-pill">{{ $news->files->count() }}</span>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            @foreach($news->files as $file)
                                @php
                                    $mimeType = $file->mime_type;
                                    $isImage = str_starts_with($mimeType, 'image/');
                                    $isPdf = $mimeType === 'application/pdf';
                                    $isTxt = $mimeType === 'text/plain';
                                    $fileName = $file->file_name;
                                    $fileUrl = Storage::disk($file->disk ?? 'public')->url($file->file_path);
                                    $fileSize = isset($file->file_size) ? number_format($file->file_size / 1024, 1) . ' KB' : 'Н/Д';
                                @endphp
                                <div class="col-md-3 col-sm-6">
                                    <div class="card h-100 text-center">
                                        <div class="card-body p-3">
                                            @if($isImage)
                                                <a href="{{ $fileUrl }}" target="_blank" class="d-block">
                                                    <img src="{{ $fileUrl }}"
                                                         alt="{{ $fileName }}"
                                                         class="img-fluid rounded"
                                                         style="max-height: 150px; width: 100%; object-fit: cover;">
                                                </a>
                                            @elseif($isPdf)
                                                <div class="display-1 text-danger">
                                                    <i class="bi bi-file-pdf"></i>
                                                </div>
                                            @elseif($isTxt)
                                                <div class="display-1 text-primary">
                                                    <i class="bi bi-file-text"></i>
                                                </div>
                                            @else
                                                <div class="display-1 text-secondary">
                                                    <i class="bi bi-file-earmark"></i>
                                                </div>
                                            @endif

                                            <a href="{{ $fileUrl }}" target="_blank" class="text-decoration-none">
                                                <small class="d-block text-truncate mt-2">{{ $fileName }}</small>
                                            </a>
                                            <small class="text-muted d-block">{{ $fileSize }}</small>

                                            @can('delete', $file)
                                                <form action="{{ route('news.files.destroy', [$news, $file]) }}"
                                                      method="POST"
                                                      class="d-inline mt-2">
                                                    @csrf @method('DELETE')
                                                    <button type="submit"
                                                            class="btn btn-sm btn-outline-danger"
                                                            onclick="return confirm('Удалить файл?')">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </form>
                                            @endcan
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- Комментарии -->
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <i class="bi bi-chat me-1"></i> Комментарии
                    </div>
                    <span class="badge bg-primary rounded-pill">{{ $news->comments->count() }}</span>
                </div>
                <div class="card-body">
                    @auth
                        <form action="{{ route('comments.store') }}" method="POST" class="mb-4">
                            @csrf
                            <input type="hidden" name="commentable_id" value="{{ $news->id }}">
                            <input type="hidden" name="commentable_type" value="news">
                            <div class="mb-2">
                                <textarea name="comment" rows="3" class="form-control" placeholder="Ваш комментарий..."></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary btn-sm">
                                <i class="bi bi-send me-1"></i> Отправить
                            </button>
                        </form>
                    @endauth

                    @if($news->comments->count() > 0)
                        <div class="list-group">
                            @foreach($news->comments as $comment)
                                <div class="list-group-item list-group-item-action flex-column align-items-start">
                                    <div class="d-flex w-100 justify-content-between align-items-center">
                                        <div>
                                            <strong>{{ $comment->user?->name ?? 'Гость' }}</strong>
                                            <small class="text-muted ms-2">{{ $comment->created_at->diffForHumans() }}</small>
                                        </div>
                                        <div>
                                            @can('update', $comment)
                                                <button type="button"
                                                        class="btn btn-sm btn-outline-primary"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#editCommentModal{{ $comment->id }}">
                                                    <i class="bi bi-pencil"></i>
                                                </button>
                                            @endcan
                                            @can('delete', $comment)
                                                <form action="{{ route('comments.destroy', $comment) }}" method="POST" class="d-inline">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Удалить комментарий?')">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
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
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="bi bi-chat-dots display-4 text-muted"></i>
                            <p class="text-muted mt-3 mb-0">Комментариев пока нет</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

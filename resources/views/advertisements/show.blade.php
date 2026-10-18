@extends('layouts.app')

@section('title', Str::limit($advertisement->content, 30))

@section('content')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Главная</a></li>
            <li class="breadcrumb-item"><a href="{{ route('advertisements.index') }}">Объявления</a></li>
            <li class="breadcrumb-item active"
                aria-current="page">{{ Str::limit($advertisement->content ?? 'Объявление', 10) }}</li>
        </ol>
    </nav>

    <div class="row g-4">
        <!-- Три колонки сверху -->
        <div class="col-lg-12">
            <div class="row g-4">
                <!-- Колонка 1: Информация об объявлении -->
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
                                <span>{{ $advertisement->user->name ?? 'Руководство' }}</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <span class="text-muted">
                                    <i class="bi bi-person me-1"></i> Кому
                                </span>
                                @php
                                    $roleSlug = $advertisement->role?->slug;

                                    $spanLabel = match($roleSlug) {

                                        'admin' => 'Администраторам',
                                        'moderator' => 'Менеджерам',
                                        'Kitchen_Staff' => 'Сотрудникам Кухни',
                                        'Service_Staff' => 'Сотрудникам Зала',
                                        'Bar_Staff' => 'Сотрудникам Бара',
                                        'Cleaning_Staff' => 'Сотрудникам Клининга',
                                        'Tech_Service' => 'Сотрудникам Техслужб',
                                        default => 'Всем',
                                    };

                                    $spanColor = match($roleSlug) {
                                        'admin' => "#8a008c",
                                        'moderator' => "#0D6EFD",
                                        'Kitchen_Staff' => "#a40e13",
                                        'Service_Staff' => "#ff661b",
                                        'Bar_Staff' => "#228B22",
                                        'Cleaning_Staff' => "#7B68EE",
                                        'Tech_Service' => "#B8860B",
                                        default => "#A9A9A9",
                                    };
                                @endphp
                                <span style="color: {{ $spanColor }}">
                                    {{ $spanLabel }}
                                </span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <span class="text-muted">
                                    <i class="bi bi-tag me-1"></i> Статус
                                </span>
                                @if($advertisement->status === 'active')
                                    <span class="badge bg-success">Активно</span>
                                @else
                                    <span class="badge bg-secondary">Не активно</span>
                                @endif
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <span class="text-muted">
                                    <i class="bi bi-calendar-event me-1"></i> Дата публикации
                                </span>
                                <span>{{ local_date($advertisement->created_at) }}</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <span class="text-muted">
                                    <i class="bi bi-clock-history me-1"></i> Создано
                                </span>
                                <span>{{ local_date($advertisement->created_at) }}</span>
                            </li>
                            @if($advertisement->updated_at && $advertisement->updated_at != $advertisement->created_at)
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <span class="text-muted">
                                        <i class="bi bi-pencil-square me-1"></i> Обновлено
                                    </span>
                                    <span>{{ local_date($advertisement->updated_at) }}</span>
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
                                @can('update', $advertisement)
                                    <a href="{{ route('advertisements.edit', $advertisement) }}"
                                       class="btn btn-sm btn-success">
                                        <i class="bi bi-pencil me-1"></i> Редактировать
                                    </a>
                                @endcan
                                @can('delete', $advertisement)
                                    <form action="{{ route('advertisements.destroy', $advertisement) }}" method="POST"
                                          class="d-inline">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger"
                                                onclick="return confirm('Удалить новость?')">
                                            <i class="bi bi-trash me-1"></i> Удалить
                                        </button>
                                    </form>
                                @endcan
                            </div>
                        </div>
                        <div class="card-body">


                            <p class="card-text"
                               style="white-space: pre-line;">{{ $advertisement->content ?? 'Содержание отсутствует' }}</p>

                        </div>
                    </div>
                </div>
            </div>
        </div>
        {{-- Просмотры --}}
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <i class="bi bi-eye me-1"></i> Просмотрели
                    </div>
                    <span class="badge bg-primary rounded-pill">{{ $viewers->count() }}</span>
                </div>

                <div class="card-body">
                    @if($viewers->isNotEmpty())
                        <div class="d-flex align-items-center flex-wrap gap-3">

                            {{-- Стопка аватаров --}}
                            <div class="d-flex align-items-center flex-nowrap overflow-auto"
                                 style="max-width: 100%; gap: 0;">
                                @foreach($viewers as $viewer)
                                    <div class="position-relative"
                                         style="
                                             flex-shrink: 0;
                                             {{ !$loop->first ? 'margin-left: -8px;' : '' }}
                                             z-index: 1;
                                             transition: transform .2s, z-index .2s;
                                         "
                                         onmouseover="this.style.transform='translateY(-3px) scale(1.1)'; this.style.zIndex=10;"
                                         onmouseout="this.style.transform=''; this.style.zIndex=1;"
                                         data-bs-toggle="tooltip"
                                         data-bs-placement="top"
                                         title="{{ $viewer->name }} ({{ $viewer->role?->label ?? 'Сотрудник' }})@if($viewer->pivot?->created_at) — {{ $viewer->pivot->created_at->diffForHumans() }}@endif">

                                        @if($viewer->avatar_path)
                                            <img src="{{ asset($viewer->avatar_path) }}" alt="Ошибка отображения аватара"
                                                 class="rounded-circle border border-2 border-body shadow-sm"
                                                 width="32" height="32"
                                                 style="object-fit: cover;">
                                        @else
                                            @php
                                                $colors = ['primary', 'secondary', 'success', 'danger', 'warning', 'info', 'dark'];
                                                $color  = $colors[abs(crc32($viewer->name)) % count($colors)];
                                            @endphp
                                            <div class="rounded-circle d-flex align-items-center justify-content-center text-white fw-bold border border-2 border-body shadow-sm bg-{{ $color }}"
                                                 style="width:32px;height:32px;font-size:.75rem;">
                                                {{ mb_strtoupper(mb_substr($viewer->name, 0, 1)) }}
                                            </div>
                                        @endif
                                    </div>
                                @endforeach

                                {{-- Плашка +N --}}
                                @if($viewers->count() > 10)
                                    <div class="position-relative"
                                         style="margin-left: -8px; z-index: 1; transition: transform .2s, z-index .2s;"
                                         onmouseover="this.style.transform='translateY(-3px) scale(1.1)'; this.style.zIndex=10;"
                                         onmouseout="this.style.transform=''; this.style.zIndex=1;"
                                         data-bs-toggle="tooltip"
                                         data-bs-placement="top"
                                         title="{{ $viewers->count() - 10 }} ещё человек(а)">
                                        <div class="rounded-circle d-flex align-items-center justify-content-center fw-bold border border-2 border-body shadow-sm bg-body-secondary text-body"
                                             style="width:32px;height:32px;font-size:.75rem;">
                                            +{{ $viewers->count() - 10 }}
                                        </div>
                                    </div>
                                @endif
                            </div>

                            {{-- Общее количество --}}
                            <span class="text-muted small">
                        {{ $viewers->count() }} чел.
                    </span>
                        </div>
                    @else
                        <div class="text-center py-3">
                            <i class="bi bi-eye-slash display-6 text-muted"></i>
                            <p class="text-muted mt-2 mb-0 small">Никто ещё не просмотрел</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        @push('scripts')
            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(function (el) {
                        new bootstrap.Tooltip(el, { trigger: 'hover focus' });
                    });
                });
            </script>
        @endpush
        <!-- Файлы -->
        @if($advertisement->files && $advertisement->files->count() > 0)
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div>
                            <i class="bi bi-paperclip me-1"></i> Прикрепленные файлы
                        </div>
                        <span class="badge bg-primary rounded-pill">{{ $advertisement->files->count() }}</span>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            @foreach($advertisement->files as $file)
                                @php
                                    $mimeType = $file->mime_type;
                                    $isImage = str_starts_with($mimeType, 'image/');
                                    $isPdf = $mimeType === 'application/pdf';
                                    $isTxt = $mimeType === 'text/plain';
                                    $fileName = $file->file_name;
                                    $fileUrl = Storage::disk('public')->url($file->file_path);
                                    $fileSize = number_format($file->file_size / 1024, 1) . ' KB';
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
                                                <form
                                                    action="{{ route('advertisements.files.destroy', [$advertisement, $file]) }}"
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
                    <span class="badge bg-primary rounded-pill">{{ $comments->count() }}</span>
                </div>
                <div class="card-body">
                    @auth
                        <form action="{{ route('comments.store') }}" method="POST" class="mb-4">
                            @csrf
                            <input type="hidden" name="commentable_id" value="{{ $advertisement->id }}">
                            <input type="hidden" name="commentable_type" value="advertisement">
                            <div class="mb-2">
                                <textarea name="comment" rows="3" class="form-control"
                                          placeholder="Ваш комментарий..."></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary btn-sm">
                                <i class="bi bi-send me-1"></i> Отправить
                            </button>
                        </form>
                    @endauth

                    @if($comments->count() > 0)
                        <div class="list-group">
                            @foreach($comments as $comment)
                                <div class="list-group-item list-group-item-action flex-column align-items-start">
                                    <div class="d-flex w-100 justify-content-between align-items-center">
                                        <div>
                                            <strong>{{ $comment->user?->name ?? 'Гость' }}</strong>
                                            <small
                                                class="text-muted ms-2">{{ $comment->created_at->diffForHumans() }}</small>
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
                                                <form action="{{ route('comments.destroy', $comment) }}" method="POST"
                                                      class="d-inline">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger"
                                                            onclick="return confirm('Удалить комментарий?')">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </form>
                                            @endcan
                                        </div>
                                    </div>
                                    <p class="mt-1 mb-0">{{ $comment->comment }}</p>
                                </div>

                                <!-- Модальное окно для редактирования комментария -->
                                <div class="modal fade" id="editCommentModal{{ $comment->id }}" tabindex="-1"
                                     aria-labelledby="editCommentModalLabel{{ $comment->id }}" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="editCommentModalLabel{{ $comment->id }}">
                                                    Редактировать комментарий</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                        aria-label="Close"></button>
                                            </div>
                                            <form action="{{ route('comments.update', $comment) }}" method="POST">
                                                @csrf @method('PUT')
                                                <div class="modal-body">
                                                    <div class="mb-3">
                                                        <label for="comment-{{ $comment->id }}" class="form-label">Текст
                                                            комментария</label>
                                                        <textarea name="comment" id="comment-{{ $comment->id }}"
                                                                  rows="4"
                                                                  class="form-control @error('comment') is-invalid @enderror">{{ old('comment', $comment->comment) }}</textarea>
                                                        @error('comment')
                                                        <div class="invalid-feedback">{{ $message }}</div> @enderror
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary"
                                                            data-bs-dismiss="modal">Отмена
                                                    </button>
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
                @if($comments->hasPages())
                    <div class="card-footer">
                        {{ $comments->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>

@endsection

@extends('layouts.app')

@section('title', 'Редактировать объявление')

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="mb-3">
                <a href="{{ route('advertisements.show', $advertisement) }}" class="btn btn-outline-secondary btn-sm">
                    &larr; Назад к объявлению
                </a>
            </div>

            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Редактировать объявление</h5>
                </div>
                <div class="card-body">
                    @if ($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <strong>Ошибка!</strong> Пожалуйста, исправьте следующие ошибки:
                            <ul class="mb-0 mt-2">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <form action="{{ route('advertisements.update', $advertisement) }}" method="POST" enctype="multipart/form-data">
                        @csrf @method('PUT')

                        <!-- Содержание -->
                        <div class="mb-3">
                            <label for="content" class="form-label">Содержание <span class="text-danger">*</span></label>
                            <textarea name="content"
                                      id="content"
                                      rows="8"
                                      class="form-control @error('content') is-invalid @enderror"
                                      placeholder="Опишите ваше объявление...">{{ old('content', $advertisement->content) }}</textarea>
                            @error('content')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Минимум 10 символов</small>
                        </div>

                        <!-- Статус -->
                        <div class="mb-3">
                            <label for="status" class="form-label">Статус</label>
                            <select name="status" id="status" class="form-select @error('status') is-invalid @enderror">
                                <option value="active" {{ old('status', $advertisement->status) == 'active' ? 'selected' : '' }}>Активно</option>
                                <option value="inactive" {{ old('status', $advertisement->status) == 'inactive' ? 'selected' : '' }}>Не активно</option>
                            </select>
                            @error('status')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Добавление новых файлов -->
                        <div class="mb-3">
                            <label for="files" class="form-label">Добавить файлы</label>
                            <input type="file"
                                   name="files[]"
                                   id="files"
                                   multiple
                                   accept=".pdf,.txt,.jpg,.jpeg,.png,.gif,.bmp,.webp,.svg"
                                   class="form-control @error('files.*') is-invalid @enderror">

                            @if ($errors->has('files'))
                                <div class="invalid-feedback d-block">
                                    {{ $errors->first('files') }}
                                </div>
                            @endif

                            @if ($errors->has('files.*'))
                                <div class="invalid-feedback d-block">
                                    @foreach ($errors->get('files.*') as $error)
                                        @if (is_array($error))
                                            @foreach ($error as $message)
                                                <div>{{ $message }}</div>
                                            @endforeach
                                        @else
                                            <div>{{ $error }}</div>
                                        @endif
                                    @endforeach
                                </div>
                            @endif

                            <small class="text-muted">Можно загрузить: PDF, TXT, изображения (JPG, PNG, GIF, BMP, WEBP, SVG). Максимум 10MB</small>
                            <div id="fileList" class="mt-2"></div>
                        </div>

                        <!-- Существующие файлы -->
                        @if($advertisement->files && $advertisement->files->count() > 0)
                            <div class="mb-3">
                                <label class="form-label">Существующие файлы</label>
                                <div class="row g-2">
                                    @foreach($advertisement->files as $file)
                                        @php
                                            $mimeType = $file->mime_type;
                                            $isImage = str_starts_with($mimeType, 'image/');
                                            $fileSize = number_format($file->file_size / 1024, 1) . ' KB';
                                            $fileUrl = Storage::disk('public')->url($file->file_path);
                                        @endphp
                                        <div class="col-md-4 col-sm-6">
                                            <div class="card h-100">
                                                <div class="card-body text-center p-2">
                                                    @if($isImage)
                                                        <img src="{{ $fileUrl }}"
                                                             alt="{{ $file->file_name }}"
                                                             class="img-fluid rounded"
                                                             style="max-height: 80px; object-fit: cover;">
                                                    @else
                                                        <i class="bi bi-file-earmark" style="font-size: 2rem;"></i>
                                                    @endif
                                                    <small class="d-block text-truncate mt-1">{{ $file->file_name }}</small>
                                                    <small class="text-muted d-block">{{ $fileSize }}</small>
                                                    <div class="form-check mt-1">
                                                        <input type="checkbox"
                                                               name="delete_files[]"
                                                               value="{{ $file->id }}"
                                                               class="form-check-input"
                                                               id="delete_file_{{ $file->id }}">
                                                        <label class="form-check-label small text-danger" for="delete_file_{{ $file->id }}">
                                                            <i class="bi bi-trash me-1"></i> Удалить
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                                <small class="text-muted">Отметьте файлы, которые нужно удалить</small>
                            </div>
                        @endif

                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('advertisements.show', $advertisement) }}" class="btn btn-secondary">Отмена</a>
                            <button type="submit" class="btn btn-primary">Сохранить изменения</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const fileInput = document.getElementById('files');
                const fileList = document.getElementById('fileList');

                if (fileInput) {
                    fileInput.addEventListener('change', function(e) {
                        fileList.innerHTML = '';

                        if (this.files.length > 0) {
                            const list = document.createElement('div');
                            list.className = 'list-group';

                            for (let i = 0; i < this.files.length; i++) {
                                const file = this.files[i];
                                const item = document.createElement('div');
                                item.className = 'list-group-item list-group-item-action d-flex justify-content-between align-items-center';

                                let icon = 'bi-file-earmark';
                                const type = file.type;
                                if (type.startsWith('image/')) {
                                    icon = 'bi-file-image';
                                } else if (type === 'application/pdf') {
                                    icon = 'bi-file-pdf';
                                } else if (type === 'text/plain') {
                                    icon = 'bi-file-text';
                                }

                                item.innerHTML = `
                                <span>
                                    <i class="bi ${icon} me-1"></i>
                                    ${file.name}
                                </span>
                                <span class="badge bg-primary rounded-pill">
                                    ${(file.size / 1024).toFixed(1)} KB
                                </span>
                            `;
                                list.appendChild(item);
                            }

                            fileList.appendChild(list);
                        }
                    });
                }
            });
        </script>
    @endpush
@endsection

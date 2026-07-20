@extends('layouts.app')

@section('title', 'Редактировать новость')

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="mb-3">
                <a href="{{ route('news.show', $news) }}" class="btn btn-outline-secondary btn-sm">
                    &larr; Назад к новости
                </a>
            </div>

            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Редактировать новость</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('news.update', $news) }}" method="POST" enctype="multipart/form-data">
                        @csrf @method('PUT')

                        <div class="mb-3">
                            <label for="content" class="form-label">Содержание <span class="text-danger">*</span></label>
                            <textarea name="content" id="content" rows="10"
                                      class="form-control @error('content') is-invalid @enderror">{{ old('content', $news->content) }}</textarea>
                            @error('content') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <label for="status" class="form-label">Статус</label>
                            <select name="status" id="status" class="form-select">
                                <option value="active" {{ old('status', $news->status) == 'active' ? 'selected' : '' }}>Опубликовано</option>
                                <option value="inactive" {{ old('status', $news->status) == 'inactive' ? 'selected' : '' }}>Черновик</option>
                            </select>
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('news.show', $news) }}" class="btn btn-secondary">Отмена</a>
                            <button type="submit" class="btn btn-primary">Сохранить</button>
                        </div>
                        <div class="mb-3">
                            <label for="files" class="form-label">Прикрепить файлы</label>
                            <input type="file" name="files[]" id="files" multiple
                                   accept=".jpg,.jpeg,.png,.gif,.bmp,.webp,.svg"
                                   class="form-control @error('files.*') is-invalid @enderror">
                            <small class="text-muted">Можно загрузить несколько файлов изображений (JPG, PNG, GIF, BMP, WEBP, SVG)</small>
                            @error('files.*')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <!-- Существующие файлы -->
                        @if($news->files && $news->files->count() > 0)
                            <div class="mb-3">
                                <label class="form-label">Существующие файлы</label>
                                <div class="row g-2">
                                    @foreach($news->files as $file)
                                        <div class="col-md-4 col-sm-6">
                                            <div class="card h-100">
                                                <div class="card-body text-center p-2">
                                                        <img src="{{ Storage::disk('public')->url($file->file_path) }}"
                                                             alt="{{ $file->file_name }}"
                                                             class="img-fluid rounded"
                                                             style="max-height: 80px; object-fit: cover;">
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
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

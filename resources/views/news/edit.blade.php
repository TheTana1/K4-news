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
                            <label for="title" class="form-label">Заголовок <span class="text-danger">*</span></label>
                            <input type="text" name="title" id="title" value="{{ old('title', $news->title) }}"
                                   class="form-control @error('title') is-invalid @enderror">
                            @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <label for="content" class="form-label">Содержание <span class="text-danger">*</span></label>
                            <textarea name="content" id="content" rows="10"
                                      class="form-control @error('content') is-invalid @enderror">{{ old('content', $news->content) }}</textarea>
                            @error('content') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        @if($news->image_path)
                            <div class="mb-3">
                                <p class="form-label">Текущее изображение:</p>
                                <img src="{{ asset($news->image_path) }}" alt="Current" class="img-fluid rounded" style="max-height: 150px;">
                            </div>
                        @endif

                        <div class="mb-3">
                            <label for="image" class="form-label">Новое изображение</label>
                            <input type="file" name="image" id="image" accept="image/*"
                                   class="form-control @error('image') is-invalid @enderror">
                            @error('image') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            <small class="text-muted">Оставьте пустым, чтобы не менять</small>
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
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

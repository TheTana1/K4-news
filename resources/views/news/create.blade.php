@extends('layouts.app')

@section('title', 'Создать новость')

@section('content')

    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="mb-3">
                <a href="{{ route('news.index') }}" class="btn btn-outline-secondary btn-sm">
                    &larr; Назад к списку
                </a>
            </div>

            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Создать новую новость</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('news.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-3">
                            <label for="content" class="form-label">Содержание <span class="text-danger">*</span></label>
                            <textarea name="content" id="content" rows="8"
                                      class="form-control @error('content') is-invalid @enderror">{{ old('content') }}</textarea>
                            @error('content') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="mb-3">
                            <label for="status" class="form-label">Статус</label>
                            <select name="status" id="status" class="form-select">
                                <option value="active" {{ old('status') == 'active' ? 'selected' : '' }}>Активно</option>
                                <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>Не активно</option>
                            </select>
                        </div>


                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('news.index') }}" class="btn btn-secondary">Отмена</a>
                            <button type="submit" class="btn btn-primary">Создать</button>
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
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

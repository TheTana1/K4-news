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
                    <form action="{{ route('advertisements.update', $advertisement) }}" method="POST">
                        @csrf @method('PUT')

                        <div class="mb-3">
                            <label for="title" class="form-label">Заголовок <span class="text-danger">*</span></label>
                            <input type="text" name="title" id="title" value="{{ old('title', $advertisement->title) }}"
                                   class="form-control @error('title') is-invalid @enderror">
                            @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <label for="content" class="form-label">Содержание <span class="text-danger">*</span></label>
                            <textarea name="content" id="content" rows="8"
                                      class="form-control @error('content') is-invalid @enderror">{{ old('content', $advertisement->content) }}</textarea>
                            @error('content') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="price" class="form-label">Цена (₽)</label>
                                <input type="number" name="price" id="price" value="{{ old('price', $advertisement->price) }}"
                                       class="form-control">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="city" class="form-label">Город</label>
                                <input type="text" name="city" id="city" value="{{ old('city', $advertisement->city) }}"
                                       class="form-control">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="status" class="form-label">Статус</label>
                            <select name="status" id="status" class="form-select">
                                <option value="active" {{ old('status', $advertisement->status) == 'active' ? 'selected' : '' }}>Активно</option>
                                <option value="inactive" {{ old('status', $advertisement->status) == 'inactive' ? 'selected' : '' }}>Не активно</option>
                            </select>
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('advertisements.show', $advertisement) }}" class="btn btn-secondary">Отмена</a>
                            <button type="submit" class="btn btn-primary">Сохранить</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

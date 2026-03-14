@extends('layouts.main')

@section('title', 'Редактировать объявление')

@section('content')
    <div class="container">
        <div class="header-with-back">
            <h1>Редактировать объявление</h1>
            <a href="{{ route('advertisements.show', $advertisement['id']) }}" class="back-btn">← Назад к объявлению</a>
        </div>

        <form action="{{ route('advertisements.update', $advertisement['id']) }}" method="POST" class="advertisement-form" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="form-group">
                <label for="title">Заголовок *</label>
                <input type="text"
                       id="title"
                       name="title"
                       class="form-control @error('title') is-invalid @enderror"
                       value="{{ old('title', $advertisement['title']) }}"
                       required>
                @error('title')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="category">Категория *</label>
                <select id="category" name="category" class="form-control @error('category') is-invalid @enderror" required>
                    <option value="">Выберите категорию</option>
                    <option value="Недвижимость" {{ old('category', $advertisement['category']) == 'Недвижимость' ? 'selected' : '' }}>Недвижимость</option>
                    <option value="Транспорт" {{ old('category', $advertisement['category']) == 'Транспорт' ? 'selected' : '' }}>Транспорт</option>
                    <option value="Работа" {{ old('category', $advertisement['category']) == 'Работа' ? 'selected' : '' }}>Работа</option>
                    <option value="Услуги" {{ old('category', $advertisement['category']) == 'Услуги' ? 'selected' : '' }}>Услуги</option>
                    <option value="Товары" {{ old('category', $advertisement['category']) == 'Товары' ? 'selected' : '' }}>Товары</option>
                </select>
                @error('category')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="price">Цена (₽)</label>
                <input type="number"
                       id="price"
                       name="price"
                       class="form-control @error('price') is-invalid @enderror"
                       value="{{ old('price', $advertisement['price']) }}"
                       min="0">
                @error('price')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="description">Описание *</label>
                <textarea id="description"
                          name="description"
                          rows="6"
                          class="form-control @error('description') is-invalid @enderror"
                          required>{{ old('description', $advertisement['description']) }}</textarea>
                @error('description')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            @if(isset($advertisement['image']))
                <div class="current-image">
                    <label>Текущее изображение:</label>
                    <img src="{{ $advertisement['image'] }}" alt="Current image" class="preview-image">
                </div>
            @endif

            <div class="form-group">
                <label for="image">Новое изображение (оставьте пустым, чтобы не менять)</label>
                <input type="file"
                       id="image"
                       name="image"
                       class="form-control @error('image') is-invalid @enderror"
                       accept="image/*">
                <small class="form-text text-muted">Поддерживаемые форматы: jpeg, png, jpg, gif (макс. 2MB)</small>
                @error('image')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="contact_info">Контактная информация *</label>
                <input type="text"
                       id="contact_info"
                       name="contact_info"
                       class="form-control @error('contact_info') is-invalid @enderror"
                       value="{{ old('contact_info', $advertisement['contact_info']) }}"
                       placeholder="Телефон, email или способ связи"
                       required>
                @error('contact_info')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="city">Город</label>
                <input type="text"
                       id="city"
                       name="city"
                       class="form-control @error('city') is-invalid @enderror"
                       value="{{ old('city', $advertisement['city'] ?? '') }}"
                       placeholder="Например: Москва">
                @error('city')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-checkbox">
                <input type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active', $advertisement['is_active'] ?? true) ? 'checked' : '' }}>
                <label for="is_active">Активно</label>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn-submit">Сохранить изменения</button>
                <a href="{{ route('advertisements.show', $advertisement['id']) }}" class="btn-cancel">Отмена</a>
            </div>
        </form>
    </div>
@endsection

@section('styles')
    <style>
        .current-image {
            margin-bottom: 1.5rem;
        }

        .current-image label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: #333;
        }

        .preview-image {
            max-width: 200px;
            max-height: 200px;
            border-radius: 5px;
            border: 1px solid #ddd;
        }
    </style>
@endsection

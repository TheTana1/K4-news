@extends('layouts.main')

@section('title', 'Создать объявление')

@section('content')
<div class="container">
    <div class="header-with-back">
        <h1>Создать новое объявление</h1>
        <a href="{{ route('advertisements.index') }}" class="back-btn">← Назад к списку</a>
    </div>

    <form action="{{ route('advertisements.store') }}" method="POST" class="advertisement-form" enctype="multipart/form-data">
        @csrf

        <div class="form-group">
            <label for="title">Заголовок *</label>
            <input type="text"
                   id="title"
                   name="title"
                   class="form-control @error('title') is-invalid @enderror"
                   value="{{ old('title') }}"
                   required>
            @error('title')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label for="category">Категория *</label>
            <select id="category" name="category" class="form-control @error('category') is-invalid @enderror" required>
                <option value="">Выберите категорию</option>
                <option value="Недвижимость" {{ old('category') == 'Недвижимость' ? 'selected' : '' }}>Недвижимость</option>
                <option value="Транспорт" {{ old('category') == 'Транспорт' ? 'selected' : '' }}>Транспорт</option>
                <option value="Работа" {{ old('category') == 'Работа' ? 'selected' : '' }}>Работа</option>
                <option value="Услуги" {{ old('category') == 'Услуги' ? 'selected' : '' }}>Услуги</option>
                <option value="Товары" {{ old('category') == 'Товары' ? 'selected' : '' }}>Товары</option>
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
                   value="{{ old('price') }}"
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
                      required>{{ old('description') }}</textarea>
            @error('description')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label for="image">Изображение</label>
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
                   value="{{ old('contact_info') }}"
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
                   value="{{ old('city') }}"
                   placeholder="Например: Москва">
            @error('city')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-checkbox">
            <input type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
            <label for="is_active">Опубликовать сразу</label>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn-submit">Создать объявление</button>
            <a href="{{ route('advertisements.index') }}" class="btn-cancel">Отмена</a>
        </div>
    </form>
</div>
@endsection

@section('styles')
<style>
    .container {
        max-width: 800px;
        margin: 0 auto;
        padding: 2rem 1rem;
    }

    .header-with-back {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 2rem;
    }

    .header-with-back h1 {
        font-size: 1.8rem;
        color: #333;
    }

    .back-btn {
        color: #666;
        text-decoration: none;
        padding: 0.5rem 1rem;
        border-radius: 5px;
        transition: background-color 0.3s;
    }

    .back-btn:hover {
        background-color: #f0f0f0;
    }

    .advertisement-form {
        background: white;
        padding: 2rem;
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }

    .form-group {
        margin-bottom: 1.5rem;
    }

    .form-group label {
        display: block;
        margin-bottom: 0.5rem;
        font-weight: 500;
        color: #333;
    }

    .form-control {
        width: 100%;
        padding: 0.75rem;
        border: 1px solid #ddd;
        border-radius: 5px;
        font-size: 1rem;
        transition: border-color 0.3s;
    }

    .form-control:focus {
        outline: none;
        border-color: #007bff;
        box-shadow: 0 0 0 3px rgba(0,123,255,0.1);
    }

    .form-control.is-invalid {
        border-color: #dc3545;
    }

    .invalid-feedback {
        color: #dc3545;
        font-size: 0.875rem;
        margin-top: 0.25rem;
    }

    .form-text {
        font-size: 0.875rem;
        color: #666;
        margin-top: 0.25rem;
    }

    .form-checkbox {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        margin-bottom: 1.5rem;
    }

    .form-checkbox input[type="checkbox"] {
        width: 1.2rem;
        height: 1.2rem;
    }

    .form-checkbox label {
        color: #333;
    }

    .form-actions {
        display: flex;
        gap: 1rem;
        margin-top: 2rem;
    }

    .btn-submit {
        background-color: #007bff;
        color: white;
        border: none;
        padding: 0.75rem 2rem;
        border-radius: 5px;
        font-size: 1rem;
        cursor: pointer;
        transition: background-color 0.3s;
    }

    .btn-submit:hover {
        background-color: #0056b3;
    }

    .btn-cancel {
        background-color: #6c757d;
        color: white;
        text-decoration: none;
        padding: 0.75rem 2rem;
        border-radius: 5px;
        font-size: 1rem;
        display: inline-block;
        transition: background-color 0.3s;
    }

    .btn-cancel:hover {
        background-color: #545b62;
    }
</style>
@endsection

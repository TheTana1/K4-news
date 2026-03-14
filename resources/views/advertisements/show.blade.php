@extends('layouts.main')

@section('title', $advertisement['title'])

@section('content')
    <div class="container">
        <div class="header-with-actions">
            <div class="header-left">
                <a href="{{ route('advertisements.index') }}" class="back-link">← Все объявления</a>
                <h1>{{ $advertisement['title'] }}</h1>
            </div>
            <div class="action-buttons">
                <a href="{{ route('advertisements.edit', $advertisement['id']) }}" class="btn-edit">Редактировать</a>
                <form action="{{ route('advertisements.destroy', $advertisement['id']) }}" method="POST" class="delete-form" onsubmit="return confirm('Вы уверены, что хотите удалить это объявление?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn-delete">Удалить</button>
                </form>
            </div>
        </div>

        <div class="advertisement-detail">
            <div class="advertisement-main">
                @if(isset($advertisement['image']))
                    <div class="advertisement-image">
                        <img src="{{ $advertisement['image'] }}" alt="{{ $advertisement['title'] }}">
                    </div>
                @endif

                <div class="advertisement-info">
                    <div class="info-section">
                        <span class="badge category">{{ $advertisement['category'] }}</span>
                        @if(isset($advertisement['is_active']) && $advertisement['is_active'])
                            <span class="badge active">Активно</span>
                        @else
                            <span class="badge inactive">Не активно</span>
                        @endif
                    </div>

                    @if(isset($advertisement['price']) && $advertisement['price'])
                        <div class="price-section">
                            <span class="price-label">Цена:</span>
                            <span class="price-value">{{ number_format($advertisement['price'], 0, ',', ' ') }} ₽</span>
                        </div>
                    @endif

                    <div class="description-section">
                        <h3>Описание</h3>
                        <p class="description">{{ $advertisement['description'] }}</p>
                    </div>

                    <div class="contact-section">
                        <h3>Контактная информация</h3>
                        <p class="contact-info">{{ $advertisement['contact_info'] }}</p>
                    </div>

                    @if(isset($advertisement['city']) && $advertisement['city'])
                        <div class="location-section">
                            <span class="location-label">📍 {{ $advertisement['city'] }}</span>
                        </div>
                    @endif

                    <div class="meta-section">
                        <span class="meta-item">📅 Создано: {{ $advertisement['created_at'] ?? date('d.m.Y') }}</span>
                        @if(isset($advertisement['views']))
                            <span class="meta-item">👁 Просмотров: {{ $advertisement['views'] }}</span>
                        @endif
                    </div>
                </div>
            </div>

            <div class="advertisement-sidebar">
                <div class="seller-card">
                    <h3>Продавец</h3>
                    <p class="seller-name">{{ $advertisement['seller_name'] ?? 'Не указан' }}</p>
                    <p class="seller-rating">⭐ {{ $advertisement['seller_rating'] ?? 'Нет оценок' }}</p>

                    <div class="seller-actions">
                        <button class="btn-contact" onclick="window.location.href='mailto:{{ $advertisement['seller_email'] ?? '' }}'">
                            ✉ Написать сообщение
                        </button>
                        <button class="btn-phone" onclick="window.location.href='tel:{{ $advertisement['seller_phone'] ?? '' }}'">
                            📞 Позвонить
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('styles')
    <style>
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem 1rem;
        }

        .header-with-actions {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 2rem;
        }

        .header-left {
            flex: 1;
        }

        .back-link {
            color: #666;
            text-decoration: none;
            font-size: 0.95rem;
            display: inline-block;
            margin-bottom: 0.5rem;
        }

        .back-link:hover {
            color: #007bff;
        }

        .header-left h1 {
            font-size: 2rem;
            color: #333;
            margin: 0;
        }

        .action-buttons {
            display: flex;
            gap: 1rem;
            align-items: center;
        }

        .btn-edit {
            background-color: #28a745;
            color: white;
            text-decoration: none;
            padding: 0.5rem 1.5rem;
            border-radius: 5px;
            transition: background-color 0.3s;
        }

        .btn-edit:hover {
            background-color: #218838;
        }

        .btn-delete {
            background-color: #dc3545;
            color: white;
            border: none;
            padding: 0.5rem 1.5rem;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1rem;
            transition: background-color 0.3s;
        }

        .btn-delete:hover {
            background-color: #c82333;
        }

        .advertisement-detail {
            display: grid;
            grid-template-columns: 1fr 300px;
            gap: 2rem;
        }

        .advertisement-main {
            background: white;
            border-radius: 10px;
            padding: 2rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .advertisement-image {
            margin-bottom: 2rem;
        }

        .advertisement-image img {
            width: 100%;
            max-height: 400px;
            object-fit: contain;
            border-radius: 5px;
        }

        .info-section {
            margin-bottom: 1.5rem;
        }

        .badge {
            display: inline-block;
            padding: 0.25rem 1rem;
            border-radius: 20px;
            font-size: 0.875rem;
            margin-right: 0.5rem;
        }

        .badge.category {
            background-color: #e9ecef;
            color: #495057;
        }

        .badge.active {
            background-color: #d4edda;
            color: #155724;
        }

        .badge.inactive {
            background-color: #f8d7da;
            color: #721c24;
        }

        .price-section {
            background-color: #f8f9fa;
            padding: 1.5rem;
            border-radius: 5px;
            margin-bottom: 1.5rem;
        }

        .price-label {
            font-size: 1rem;
            color: #666;
            margin-right: 1rem;
        }

        .price-value {
            font-size: 2rem;
            font-weight: bold;
            color: #28a745;
        }

        .description-section {
            margin-bottom: 1.5rem;
        }

        .description-section h3 {
            font-size: 1.2rem;
            color: #333;
            margin-bottom: 1rem;
        }

        .description {
            line-height: 1.6;
            color: #555;
            white-space: pre-line;
        }

        .contact-section {
            background-color: #f8f9fa;
            padding: 1.5rem;
            border-radius: 5px;
            margin-bottom: 1.5rem;
        }

        .contact-section h3 {
            font-size: 1.1rem;
            color: #333;
            margin-bottom: 1rem;
        }

        .contact-info {
            font-size: 1.1rem;
            color: #007bff;
            font-weight: 500;
        }

        .location-section {
            margin-bottom: 1rem;
        }

        .location-label {
            color: #666;
            font-size: 1rem;
        }

        .meta-section {
            border-top: 1px solid #dee2e6;
            padding-top: 1rem;
            margin-top: 1rem;
        }

        .meta-item {
            color: #999;
            font-size: 0.875rem;
            margin-right: 2rem;
        }

        .advertisement-sidebar {
            background: white;
            border-radius: 10px;
            padding: 1.5rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            height: fit-content;
        }

        .seller-card {
            text-align: center;
        }

        .seller-card h3 {
            font-size: 1.2rem;
            color: #333;
            margin-bottom: 1rem;
        }

        .seller-name {
            font-size: 1.1rem;
            font-weight: 500;
            color: #333;
            margin-bottom: 0.5rem;
        }

        .seller-rating {
            color: #ffc107;
            margin-bottom: 1.5rem;
        }

        .seller-actions {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
        }

        .btn-contact, .btn-phone {
            padding: 0.75rem;
            border: none;
            border-radius: 5px;
            font-size: 1rem;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .btn-contact {
            background-color: #007bff;
            color: white;
        }

        .btn-contact:hover {
            background-color: #0056b3;
        }

        .btn-phone {
            background-color: #28a745;
            color: white;
        }

        .btn-phone:hover {
            background-color: #218838;
        }

        .delete-form {
            display: inline;
        }
    </style>
@endsection

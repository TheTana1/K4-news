@extends('layouts.app')

@section('title', 'Главная панель')

@section('content')
    <!-- Приветствие -->
    <div class="bg-primary bg-gradient text-white rounded-3 p-4 mb-4 shadow">
        @php
            $hour = date('H');
            $greeting = match (true) {
                $hour >= 5 && $hour < 12 => 'Доброе утро',
                $hour >= 12 && $hour < 18 => 'Добрый день',
                $hour >= 18 && $hour < 23 => 'Добрый вечер',
                default => 'Доброй ночи',
            };
        @endphp
        <h1 class="h3 fw-bold mb-1">{{ $greeting }}, {{ Auth::user()->name ?? 'Гость' }}!</h1>
        <p class="text-white-50 mb-0">Сегодня {{ local_date(now()) }}</p>
    </div>

    {{-- Плитки статистики --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
            <div class="card h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="bg-primary bg-opacity-10 rounded p-3 me-3">
                        <i class="bi bi-megaphone fs-4 text-primary"></i>
                    </div>
                    <div>
                        <div class="text-muted small text-uppercase">Важное</div>
                        <div class="h4 mb-0">{{ $stats['ads_count'] }}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="bg-success bg-opacity-10 rounded p-3 me-3">
                        <i class="bi bi-newspaper fs-4 text-success"></i>
                    </div>
                    <div>
                        <div class="text-muted small text-uppercase">Новости</div>
                        <div class="h4 mb-0">{{ $stats['news_count'] }}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="bg-warning bg-opacity-10 rounded p-3 me-3">
                        <i class="bi bi-star fs-4 text-warning"></i>
                    </div>
                    <div>
                        <div class="text-muted small text-uppercase">Отзывы</div>
                        <div class="h4 mb-0">{{ $stats['reviews_count'] }}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="bg-danger bg-opacity-10 rounded p-3 me-3">
                        <i class="bi bi-people fs-4 text-danger"></i>
                    </div>
                    <div>
                        <div class="text-muted small text-uppercase">Коллеги</div>
                        <div class="h4 mb-0">{{ $stats['users_count'] }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Последние записи --}}
    <div class="row row-cols-1 row-cols-lg-3 g-4 mb-4">
        <div class="col">
            <div class="card h-100 shadow-sm">
                <div class="card-header bg-body-tertiary fw-semibold">
                    <i class="bi bi-megaphone me-1"></i> Последние важные объявления
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                        @auth()
                            @forelse($recentAds ?? [] as $ad)
                                <li class="list-group-item">
                                    <a href="{{ route('advertisements.show', $ad) }}"
                                       class="text-primary fw-semibold text-decoration-none">
                                        <div>{{ Str::limit($ad->content, 50) }}</div>
                                        <div class="small text-muted">{{ $ad->created_at->diffForHumans() }}</div>
                                    </a>
                                </li>
                            @empty
                                <li class="list-group-item text-muted">Нет объявлений</li>
                            @endforelse
                        @else
                            <li class="list-group-item text-muted">Авторизируйтесь для просмотра</li>
                        @endauth
                    </ul>
                </div>
            </div>
        </div>

        <div class="col">
            <div class="card h-100 shadow-sm">
                <div class="card-header bg-body-tertiary fw-semibold">
                    <i class="bi bi-newspaper me-1"></i> Последние новости
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                        @auth()
                            @forelse($recentNews ?? [] as $news)
                                <li class="list-group-item">
                                    <a href="{{ route('news.show', $news) }}"
                                       class="text-success fw-semibold text-decoration-none">
                                        <div>
                                            {{ Str::limit($news->content, 50) }}
                                            <div class="small text-muted">{{ $news->created_at->diffForHumans() }}</div>
                                        </div>
                                    </a>
                                </li>
                            @empty
                                <li class="list-group-item text-muted">Нет новостей</li>
                            @endforelse
                        @else
                            <li class="list-group-item text-muted">Авторизируйтесь для просмотра</li>
                        @endauth
                    </ul>
                </div>
            </div>
        </div>

        <div class="col">
            <div class="card h-100 shadow-sm">
                <div class="card-header bg-body-tertiary fw-semibold">
                    <i class="bi bi-star me-1"></i> Последние отзывы
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                        @auth()
                            @forelse($recentReviews ?? [] as $review)
                                <li class="list-group-item">
                                    <a href="{{ route('reviews.show', $review) }}"
                                       class="fw-semibold text-decoration-none" style="color: #ff661b">
                                        {{ Str::limit($review->content, 50) }}
                                        <div class="small text-muted">{{ $review->created_at->diffForHumans() }}</div>
                                    </a>
                                </li>
                            @empty
                                <li class="list-group-item text-muted">Нет отзывов</li>
                            @endforelse
                        @else
                            <li class="list-group-item text-muted">Авторизируйтесь для просмотра</li>
                        @endauth
                    </ul>
                </div>
            </div>
        </div>
    </div>

    {{-- Календарь смен --}}
    @auth()
        <div class="card shadow-sm">
            <div class="card-header bg-body-tertiary fw-semibold">
                <i class="bi bi-calendar-week me-1"></i> Календарь смен — {{ now()->isoFormat('D MMMM YYYY') }}
            </div>
            <div class="card-body">
                <div class="row g-2 flex-nowrap overflow-auto" id="calendar">
                    @foreach ($days as $day)
                        <div class="col">
                            <div
                                class="card h-100 text-center p-2 user-select-none"
                                role="button"
                                data-date="{{ $day['date'] }}"
                                data-type="{{ $day['type'] }}"
                            >
                                <div class="fs-4 fw-semibold">{{ $day['day'] }}</div>
                                <div class="text-muted small">{{ $day['weekday'] }}</div>

                                {{-- Бейдж типа смены --}}
                                <span class="badge mt-1">&nbsp;</span>

                                {{-- Бейдж статуса согласования --}}
                                @if ($day['status'] === 'pending')
                                    <span class="badge bg-info-subtle text-info-emphasis mt-1">На проверке</span>
                                @elseif ($day['status'] === 'approved')
                                    <span class="badge bg-primary-subtle text-primary-emphasis mt-1">Согласовано</span>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="card-footer d-flex justify-content-end gap-2 bg-body-tertiary">
                <button type="button" class="btn btn-outline-secondary" id="btnCancel">Отмена</button>
                <button type="button" class="btn btn-primary" id="btnSubmit">Отправить</button>
            </div>
        </div>
    @endauth
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const TYPES = [
                { key: 'off',  label: 'Выходной',      badge: 'bg-danger',            wrap: 'bg-danger-subtle border-danger-subtle'   },
                { key: 'full', label: 'Работа',        badge: 'bg-success',           wrap: 'bg-success-subtle border-success-subtle' },
                { key: 'half', label: 'Неполный день', badge: 'bg-warning text-dark', wrap: 'bg-warning-subtle border-warning-subtle' },
            ];

            const calendarEl = document.getElementById('calendar');
            if (!calendarEl) return;

            // Первичная отрисовка бейджей типов
            calendarEl.querySelectorAll('.card[data-date]').forEach(cell => {
                renderCell(cell, cell.dataset.type);
            });

            // Клик — циклическое переключение off → full → half → off
            calendarEl.addEventListener('click', e => {
                const cell = e.target.closest('.card[data-date]');
                if (!cell) return;

                const currentIdx = TYPES.findIndex(t => t.key === cell.dataset.type);
                const nextIdx    = (currentIdx + 1) % TYPES.length;
                renderCell(cell, TYPES[nextIdx].key);
            });

            function renderCell(cell, typeKey) {
                const type = TYPES.find(t => t.key === typeKey) || TYPES[0];

                cell.dataset.type = type.key;

                cell.classList.remove(
                    'bg-success-subtle', 'border-success-subtle',
                    'bg-warning-subtle', 'border-warning-subtle',
                    'bg-danger-subtle',  'border-danger-subtle'
                );
                type.wrap.split(' ').forEach(cls => cell.classList.add(cls));

                const typeBadge = cell.querySelector('.badge');
                typeBadge.className = 'badge mt-1 ' + type.badge;
                typeBadge.textContent = type.label;
            }

            // Отмена
            document.getElementById('btnCancel').addEventListener('click', () => {
                if (confirm('Отменить все изменения?')) {
                    window.location.reload();
                }
            });

            // Отправить
            document.getElementById('btnSubmit').addEventListener('click', async () => {
                const days = [...calendarEl.querySelectorAll('.card[data-date]')].map(cell => ({
                    date: cell.dataset.date,
                    type: cell.dataset.type,
                }));

                try {
                    const res = await fetch('{{ route('shifts.store') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({ days }),
                    });

                    if (!res.ok) throw new Error('Ошибка сервера');

                    alert('Смены сохранены!');
                    window.location.reload();
                } catch (err) {
                    console.error(err);
                    alert('Не удалось сохранить данные');
                }
            });
        });
    </script>
@endpush

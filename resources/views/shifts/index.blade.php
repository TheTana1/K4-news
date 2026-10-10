@extends('layouts.app')

@section('title', 'Смены сотрудников')

@section('content')
    @php
        $monthLabel = \Carbon\Carbon::createFromFormat('Y-m', $month)->isoFormat('MMMM YYYY');
        $auth = auth()->user();
        $canSaveAll = $auth->can('saveAll', \App\Models\Shift::class);
    @endphp

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">
            <i class="bi bi-calendar-week me-1"></i>
            Смены сотрудников — {{ $monthLabel }}
        </h1>

        <div class="d-flex gap-2">
            <a href="{{ route('shifts.index', ['month' => \Carbon\Carbon::parse($month)->subMonth()->format('Y-m')]) }}"
               class="btn btn-sm btn-outline-secondary"><i class="bi bi-chevron-left"></i></a>
            <a href="{{ route('shifts.index') }}" class="btn btn-sm btn-outline-secondary">Текущий месяц</a>
            <a href="{{ route('shifts.index', ['month' => \Carbon\Carbon::parse($month)->addMonth()->format('Y-m')]) }}"
               class="btn btn-sm btn-outline-secondary"><i class="bi bi-chevron-right"></i></a>
        </div>
    </div>

    {{-- Легенда --}}
    <div class="card shadow-sm mb-3">
        <div class="card-body py-2 small">
            <span class="badge bg-success me-1">•</span> Работа
            <span class="badge bg-warning text-dark ms-3 me-1">•</span> Неполный день
            <span class="badge bg-danger ms-3 me-1">•</span> Выходной
            &nbsp;|&nbsp;
            <span class="fw-semibold text-success">✓</span> Согласовано
            <span class="fw-semibold text-secondary ms-2">…</span> На проверке
        </div>
    </div>

    <form method="POST"
          action="{{ $canSaveAll ? route('shifts.save') : route('shifts.submit') }}"
          id="shifts-form">
        @csrf
        <input type="hidden" name="month" value="{{ $month }}">

        <div class="card shadow-sm">
            <div class="table-responsive">
                <table class="table table-sm table-bordered align-middle mb-0 text-center">
                    <thead>
                    <tr>
                        <th class="text-start bg-body-tertiary" style="position: sticky; top: 0; z-index: 3;">
                            Сотрудник
                        </th>
                        @foreach ($days as $day)
                            <th class="small {{ $day['is_weekend'] ? 'text-danger' : '' }}"
                                style="min-width: 44px; position: sticky; top: 0; z-index: 2;">
                                <div>{{ $day['day'] }}</div>
                                <div class="text-muted fw-normal small">{{ $day['weekday'] }}</div>
                            </th>
                        @endforeach
                    </tr>
                    </thead>
                    <tbody>
                    @php
                        $groupedUsers = $users->groupBy(fn ($user) => $user->role?->label ?? 'Без роли');
                    @endphp

                    @forelse ($groupedUsers as $roleLabel => $roleUsers)
                        {{-- Заголовок группы --}}
                        <tr class="table-secondary">
                            <td colspan="{{ count($days) + 1 }}" class="text-start fw-bold small py-2">
                                <i class="bi bi-people-fill me-1"></i> {{ $roleLabel }}
                                <span class="badge bg-secondary ms-2">{{ $roleUsers->count() }}</span>
                            </td>
                        </tr>

                        @foreach ($roleUsers as $user)
                            @php
                                $editable = $canSaveAll || $auth->id === $user->id;
                            @endphp

                            <tr data-user="{{ $user->id }}">
                                <td class="text-start" style="position: sticky; left: 0; background: inherit; z-index: 1;">
                                    <div class="d-flex align-items-center">
                                        <div class="flex-shrink-0 me-2">
                                            @if($user->avatar_path)
                                                <img src="{{ asset($user->avatar_path) }}" alt="{{ $user->name }}"
                                                     class="rounded-circle" width="32" height="32" style="object-fit: cover;">
                                            @else
                                                @php
                                                    $colors = ['primary', 'secondary', 'success', 'danger', 'warning', 'info', 'dark'];
                                                    $color  = $colors[abs(crc32($user->name)) % count($colors)];
                                                @endphp
                                                <div class="rounded-circle d-flex align-items-center justify-content-center text-white fw-bold bg-{{ $color }}"
                                                     style="width:32px;height:32px;font-size:.85rem;">
                                                    {{ mb_strtoupper(mb_substr($user->name, 0, 1)) }}
                                                </div>
                                            @endif
                                        </div>
                                        <div class="min-width-0">
                                            <div class="fw-semibold text-truncate" style="max-width: 150px;">
                                                {{ $user->name }}
                                            </div>
                                            <div class="small text-muted text-truncate" style="max-width: 150px;">
                                                {{ $user->role?->label ?? '—' }}
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                @foreach ($days as $day)
                                    @php
                                        $cell   = $shiftsMatrix[$user->id][$day['date']] ?? null;
                                        $type   = $cell['type']   ?? 'off';
                                        $status = $cell['status'] ?? 'draft';

                                        $badgeClass = match ($type) {
                                            'full'  => 'bg-success',
                                            'half'  => 'bg-warning text-dark',
                                            default => 'bg-danger',
                                        };

                                        $statusIcon = match ($status) {
                                            'approved' => '✓',
                                            'pending'  => '…',
                                            default    => '•',
                                        };
                                    @endphp

                                    <td class="p-1 {{ $editable ? 'day-cell' : '' }}"
                                        data-user="{{ $user->id }}"
                                        data-date="{{ $day['date'] }}"
                                        data-type="{{ $type }}"
                                        data-status="{{ $status }}"
                                        data-editable="{{ $editable ? '1' : '0' }}"
                                        style="{{ $editable ? 'cursor: pointer;' : '' }}">
                                        @if ($type === 'off' && $status === 'draft')
                                            <span class="text-muted small">—</span>
                                        @else
                                            <span class="badge {{ $badgeClass }}">{{ $statusIcon }}</span>
                                        @endif
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    @empty
                        <tr>
                            <td colspan="{{ count($days) + 1 }}" class="text-muted text-center py-4">
                                Сотрудники не найдены
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

            <div class="card-footer d-flex gap-2">
                @if($canSaveAll)
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-check-lg me-1"></i> Сохранить изменения
                    </button>
                @else
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-send me-1"></i> Отправить свою строку на рассмотрение
                    </button>
                @endif

                <a href="{{ route('shifts.index', ['month' => $month]) }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-counterclockwise me-1"></i> Отменить изменения
                </a>
            </div>
        </div>
    </form>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const states = ['off', 'full', 'half'];
            const canSaveAll = {{ $canSaveAll ? 'true' : 'false' }};
            const authId = {{ $auth->id }};
            const form = document.getElementById('shifts-form');

            // --- Клик по ячейке: цикл ---
            document.querySelectorAll('.day-cell[data-editable="1"]').forEach(cell => {
                cell.addEventListener('click', function () {
                    const current = cell.dataset.type || 'off';
                    const next = states[(states.indexOf(current) + 1) % states.length];

                    cell.dataset.type = next;
                    cell.dataset.changed = '1';

                    const badgeClass = {
                        'full': 'bg-success',
                        'half': 'bg-warning text-dark',
                        'off':  'bg-danger',
                    }[next];

                    cell.innerHTML = `<span class="badge ${badgeClass}">•</span>`;
                });
            });

            // --- Перед submit: создать inputs days[N][key] ---
            form?.addEventListener('submit', function (e) {
                // Удаляем старые inputs
                form.querySelectorAll('input[name^="days["]').forEach(el => el.remove());

                const days = [];

                document.querySelectorAll('.day-cell[data-changed="1"]').forEach(cell => {
                    days.push({
                        user_id: Number(cell.dataset.user),
                        date:    cell.dataset.date,
                        type:    cell.dataset.type,
                    });
                });

                if (days.length === 0) {
                    e.preventDefault();
                    alert('Нет изменений.');
                    return;
                }

                // Для сотрудника — только своя строка
                const filtered = canSaveAll
                    ? days
                    : days.filter(d => d.user_id === authId);

                // Создаём inputs
                filtered.forEach((day, i) => {
                    Object.entries(day).forEach(([key, value]) => {
                        const input = document.createElement('input');
                        input.type  = 'hidden';
                        input.name  = `days[${i}][${key}]`;
                        input.value = value;
                        form.appendChild(input);
                    });
                });
            });
        });
    </script>
@endpush

@extends('layouts.app')

@section('title', 'Объявления')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h3">Объявления</h1>
        <a href="{{ route('advertisements.create') }}" class="btn btn-primary">
            + Добавить объявление
        </a>
    </div>

    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-striped mb-0">
                    <thead>
                    <tr>
                        <th>Заголовок</th>
                        <th>Автор</th>
                        <th>Статус</th>
                        <th>Дата</th>
                        <th class="text-end">Действия</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($advertisements as $ad)
                        <tr>
                            <td>{{ Str::limit($ad->title, 50) }}</td>
                            <td>{{ $ad->author?->name ?? $ad->telegram_author_name ?? 'Неизвестно' }}</td>
                            <td>
                                @if($ad->status === 'active')
                                    <span class="badge bg-success">Активно</span>
                                @else
                                    <span class="badge bg-secondary">Не активно</span>
                                @endif
                            </td>
                            <td>{{ $ad->created_at->format('d.m.Y') }}</td>
                            <td class="text-end">
                                <a href="{{ route('advertisements.show', $ad) }}" class="btn btn-sm btn-outline-primary">Просмотр</a>
                                <a href="{{ route('advertisements.edit', $ad) }}" class="btn btn-sm btn-outline-success">Ред.</a>
                                <form action="{{ route('advertisements.destroy', $ad) }}" method="POST" class="d-inline">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Удалить?')">Удалить</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">Объявлений пока нет</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($advertisements->hasPages())
            <div class="card-footer">
                {{ $advertisements->links() }}
            </div>
        @endif
    </div>
@endsection

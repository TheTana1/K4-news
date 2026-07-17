@extends('layouts.app')

@section('title', 'Новости')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h3">Новости</h1>
        <a href="{{ route('news.create') }}" class="btn btn-primary">
            + Добавить новость
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
                    @forelse($news as $item)
                        <tr>
                            <td>{{ Str::limit($item->title, 50) }}</td>
                            <td>{{ $item->author?->name ?? $item->telegram_author_name ?? 'Неизвестно' }}</td>
                            <td>
                                @if($item->status === 'active')
                                    <span class="badge bg-success">Опубликовано</span>
                                @else
                                    <span class="badge bg-secondary">Черновик</span>
                                @endif
                            </td>
                            <td>{{ $item->created_at->format('d.m.Y') }}</td>
                            <td class="text-end">
                                <a href="{{ route('news.show', $item) }}" class="btn btn-sm btn-outline-primary">Просмотр</a>
                                <a href="{{ route('news.edit', $item) }}" class="btn btn-sm btn-outline-success">Ред.</a>
                                <form action="{{ route('news.destroy', $item) }}" method="POST" class="d-inline">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Удалить новость?')">Удалить</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">Новостей пока нет</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($news->hasPages())
            <div class="card-footer">
                {{ $news->links() }}
            </div>
        @endif
    </div>
@endsection

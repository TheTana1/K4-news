@extends('layouts.main')

@section('title', 'Новости')

@section('content')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Заголовок -->
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold text-gray-900">Новости</h1>
            <a href="{{ route('news.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg transition">
                + Добавить новость
            </a>
        </div>

        <!-- Список новостей -->
        <div class="bg-white rounded-xl shadow-md overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Заголовок</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Автор</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Дата</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Действия</th>
                    </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($news as $item)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4">
                                <div class="text-sm font-medium text-gray-900">{{ $item->title }}</div>
                                <div class="text-sm text-gray-500">{{ Str::limit($item->content, 50) }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $item->author?->name ?? $item->telegram_author_name ?? 'Неизвестно' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $item->created_at->format('d.m.Y') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <a href="{{ route('news.show', $item) }}" class="text-blue-600 hover:text-blue-900 mr-3">Просмотр</a>
                                <a href="{{ route('news.edit', $item) }}" class="text-green-600 hover:text-green-900 mr-3">Ред.</a>
                                <form action="{{ route('news.destroy', $item) }}" method="POST" class="inline">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-900" onclick="return confirm('Удалить новость?')">Удалить</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                                Новостей пока нет
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

            @if($news->hasPages())
                <div class="px-6 py-4 border-t border-gray-200">
                    {{ $news->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection

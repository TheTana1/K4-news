@extends('layouts.main')

@section('title', 'Объявления')

@section('content')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Заголовок -->
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold text-gray-900">Объявления</h1>
            <a href="{{ route('advertisements.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg transition">
                + Добавить объявление
            </a>
        </div>

        <!-- Список объявлений -->
        <div class="bg-white rounded-xl shadow-md overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Название</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Автор</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Статус</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Дата</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Действия</th>
                    </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($advertisements as $ad)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">{{ $ad->title }}</div>
                                <div class="text-sm text-gray-500">{{ Str::limit($ad->content, 50) }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $ad->author?->name ?? $ad->telegram_author_name ?? 'Неизвестно' }}
                            </td>

                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($ad->status === 'active')
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                        Активно
                                    </span>
                                @else
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                        Не активно
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $ad->created_at->format('d.m.Y') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <a href="{{ route('advertisements.show', $ad) }}" class="text-blue-600 hover:text-blue-900 mr-3">Просмотр</a>
                                <a href="{{ route('advertisements.edit', $ad) }}" class="text-green-600 hover:text-green-900 mr-3">Ред.</a>
                                <form action="{{ route('advertisements.destroy', $ad) }}" method="POST" class="inline">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-900" onclick="return confirm('Удалить?')">Удалить</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                                Объявлений пока нет
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

            @if($advertisements->hasPages())
                <div class="px-6 py-4 border-t border-gray-200">
                    {{ $advertisements->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection

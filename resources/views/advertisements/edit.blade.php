@extends('layouts.main')

@section('title', 'Редактировать объявление')

@section('content')
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="mb-6">
            <a href="{{ route('advertisements.show', $advertisement) }}" class="text-blue-600 hover:text-blue-800">← Назад к объявлению</a>
        </div>

        <div class="bg-white rounded-xl shadow-md overflow-hidden">
            <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                <h1 class="text-2xl font-bold text-gray-900">Редактировать объявление</h1>
            </div>

            <form action="{{ route('advertisements.update', $advertisement) }}" method="POST" class="p-6">
                @csrf @method('PUT')

                <div class="space-y-4">
                    <div>
                        <label for="title" class="block text-sm font-medium text-gray-700 mb-1">Заголовок *</label>
                        <input type="text" name="title" id="title" value="{{ old('title', $advertisement->title) }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('title') border-red-500 @enderror">
                        @error('title') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="content" class="block text-sm font-medium text-gray-700 mb-1">Содержание *</label>
                        <textarea name="content" id="content" rows="8"
                                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('content') border-red-500 @enderror">{{ old('content', $advertisement->content) }}</textarea>
                        @error('content') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="price" class="block text-sm font-medium text-gray-700 mb-1">Цена (₽)</label>
                            <input type="number" name="price" id="price" value="{{ old('price', $advertisement->price) }}"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>

                        <div>
                            <label for="city" class="block text-sm font-medium text-gray-700 mb-1">Город</label>
                            <input type="text" name="city" id="city" value="{{ old('city', $advertisement->city) }}"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>

                    <div>
                        <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Статус</label>
                        <select name="status" id="status" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="active" {{ old('status', $advertisement->status) == 'active' ? 'selected' : '' }}>Активно</option>
                            <option value="inactive" {{ old('status', $advertisement->status) == 'inactive' ? 'selected' : '' }}>Не активно</option>
                        </select>
                    </div>
                </div>

                <div class="mt-6 flex justify-end space-x-3">
                    <a href="{{ route('advertisements.show', $advertisement) }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-lg">
                        Отмена
                    </a>
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">
                        Сохранить
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

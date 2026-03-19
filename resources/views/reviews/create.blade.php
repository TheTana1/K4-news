@extends('layouts.main')

@section('title', 'Оставить отзыв')

@section('content')
    <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="mb-6">
            <a href="{{ route('reviews.index') }}" class="text-blue-600 hover:text-blue-800">← Назад к отзывам</a>
        </div>

        <div class="bg-white rounded-xl shadow-md overflow-hidden">
            <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                <h1 class="text-2xl font-bold text-gray-900">Оставить отзыв</h1>
            </div>

            <form action="{{ route('reviews.store') }}" method="POST" class="p-6">
                @csrf

                <div class="space-y-4">
                    <!-- Рейтинг -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Оценка</label>
                        <div class="flex space-x-2" x-data="{ rating: {{ old('rating', 5) }} }">
                            @for($i = 1; $i <= 5; $i++)
                                <button type="button" @click="rating = {{ $i }}" class="focus:outline-none">
                                    <svg class="w-8 h-8" :class="rating >= {{ $i }} ? 'text-yellow-400' : 'text-gray-300'" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                    </svg>
                                </button>
                            @endfor
                            <input type="hidden" name="rating" x-model="rating">
                        </div>
                    </div>

                    <!-- Имя (если не авторизован) -->
                    @guest
                        <div>
                            <label for="author_name" class="block text-sm font-medium text-gray-700 mb-1">Ваше имя</label>
                            <input type="text" name="author_name" id="author_name" value="{{ old('author_name') }}"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                    @endguest

                    <!-- Текст отзыва -->
                    <div>
                        <label for="content" class="block text-sm font-medium text-gray-700 mb-1">Ваш отзыв *</label>
                        <textarea name="content" id="content" rows="6"
                                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('content') border-red-500 @enderror"
                                  placeholder="Поделитесь своим мнением...">{{ old('content') }}</textarea>
                        @error('content') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="mt-6 flex justify-end space-x-3">
                    <a href="{{ route('reviews.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-lg">
                        Отмена
                    </a>
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">
                        Отправить
                    </button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
        <script src="//unpkg.com/alpinejs" defer></script>
    @endpush
@endsection

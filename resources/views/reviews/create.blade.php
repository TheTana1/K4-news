@extends('layouts.app')

@section('title', 'Оставить отзыв')

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="mb-3">
                <a href="{{ route('reviews.index') }}" class="btn btn-outline-secondary btn-sm">
                    &larr; Назад к отзывам
                </a>
            </div>

            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Оставить отзыв</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('reviews.store') }}" method="POST">
                        @csrf

                        <!-- Рейтинг -->
                        <div class="mb-3">
                            <label class="form-label">Оценка</label>
                            <div class="d-flex gap-2" x-data="{ rating: {{ old('rating', 5) }} }">
                                @for($i = 1; $i <= 5; $i++)
                                    <button type="button" @click="rating = {{ $i }}" class="btn btn-link p-0 border-0" style="font-size: 2rem; line-height: 1;">
                                        <span x-text="rating >= {{ $i }} ? '★' : '☆'" class="text-warning"></span>
                                    </button>
                                @endfor
                                <input type="hidden" name="rating" x-model="rating">
                            </div>
                            @error('rating') <div class="text-danger small">{{ $message }}</div> @enderror
                        </div>

                        <!-- Имя для гостей -->
                        @guest
                            <div class="mb-3">
                                <label for="author_name" class="form-label">Ваше имя</label>
                                <input type="text" name="author_name" id="author_name" value="{{ old('author_name') }}"
                                       class="form-control @error('author_name') is-invalid @enderror">
                                @error('author_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        @endguest

                        <!-- Текст отзыва -->
                        <div class="mb-3">
                            <label for="content" class="form-label">Ваш отзыв <span class="text-danger">*</span></label>
                            <textarea name="content" id="content" rows="6"
                                      class="form-control @error('content') is-invalid @enderror"
                                      placeholder="Поделитесь своим мнением...">{{ old('content') }}</textarea>
                            @error('content') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('reviews.index') }}" class="btn btn-secondary">Отмена</a>
                            <button type="submit" class="btn btn-primary">Отправить</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script src="//unpkg.com/alpinejs" defer></script>
    @endpush
@endsection

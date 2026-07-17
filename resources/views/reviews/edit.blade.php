@extends('layouts.app')

@section('title', 'Редактировать отзыв')

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="mb-3">
                <a href="{{ route('reviews.show', $review) }}" class="btn btn-outline-secondary btn-sm">
                    &larr; Назад к отзыву
                </a>
            </div>

            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Редактировать отзыв</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('reviews.update', $review) }}" method="POST">
                        @csrf @method('PUT')

                        <!-- Рейтинг -->
                        <div class="mb-3">
                            <label class="form-label">Оценка</label>
                            <div class="d-flex gap-2" x-data="{ rating: {{ old('rating', $review->rating ?? 5) }} }">
                                @for($i = 1; $i <= 5; $i++)
                                    <button type="button" @click="rating = {{ $i }}" class="btn btn-link p-0 border-0" style="font-size: 2rem; line-height: 1;">
                                        <span x-text="rating >= {{ $i }} ? '★' : '☆'" class="text-warning"></span>
                                    </button>
                                @endfor
                                <input type="hidden" name="rating" x-model="rating">
                            </div>
                            @error('rating') <div class="text-danger small">{{ $message }}</div> @enderror
                        </div>

                        <!-- Текст отзыва -->
                        <div class="mb-3">
                            <label for="content" class="form-label">Ваш отзыв <span class="text-danger">*</span></label>
                            <textarea name="content" id="content" rows="6"
                                      class="form-control @error('content') is-invalid @enderror"
                                      placeholder="Поделитесь своим мнением...">{{ old('content', $review->content) }}</textarea>
                            @error('content') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <!-- Статус (для модераторов) -->
                        @can('moderate', $review)
                            <div class="mb-3">
                                <label for="status" class="form-label">Статус</label>
                                <select name="status" id="status" class="form-select">
                                    <option value="active" {{ old('status', $review->status) == 'active' ? 'selected' : '' }}>Активен</option>
                                    <option value="inactive" {{ old('status', $review->status) == 'inactive' ? 'selected' : '' }}>Неактивен</option>
                                </select>
                            </div>
                        @endcan

                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('reviews.show', $review) }}" class="btn btn-secondary">Отмена</a>
                            <button type="submit" class="btn btn-primary">Сохранить изменения</button>
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

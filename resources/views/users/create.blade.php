@extends('layouts.app')

@section('title', 'Создать пользователя')

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="mb-3">
                <a href="{{ route('users.index') }}" class="btn btn-outline-secondary btn-sm">
                    &larr; Назад к списку
                </a>
            </div>

            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Создать нового пользователя</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('users.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <!-- Аватар -->
                        <div class="row mb-4 align-items-center">
                            <div class="col-auto">
                                <div class="position-relative d-inline-block">
                                    <div id="avatarPreview" class="rounded-circle d-flex align-items-center justify-content-center text-white"
                                         style="width:80px;height:80px;font-size:2rem;background-size:cover;background-position:center; background-color: #0D6EFD">
                                        <i class="bi bi-person-add"></i>
                                    </div>
                                    <label for="avatar"
                                           class="position-absolute bottom-0 end-0 bg-white rounded-circle p-1 shadow-sm d-flex align-items-center justify-content-center"
                                           style="cursor:pointer; transform:translate(10%,10%); width:36px; height:36px;">
                                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-gray-600">
                                            <path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z" />
                                            <circle cx="12" cy="13" r="4" />
                                        </svg>
                                    </label>
                                    <input type="file" id="avatar" name="avatar" class="d-none" accept="image/*">
                                </div>
                            </div>
                            <div class="col">
                                <small class="text-muted">Нажмите на иконку камеры, чтобы загрузить фото</small>
                                @error('avatar')
                                <div class="text-danger small mt-1">
                                    <i class="bi bi-exclamation-triangle-fill me-1"></i>
                                    {{ $message }}
                                </div>
                                @enderror
                            </div>

                        </div>

                        <!-- Основные поля -->
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="name" class="form-label">Имя <span class="text-danger">*</span></label>
                                <input type="text" name="name" id="name" value="{{ old('name') }}" required
                                       class="form-control @error('name') is-invalid @enderror">
                                @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                                <input type="email" name="email" id="email" value="{{ old('email') }}" required
                                       class="form-control @error('email') is-invalid @enderror">
                                @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="password" class="form-label">Пароль <span class="text-danger">*</span></label>
                                <input type="password" name="password" id="password" required
                                       class="form-control @error('password') is-invalid @enderror">
                                @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="password_confirmation" class="form-label">Подтверждение пароля <span class="text-danger">*</span></label>
                                <input type="password" name="password_confirmation" id="password_confirmation" required
                                       class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label for="telegram_username" class="form-label">Telegram username</label>
                                <div class="input-group">
                                    <span class="input-group-text">@</span>
                                    <input type="text" name="telegram_username" id="telegram_username" value="{{ old('telegram_username') }}"
                                           class="form-control">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="telegram_id" class="form-label">Telegram ID</label>
                                <input type="text" name="telegram_id" id="telegram_id" value="{{ old('telegram_id') }}"
                                       class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label for="birthday" class="form-label">Дата рождения</label>
                                <input type="date" name="birthday" id="birthday" value="{{ old('birthday') }}"
                                       class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label for="gender" class="form-label">Пол</label>
                                <select name="gender" id="gender" class="form-select">
                                    <option value="">Не указан</option>
                                    <option value="0" {{ old('gender') === '0' ? 'selected' : '' }}>Мужской</option>
                                    <option value="1" {{ old('gender') === '1' ? 'selected' : '' }}>Женский</option>
                                </select>
                            </div>
                        </div>
                        <div class="mt-3" x-data="{ phones: [{ number: '', primary: false }] }">
                            <label class="form-label">Телефоны <span class="text-danger">*</span></label>
                            <template x-for="(phone, index) in phones" :key="index">

                                <div class="input-group mb-2">
                                    <input type="text" x-model="phone.number" :name="`phones[${index}][number]`"
                                           class="form-control" placeholder="+7 (999) 123-45-67">
                                    <button type="button" @click="phones.splice(index, 1)" class="btn btn-outline-danger">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </template>
                            <button type="button" @click="phones.push({ number: '', primary: false })" class="btn btn-sm btn-outline-primary">
                                + Добавить телефон
                            </button>
                        </div>

                        <!-- Роль и статус -->
                        <div class="row g-3 mt-2">
                            <div class="col-md-6">
                                <label for="role_id" class="form-label">Роль</label>
                                <select name="role_id" id="role_id" class="form-select">
                                    @foreach($roles as $role)
                                        <option value="{{ $role->id }}" {{ old('role_id') == $role->id ? 'selected' : '' }}>
                                            {{ $role->label }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6 d-flex align-items-center">
                                <div class="form-check">
                                    <input type="checkbox" name="is_active_in_group" id="is_active_in_group" value="1"
                                           {{ old('is_active_in_group') ? 'checked' : '' }}
                                           class="form-check-input">
                                    <label for="is_active_in_group" class="form-check-label">
                                        Активен в Telegram группе
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2 mt-4">
                            <a href="{{ route('users.index') }}" class="btn btn-secondary">Отмена</a>
                            <button type="submit" class="btn btn-primary">Создать пользователя</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script src="//unpkg.com/alpinejs" defer></script>
        <script>
            // Предпросмотр аватара
            document.getElementById('avatar').addEventListener('change', function(e) {
                const file = e.target.files[0];
                const validTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/svg+xml'];
                if (file && validTypes.includes(file.type)) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const preview = document.getElementById('avatarPreview');
                        preview.style.backgroundImage = `url('${e.target.result}')`;
                        preview.style.backgroundSize = 'cover';
                        preview.style.backgroundPosition = 'center';
                        preview.textContent = '';
                    }
                    reader.readAsDataURL(file);
                }
            });

            // Маска для телефона
            function phoneMask(selector) {
                document.querySelectorAll(selector).forEach(input => {
                    input.addEventListener('input', function(e) {
                        let x = e.target.value.replace(/\D/g, '').match(/(\d{0,1})(\d{0,3})(\d{0,3})(\d{0,2})(\d{0,2})/);
                        e.target.value = !x[2] ? x[1] : '+7 (' + x[2] + ') ' + x[3] + (x[4] ? '-' + x[4] : '') + (x[5] ? '-' + x[5] : '');
                    });
                });
            }

            // Применяем маску к динамически добавляемым полям
            document.addEventListener('DOMContentLoaded', function() {
                phoneMask('input[name*="[number]"]');
            });
        </script>
    @endpush
@endsection

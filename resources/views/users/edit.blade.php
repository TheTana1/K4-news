@extends('layouts.app')

@section('title', 'Редактировать пользователя')

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="mb-3">
                <a href="{{ route('users.show', $user) }}" class="btn btn-outline-secondary btn-sm">
                    &larr; Назад к профилю
                </a>
            </div>

            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Редактировать пользователя</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('users.update', $user) }}" method="POST" enctype="multipart/form-data">
                        @csrf @method('PUT')

                        <!-- Аватар -->
                        <div class="row mb-4 align-items-center">
                            <div class="col-auto">
                                <div class="position-relative d-inline-block">
                                    <div id="avatarPreview" class="rounded-circle d-flex align-items-center justify-content-center text-white overflow-hidden" style="width:80px;height:80px;font-size:2rem;background-size:cover;background-position:center;background-image:{{ $user->avatar_path ? "url('".asset($user->avatar_path)."')" : 'none' }};background-color:{{ $user->avatar_path ? 'transparent' : '#6366f1' }};">
                                        @if(!$user->avatar_path)
                                            {{ mb_strtoupper(mb_substr($user->name, 0, 1)) }}
                                        @endif
                                    </div>
                                    <label for="avatar" class="position-absolute bottom-0 end-0 bg-white rounded-circle p-1 shadow-sm cursor-pointer" style="transform:translate(10%,10%);">
                                        <i class="bi bi-camera fs-6"></i>
                                    </label>
                                    <input type="file" id="avatar" name="avatar" class="d-none" accept="image/*">
                                </div>
                            </div>
                            <div class="col">
                                <small class="text-muted">Нажмите на иконку камеры, чтобы изменить фото</small>
                            </div>
                        </div>

                        <!-- Основные поля -->
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="name" class="form-label">Имя <span class="text-danger">*</span></label>
                                <input type="text" name="name" id="name" value="{{ old('name', $user->name) }}" required
                                       class="form-control @error('name') is-invalid @enderror">
                                @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                                <input type="email" name="email" id="email" value="{{ old('email', $user->email) }}" required
                                       class="form-control @error('email') is-invalid @enderror">
                                @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="password" class="form-label">Новый пароль</label>
                                <input type="password" name="password" id="password"
                                       class="form-control @error('password') is-invalid @enderror">
                                <small class="text-muted">Оставьте пустым, чтобы не менять</small>
                                @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="password_confirmation" class="form-label">Подтверждение пароля</label>
                                <input type="password" name="password_confirmation" id="password_confirmation"
                                       class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label for="telegram_username" class="form-label">Telegram username</label>
                                <div class="input-group">
                                    <span class="input-group-text">@</span>
                                    <input type="text" name="telegram_username" id="telegram_username"
                                           value="{{ old('telegram_username', $user->telegram_username) }}"
                                           class="form-control">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="telegram_id" class="form-label">Telegram ID</label>
                                <input type="text" name="telegram_id" id="telegram_id"
                                       value="{{ old('telegram_id', $user->telegram_id) }}"
                                       class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label for="birthday" class="form-label">Дата рождения</label>
                                <input type="date" name="birthday" id="birthday"
                                       value="{{ old('birthday', $user->birthday?->format('Y-m-d')) }}"
                                       class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label for="gender" class="form-label">Пол</label>
                                <select name="gender" id="gender" class="form-select">
                                    <option value="">Не указан</option>
                                    <option value="0" {{ old('gender', $user->gender) === 0 ? 'selected' : '' }}>Мужской</option>
                                    <option value="1" {{ old('gender', $user->gender) === 1 ? 'selected' : '' }}>Женский</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="likes" class="form-label">Лайки</label>
                                <input type="number" name="likes" id="likes" value="{{ old('likes', $user->likes) }}"
                                       class="form-control">
                            </div>
                        </div>

                        <!-- Телефоны (Alpine) -->
                        <div class="mt-3" x-data="{
                            phones: {{ json_encode($user->phones->map(fn($phone) => ['id' => $phone->id, 'number' => $phone->phone_number, 'primary' => $phone->is_primary])) }}
                        }">
                            <label class="form-label">Телефоны</label>
                            <template x-for="(phone, index) in phones" :key="index">
                                <div class="input-group mb-2">
                                    <input type="hidden" :name="`phones[${index}][id]`" x-model="phone.id">
                                    <input type="text" x-model="phone.number" :name="`phones[${index}][number]`"
                                           class="form-control" placeholder="+7 (999) 123-45-67">
                                    <div class="input-group-text">
                                        <input type="radio" name="primary_phone" :checked="phone.primary"
                                               @change="phones.forEach((p, i) => p.primary = i === index)"
                                               class="form-check-input mt-0">
                                        <label class="form-check-label ms-1">Основной</label>
                                    </div>
                                    <button type="button" @click="phones.splice(index, 1)" class="btn btn-outline-danger">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </template>
                            <button type="button" @click="phones.push({ id: null, number: '', primary: false })" class="btn btn-sm btn-outline-primary">
                                + Добавить телефон
                            </button>
                        </div>

                        <!-- Роль и статус -->
                        <div class="row g-3 mt-2">
                            <div class="col-md-6">
                                <label for="role_id" class="form-label">Роль</label>
                                <select name="role_id" id="role_id" class="form-select">
                                    @foreach($roles as $role)
                                        <option value="{{ $role->id }}" {{ old('role_id', $user->role_id) == $role->id ? 'selected' : '' }}>
                                            {{ $role->label }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6 d-flex align-items-center">
                                <div class="form-check">
                                    <input type="checkbox" name="is_active_in_group" id="is_active_in_group" value="1"
                                           {{ old('is_active_in_group', $user->is_active_in_group) ? 'checked' : '' }}
                                           class="form-check-input">
                                    <label for="is_active_in_group" class="form-check-label">
                                        Активен в Telegram группе
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Даты (только для просмотра) -->
                        <hr>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label text-muted">Создан</label>
                                <p class="fw-bold">{{ $user->created_at->format('d.m.Y H:i') }}</p>
                            </div>
                            @if($user->joined_at)
                                <div class="col-md-4">
                                    <label class="form-label text-muted">Вступил в группу</label>
                                    <p class="fw-bold">{{ $user->joined_at->format('d.m.Y H:i') }}</p>
                                </div>
                            @endif
                            @if($user->left_at)
                                <div class="col-md-4">
                                    <label class="form-label text-muted">Покинул группу</label>
                                    <p class="fw-bold">{{ $user->left_at->format('d.m.Y H:i') }}</p>
                                </div>
                            @endif
                        </div>

                        <div class="d-flex justify-content-end gap-2 mt-4">
                            <a href="{{ route('users.show', $user) }}" class="btn btn-secondary">Отмена</a>
                            <button type="submit" class="btn btn-primary">Сохранить изменения</button>
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
                if (file) {
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

            document.addEventListener('DOMContentLoaded', function() {
                phoneMask('input[name*="[number]"]');
            });
        </script>
    @endpush
@endsection

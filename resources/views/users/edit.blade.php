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
                    <!-- Вывод общих ошибок формы -->
                    @if ($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <strong>Ошибка!</strong> Пожалуйста, исправьте следующие ошибки:
                            <ul class="mb-0 mt-2">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif
                    <form action="{{ route('users.update', $user) }}" method="POST" enctype="multipart/form-data">
                        @csrf @method('PUT')

                        <!-- Аватар -->
                        <div class="row mb-4 align-items-center">
                            <div class="col-auto">
                                <div class="position-relative d-inline-block">
                                    <div id="avatarContainer" class="position-relative d-inline-block mb-3">
                                        @if($user->avatar_path)
                                            <img id="avatarPreview" src="{{ asset($user->avatar_path) }}"
                                                 alt="{{ $user->name }}"
                                                 class="rounded-circle border border-3 border-white shadow" width="120"
                                                 height="120" style="object-fit: cover;">
                                        @else
                                            @php
                                                $colors = ['primary', 'secondary', 'success', 'danger', 'warning', 'info', 'dark'];
                                                $color = $colors[abs(crc32($user->name)) % count($colors)];
                                            @endphp
                                            <div id="avatarPreview"
                                                 class="rounded-circle d-flex align-items-center justify-content-center text-white mx-auto bg-{{ $color }}"
                                                 style="width:120px;height:120px;font-size:3rem;">
                                                {{ mb_strtoupper(mb_substr($user->name, 0, 1)) }}
                                            </div>
                                        @endif
                                    </div>
                                    <label for="avatar"
                                           class="position-absolute bottom-0 end-0 bg-white rounded-circle p-1 shadow-sm cursor-pointer"
                                           style="transform:translate(10%,10%);cursor:pointer;">
                                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none"
                                             stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                             stroke-linejoin="round" class="text-gray-600">
                                            <path
                                                d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/>
                                            <circle cx="12" cy="13" r="4"/>
                                        </svg>
                                    </label>
                                    <input type="file" id="avatar" name="avatar" class="d-none" accept="image/*">
                                </div>
                                @error('avatar')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col">
                                <small class="text-muted">Нажмите на иконку камеры, чтобы изменить фото</small>
                                <div>
                                    <small class="text-muted">Формат: jpeg, png, jpg, gif. Максимум: 2MB</small>
                                </div>
                            </div>
                        </div>

                        <!-- Основные поля -->
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="name" class="form-label">Имя <span class="text-danger">*</span></label>
                                <input type="text" name="name" id="name" value="{{ old('name', $user->name) }}"
                                       class="form-control @error('name') is-invalid @enderror">
                                @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                                <input type="email" name="email" id="email" value="{{ old('email', $user->email) }}"
                                       class="form-control @error('email') is-invalid @enderror">
                                @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="password" class="form-label">Новый пароль</label>
                                <input type="password" name="password" id="password"
                                       class="form-control @error('password') is-invalid @enderror">
                                @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">Оставьте пустым, чтобы не менять. Минимум 8 символов</small>
                            </div>

                            <div class="col-md-6">
                                <label for="password_confirmation" class="form-label">Подтверждение пароля</label>
                                <input type="password" name="password_confirmation" id="password_confirmation"
                                       class="form-control @error('password_confirmation') is-invalid @enderror">
                                @error('password_confirmation')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="telegram_username" class="form-label">Telegram username</label>
                                <div class="input-group">
                                    <span class="input-group-text">@</span>
                                    <input type="text" name="telegram_username" id="telegram_username"
                                           value="{{ old('telegram_username', $user->telegram_username) }}"
                                           class="form-control @error('telegram_username') is-invalid @enderror">
                                    @error('telegram_username')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label for="telegram_id" class="form-label">Telegram ID</label>
                                <input type="text" name="telegram_id" id="telegram_id"
                                       value="{{ old('telegram_id', $user->telegram_id) }}"
                                       class="form-control @error('telegram_id') is-invalid @enderror">
                                @error('telegram_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="birthday" class="form-label">Дата рождения</label>
                                <input type="date" name="birthday" id="birthday"
                                       value="{{ old('birthday', $user->birthday?->format('Y-m-d')) }}"
                                       class="form-control @error('birthday') is-invalid @enderror">
                                @error('birthday')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="gender" class="form-label">Пол</label>
                                <select name="gender" id="gender"
                                        class="form-select @error('gender') is-invalid @enderror">
                                    <option value="">Не указан</option>
                                    <option value="0" {{ old('gender', $user->gender) === 0 ? 'selected' : '' }}>
                                        Мужской
                                    </option>
                                    <option value="1" {{ old('gender', $user->gender) === 1 ? 'selected' : '' }}>
                                        Женский
                                    </option>
                                </select>
                                @error('gender')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Телефоны (Alpine) -->
                        <div class="mt-3" x-data="{
                            phones: {{ json_encode($user->phones->map(fn($phone) => ['id' => $phone->id, 'number' => $phone->phone_number, 'primary' => $phone->is_primary])) }},
                            errors: {{ json_encode(session('errors') ? session('errors')->getBag('default')->toArray() : []) }}
                        }">
                            <label class="form-label">Телефоны</label>
                            <template x-for="(phone, index) in phones" :key="index">
                                <div>
                                    <div class="input-group mb-2">
                                        <input type="hidden" :name="`phones[${index}][id]`" x-model="phone.id">
                                        <input type="text" x-model="phone.number" :name="`phones[${index}][number]`"
                                               class="form-control phone-mask" placeholder="+7 (999) 123-45-67">
                                        <div class="input-group-text">
                                            <input type="radio" name="primary_phone" :checked="phone.primary"
                                                   @change="phones.forEach((p, i) => p.primary = i === index)"
                                                   class="form-check-input mt-0">
                                            <label class="form-check-label ms-1">Основной</label>
                                        </div>
                                        <button type="button" @click="phones.splice(index, 1)"
                                                class="btn btn-outline-danger">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                    <!-- Ошибки для телефона -->
                                    <div x-show="errors[`phones.${index}.number`]" class="text-danger small mt-1"
                                         x-text="errors[`phones.${index}.number`]"></div>
                                </div>
                            </template>
                            <button type="button" @click="phones.push({ id: null, number: '', primary: false })"
                                    class="btn btn-sm btn-outline-primary">
                                + Добавить телефон
                            </button>
                            @error('phones.*.number')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Роль и статус -->

                        <div class="row g-3 mt-2">
                            @if(auth()->user()->isAdmin())
                                <div class="col-md-6">
                                    <label class="form-label">Роль</label>
                                    <div class="fw-bold">
                                        <select name="role_id" id="role_id"
                                                class="form-select @error('role_id') is-invalid @enderror">
                                            @foreach($roles as $role)
                                                <option
                                                    value="{{ $role->id }}" {{ old('role_id', $user->role_id) == $role->id ? 'selected' : '' }}>
                                                    {{ $role->label }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('role_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            @endif
                            <div class="col-md-6">
                                <label class="form-label">Статус в Telegram группе</label>
                                <div class="fw-bold">
                                    @if($user->is_active_in_group)
                                        <span class="text-success">
                    <i class="bi bi-check-circle fs-3"></i> Активен
                </span>
                                    @else
                                        <span class="text-secondary">
                    <i class="bi bi-x-circle fs-3"></i> Не активен
                </span>
                                    @endif
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
            document.addEventListener('DOMContentLoaded', function () {
                // Предпросмотр аватара
                const avatarInput = document.getElementById('avatar');
                const avatarPreview = document.getElementById('avatarPreview');

                if (avatarInput && avatarPreview) {
                    avatarInput.addEventListener('change', function (e) {
                        const file = e.target.files[0];
                        if (file) {
                            const reader = new FileReader();
                            reader.onload = function (e) {
                                if (avatarPreview.tagName === 'IMG') {
                                    avatarPreview.src = e.target.result;
                                } else {
                                    const newImg = document.createElement('img');
                                    newImg.id = 'avatarPreview';
                                    newImg.src = e.target.result;
                                    newImg.alt = 'Новый аватар';
                                    newImg.className = 'rounded-circle border border-3 border-white shadow';
                                    newImg.width = 120;
                                    newImg.height = 120;
                                    newImg.style.objectFit = 'cover';
                                    avatarPreview.parentNode.replaceChild(newImg, avatarPreview);
                                }
                            }
                            reader.readAsDataURL(file);
                        }
                    });
                }

                // Маска для телефона
                function phoneMask(selector) {
                    document.querySelectorAll(selector).forEach(input => {
                        input.addEventListener('input', function (e) {
                            let x = e.target.value.replace(/\D/g, '').match(/(\d{0,1})(\d{0,3})(\d{0,3})(\d{0,2})(\d{0,2})/);
                            e.target.value = !x[2] ? x[1] : '+7 (' + x[2] + ') ' + x[3] + (x[4] ? '-' + x[4] : '') + (x[5] ? '-' + x[5] : '');
                        });
                    });
                }

                phoneMask('.phone-mask');

                // MutationObserver для динамических полей
                const observer = new MutationObserver(function (mutations) {
                    mutations.forEach(function (mutation) {
                        if (mutation.type === 'childList') {
                            mutation.addedNodes.forEach(function (node) {
                                if (node.nodeType === 1 && node.querySelector) {
                                    node.querySelectorAll('.phone-mask').forEach(function (input) {
                                        input.removeEventListener('input', input._maskHandler);
                                        input._maskHandler = function (e) {
                                            let x = e.target.value.replace(/\D/g, '').match(/(\d{0,1})(\d{0,3})(\d{0,3})(\d{0,2})(\d{0,2})/);
                                            e.target.value = !x[2] ? x[1] : '+7 (' + x[2] + ') ' + x[3] + (x[4] ? '-' + x[4] : '') + (x[5] ? '-' + x[5] : '');
                                        };
                                        input.addEventListener('input', input._maskHandler);
                                    });
                                }
                            });
                        }
                    });
                });

                observer.observe(document.body, {
                    childList: true,
                    subtree: true
                });
            });
        </script>
    @endpush
@endsection


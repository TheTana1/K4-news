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


                        <div class="row mb-4 align-items-center">
                            <div class="col-auto">
                                <div class="position-relative d-inline-block">
                                    <div id="avatarPreview"
                                         class="rounded-circle d-flex align-items-center justify-content-center text-white overflow-hidden"
                                         style="width:80px;height:80px;background-color:#0D6EFD;">

                                        @if ($user->avatar_path)
                                            <img src="{{ $user->avatar_path }}" alt=""
                                                 style="width:100%;height:100%;object-fit:cover;">
                                        @else
                                            {{-- инициалы или иконка --}}
                                            {{ mb_substr($user->name, 0, 1) }}
                                        @endif
                                    </div>
                                    <label for="avatar"
                                           class="position-absolute bottom-0 end-0 bg-body-tertiary fw-semibold rounded-circle p-1 shadow-sm d-flex align-items-center justify-content-center"
                                           style="cursor:pointer; transform:translate(10%,10%); width:36px; height:36px;">
                                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none"
                                             stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                             stroke-linejoin="round" class="text-gray-600">
                                            <path
                                                d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/>
                                            <circle cx="12" cy="13" r="4"/>
                                        </svg>
                                    </label>
                                    <input type="file"
                                           id="avatar"
                                           name="avatar"
                                           class="d-none"
                                           accept=".jpg,.jpeg,.png,.gif,.bmp,.webp,.svg">
                                </div>
                            </div>

                            <div class="col">
                                <small class="text-muted">Нажмите на иконку камеры, чтобы загрузить фото</small>
                                @error('avatar')
                                <div class="invalid-feedback d-block">
                                    {{ $message }}
                                </div>
                                @enderror
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
                                           class="form-control @error('telegram_username') is-invalid @enderror"
                                           disabled>
                                    @error('telegram_username')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label for="telegram_id" class="form-label">Telegram ID</label>
                                <input type="text" name="telegram_id" id="telegram_id"
                                       value="{{ old('telegram_id', $user->telegram_id) }}"
                                       class="form-control @error('telegram_id') is-invalid @enderror" disabled>
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

                        <div class="mt-3" id="phones-wrapper">
                            <label class="form-label">Телефоны <span class="text-danger">*</span></label>

                            <div id="phones-list">
                                @forelse($user->phones as $index => $phone)
                                    <div class="phone-item mb-2" data-index="{{ $index }}">
                                        <div class="input-group">
                                            <input type="hidden" name="phones[{{ $index }}][id]" value="{{ $phone->id }}">
                                            <input type="text"
                                                   name="phones[{{ $index }}][number]"
                                                   value="{{ $phone->phone_number }}"
                                                   class="form-control phone-mask"
                                                   placeholder="+7 (999) 123-45-67">

                                            <button type="button" class="btn btn-outline-danger btn-remove-phone">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                        @error("phones.$index.number")
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>
                                @empty
                                    <div class="phone-item mb-2" data-index="0">
                                        <div class="input-group">
                                            <input type="hidden" name="phones[0][id]" value="">
                                            <input type="text"
                                                   name="phones[0][number]"
                                                   class="form-control phone-mask"
                                                   placeholder="+7 (999) 123-45-67">

                                            <button type="button" class="btn btn-outline-danger btn-remove-phone" disabled>
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                @endforelse
                            </div>

                            <button type="button" id="add-phone" class="btn btn-sm btn-outline-primary mt-2">
                                + Добавить телефон
                            </button>

                            @error('phones')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                            @error('phones.*.number')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>


                            <!-- Роль и статус -->

                        <div class="row g-3 mt-2">
                            @if(auth()->user()->isAdmin())
                                <div class="col-md-6">
                                    <div class="d-flex align-items-center gap-2">
                                        <label for="role_id" class="form-label mb-0">Роль:</label>
                                        <select name="role_id" id="role_id"
                                                class="form-select form-select-sm w-auto @error('role_id') is-invalid @enderror">
                                            @foreach($roles as $role)
                                                <option value="{{ $role->id }}" @selected(old('role_id', $user->role_id) == $role->id)>
                                                    {{ $role->label }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('role_id')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            @endif

                            <div class="col-md-6">
                                <div class="d-flex align-items-center gap-2">
                                    <label for="is_active_in_group" class="form-label mb-0">Статус в Telegram:</label>
                                    @if(auth()->user()->isModerator()||auth()->user()->isAdmin())
                                    <select name="is_active_in_group" id="is_active_in_group"
                                            class="form-select form-select-sm w-auto @error('is_active_in_group') is-invalid @enderror">
                                        <option value="1" @selected(old('is_active_in_group', $user->is_active_in_group) == 1)>Активен</option>
                                        <option value="0" @selected(old('is_active_in_group', $user->is_active_in_group) == 0)>Неактивен</option>
                                    </select>
                                    @endif
                                    @if($user->is_active_in_group)
                                        <i class="bi bi-check-circle-fill text-success fs-5" title="Активен"></i>
                                    @else
                                        <i class="bi bi-x-circle-fill text-secondary fs-5" title="Не активен"></i>
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

            document.getElementById('avatar').addEventListener('change', function (e) {
                const file = e.target.files[0];
                const validTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/svg+xml'];
                if (file && validTypes.includes(file.type)) {
                    const reader = new FileReader();
                    reader.onload = function (e) {
                        const preview = document.getElementById('avatarPreview');
                        preview.style.backgroundImage = `url('${e.target.result}')`;
                        preview.style.backgroundSize = 'cover';
                        preview.style.backgroundPosition = 'center';
                        preview.textContent = '';
                    }
                    reader.readAsDataURL(file);
                }
            });

            document.addEventListener('DOMContentLoaded', function () {
                const list = document.getElementById('phones-list');
                const addBtn = document.getElementById('add-phone');

                // Маска телефона
                function applyPhoneMask(input) {
                    if (input.dataset.maskApplied) return;
                    input.dataset.maskApplied = 'true';

                    input.addEventListener('input', function (e) {
                        let x = e.target.value.replace(/\D/g, '').match(/(\d{0,1})(\d{0,3})(\d{0,3})(\d{0,2})(\d{0,2})/);
                        e.target.value = !x[2] ? x[1] : '+7 (' + x[2] + ') ' + x[3] + (x[4] ? '-' + x[4] : '') + (x[5] ? '-' + x[5] : '');
                    });
                }

                // Применяем маску ко всем существующим полям
                document.querySelectorAll('.phone-mask').forEach(applyPhoneMask);

                // Добавление нового телефона
                addBtn.addEventListener('click', function () {
                    const index = list.querySelectorAll('.phone-item').length;

                    const html = `
            <div class="phone-item mb-2" data-index="${index}">
                <div class="input-group">
                    <input type="hidden" name="phones[${index}][id]" value="">
                    <input type="text"
                           name="phones[${index}][number]"
                           class="form-control phone-mask"
                           placeholder="+7 (999) 123-45-67">
                    <button type="button" class="btn btn-outline-danger btn-remove-phone">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
            </div>
        `;

                    list.insertAdjacentHTML('beforeend', html);

                    // Навешиваем маску на новое поле
                    const newInput = list.querySelector(`.phone-item[data-index="${index}"] .phone-mask`);
                    applyPhoneMask(newInput);

                    updateRemoveButtons();
                });

                // Удаление телефона
                list.addEventListener('click', function (e) {
                    const btn = e.target.closest('.btn-remove-phone');
                    if (!btn) return;

                    const items = list.querySelectorAll('.phone-item');
                    if (items.length <= 1) return; // нельзя удалить последний

                    btn.closest('.phone-item').remove();
                    reindexPhones();
                    updateRemoveButtons();
                });

                // Переиндексация после удаления
                function reindexPhones() {
                    list.querySelectorAll('.phone-item').forEach((item, index) => {
                        item.dataset.index = index;

                        item.querySelector('input[type="hidden"]').name = `phones[${index}][id]`;
                        item.querySelector('.phone-mask').name = `phones[${index}][number]`;

                    });
                }

                // Блокируем кнопку удаления, если остался один телефон
                function updateRemoveButtons() {
                    const items = list.querySelectorAll('.phone-item');
                    items.forEach(item => {
                        const btn = item.querySelector('.btn-remove-phone');
                        btn.disabled = items.length === 1;
                    });
                }

                updateRemoveButtons();
            });

        </script>
    @endpush
@endsection


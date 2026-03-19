@extends('layouts.main')

@section('title', 'Редактировать пользователя')

@section('content')
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="mb-6">
            <a href="{{ route('users.show', $user) }}" class="text-blue-600 hover:text-blue-800">← Назад к профилю</a>
        </div>

        <div class="bg-white rounded-xl shadow-md overflow-hidden">
            <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                <h1 class="text-2xl font-bold text-gray-900">Редактировать пользователя</h1>
            </div>

            <form action="{{ route('users.update', $user) }}" method="POST" enctype="multipart/form-data" class="p-6">
                @csrf @method('PUT')

                <div class="space-y-6">
                    <!-- Аватар -->
                    <div class="flex items-center space-x-6">
                        <div class="relative">
                            <div id="avatarPreview" class="h-20 w-20 rounded-full bg-gradient-to-r from-blue-500 to-purple-600 flex items-center justify-center text-white text-2xl font-bold overflow-hidden"
                                 style="@if($user->avatar_path) background-image: url('{{ asset($user->avatar_path) }}'); background-size: cover; background-position: center; @endif">
                                @if(!$user->avatar_path)
                                    {{ substr($user->name, 0, 1) }}
                                @endif
                            </div>
                            <label for="avatar" class="absolute bottom-0 right-0 bg-white rounded-full p-1 shadow-lg cursor-pointer">
                                <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                </svg>
                            </label>
                        </div>
                        <input type="file" id="avatar" name="avatar" class="hidden" accept="image/*">
                        <div class="text-sm text-gray-500">
                            Нажмите на иконку, чтобы изменить фото
                        </div>
                    </div>

                    <!-- Основные поля -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Имя *</label>
                            <input type="text" name="name" id="name" value="{{ old('name', $user->name) }}" required
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('name') border-red-500 @enderror">
                            @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email *</label>
                            <input type="email" name="email" id="email" value="{{ old('email', $user->email) }}" required
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('email') border-red-500 @enderror">
                            @error('email') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Новый пароль</label>
                            <input type="password" name="password" id="password"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('password') border-red-500 @enderror">
                            <p class="mt-1 text-xs text-gray-500">Оставьте пустым, чтобы не менять</p>
                            @error('password') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">Подтверждение пароля</label>
                            <input type="password" name="password_confirmation" id="password_confirmation"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>

                        <div>
                            <label for="telegram_username" class="block text-sm font-medium text-gray-700 mb-1">Telegram username</label>
                            <div class="flex">
                                <span class="inline-flex items-center px-3 bg-gray-100 border border-r-0 border-gray-300 rounded-l-lg text-gray-500">@</span>
                                <input type="text" name="telegram_username" id="telegram_username"
                                       value="{{ old('telegram_username', $user->telegram_username) }}"
                                       class="flex-1 px-3 py-2 border border-gray-300 rounded-r-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                        </div>

                        <div>
                            <label for="telegram_id" class="block text-sm font-medium text-gray-700 mb-1">Telegram ID</label>
                            <input type="text" name="telegram_id" id="telegram_id"
                                   value="{{ old('telegram_id', $user->telegram_id) }}"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>

                        <div>
                            <label for="birthday" class="block text-sm font-medium text-gray-700 mb-1">Дата рождения</label>
                            <input type="date" name="birthday" id="birthday"
                                   value="{{ old('birthday', $user->birthday?->format('Y-m-d')) }}"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>

                        <div>
                            <label for="gender" class="block text-sm font-medium text-gray-700 mb-1">Пол</label>
                            <select name="gender" id="gender" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">Не указан</option>
                                <option value="0" {{ old('gender', $user->gender) === 0 ? 'selected' : '' }}>Мужской</option>
                                <option value="1" {{ old('gender', $user->gender) === 1 ? 'selected' : '' }}>Женский</option>
                            </select>
                        </div>

                        <div>
                            <label for="likes" class="block text-sm font-medium text-gray-700 mb-1">Лайки</label>
                            <input type="number" name="likes" id="likes" value="{{ old('likes', $user->likes) }}"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>

                    <!-- Телефоны -->
                    <div x-data="{
                    phones: {{ json_encode($user->phones->map(fn($phone) => ['id' => $phone->id, 'number' => $phone->phone_number, 'primary' => $phone->is_primary])) }}
                }">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Телефоны</label>

                        <template x-for="(phone, index) in phones" :key="index">
                            <div class="flex items-center space-x-2 mb-2">
                                <input type="hidden" :name="`phones[${index}][id]`" x-model="phone.id">
                                <input type="text"
                                       x-model="phone.number"
                                       :name="`phones[${index}][number]`"
                                       class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                       placeholder="+7 (999) 123-45-67">

                                <label class="flex items-center space-x-1">
                                    <input type="radio"
                                           name="primary_phone"
                                           :checked="phone.primary"
                                           @change="phones.forEach((p, i) => p.primary = i === index)"
                                           class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300">
                                    <span class="text-sm text-gray-600">Основной</span>
                                </label>

                                <button type="button"
                                        @click="phones.splice(index, 1)"
                                        class="text-red-600 hover:text-red-800 p-1">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                    </svg>
                                </button>
                            </div>
                        </template>

                        <button type="button"
                                @click="phones.push({ id: null, number: '', primary: false })"
                                class="mt-2 text-sm text-blue-600 hover:text-blue-800">
                            + Добавить телефон
                        </button>
                    </div>

                    <!-- Роль -->
                    <div>
                        <label for="role_id" class="block text-sm font-medium text-gray-700 mb-1">Роль</label>
                        <select name="role_id" id="role_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                            @foreach($roles as $role)
                                <option value="{{ $role->id }}" {{ old('role_id', $user->role_id) == $role->id ? 'selected' : '' }}>
                                    {{ $role->label }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Статус в группе -->
                    <div class="flex items-center">
                        <input type="checkbox" name="is_active_in_group" id="is_active_in_group" value="1"
                               {{ old('is_active_in_group', $user->is_active_in_group) ? 'checked' : '' }}
                               class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                        <label for="is_active_in_group" class="ml-2 block text-sm text-gray-700">
                            Активен в Telegram группе
                        </label>
                    </div>

                    <!-- Даты (только для просмотра) -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 pt-4 border-t border-gray-200">
                        <div>
                            <label class="block text-sm text-gray-500">Создан</label>
                            <p class="text-sm font-medium text-gray-900">{{ $user->created_at->format('d.m.Y H:i') }}</p>
                        </div>

                        @if($user->joined_at)
                            <div>
                                <label class="block text-sm text-gray-500">Вступил в группу</label>
                                <p class="text-sm font-medium text-gray-900">{{ $user->joined_at->format('d.m.Y H:i') }}</p>
                            </div>
                        @endif

                        @if($user->left_at)
                            <div>
                                <label class="block text-sm text-gray-500">Покинул группу</label>
                                <p class="text-sm font-medium text-gray-900">{{ $user->left_at->format('d.m.Y H:i') }}</p>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="mt-6 flex justify-end space-x-3">
                    <a href="{{ route('users.show', $user) }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-lg">
                        Отмена
                    </a>
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">
                        Сохранить изменения
                    </button>
                </div>
            </form>
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

            // Применяем маску к динамически добавляемым полям
            document.addEventListener('DOMContentLoaded', function() {
                phoneMask('input[name*="[number]"]');
            });
        </script>
    @endpush
@endsection

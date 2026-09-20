@extends('layouts.app')

@section('title', 'Создать объявление')

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="mb-3">
                <a href="{{ route('advertisements.index') }}" class="btn btn-outline-secondary btn-sm">
                    &larr; Назад к списку
                </a>
            </div>

            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Создать новое объявление</h5>
                </div>
                <div class="card-body">
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


                    <form action="{{ route('advertisements.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-3">
                            <label for="content" class="form-label">Содержание <span class="text-danger">*</span></label>
                            <textarea name="content" id="content" rows="8"
                                      class="form-control @error('content') is-invalid @enderror">{{ old('content') }}</textarea>
                            @error('content') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <label for="status" class="form-label">Статус</label>
                            <select name="status" id="status" class="form-select">
                                <option value="active">Активно</option>
                                <option value="inactive">Не активно</option>
                            </select>
                            @error('status')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="role_id" class="form-label">Кому отправить?</label>
                            <select name="role_id" id="role_id" class="form-select">
                                <option value="2">Всем</option>
                                <option value="3">Сотрудникам кухни</option>
                                <option value="4">Сотрудникам зала</option>

                            </select>
                            @error('role_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <input type="hidden"
                               name="user_id"
                               value="{{ old('user_id', auth()->user()->id) }}">


                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('advertisements.index') }}" class="btn btn-secondary">Отмена</a>
                            <button type="submit" class="btn btn-primary">Создать</button>
                        </div>



                        <div class="mb-3">
                            <label for="files" class="form-label">Добавить файлы</label>
                            <input type="file"
                                   name="files[]"
                                   id="files"
                                   multiple
                                   accept=".pdf,.txt,.xls,.xlsx,.doc,.docx,.jpg,.jpeg,.png,.gif,.bmp,.webp,.svg"
                                   class="form-control @error('files.*') is-invalid @enderror">
                            @if ($errors->has('files'))
                                <div class="invalid-feedback d-block">
                                    {{ $errors->first('files') }}
                                </div>
                            @endif

                            @if ($errors->has('files.*'))
                                <div class="invalid-feedback d-block">
                                    @foreach ($errors->get('files.*') as $error)
                                        @if (is_array($error))
                                            @foreach ($error as $message)
                                                <div>{{ $message }}</div>
                                            @endforeach
                                        @else
                                            <div>{{ $error }}</div>
                                        @endif
                                    @endforeach
                                </div>
                            @endif

                            <small class="text-muted">Можно загрузить: PDF, TXT, XLS, XLSX, DOC, DOCX изображения (JPG, PNG, GIF, BMP, WEBP, SVG)</small>
                            <div id="fileList" class="mt-2"></div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const fileInput = document.getElementById('files');
                const fileList = document.getElementById('fileList');

                if (fileInput) {
                    fileInput.addEventListener('change', function(e) {
                        fileList.innerHTML = '';

                        if (this.files.length > 0) {
                            const list = document.createElement('div');
                            list.className = 'list-group';

                            for (let i = 0; i < this.files.length; i++) {
                                const file = this.files[i];
                                const item = document.createElement('div');
                                item.className = 'list-group-item list-group-item-action d-flex justify-content-between align-items-center';

                                let icon = 'bi-file-earmark';
                                const type = file.type;
                                if (type.startsWith('image/')) {
                                    icon = 'bi-file-image';
                                } else if (type === 'application/pdf') {
                                    icon = 'bi-file-pdf';
                                } else if (type === 'text/plain') {
                                    icon = 'bi-file-text';
                                }

                                item.innerHTML = `
                                <span>
                                    <i class="bi ${icon} me-1"></i>
                                    ${file.name}
                                </span>
                                <span class="badge bg-primary rounded-pill">
                                    ${(file.size / 1024).toFixed(1)} KB
                                </span>
                            `;
                                list.appendChild(item);
                            }

                            fileList.appendChild(list);
                        }
                    });
                }
            });
        </script>
    @endpush
@endsection

@extends('layouts.main')

@section('title', 'Объявления')

@section('content')
    <h1>Объявления</h1>

    <div class="announcements-list">
        @forelse($announcements ?? [] as $announcement)
            <div class="announcement-item">
                <h3>{{ $announcement['title'] }}</h3>
                <p>{{ $announcement['content'] }}</p>
                <small>Дата: {{ $announcement['date'] }}</small>
            </div>
        @empty
            <p>Нет активных объявлений</p>
        @endforelse
    </div>
@endsection

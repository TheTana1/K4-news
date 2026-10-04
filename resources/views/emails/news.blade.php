<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>{{ $header }}</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.5; color: #333;">
<h1 style="color: #0d6efd;">{{ $header }}</h1>

<p><strong>🆔 ID:</strong> {{ $news->id }}</p>
<p><strong>📅 Дата:</strong> {{ local_date($news->published_at ?? now()) }}</p>

<p><strong>✏️ Текст:</strong></p>
<blockquote style="border-left: 3px solid #0d6efd; padding-left: 15px; color: #555;">
    {{ $news->content }}
</blockquote>
<a href="{{route('news.show',$news->id)}}">Подробнее на сайте K4-News</a>
<hr style="border: none; border-top: 1px solid #eee; margin: 30px 0;">
<p style="font-size: 12px; color: #999;">
    Это письмо отправлено автоматически. Не отвечайте на него.
</p>
</body>
</html>

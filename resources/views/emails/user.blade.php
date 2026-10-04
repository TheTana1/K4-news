<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>{{ $header }}</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.5; color: #333;">
<h1 style="color: #0d6efd;">👤 {{ $header }}</h1>

<blockquote style="border-left: 3px solid #0d6efd; padding-left: 15px; color: #555;">
    {!! nl2br(e($text)) !!}
</blockquote>

<hr style="border: none; border-top: 1px solid #eee; margin: 30px 0;">
<p style="font-size: 12px; color: #999;">
    Это письмо отправлено автоматически. Не отвечайте на него.
</p>
</body>
</html>

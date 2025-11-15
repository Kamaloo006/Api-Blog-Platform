<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hello Sir</title>
</head>

<body>
    <h2>مرحباً {{ $post->user->name }}! 👋</h2>

    <p>هناك تعليق جديد على مقالك:</p>

    <div style="background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 10px 0;">
        <strong>المقال:</strong> {{ $post->title }}<br>
        <strong>الكاتب:</strong> {{ $comment->user->name }}<br>
        <strong>التعليق:</strong> {{ $comment->content }}
    </div>

    <p>
        <a href="{{ url('/posts/' . $post->id) }}"
            style="background: #007bff; color: white; padding: 10px 15px; text-decoration: none; border-radius: 5px;">
            عرض التعليق
        </a>
    </p>

    <hr>
    <small>تم إرسال هذا الإيميل تلقائياً من {{ config('app.name') }}</small>
</body>

</html>
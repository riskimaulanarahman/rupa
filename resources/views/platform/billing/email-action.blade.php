<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }}</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f8fafc; padding: 32px; }
        .card { max-width: 560px; margin: 0 auto; background: #fff; border: 1px solid #e5e7eb; border-radius: 16px; padding: 24px; }
        .success { color: #047857; }
        .error { color: #b91c1c; }
    </style>
</head>
<body>
    <div class="card">
        <h1 class="{{ $status === 'success' ? 'success' : 'error' }}">{{ $title }}</h1>
        <p>{{ $message }}</p>
    </div>
</body>
</html>

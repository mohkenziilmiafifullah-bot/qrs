<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#F2760C">
    <title inertia>{{ config('app.name', 'QR Field Sales') }}</title>

    <link rel="manifest" href="/manifest.json">
    <link rel="icon" type="image/png" href="/images/qr-review-logo.png">
    <link rel="apple-touch-icon" href="/images/qr-review-logo.png">

    @routes
    @viteReactRefresh
    @vite(['resources/css/app.css', 'resources/js/app.jsx'])
    @inertiaHead
</head>
<body>
    @inertia
</body>
</html>

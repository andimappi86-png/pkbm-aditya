<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>419 - Sesi Berakhir</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo-pkbm-aditya.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="antialiased bg-background">
    <div class="min-h-screen flex items-center justify-center p-6">
        <div class="text-center">
            <img src="{{ asset('images/logo-pkbm-aditya.png') }}" alt="Logo PKBM Aditya" class="w-24 h-24 mx-auto mb-8 opacity-50">
            <h1 class="text-6xl font-bold text-primary mb-4">419</h1>
            <h2 class="text-2xl font-semibold text-navy mb-4">Sesi Berakhir</h2>
            <p class="text-muted mb-8 max-w-md">Sesi Anda telah berakhir. Silakan muat ulang halaman.</p>
            <div class="flex gap-4 justify-center">
                <button onclick="window.location.reload()" class="btn-primary">
                    Muat Ulang Halaman
                </button>
            </div>
        </div>
    </div>
</body>
</html>
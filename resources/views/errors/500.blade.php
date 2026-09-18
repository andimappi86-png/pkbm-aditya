<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>500 - Terjadi Kesalahan Server</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo-pkbm-aditya.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="antialiased bg-background">
    <div class="min-h-screen flex items-center justify-center p-6">
        <div class="text-center">
            <img src="{{ asset('images/logo-pkbm-aditya.png') }}" alt="Logo PKBM Aditya" class="w-24 h-24 mx-auto mb-8 opacity-50">
            <h1 class="text-6xl font-bold text-accent mb-4">500</h1>
            <h2 class="text-2xl font-semibold text-navy mb-4">Terjadi Kesalahan Server</h2>
            <p class="text-muted mb-8 max-w-md">Maaf, terjadi kesalahan pada server. Silakan coba lagi nanti.</p>
            <div class="flex gap-4 justify-center">
                <a href="{{ route('landing') }}" class="btn-secondary">
                    Kembali ke Beranda
                </a>
            </div>
        </div>
    </div>
</body>
</html>
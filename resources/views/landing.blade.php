<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>PKBM Aditya - Kabupaten Mamuju</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo-pkbm-aditya.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="antialiased bg-background">
    <!-- Navbar -->
    <nav class="bg-surface/95 backdrop-blur border-b border-border sticky top-0 z-50 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <x-brand-logo size="md" withText subtitle />
                <div class="flex items-center gap-4">
                    @auth
                        <a href="{{ url('/dashboard') }}" class="btn-primary">
                            Dashboard
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="btn-primary">
                            Masuk ke Sistem
                        </a>
                    @endauth
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero -->
    <section class="relative py-20 lg:py-32 overflow-hidden bg-gradient-to-b from-primary-soft/50 via-background to-background">
        <div class="absolute inset-0 opacity-5">
            <div class="absolute top-0 left-0 w-full h-full bg-gradient-to-br from-primary/20 via-transparent to-primary-dark/10"></div>
            <div class="absolute top-20 left-10 w-72 h-72 bg-primary rounded-full blur-3xl opacity-30"></div>
            <div class="absolute bottom-20 right-10 w-96 h-96 bg-primary-dark rounded-full blur-3xl opacity-20"></div>
        </div>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            <div class="text-center max-w-4xl mx-auto">
                <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-surface text-primary-dark border border-primary/20 shadow-sm text-sm font-semibold mb-6">
                    <span class="relative flex h-2 w-2">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-primary opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-primary"></span>
                    </span>
                    Sistem Baru - Phase 1 Foundation
                </div>
                <img src="{{ asset('images/logo-pkbm-aditya.png') }}" alt="Logo PKBM Aditya" class="w-36 h-36 mx-auto mb-8 drop-shadow-2xl">
                <h1 class="text-5xl lg:text-6xl font-extrabold text-navy tracking-tight mb-6 leading-tight">
                    PKBM <span class="text-primary">ADITYA</span>
                </h1>
                <p class="text-xl lg:text-2xl text-primary-dark font-medium mb-4">
                    Kabupaten Mamuju, Sulawesi Barat
                </p>
                <p class="text-lg lg:text-xl text-muted mb-10 max-w-3xl mx-auto leading-relaxed">
                    Sistem Informasi Manajemen dan Pembelajaran terintegrasi untuk mendukung pengelolaan data akademik, peserta didik, tutor, dan administrasi PKBM secara digital.
                </p>
                <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
                    <a href="{{ route('login') }}" class="btn-primary text-lg px-10 py-4 shadow-soft hover:shadow-soft-md transition-all">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                        </svg>
                        Masuk ke Sistem
                    </a>
                    <a href="#program" class="btn-secondary text-lg px-10 py-4">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        Lihat Program
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Program -->
    <section id="program" class="py-16 bg-surface border-y border-border">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h2 class="text-3xl font-bold text-center text-navy mb-12">Program Pembelajaran</h2>
            <div class="grid md:grid-cols-3 gap-6">
                <div class="card p-6 text-center transition-all duration-200 hover:-translate-y-1 hover:shadow-soft-md">
                    <div class="w-16 h-16 rounded-2xl bg-primary-soft text-primary-dark flex items-center justify-center mx-auto mb-4">
                        <x-icon name="book" class="w-8 h-8" />
                    </div>
                    <h3 class="text-xl font-semibold text-navy mb-2">Paket A</h3>
                    <p class="text-muted">Setara SD/MI</p>
                </div>
                <div class="card p-6 text-center transition-all duration-200 hover:-translate-y-1 hover:shadow-soft-md">
                    <div class="w-16 h-16 rounded-2xl bg-primary-soft text-primary-dark flex items-center justify-center mx-auto mb-4">
                        <x-icon name="book-open" class="w-8 h-8" />
                    </div>
                    <h3 class="text-xl font-semibold text-navy mb-2">Paket B</h3>
                    <p class="text-muted">Setara SMP/MTs</p>
                </div>
                <div class="card p-6 text-center transition-all duration-200 hover:-translate-y-1 hover:shadow-soft-md">
                    <div class="w-16 h-16 rounded-2xl bg-primary-soft text-primary-dark flex items-center justify-center mx-auto mb-4">
                        <x-icon name="book" class="w-8 h-8" />
                    </div>
                    <h3 class="text-xl font-semibold text-navy mb-2">Paket C</h3>
                    <p class="text-muted">Setara SMA/MA</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Features -->
    <section class="py-16 bg-background">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h2 class="text-3xl font-bold text-center text-navy mb-12">Fitur Sistem</h2>
            <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-6">
                <div class="text-center rounded-2xl border border-border bg-surface p-6 shadow-card transition-all duration-200 hover:-translate-y-1 hover:shadow-soft-md">
                    <div class="w-12 h-12 rounded-xl bg-primary-soft text-primary-dark flex items-center justify-center mx-auto mb-4">
                        <x-icon name="users" />
                    </div>
                    <h3 class="font-semibold text-navy mb-2">Manajemen Peserta Didik</h3>
                </div>
                <div class="text-center rounded-2xl border border-border bg-surface p-6 shadow-card transition-all duration-200 hover:-translate-y-1 hover:shadow-soft-md">
                    <div class="w-12 h-12 rounded-xl bg-primary-soft text-primary-dark flex items-center justify-center mx-auto mb-4">
                        <x-icon name="user-check" />
                    </div>
                    <h3 class="font-semibold text-navy mb-2">Manajemen Tutor</h3>
                </div>
                <div class="text-center rounded-2xl border border-border bg-surface p-6 shadow-card transition-all duration-200 hover:-translate-y-1 hover:shadow-soft-md">
                    <div class="w-12 h-12 rounded-xl bg-primary-soft text-primary-dark flex items-center justify-center mx-auto mb-4">
                        <x-icon name="clipboard" />
                    </div>
                    <h3 class="font-semibold text-navy mb-2">Absensi Digital</h3>
                </div>
                <div class="text-center rounded-2xl border border-border bg-surface p-6 shadow-card transition-all duration-200 hover:-translate-y-1 hover:shadow-soft-md">
                    <div class="w-12 h-12 rounded-xl bg-primary-soft text-primary-dark flex items-center justify-center mx-auto mb-4">
                        <x-icon name="chart-bar" />
                    </div>
                    <h3 class="font-semibold text-navy mb-2">Laporan Akademik</h3>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-surface border-t border-border py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col md:flex-row justify-between items-center gap-4">
                <div class="flex items-center gap-3">
                    <img src="{{ asset('images/logo-pkbm-aditya.png') }}" alt="Logo" class="h-8 w-8">
                    <div>
                        <div class="font-semibold text-navy">PKBM ADITYA</div>
                        <div class="text-sm text-muted">Kabupaten Mamuju</div>
                    </div>
                </div>
                <div class="text-sm text-muted">
                    © {{ date('Y') }} PKBM Aditya. All rights reserved.
                </div>
            </div>
        </div>
    </footer>
</body>
</html>
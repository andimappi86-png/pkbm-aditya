<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Login - PKBM Aditya</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo-pkbm-aditya.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="antialiased bg-background text-navy">
    <div class="min-h-screen flex">
        <!-- Left Panel - Branding -->
        <div class="hidden lg:flex lg:w-1/2 bg-gradient-to-br from-primary-dark via-primary to-primary-dark p-12 items-center justify-center relative overflow-hidden">
            <div class="absolute inset-0 opacity-10">
                <div class="absolute top-0 left-0 w-64 h-64 bg-white rounded-full -translate-x-1/2 -translate-y-1/2"></div>
                <div class="absolute bottom-0 right-0 w-96 h-96 bg-white rounded-full translate-x-1/2 translate-y-1/2"></div>
            </div>
            <div class="relative z-10 text-center">
                <img src="{{ asset('images/logo-pkbm-aditya.png') }}" alt="Logo PKBM Aditya" class="w-32 h-32 mx-auto mb-8 drop-shadow-2xl">
                <h1 class="text-4xl font-bold text-white mb-4">PKBM ADITYA</h1>
                <p class="text-xl text-white/90 mb-2">Kabupaten Mamuju</p>
                <p class="text-white/80">Sistem Informasi Manajemen dan Pembelajaran</p>
            </div>
        </div>

        <!-- Right Panel - Login Form -->
        <div class="w-full lg:w-1/2 flex items-center justify-center p-8">
            <div class="w-full max-w-md">
                <!-- Mobile Logo -->
                <div class="lg:hidden text-center mb-8">
                    <x-brand-logo size="lg" withText subtitle class="justify-center" />
                </div>

                <div class="card p-8 shadow-soft-md">
                    <h2 class="text-2xl font-bold text-navy mb-2">Masuk ke Sistem</h2>
                    <p class="text-muted mb-6">Silakan masuk dengan akun Anda</p>

                    <!-- Session Status -->
                    <x-auth-session-status class="mb-4" :status="session('status')" />

                    <form method="POST" action="{{ route('login') }}">
                        @csrf

                        <!-- Email -->
                        <div class="mb-4">
                            <label for="email" class="label">Email</label>
                            <input 
                                id="email" 
                                type="email" 
                                name="email" 
                                value="{{ old('email') }}" 
                                required 
                                autofocus 
                                autocomplete="username"
                                class="input"
                                placeholder="nama@example.com"
                            >
                            @error('email')
                                <p class="text-xs text-accent mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Password -->
                        <div class="mb-4">
                            <label for="password" class="label">Password</label>
                            <input 
                                id="password" 
                                type="password" 
                                name="password" 
                                required 
                                autocomplete="current-password"
                                class="input"
                                placeholder="••••••••"
                            >
                            @error('password')
                                <p class="text-xs text-accent mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Remember Me -->
                        <div class="flex items-center justify-between mb-6">
                            <label class="inline-flex items-center">
                                <input 
                                    type="checkbox" 
                                    name="remember" 
                                    class="rounded border-border text-primary focus:ring-primary"
                                >
                                <span class="ml-2 text-sm text-muted">Ingat saya</span>
                            </label>

                            @if (Route::has('password.request'))
                                <a href="{{ route('password.request') }}" class="text-sm text-primary-dark hover:text-primary">
                                    Lupa password?
                                </a>
                            @endif
                        </div>

                        <!-- Submit -->
                        <button type="submit" class="btn-primary w-full">
                            Masuk
                        </button>
                    </form>

                    <!-- Back to landing -->
                    <div class="mt-6 text-center">
                        <a href="{{ route('landing') }}" class="text-sm text-muted hover:text-navy inline-flex items-center gap-2">
                            <x-icon name="chevron-left" class="w-4 h-4" />
                            Kembali ke beranda
                        </a>
                    </div>
                </div>

                <!-- Development Info -->
                <div class="mt-6 p-4 rounded-xl bg-primary-soft border border-primary/20">
                    <p class="text-xs text-primary-dark font-medium mb-2">Akun Development (Phase 1)</p>
                    <p class="text-xs text-muted">Fitur RBAC akan tersedia di Phase 2</p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
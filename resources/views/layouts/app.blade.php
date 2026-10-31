<!DOCTYPE html>

<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ $title ?? config('app.name', 'PKBM Aditya') }}</title>

        <!-- Favicon -->
        <link rel="icon" type="image/png" href="{{ asset('images/logo-pkbm-aditya.png') }}">

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('scripts')
    </head>
    <body class="font-sans antialiased bg-background" x-data="{ sidebarOpen: false }" :class="sidebarOpen ? 'overflow-hidden lg:overflow-auto' : ''">
        <div class="flex flex-col lg:flex-row min-h-screen">
            <!-- Sidebar -->
            <x-sidebar />

            <!-- Main Content Area -->
            <div class="flex-1 lg:pl-64 flex flex-col min-w-0">
                <!-- Topbar -->
                <x-topbar :title="$title ?? ''" />

                <!-- Page Content -->
                <main class="px-4 lg:px-8 py-2 lg:py-4">
                    <!-- Flash Messages -->
                    <x-toast />

                    {{ $slot }}
                </main>

                <!-- Footer -->
                <footer class="border-t border-border bg-surface py-6 px-4 lg:px-8">
                    <div class="flex flex-col lg:flex-row justify-between items-center gap-4 text-sm text-muted">
                        <div class="flex items-center gap-2">
                            <img src="{{ asset('images/logo-pkbm-aditya.png') }}" alt="Logo" class="h-6 w-6">
                            <span>© {{ date('Y') }} <strong class="text-primary">PKBM Aditya</strong> Kabupaten Mamuju</span>
                        </div>
                        <div class="text-xs">
                            Sistem Informasi Manajemen v1.0 • Phase 1 Foundation
                        </div>
                    </div>
                </footer>
            </div>
        </div>
    </body>
</html>

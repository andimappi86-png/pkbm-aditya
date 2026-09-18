<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Operator Dashboard - PKBM Aditya</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: { DEFAULT: '#167C80', dark: '#0F5F63', soft: '#E8F5F4' },
                        text: '#172B3A',
                        background: '#F5F7F8',
                        white: '#FFFFFF',
                        accent: '#C62828',
                        warning: '#F59E0B',
                        danger: '#EF4444',
                        success: '#10B981',
                    },
                    fontFamily: { sans: ['Inter', 'sans-serif'] },
                }
            }
        }
    </script>
    <style>
        * { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-background min-h-screen">
    <!-- Sidebar -->
    <aside class="fixed inset-y-0 left-0 z-40 w-64 bg-white border-r border-gray-200 hidden lg:block">
        <div class="flex flex-col h-full">
            <!-- Logo -->
            <div class="p-6 border-b border-gray-100">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-primary-soft flex items-center justify-center">
                        <svg class="w-6 h-6 text-primary-dark" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" />
                        </svg>
                    </div>
                    <div>
                        <h1 class="font-bold text-text">PKBM ADITYA</h1>
                        <p class="text-xs text-muted">Operator Panel</p>
                    </div>
                </div>
            </div>

            <!-- Navigation -->
            <nav class="flex-1 p-4 space-y-1 overflow-y-auto">
                <a href="{{ route('operator.dashboard') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl bg-primary-soft text-primary-dark font-medium">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                    </svg>
                    Dashboard
                </a>
                
                <a href="{{ route('operator.security') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl text-text hover:bg-primary-soft hover:text-primary-dark transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                    </svg>
                    Security / AES-256-GCM
                </a>

                <div class="pt-4 mt-4 border-t border-gray-100">
                    <a href="{{ route('operator.dashboard') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit()" class="flex items-center gap-3 px-4 py-3 rounded-xl text-text hover:bg-red-50 hover:text-red-600 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                        </svg>
                        Logout
                    </a>
                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">
                        @csrf
                    </form>
                </div>
            </nav>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="lg:ml-64 min-h-screen">
        <!-- Topbar -->
        <header class="sticky top-0 z-30 bg-white border-b border-gray-200">
            <div class="flex items-center justify-between px-6 py-4">
                <h1 class="text-xl font-semibold text-text">Operator Dashboard</h1>
                <div class="flex items-center gap-4">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-full bg-primary-soft flex items-center justify-center">
                            <svg class="w-5 h-5 text-primary-dark" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                        </div>
                        <div class="text-right hidden sm:block">
                            <p class="text-sm font-medium text-text">{{ auth()->user()->name }}</p>
                            <p class="text-xs text-muted">Operator</p>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <!-- Page Content -->
        <div class="p-6 lg:p-8">
            <!-- Welcome Card -->
            <div class="mb-8 card p-6 lg:p-8 bg-gradient-to-br from-surface via-surface to-primary-soft/40">
                <div class="flex items-start gap-6">
                    <div class="flex-shrink-0">
                        <div class="w-16 h-16 rounded-2xl bg-primary-soft flex items-center justify-center">
                            <svg class="w-8 h-8 text-primary-dark" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" />
                            </svg>
                        </div>
                    </div>
                    <div class="flex-1">
                        <h2 class="text-2xl lg:text-3xl font-bold text-text mb-2">
                            Selamat datang, {{ auth()->user()->name }}
                        </h2>
                        <p class="text-muted">
                            Anda masuk sebagai <span class="font-semibold text-primary-dark">Operator Sistem</span>
                        </p>
                        <div class="flex items-center gap-4 text-sm text-muted mt-2">
                            <div class="flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                <span>{{ now()->locale('id')->isoFormat('dddd, D MMMM Y') }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="hidden lg:block flex-shrink-0">
                        <img src="{{ asset('images/logo-pkbm-aditya.png') }}" alt="Logo" class="w-24 h-24 opacity-20">
                    </div>
                </div>
            </div>

            <!-- KPI Stats Grid -->
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4 mb-8">
                <x-stat-card title="Peserta Didik" :value="$stats['students']" variant="primary" icon="users" />
                <x-stat-card title="Tutor" :value="$stats['tutors']" variant="primary" icon="user-check" />
                <x-stat-card title="Rombel" :value="$stats['classrooms']" variant="primary" icon="layers" />
                <x-stat-card title="Mata Pelajaran" :value="$stats['subjects']" variant="primary" icon="book" />
                <x-stat-card title="Jadwal" :value="$stats['schedules']" variant="primary" icon="calendar" />
                
                <x-stat-card title="Absensi" :value="$stats['attendance']" variant="secondary" icon="clipboard-check" />
                <x-stat-card title="Nilai" :value="$stats['grades']" variant="secondary" icon="chart-bar" />
                <x-stat-card title="Tugas" :value="$stats['assignments']" variant="secondary" icon="clipboard" />
                <x-stat-card title="Kuis" :value="$stats['quizzes']" variant="secondary" icon="lightning-bolt" />
                <x-stat-card title="Materi" :value="$stats['materials']" variant="secondary" icon="book-open" />
                
                <x-stat-card title="PPDB" :value="$stats['ppdb']" variant="accent" icon="document-text" />
                <x-stat-card title="Dokumen" :value="$stats['documents']" variant="accent" icon="file-text" />
                <x-stat-card title="Surat" :value="$stats['letters']" variant="accent" icon="envelope" />
                <x-stat-card title="Pengumuman" :value="$stats['announcements']" variant="accent" icon="megaphone" />
            </div>

            <!-- Quick Actions -->
            <div class="card p-6 mb-8">
                <h3 class="text-lg font-semibold text-text mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                    </svg>
                    Aksi Cepat
                </h3>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <a href="{{ route('peserta-didik.index') }}" class="flex flex-col items-center gap-2 p-4 rounded-xl border border-gray-200 hover:border-primary/50 hover:bg-primary-soft transition-colors">
                        <div class="w-12 h-12 rounded-xl bg-primary-soft text-primary-dark flex items-center justify-center">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
                        </div>
                        <span class="text-sm font-medium text-text">Kelola Siswa</span>
                    </a>

                    <a href="{{ route('tutors.index') }}" class="flex flex-col items-center gap-2 p-4 rounded-xl border border-gray-200 hover:border-primary/50 hover:bg-primary-soft transition-colors">
                        <div class="w-12 h-12 rounded-xl bg-primary-soft text-primary-dark flex items-center justify-center">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                        </div>
                        <span class="text-sm font-medium text-text">Kelola Tutor</span>
                    </a>

                    <a href="{{ route('rombel.index') }}" class="flex flex-col items-center gap-2 p-4 rounded-xl border border-gray-200 hover:border-primary/50 hover:bg-primary-soft transition-colors">
                        <div class="w-12 h-12 rounded-xl bg-primary-soft text-primary-dark flex items-center justify-center">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z" /></svg>
                        </div>
                        <span class="text-sm font-medium text-text">Kelola Rombel</span>
                    </a>

                    <a href="{{ route('operator.security') }}" class="flex flex-col items-center gap-2 p-4 rounded-xl border border-gray-200 hover:border-primary/50 hover:bg-primary-soft transition-colors">
                        <div class="w-12 h-12 rounded-xl bg-primary-soft text-primary-dark flex items-center justify-center">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" /></svg>
                        </div>
                        <span class="text-sm font-medium text-text">Security / AES</span>
                    </a>
                </div>
            </div>
        </div>
    </main>
</body>
</html>
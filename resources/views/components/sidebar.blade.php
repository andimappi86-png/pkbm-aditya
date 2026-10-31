@props([
    'active' => 'dashboard',
])

<!-- Overlay for mobile -->
<div 
    x-show="sidebarOpen" 
    x-cloak 
    @click="sidebarOpen = false" 
    class="fixed inset-0 z-30 bg-navy/60 backdrop-blur-xs lg:hidden"
    x-transition:enter="transition-opacity ease-linear duration-300"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition-opacity ease-linear duration-300"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
></div>

<!-- Sidebar -->
<aside 
    class="fixed inset-y-0 left-0 z-40 w-64 bg-surface border-r border-border shadow-soft min-h-screen flex flex-col transition-transform duration-300 ease-in-out lg:translate-x-0"
    :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
    @keydown.window.escape="sidebarOpen = false"
>
    <!-- Brand / Logo Header -->
    <div class="h-16 lg:h-20 flex items-center justify-between px-4 lg:px-6 border-b border-border shrink-0">
        <div class="flex items-center gap-3.5">
            <img
                src="{{ asset('images/logo-pkbm-aditya.png') }}"
                alt="Logo PKBM Aditya"
                class="w-10 h-10 lg:w-11 lg:h-11 object-contain flex-shrink-0 drop-shadow-sm"
            />
            <div class="flex flex-col min-w-0">
                <span class="font-extrabold text-sm text-navy tracking-tight uppercase leading-tight truncate">
                    PKBM ADITYA
                </span>
                <span class="text-[11px] font-medium text-muted truncate">
                    Kabupaten Mamuju
                </span>
            </div>
        </div>
        
        <!-- Mobile Close Button -->
        <button 
            type="button" 
            @click="sidebarOpen = false" 
            class="lg:hidden p-1 rounded-lg text-muted hover:text-primary-dark hover:bg-primary-soft transition-colors focus:outline-none"
            aria-label="Tutup sidebar"
        >
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
        </button>
    </div>

    <!-- Navigation List -->
    <div class="flex-1 overflow-y-auto px-4 py-6 space-y-6">
        <!-- Main Navigation (Global - hidden for peserta-didik which has its own) -->
        @unless(auth()->user() && auth()->user()->hasRole('peserta-didik'))
        <div>
            <div class="text-[11px] font-bold uppercase tracking-wider text-muted px-3 mb-2">
                Menu Utama
            </div>
            <nav class="space-y-1" @click="if (window.innerWidth < 1024) sidebarOpen = false">
                <a
                    href="{{ route('dashboard') }}"
                    class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-colors {{ request()->routeIs('dashboard*') ? 'bg-primary-soft text-primary-dark font-semibold shadow-sm ring-1 ring-primary/10' : 'text-muted hover:text-primary-dark hover:bg-primary-soft/60' }}"
                >
                    <svg class="w-5 h-5 {{ request()->routeIs('dashboard*') ? 'text-primary' : 'text-muted' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                    </svg>
                    <span>Dashboard</span>
                </a>

                <a
                    href="{{ route('profile.edit') }}"
                    class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-colors {{ request()->routeIs('profile.*') ? 'bg-primary-soft text-primary-dark font-semibold shadow-sm ring-1 ring-primary/10' : 'text-muted hover:text-primary-dark hover:bg-primary-soft/60' }}"
                >
                    <svg class="w-5 h-5 {{ request()->routeIs('profile.*') ? 'text-primary' : 'text-muted' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                    <span>Profil Saya</span>
                </a>
            </nav>
        </div>
        @endunless

        @role('ketua-yayasan')
            <!-- Ketua Yayasan Sidebar -->
            <div>
                <div class="text-[11px] font-bold uppercase tracking-wider text-muted px-3 mb-2">
                    Laporan Institusi
                </div>
                <nav class="space-y-1" @click="if (window.innerWidth < 1024) sidebarOpen = false">
                    <a href="{{ route('reports.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-colors {{ request()->routeIs('reports.*') ? 'bg-primary-soft text-primary-dark font-semibold' : 'text-muted hover:text-primary-dark hover:bg-primary-soft/60' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" /></svg>
                        <span>Laporan & Statistik</span>
                    </a>
                    <a href="{{ route('documents.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-colors {{ request()->routeIs('documents.*') ? 'bg-primary-soft text-primary-dark font-semibold' : 'text-muted hover:text-primary-dark hover:bg-primary-soft/60' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                        <span>Dokumen & Laporan</span>
                    </a>
                </nav>
            </div>
        @endrole

        @role('kepala-pkbm')
            <!-- Kepala PKBM Sidebar -->
            <div>
                <div class="text-[11px] font-bold uppercase tracking-wider text-muted px-3 mb-2">Master Data</div>
                <nav class="space-y-1" @click="if (window.innerWidth < 1024) sidebarOpen = false">
                    <a href="{{ route('peserta-didik.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-colors {{ request()->routeIs('peserta-didik.*') ? 'bg-primary-soft text-primary-dark font-semibold' : 'text-muted hover:text-primary-dark hover:bg-primary-soft/60' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
                        <span>Peserta Didik</span>
                    </a>
                    <a href="{{ route('tutors.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-colors {{ request()->routeIs('tutors.*') ? 'bg-primary-soft text-primary-dark font-semibold' : 'text-muted hover:text-primary-dark hover:bg-primary-soft/60' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        <span>Tutor</span>
                    </a>
                    <a href="{{ route('rombel.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-colors {{ request()->routeIs('rombel.*') ? 'bg-primary-soft text-primary-dark font-semibold' : 'text-muted hover:text-primary-dark hover:bg-primary-soft/60' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg>
                        <span>Rombel</span>
                    </a>
                    <a href="{{ route('classroom-assignments.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-colors {{ request()->routeIs('classroom-assignments.*') ? 'bg-primary-soft text-primary-dark font-semibold' : 'text-muted hover:text-primary-dark hover:bg-primary-soft/60' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                        <span>Penugasan</span>
                    </a>
                    <a href="{{ route('academic-years.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-colors {{ request()->routeIs('academic-years.*') ? 'bg-primary-soft text-primary-dark font-semibold' : 'text-muted hover:text-primary-dark hover:bg-primary-soft/60' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                        <span>Tahun Ajaran</span>
                    </a>
                </nav>
            </div>
            
            <div>
                <div class="text-[11px] font-bold uppercase tracking-wider text-muted px-3 mb-2">Akademik & Operasional</div>
                <nav class="space-y-1" @click="if (window.innerWidth < 1024) sidebarOpen = false">
                    <a href="{{ route('jadwal.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-colors {{ request()->routeIs('jadwal.*') ? 'bg-primary-soft text-primary-dark font-semibold' : 'text-muted hover:text-primary-dark hover:bg-primary-soft/60' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                        <span>Jadwal</span>
                    </a>
                    <a href="{{ route('absensi.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-colors {{ request()->routeIs('absensi.*') ? 'bg-primary-soft text-primary-dark font-semibold' : 'text-muted hover:text-primary-dark hover:bg-primary-soft/60' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" /></svg>
                        <span>Absensi</span>
                    </a>
                    <a href="{{ route('nilai.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-colors {{ request()->routeIs('nilai.*') ? 'bg-primary-soft text-primary-dark font-semibold' : 'text-muted hover:text-primary-dark hover:bg-primary-soft/60' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" /></svg>
                        <span>Nilai</span>
                    </a>
                </nav>
            </div>

            <div>
                <div class="text-[11px] font-bold uppercase tracking-wider text-muted px-3 mb-2">Monitoring</div>
                <nav class="space-y-1" @click="if (window.innerWidth < 1024) sidebarOpen = false">
                    <a href="{{ route('reports.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-colors {{ request()->routeIs('reports.*') ? 'bg-primary-soft text-primary-dark font-semibold' : 'text-muted hover:text-primary-dark hover:bg-primary-soft/60' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" /></svg>
                        <span>Laporan & Statistik</span>
                    </a>
                    <a href="{{ route('announcements.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-colors {{ request()->routeIs('announcements.*') ? 'bg-primary-soft text-primary-dark font-semibold' : 'text-muted hover:text-primary-dark hover:bg-primary-soft/60' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z" /></svg>
                        <span>Pengumuman</span>
                    </a>
                </nav>
            </div>
        @endrole

        @if(auth()->user() && auth()->user()->hasRole('tutor') && auth()->user()->hasRole('wali-kelas'))
            <!-- Combined Tutor & Wali Kelas Sidebar (Unified, no duplicates) -->
            <div>
                <div class="text-[11px] font-bold uppercase tracking-wider text-muted px-3 mb-2">Penugasan & Pembelajaran</div>
                <nav class="space-y-1" @click="if (window.innerWidth < 1024) sidebarOpen = false">
                    <a href="{{ route('kelas-saya.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-colors {{ request()->routeIs('kelas-saya.*') ? 'bg-primary-soft text-primary-dark font-semibold' : 'text-muted hover:text-primary-dark hover:bg-primary-soft/60' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" /></svg>
                        <span>Kelas yang Diampu</span>
                    </a>
                    <a href="{{ route('peserta-didik.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-colors {{ request()->routeIs('peserta-didik.*') ? 'bg-primary-soft text-primary-dark font-semibold' : 'text-muted hover:text-primary-dark hover:bg-primary-soft/60' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
                        <span>Peserta Didik (Kelas)</span>
                    </a>
                    <a href="{{ route('jadwal.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-colors {{ request()->routeIs('jadwal.*') ? 'bg-primary-soft text-primary-dark font-semibold' : 'text-muted hover:text-primary-dark hover:bg-primary-soft/60' }}">
                        <x-icon name="calendar" class="w-5 h-5" />
                        <span>Jadwal</span>
                    </a>
                    <a href="{{ route('materi.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-colors {{ request()->routeIs('materi.*') ? 'bg-primary-soft text-primary-dark font-semibold' : 'text-muted hover:text-primary-dark hover:bg-primary-soft/60' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" /></svg>
                        <span>Materi</span>
                    </a>
                    <a href="{{ route('tugas.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-colors {{ request()->routeIs('tugas.*') ? 'bg-primary-soft text-primary-dark font-semibold' : 'text-muted hover:text-primary-dark hover:bg-primary-soft/60' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" /></svg>
                        <span>Tugas</span>
                    </a>
                    <a href="{{ route('quiz.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-colors {{ request()->routeIs('quiz.*') ? 'bg-primary-soft text-primary-dark font-semibold' : 'text-muted hover:text-primary-dark hover:bg-primary-soft/60' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        <span>Kuis & Ujian</span>
                    </a>
                    <a href="{{ route('absensi.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-colors {{ request()->routeIs('absensi.*') ? 'bg-primary-soft text-primary-dark font-semibold' : 'text-muted hover:text-primary-dark hover:bg-primary-soft/60' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" /></svg>
                        <span>Absensi</span>
                    </a>
                    <a href="{{ route('nilai.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-colors {{ request()->routeIs('nilai.*') ? 'bg-primary-soft text-primary-dark font-semibold' : 'text-muted hover:text-primary-dark hover:bg-primary-soft/60' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" /></svg>
                        <span>Nilai</span>
                    </a>
                    <a href="{{ route('forum.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-colors {{ request()->routeIs('forum.*') ? 'bg-primary-soft text-primary-dark font-semibold' : 'text-muted hover:text-primary-dark hover:bg-primary-soft/60' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a1.994 1.994 0 01-1.414-.586m0 0L11 14h4a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2v4l.586-.586z" /></svg>
                        <span>Forum Diskusi</span>
                    </a>
                </nav>
            </div>
            <div>
                <div class="text-[11px] font-bold uppercase tracking-wider text-muted px-3 mb-2">Tanggung Jawab Wali Kelas</div>
                <nav class="space-y-1" @click="if (window.innerWidth < 1024) sidebarOpen = false">
                    <a href="{{ route('rekap.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-colors {{ request()->routeIs('rekap.*') || request()->routeIs('rapor.*') ? 'bg-primary-soft text-primary-dark font-semibold' : 'text-muted hover:text-primary-dark hover:bg-primary-soft/60' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v1a3 3 0 106 0v-1m-6 0a3 3 0 006 0m-6 0H3m12 0h6m-9-5a3 3 0 100-6 3 3 0 000 6z" /></svg>
                        <span>Rekap Akademik & Rapor</span>
                    </a>
                    <a href="{{ route('announcements.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-colors {{ request()->routeIs('announcements.*') ? 'bg-primary-soft text-primary-dark font-semibold' : 'text-muted hover:text-primary-dark hover:bg-primary-soft/60' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z" /></svg>
                        <span>Pengumuman</span>
                    </a>
                </nav>
            </div>
        @else
            @role('wali-kelas')
            <!-- Wali Kelas Sidebar -->
            <div>
                <div class="text-[11px] font-bold uppercase tracking-wider text-muted px-3 mb-2">Akademik</div>
                <nav class="space-y-1" @click="if (window.innerWidth < 1024) sidebarOpen = false">
                    <a href="{{ route('kelas-saya.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-colors {{ request()->routeIs('kelas-saya.*') ? 'bg-primary-soft text-primary-dark font-semibold' : 'text-muted hover:text-primary-dark hover:bg-primary-soft/60' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" /></svg>
                        <span>Kelas yang Diampu</span>
                    </a>
                    <a href="{{ route('peserta-didik.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-colors {{ request()->routeIs('peserta-didik.*') ? 'bg-primary-soft text-primary-dark font-semibold' : 'text-muted hover:text-primary-dark hover:bg-primary-soft/60' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
                        <span>Kelasku</span>
                    </a>
                    <a href="{{ route('jadwal.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-colors {{ request()->routeIs('jadwal.*') ? 'bg-primary-soft text-primary-dark font-semibold' : 'text-muted hover:text-primary-dark hover:bg-primary-soft/60' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                        <span>Jadwal</span>
                    </a>
                    <a href="{{ route('absensi.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-colors {{ request()->routeIs('absensi.*') || request()->routeIs('attendances.*') ? 'bg-primary-soft text-primary-dark font-semibold' : 'text-muted hover:text-primary-dark hover:bg-primary-soft/60' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" /></svg>
                        <span>Absensi Kelas</span>
                    </a>
                    <a href="{{ route('nilai.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-colors {{ request()->routeIs('nilai.*') ? 'bg-primary-soft text-primary-dark font-semibold' : 'text-muted hover:text-primary-dark hover:bg-primary-soft/60' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" /></svg>
                        <span>Nilai</span>
                    </a>
                </nav>
            </div>
            <div>
                <div class="text-[11px] font-bold uppercase tracking-wider text-muted px-3 mb-2">Laporan & Pengumuman</div>
                <nav class="space-y-1" @click="if (window.innerWidth < 1024) sidebarOpen = false">
                    <a href="{{ route('rekap.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-colors {{ request()->routeIs('rekap.*') || request()->routeIs('rapor.*') ? 'bg-primary-soft text-primary-dark font-semibold' : 'text-muted hover:text-primary-dark hover:bg-primary-soft/60' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v1a3 3 0 106 0v-1m-6 0a3 3 0 006 0m-6 0H3m12 0h6m-9-5a3 3 0 100-6 3 3 0 000 6z" /></svg>
                        <span>Rekap Akademik</span>
                    </a>
                    <a href="{{ route('announcements.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-colors {{ request()->routeIs('announcements.*') ? 'bg-primary-soft text-primary-dark font-semibold' : 'text-muted hover:text-primary-dark hover:bg-primary-soft/60' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z" /></svg>
                        <span>Pengumuman</span>
                    </a>
                </nav>
            </div>
            @endrole

            @role('tutor')
            <!-- Tutor Sidebar -->
            <div>
                <div class="text-[11px] font-bold uppercase tracking-wider text-muted px-3 mb-2">Penugasan & Pembelajaran</div>
                <nav class="space-y-1" @click="if (window.innerWidth < 1024) sidebarOpen = false">
                    <a href="{{ route('kelas-saya.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-colors {{ request()->routeIs('kelas-saya.*') ? 'bg-primary-soft text-primary-dark font-semibold' : 'text-muted hover:text-primary-dark hover:bg-primary-soft/60' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" /></svg>
                        <span>Kelas yang Diampu</span>
                    </a>
                    <a href="{{ route('peserta-didik.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-colors {{ request()->routeIs('peserta-didik.*') ? 'bg-primary-soft text-primary-dark font-semibold' : 'text-muted hover:text-primary-dark hover:bg-primary-soft/60' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
                        <span>Peserta Didik (Kelas)</span>
                    </a>
                    <a href="{{ route('jadwal.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-colors {{ request()->routeIs('jadwal.*') ? 'bg-primary-soft text-primary-dark font-semibold' : 'text-muted hover:text-primary-dark hover:bg-primary-soft/60' }}">
                        <x-icon name="calendar" class="w-5 h-5" />
                        <span>Jadwal</span>
                    </a>
                    <a href="{{ route('materi.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-colors {{ request()->routeIs('materi.*') ? 'bg-primary-soft text-primary-dark font-semibold' : 'text-muted hover:text-primary-dark hover:bg-primary-soft/60' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" /></svg>
                        <span>Materi</span>
                    </a>
                    <a href="{{ route('tugas.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-colors {{ request()->routeIs('tugas.*') ? 'bg-primary-soft text-primary-dark font-semibold' : 'text-muted hover:text-primary-dark hover:bg-primary-soft/60' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" /></svg>
                        <span>Tugas</span>
                    </a>
                    <a href="{{ route('quiz.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-colors {{ request()->routeIs('quiz.*') ? 'bg-primary-soft text-primary-dark font-semibold' : 'text-muted hover:text-primary-dark hover:bg-primary-soft/60' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        <span>Kuis & Ujian</span>
                    </a>
                    <a href="{{ route('absensi.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-colors {{ request()->routeIs('absensi.*') ? 'bg-primary-soft text-primary-dark font-semibold' : 'text-muted hover:text-primary-dark hover:bg-primary-soft/60' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" /></svg>
                        <span>Absensi</span>
                    </a>
                    <a href="{{ route('nilai.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-colors {{ request()->routeIs('nilai.*') ? 'bg-primary-soft text-primary-dark font-semibold' : 'text-muted hover:text-primary-dark hover:bg-primary-soft/60' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" /></svg>
                        <span>Nilai</span>
                    </a>
                    <a href="{{ route('forum.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-colors {{ request()->routeIs('forum.*') ? 'bg-primary-soft text-primary-dark font-semibold' : 'text-muted hover:text-primary-dark hover:bg-primary-soft/60' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a1.994 1.994 0 01-1.414-.586m0 0L11 14h4a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2v4l.586-.586z" /></svg>
                        <span>Forum Diskusi</span>
                    </a>
                    <a href="{{ route('announcements.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-colors {{ request()->routeIs('announcements.*') ? 'bg-primary-soft text-primary-dark font-semibold' : 'text-muted hover:text-primary-dark hover:bg-primary-soft/60' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z" /></svg>
                        <span>Pengumuman</span>
                    </a>
                </nav>
            </div>
            @endrole
        @endif

@role('operator')
            <!-- Operator Sidebar -->
            <div>
                <div class="text-[11px] font-bold uppercase tracking-wider text-muted px-3 mb-2">Dashboard</div>
                <nav class="space-y-1" @click="if (window.innerWidth < 1024) sidebarOpen = false">
                    @can('dashboard.view')
                    <a href="{{ route('operator.dashboard') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-colors {{ request()->routeIs('operator.dashboard') ? 'bg-primary-soft text-primary-dark font-semibold' : 'text-muted hover:text-primary-dark hover:bg-primary-soft/60' }}">
                        <x-icon name="home" class="w-5 h-5" />
                        <span>Dashboard Operator</span>
                    </a>
                    @endcan
                    @can('reports.view')
                    <a href="{{ route('reports.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-colors {{ request()->routeIs('reports.*') ? 'bg-primary-soft text-primary-dark font-semibold' : 'text-muted hover:text-primary-dark hover:bg-primary-soft/60' }}">
                        <x-icon name="chart-bar" class="w-5 h-5" />
                        <span>Laporan & Aktivitas</span>
                    </a>
                    @endcan
                </nav>
            </div>

            <div>
                <div class="text-[11px] font-bold uppercase tracking-wider text-muted px-3 mb-2">Pengelolaan Pengguna</div>
                <nav class="space-y-1" @click="if (window.innerWidth < 1024) sidebarOpen = false">
                    @can('users.manage')
                    <a href="{{ route('users.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-colors {{ request()->routeIs('users.*') ? 'bg-primary-soft text-primary-dark font-semibold' : 'text-muted hover:text-primary-dark hover:bg-primary-soft/60' }}">
                        <x-icon name="users" class="w-5 h-5" />
                        <span>Semua Pengguna</span>
                    </a>
                    <a href="{{ route('users.create') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-colors {{ request()->routeIs('users.create') ? 'bg-primary-soft text-primary-dark font-semibold' : 'text-muted hover:text-primary-dark hover:bg-primary-soft/60' }}">
                        <x-icon name="plus" class="w-5 h-5" />
                        <span>Tambah Pengguna</span>
                    </a>
                    <a href="{{ route('roles.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-colors {{ request()->routeIs('roles.*') ? 'bg-primary-soft text-primary-dark font-semibold' : 'text-muted hover:text-primary-dark hover:bg-primary-soft/60' }}">
                        <x-icon name="user-group" class="w-5 h-5" />
                        <span>Role & Permission</span>
                    </a>
                    @endcan
                    @can('students.manage')
                    <a href="{{ route('peserta-didik.link-accounts') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-colors {{ request()->routeIs('peserta-didik.link-accounts*') ? 'bg-primary-soft text-primary-dark font-semibold' : 'text-muted hover:text-primary-dark hover:bg-primary-soft/60' }}">
                        <x-icon name="user-check" class="w-5 h-5" />
                        <span>Penautan Akun Siswa</span>
                    </a>
                    @endcan
                </nav>
            </div>

            <div>
                <div class="text-[11px] font-bold uppercase tracking-wider text-muted px-3 mb-2">Data Akademik</div>
                <nav class="space-y-1" @click="if (window.innerWidth < 1024) sidebarOpen = false">
                    @can('students.view')
                    <a href="{{ route('peserta-didik.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-colors {{ request()->routeIs('peserta-didik.*') ? 'bg-primary-soft text-primary-dark font-semibold' : 'text-muted hover:text-primary-dark hover:bg-primary-soft/60' }}">
                        <x-icon name="users" class="w-5 h-5" />
                        <span>Peserta Didik</span>
                    </a>
                    @endcan
                    @can('tutors.view')
                    <a href="{{ route('tutors.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-colors {{ request()->routeIs('tutors.*') ? 'bg-primary-soft text-primary-dark font-semibold' : 'text-muted hover:text-primary-dark hover:bg-primary-soft/60' }}">
                        <x-icon name="user-group" class="w-5 h-5" />
                        <span>Tutor</span>
                    </a>
                    @endcan
                    @can('classrooms.view')
                    <a href="{{ route('rombel.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-colors {{ request()->routeIs('rombel.*') ? 'bg-primary-soft text-primary-dark font-semibold' : 'text-muted hover:text-primary-dark hover:bg-primary-soft/60' }}">
                        <x-icon name="layers" class="w-5 h-5" />
                        <span>Rombel</span>
                    </a>
                    @endcan
                    @can('subjects.view')
                    <a href="{{ route('mata-pelajaran.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-colors {{ request()->routeIs('mata-pelajaran.*') ? 'bg-primary-soft text-primary-dark font-semibold' : 'text-muted hover:text-primary-dark hover:bg-primary-soft/60' }}">
                        <x-icon name="book" class="w-5 h-5" />
                        <span>Mata Pelajaran</span>
                    </a>
                    @endcan
                    @can('academic-years.view')
                    <a href="{{ route('academic-years.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-colors {{ request()->routeIs('academic-years.*') ? 'bg-primary-soft text-primary-dark font-semibold' : 'text-muted hover:text-primary-dark hover:bg-primary-soft/60' }}">
                        <x-icon name="calendar" class="w-5 h-5" />
                        <span>Tahun Akademik</span>
                    </a>
                    @endcan
                    @can('classrooms.manage')
                    <a href="{{ route('penugasan-mengajar.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-colors {{ request()->routeIs('penugasan-mengajar.*') ? 'bg-primary-soft text-primary-dark font-semibold' : 'text-muted hover:text-primary-dark hover:bg-primary-soft/60' }}">
                        <x-icon name="presentation" class="w-5 h-5" />
                        <span>Penugasan Mengajar</span>
                    </a>
                    @endcan
                    @can('schedules.view')
                    <a href="{{ route('jadwal.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-colors {{ request()->routeIs('jadwal.*') ? 'bg-primary-soft text-primary-dark font-semibold' : 'text-muted hover:text-primary-dark hover:bg-primary-soft/60' }}">
                        <x-icon name="calendar" class="w-5 h-5" />
                        <span>Jadwal Pelajaran</span>
                    </a>
                    @endcan
                    @can('attendance.view')
                    <a href="{{ route('absensi.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-colors {{ request()->routeIs('absensi.*') ? 'bg-primary-soft text-primary-dark font-semibold' : 'text-muted hover:text-primary-dark hover:bg-primary-soft/60' }}">
                        <x-icon name="clipboard-check" class="w-5 h-5" />
                        <span>Absensi</span>
                    </a>
                    @endcan
                    @can('grades.view')
                    <a href="{{ route('nilai.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-colors {{ request()->routeIs('nilai.*') ? 'bg-primary-soft text-primary-dark font-semibold' : 'text-muted hover:text-primary-dark hover:bg-primary-soft/60' }}">
                        <x-icon name="chart-bar" class="w-5 h-5" />
                        <span>Nilai</span>
                    </a>
                    @endcan
                    @can('reports.view')
                    <a href="{{ route('rekap.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-colors {{ request()->routeIs('rekap.*') ? 'bg-primary-soft text-primary-dark font-semibold' : 'text-muted hover:text-primary-dark hover:bg-primary-soft/60' }}">
                        <x-icon name="document-text" class="w-5 h-5" />
                        <span>Rekap Akademik</span>
                    </a>
                    @endcan
                </nav>
            </div>

            <div>
                <div class="text-[11px] font-bold uppercase tracking-wider text-muted px-3 mb-2">Pembelajaran</div>
                <nav class="space-y-1" @click="if (window.innerWidth < 1024) sidebarOpen = false">
                    @can('assignments.view')
                    <a href="{{ route('tugas.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-colors {{ request()->routeIs('tugas.*') ? 'bg-primary-soft text-primary-dark font-semibold' : 'text-muted hover:text-primary-dark hover:bg-primary-soft/60' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" /></svg>
                        <span>Tugas</span>
                    </a>
                    @endcan
                    @can('reports.view')
                    <a href="{{ route('reports.assignments') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-colors {{ request()->routeIs('reports.assignments') ? 'bg-primary-soft text-primary-dark font-semibold' : 'text-muted hover:text-primary-dark hover:bg-primary-soft/60' }}">
                        <x-icon name="clipboard-check" class="w-5 h-5" />
                        <span>Pengumpulan Tugas</span>
                    </a>
                    @endcan
                    @can('quizzes.view')
                    <a href="{{ route('quiz.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-colors {{ request()->routeIs('quiz.*') ? 'bg-primary-soft text-primary-dark font-semibold' : 'text-muted hover:text-primary-dark hover:bg-primary-soft/60' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        <span>Kuis & Ujian</span>
                    </a>
                    @endcan
                    @can('materials.view')
                    <a href="{{ route('materi.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-colors {{ request()->routeIs('materi.*') ? 'bg-primary-soft text-primary-dark font-semibold' : 'text-muted hover:text-primary-dark hover:bg-primary-soft/60' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" /></svg>
                        <span>Materi</span>
                    </a>
                    @endcan
                    @can('announcements.view')
                    <a href="{{ route('forum.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-colors {{ request()->routeIs('forum.*') ? 'bg-primary-soft text-primary-dark font-semibold' : 'text-muted hover:text-primary-dark hover:bg-primary-soft/60' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a1.994 1.994 0 01-1.414-.586m0 0L11 14h4a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2v4l.586-.586z" /></svg>
                        <span>Forum Diskusi</span>
                    </a>
                    @endcan
                </nav>
            </div>

            <div>
                <div class="text-[11px] font-bold uppercase tracking-wider text-muted px-3 mb-2">Administrasi</div>
                <nav class="space-y-1" @click="if (window.innerWidth < 1024) sidebarOpen = false">
                    @can('documents.view')
                    <a href="{{ route('documents.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-colors {{ request()->routeIs('documents.*') ? 'bg-primary-soft text-primary-dark font-semibold' : 'text-muted hover:text-primary-dark hover:bg-primary-soft/60' }}">
                        <x-icon name="file-text" class="w-5 h-5" />
                        <span>Dokumen</span>
                    </a>
                    @endcan
                    @can('letters.view')
                    <a href="{{ route('letters.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-colors {{ request()->routeIs('letters.*') ? 'bg-primary-soft text-primary-dark font-semibold' : 'text-muted hover:text-primary-dark hover:bg-primary-soft/60' }}">
                        <x-icon name="envelope" class="w-5 h-5" />
                        <span>Surat</span>
                    </a>
                    @endcan
                    @can('announcements.view')
                    <a href="{{ route('announcements.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-colors {{ request()->routeIs('announcements.*') ? 'bg-primary-soft text-primary-dark font-semibold' : 'text-muted hover:text-primary-dark hover:bg-primary-soft/60' }}">
                        <x-icon name="megaphone" class="w-5 h-5" />
                        <span>Pengumuman</span>
                    </a>
                    @endcan
                    @can('reports.view')
                    <a href="{{ route('reports.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-colors {{ request()->routeIs('reports.*') ? 'bg-primary-soft text-primary-dark font-semibold' : 'text-muted hover:text-primary-dark hover:bg-primary-soft/60' }}">
                        <x-icon name="chart-bar" class="w-5 h-5" />
                        <span>Laporan</span>
                    </a>
                    @endcan
                </nav>
            </div>

            <div>
                <div class="text-[11px] font-bold uppercase tracking-wider text-muted px-3 mb-2">Sistem</div>
                <nav class="space-y-1" @click="if (window.innerWidth < 1024) sidebarOpen = false">
                    @can('operator.import')
                    <a href="{{ route('operator.import.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-colors {{ request()->routeIs('operator.import*') ? 'bg-primary-soft text-primary-dark font-semibold' : 'text-muted hover:text-primary-dark hover:bg-primary-soft/60' }}">
                        <x-icon name="upload" class="w-5 h-5" />
                        <span>Import Data</span>
                    </a>
                    <a href="{{ route('operator.import-tutor.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-colors {{ request()->routeIs('operator.import-tutor*') ? 'bg-primary-soft text-primary-dark font-semibold' : 'text-muted hover:text-primary-dark hover:bg-primary-soft/60' }}">
                        <x-icon name="download" class="w-5 h-5" />
                        <span>Import Tutor</span>
                    </a>
                    @endcan
                    @can('dashboard.view')
                    <a href="{{ route('operator.security') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-colors {{ request()->routeIs('operator.security') ? 'bg-primary-soft text-primary-dark font-semibold' : 'text-muted hover:text-primary-dark hover:bg-primary-soft/60' }}">
                        <x-icon name="cog" class="w-5 h-5" />
                        <span>Keamanan</span>
                    </a>
                    @endcan
                </nav>
            </div>
        @endrole

@role('peserta-didik')
            <!-- Peserta Didik Sidebar -->
            <div>
                <div class="text-[11px] font-bold uppercase tracking-wider text-muted px-3 mb-2">Menu Utama</div>
                <nav class="space-y-1" @click="if (window.innerWidth < 1024) sidebarOpen = false">
                    <a href="{{ route('dashboard') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-colors {{ request()->routeIs('dashboard*') ? 'bg-primary-soft text-primary-dark font-semibold' : 'text-muted hover:text-primary-dark hover:bg-primary-soft/60' }}">
                        <x-icon name="layout-dashboard" class="w-5 h-5" />
                        <span>Dashboard</span>
                    </a>
                    <a href="{{ route('profile.edit') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-colors {{ request()->routeIs('profile.*') ? 'bg-primary-soft text-primary-dark font-semibold' : 'text-muted hover:text-primary-dark hover:bg-primary-soft/60' }}">
                        <x-icon name="user" class="w-5 h-5" />
                        <span>Profil Saya</span>
                    </a>
                </nav>
            </div>
            <div>
                <div class="text-[11px] font-bold uppercase tracking-wider text-muted px-3 mb-2">Pembelajaran</div>
                <nav class="space-y-1" @click="if (window.innerWidth < 1024) sidebarOpen = false">
                    <a href="{{ route('kelas-saya.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-colors {{ request()->routeIs('kelas-saya.*') ? 'bg-primary-soft text-primary-dark font-semibold' : 'text-muted hover:text-primary-dark hover:bg-primary-soft/60' }}">
                        <x-icon name="book-open" class="w-5 h-5" />
                        <span>Kelas Saya</span>
                    </a>
                    <a href="{{ route('materi.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-colors {{ request()->routeIs('materi.*') ? 'bg-primary-soft text-primary-dark font-semibold' : 'text-muted hover:text-primary-dark hover:bg-primary-soft/60' }}">
                        <x-icon name="file-text" class="w-5 h-5" />
                        <span>Materi</span>
                    </a>
                    <a href="{{ route('tugas.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-colors {{ request()->routeIs('tugas.*') ? 'bg-primary-soft text-primary-dark font-semibold' : 'text-muted hover:text-primary-dark hover:bg-primary-soft/60' }}">
                        <x-icon name="clipboard-list" class="w-5 h-5" />
                        <span>Tugas</span>
                    </a>
                    <a href="{{ route('quiz.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-colors {{ request()->routeIs('quiz.*') ? 'bg-primary-soft text-primary-dark font-semibold' : 'text-muted hover:text-primary-dark hover:bg-primary-soft/60' }}">
                        <x-icon name="help-circle" class="w-5 h-5" />
                        <span>Kuis & Ujian</span>
                    </a>
                    <a href="{{ route('jadwal.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-colors {{ request()->routeIs('jadwal.*') ? 'bg-primary-soft text-primary-dark font-semibold' : 'text-muted hover:text-primary-dark hover:bg-primary-soft/60' }}">
                        <x-icon name="calendar" class="w-5 h-5" />
                        <span>Jadwal</span>
                    </a>
                    <a href="{{ route('absensi.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-colors {{ request()->routeIs('absensi.*') ? 'bg-primary-soft text-primary-dark font-semibold' : 'text-muted hover:text-primary-dark hover:bg-primary-soft/60' }}">
                        <x-icon name="calendar-check" class="w-5 h-5" />
                        <span>Kehadiran</span>
                    </a>
                    <a href="{{ route('rekap.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-colors {{ request()->routeIs('rekap.*') || request()->routeIs('rapor.*') ? 'bg-primary-soft text-primary-dark font-semibold' : 'text-muted hover:text-primary-dark hover:bg-primary-soft/60' }}">
                        <x-icon name="bar-chart-2" class="w-5 h-5" />
                        <span>Nilai & Rapor</span>
                    </a>
                    <a href="{{ route('kelas-saya.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-colors {{ request()->routeIs('kelas-saya.*') ? 'bg-primary-soft text-primary-dark font-semibold' : 'text-muted hover:text-primary-dark hover:bg-primary-soft/60' }}">
                        <x-icon name="trending-up" class="w-5 h-5" />
                        <span>Progress Pembelajaran</span>
                    </a>
                </nav>
            </div>
            <div>
                <div class="text-[11px] font-bold uppercase tracking-wider text-muted px-3 mb-2">Informasi</div>
                <nav class="space-y-1" @click="if (window.innerWidth < 1024) sidebarOpen = false">
                    <a href="{{ route('announcements.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-colors {{ request()->routeIs('announcements.*') ? 'bg-primary-soft text-primary-dark font-semibold' : 'text-muted hover:text-primary-dark hover:bg-primary-soft/60' }}">
                        <x-icon name="bell" class="w-5 h-5" />
                        <span>Pengumuman</span>
                    </a>
                    <a href="{{ route('forum.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-colors {{ request()->routeIs('forum.*') ? 'bg-primary-soft text-primary-dark font-semibold' : 'text-muted hover:text-primary-dark hover:bg-primary-soft/60' }}">
                        <x-icon name="message-square" class="w-5 h-5" />
                        <span>Forum Diskusi</span>
                    </a>
                    <span class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-colors text-muted/60 cursor-not-allowed opacity-75" title="Fitur segera hadir">
                        <x-icon name="calendar-days" class="w-5 h-5" />
                        <span>Kalender Akademik</span>
                        <span class="ml-auto text-[10px] bg-muted/15 text-muted px-2 py-0.5 rounded-full font-medium">Segera</span>
                    </span>
                    <span class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-colors text-muted/60 cursor-not-allowed opacity-75" title="Fitur segera hadir">
                        <x-icon name="history" class="w-5 h-5" />
                        <span>Riwayat Pembelajaran</span>
                        <span class="ml-auto text-[10px] bg-muted/15 text-muted px-2 py-0.5 rounded-full font-medium">Segera</span>
                    </span>
                </nav>
            </div>
        @endrole
    </div>

    <!-- User Profile Footer / Logout Mobile -->
    <div class="p-4 border-t border-border lg:hidden">
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="w-full flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl text-sm font-medium text-accent bg-accent-soft hover:bg-accent-soft/80 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" /></svg>
                <span>Keluar Sistem</span>
            </button>
        </form>
    </div>
</aside>
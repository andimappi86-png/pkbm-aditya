@props([
    'active' => 'dashboard',
])

<!-- Desktop & Tablet Sidebar -->
<aside
    class="hidden lg:flex flex-col w-64 bg-surface border-r border-border shadow-soft min-h-screen fixed inset-y-0 left-0 z-30 transition-all duration-300"
>
    <!-- Brand / Logo Header -->
    <div class="h-20 flex items-center gap-3.5 px-6 border-b border-border">
        <img
            src="{{ asset('images/logo-pkbm-aditya.png') }}"
            alt="Logo PKBM Aditya"
            class="w-11 h-11 object-contain flex-shrink-0 drop-shadow-sm"
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

    <!-- Navigation List -->
    <div class="flex-1 overflow-y-auto px-4 py-6 space-y-6">
        <!-- Main Navigation -->
        <div>
            <div class="text-[11px] font-bold uppercase tracking-wider text-muted px-3 mb-2">
                Menu Utama
            </div>
            <nav class="space-y-1">
                <a
                    href="{{ route('dashboard') }}"
                    class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-colors {{ request()->routeIs('dashboard') ? 'bg-primary-soft text-primary-dark font-semibold shadow-sm ring-1 ring-primary/10' : 'text-muted hover:text-primary-dark hover:bg-primary-soft/60' }}"
                >
                    <svg class="w-5 h-5 {{ request()->routeIs('dashboard') ? 'text-primary' : 'text-muted' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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

        <!-- Master Data (Permission-Based) -->
        @if(auth()->user()->can('students.view') || auth()->user()->can('tutors.view') || auth()->user()->can('classrooms.view') || auth()->user()->can('programs.view'))
        <div>
            <div class="text-[11px] font-bold uppercase tracking-wider text-muted px-3 mb-2">
                Master Data
            </div>
            <nav class="space-y-1">
                @can('students.view')
                <a href="{{ route('peserta-didik.index') }}" class="flex items-center justify-between px-3.5 py-2 rounded-xl text-sm font-medium transition-colors {{ request()->routeIs('peserta-didik.*') ? 'bg-primary-soft text-primary-dark font-semibold shadow-sm ring-1 ring-primary/10' : 'text-muted hover:text-primary-dark hover:bg-primary-soft/60' }}">
                    <div class="flex items-center gap-3">
                        <svg class="w-5 h-5 {{ request()->routeIs('peserta-didik.*') ? 'text-primary' : 'text-muted' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                        <span>Peserta Didik</span>
                    </div>
                </a>
                @endcan

@can('tutors.view')
<a href="{{ route('tutors.index') }}" class="flex items-center justify-between px-3.5 py-2 rounded-xl text-sm font-medium transition-colors {{ request()->routeIs('tutors.*') ? 'bg-primary-soft text-primary-dark font-semibold shadow-sm ring-1 ring-primary/10' : 'text-muted hover:text-primary-dark hover:bg-primary-soft/60' }}">
    <div class="flex items-center gap-3">
        <svg class="w-5 h-5 {{ request()->routeIs('tutors.*') ? 'text-primary' : 'text-muted' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <span>Tutor</span>
    </div>
    <span class="text-[10px] font-medium text-muted">Phase 3</span>
</a>
@endcan

                @can('classrooms.view')
                <a href="{{ route('rombel.index') }}" class="flex items-center justify-between px-3.5 py-2 rounded-xl text-sm font-medium transition-colors {{ request()->routeIs('rombel.*') ? 'bg-primary-soft text-primary-dark font-semibold shadow-sm ring-1 ring-primary/10' : 'text-primary hover:text-primary-dark hover:bg-primary-soft/60' }}">
                    <div class="flex items-center gap-3">
                        <svg class="w-5 h-5 {{ request()->routeIs('rombel.index') ? 'text-primary' : 'text-muted' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z" />
                        </svg>
                        <span>Rombel</span>
                    </div>
                    <span class="text-[10px] font-medium text-muted">Phase 3</span>
                </a>
                @endcan

                @can('classrooms.manage')
                <a href="{{ route('classroom-assignments.index') }}" class="flex items-center justify-between px-3.5 py-2 rounded-xl text-sm font-medium transition-colors {{ request()->routeIs('classroom-assignments*') ? 'bg-primary-soft text-primary-dark font-semibold shadow-sm ring-1 ring-primary/10' : 'text-primary hover:text-primary-dark hover:bg-primary-soft/60' }}">
                    <div class="flex items-center gap-3">
                        <svg class="w-5 h-5 {{ request()->routeIs('classroom-assignments*') ? 'text-primary' : 'text-muted' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9l6 2 6-2m-6 2l6 2 6-2m-6-2l6-2-6 2m6-6v6m6-6v-6m-7-3h10m-7 3h10" />
                        </svg>
                        <span>Penugasan</span>
                    </div>
                    <span class="text-[10px] font-medium text-muted">Phase 3</span>
                </a>
                @endcan

                @can('programs.view')
                <div class="flex items-center justify-between px-3.5 py-2 rounded-xl text-sm text-muted cursor-not-allowed select-none bg-background/70">
                    <div class="flex items-center gap-3">
                        <svg class="w-5 h-5 text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                        </svg>
                        <span>Program</span>
                    </div>
                    <span class="text-[10px] font-medium text-muted">Phase 3</span>
                </div>
                @endcan
            </nav>
        </div>
        @endif

        <!-- Akademik (Permission-Based) -->
        @if(auth()->user()->can('schedules.view') || auth()->user()->can('attendance.view') || auth()->user()->can('grades.view'))
        <div>
            <div class="text-[11px] font-bold uppercase tracking-wider text-muted px-3 mb-2">
                Akademik
            </div>
            <nav class="space-y-1">
                @can('schedules.view')
                <a href="{{ route('jadwal.index') }}" class="flex items-center justify-between px-3.5 py-2 rounded-xl text-sm font-medium transition-colors {{ request()->routeIs('jadwal.*') ? 'bg-primary-soft text-primary-dark font-semibold shadow-sm ring-1 ring-primary/10' : 'text-primary hover:text-primary-dark hover:bg-primary-soft/60' }}">
                    <div class="flex items-center gap-3">
                        <svg class="w-5 h-5 {{ request()->routeIs('jadwal.*') ? 'text-primary' : 'text-muted' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        <span>Jadwal</span>
                    </div>
                </a>
                @endcan

                @can('attendance.view')
                <a href="{{ route('absensi.index') }}" class="flex items-center justify-between px-3.5 py-2 rounded-xl text-sm font-medium transition-colors {{ request()->routeIs('absensi.*') ? 'bg-primary-soft text-primary-dark font-semibold shadow-sm ring-1 ring-primary/10' : 'text-primary hover:text-primary-dark hover:bg-primary-soft/60' }}">
                    <div class="flex items-center gap-3">
                        <svg class="w-5 h-5 {{ request()->routeIs('absensi.*') ? 'text-primary' : 'text-muted' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                        <span>Absensi</span>
                    </div>
                </a>
                @endcan

                @can('grades.view')
                <a href="{{ route('nilai.index') }}" class="flex items-center justify-between px-3.5 py-2 rounded-xl text-sm font-medium transition-colors {{ request()->routeIs('nilai.*') ? 'bg-primary-soft text-primary-dark font-semibold' : 'text-muted hover:text-primary-dark hover:bg-primary-soft/60' }}">
                    <div class="flex items-center gap-3">
                        <svg class="w-5 h-5 {{ request()->routeIs('nilai.*') ? 'text-primary' : 'text-muted' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                        <span>Nilai</span>
                    </div>
                </a>
                @endcan
            </nav>
        </div>
        @endif

        <!-- Pembelajaran (Permission-Based) -->
        @if(auth()->user()->can('materials.view') || auth()->user()->can('assignments.view'))
        <div>
            <div class="text-[11px] font-bold uppercase tracking-wider text-muted px-3 mb-2">
                Pembelajaran
            </div>
            <nav class="space-y-1">
                @can('materials.view')
                <a href="{{ route('materi.index') }}" class="flex items-center justify-between px-3.5 py-2 rounded-xl text-sm font-medium transition-colors {{ request()->routeIs('materi.*') ? 'bg-primary-soft text-primary-dark font-semibold' : 'text-muted hover:text-primary-dark hover:bg-primary-soft/60' }}">
                    <div class="flex items-center gap-3">
                        <svg class="w-5 h-5 {{ request()->routeIs('materi.*') ? 'text-primary' : 'text-muted' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                        </svg>
                        <span>Materi</span>
                    </div>
                </a>
                @endcan

                @can('materials.view')
                <a href="{{ route('forum.index') }}" class="flex items-center justify-between px-3.5 py-2 rounded-xl text-sm font-medium transition-colors {{ request()->routeIs('forum.*') ? 'bg-primary-soft text-primary-dark font-semibold' : 'text-muted hover:text-primary-dark hover:bg-primary-soft/60' }}">
                    <div class="flex items-center gap-3">
                        <svg class="w-5 h-5 {{ request()->routeIs('forum.*') ? 'text-primary' : 'text-muted' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a1.994 1.994 0 01-1.414-.586m0 0L11 14h4a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2v4l.586-.586z" />
                        </svg>
                        <span>Forum Diskusi</span>
                    </div>
                </a>
                @endcan

                @can('assignments.view')
                <a href="{{ route('tugas.index') }}" class="flex items-center justify-between px-3.5 py-2 rounded-xl text-sm font-medium transition-colors {{ request()->routeIs('tugas.*') ? 'bg-primary-soft text-primary-dark font-semibold' : 'text-muted hover:text-primary-dark hover:bg-primary-soft/60' }}">
                    <div class="flex items-center gap-3">
                        <svg class="w-5 h-5 {{ request()->routeIs('tugas.*') ? 'text-primary' : 'text-muted' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                        <span>Tugas</span>
                    </div>
                </a>
                @endcan
            </nav>
        </div>
        @endif

        <!-- Laporan & Rekap (Permission-Based) -->
        @if(auth()->user()->can('reports.view') || auth()->user()->hasAnyRole(['tutor', 'peserta-didik']))
        <div>
            <div class="text-[11px] font-bold uppercase tracking-wider text-muted px-3 mb-2">
                Laporan
            </div>
            <nav class="space-y-1">
                <a href="{{ route('rekap.index') }}" class="flex items-center justify-between px-3.5 py-2 rounded-xl text-sm font-medium transition-colors {{ request()->routeIs('rekap.*') ? 'bg-primary-soft text-primary-dark font-semibold' : 'text-muted hover:text-primary-dark hover:bg-primary-soft/60' }}">
                    <div class="flex items-center gap-3">
                        <svg class="w-5 h-5 {{ request()->routeIs('rekap.*') ? 'text-primary' : 'text-muted' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v1a3 3 0 106 0v-1m-6 0a3 3 0 006 0m-6 0H3m12 0h6m-9-5a3 3 0 100-6 3 3 0 000 6z" />
                        </svg>
                        <span>Rekap Akademik</span>
                    </div>
                </a>

                <a href="{{ route('rapor.index') }}" class="flex items-center justify-between px-3.5 py-2 rounded-xl text-sm font-medium transition-colors {{ request()->routeIs('rapor.*') ? 'bg-primary-soft text-primary-dark font-semibold' : 'text-muted hover:text-primary-dark hover:bg-primary-soft/60' }}">
                    <div class="flex items-center gap-3">
                        <svg class="w-5 h-5 {{ request()->routeIs('rapor.*') ? 'text-primary' : 'text-muted' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                        </svg>
                        <span>Rapor</span>
                    </div>
                </a>
            </nav>
        </div>
        @endif

        <!-- System (Admin Only) -->
        @if(auth()->user()->can('users.view') || auth()->user()->can('settings.view'))
        <div>
            <div class="text-[11px] font-bold uppercase tracking-wider text-muted px-3 mb-2">
                Sistem
            </div>
            <nav class="space-y-1">
                @can('users.view')
                <div class="flex items-center justify-between px-3.5 py-2 rounded-xl text-sm text-muted cursor-not-allowed select-none bg-background/70">
                    <div class="flex items-center gap-3">
                        <svg class="w-5 h-5 text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                        <span>Pengguna</span>
                    </div>
                    <span class="text-[10px] font-medium text-muted">Phase 3</span>
                </div>
                @endcan

                @can('settings.view')
                <div class="flex items-center justify-between px-3.5 py-2 rounded-xl text-sm text-muted cursor-not-allowed select-none bg-background/70">
                    <div class="flex items-center gap-3">
                        <svg class="w-5 h-5 text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        <span>Pengaturan</span>
                    </div>
                    <span class="text-[10px] font-medium text-muted">Phase 6</span>
                </div>
                @endcan
            </nav>
        </div>
        @endif
    </div>

    <!-- Bottom PKBM Info Card -->
    <div class="p-4 border-t border-border">
        <div class="p-3.5 rounded-2xl bg-gradient-to-br from-primary-soft to-primary-soft/50 border border-primary/20 flex items-center gap-3">
            <div class="w-8 h-8 rounded-xl bg-primary text-white flex items-center justify-center flex-shrink-0 shadow-xs">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div class="min-w-0 flex-1">
                <div class="text-xs font-semibold text-navy">Sistem Versi 1.0</div>
                <div class="text-[11px] text-muted">Phase 1 Foundation</div>
            </div>
        </div>
    </div>
</aside>

<!-- Mobile Drawer Sidebar (Alpine.js controlled) -->
<div
    x-show="sidebarOpen"
    x-cloak
    class="relative z-50 lg:hidden"
    role="dialog"
    aria-modal="true"
>
    <!-- Backdrop Overlay -->
    <div
        x-show="sidebarOpen"
        x-transition:enter="transition-opacity ease-linear duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition-opacity ease-linear duration-300"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        @click="sidebarOpen = false"
        class="fixed inset-0 bg-navy/60 backdrop-blur-xs"
    ></div>

    <!-- Drawer Panel -->
    <div class="fixed inset-0 flex">
        <div
            x-show="sidebarOpen"
            x-transition:enter="transition ease-in-out duration-300 transform"
            x-transition:enter-start="-translate-x-full"
            x-transition:enter-end="translate-x-0"
            x-transition:leave="transition ease-in-out duration-300 transform"
            x-transition:leave-start="translate-x-0"
            x-transition:leave-end="-translate-x-full"
            class="relative mr-16 flex w-full max-w-xs flex-1"
        >
            <div class="flex flex-col w-full bg-surface">
                <!-- Mobile Drawer Header -->
                <div class="h-16 flex items-center justify-between px-6 border-b border-border">
                    <div class="flex items-center gap-3">
                        <img
                            src="{{ asset('images/logo-pkbm-aditya.png') }}"
                            alt="Logo PKBM Aditya"
                            class="w-9 h-9 object-contain"
                        />
                        <div class="flex flex-col">
                            <span class="font-extrabold text-xs text-navy tracking-tight uppercase">PKBM ADITYA</span>
                            <span class="text-[10px] text-muted">Kabupaten Mamuju</span>
                        </div>
                    </div>
                    <button
                        type="button"
                        @click="sidebarOpen = false"
                        class="p-1 rounded-lg text-muted hover:text-primary-dark focus:outline-none focus:ring-2 focus:ring-primary/20"
                    >
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- Drawer Nav Links -->
                <div class="flex-1 overflow-y-auto px-4 py-6 space-y-6">
                    <div>
                        <div class="text-[11px] font-bold uppercase tracking-wider text-muted px-3 mb-2">
                            Menu Utama
                        </div>
                        <nav class="space-y-1">
                            <a
                                href="{{ route('dashboard') }}"
                                class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-colors {{ request()->routeIs('dashboard') ? 'bg-primary-soft text-primary-dark font-semibold' : 'text-muted hover:text-primary-dark hover:bg-primary-soft/60' }}"
                            >
                                <svg class="w-5 h-5 {{ request()->routeIs('dashboard') ? 'text-primary' : 'text-muted' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                                </svg>
                                <span>Dashboard</span>
                            </a>

                            <a
                                href="{{ route('profile.edit') }}"
                                class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-colors {{ request()->routeIs('profile.*') ? 'bg-primary-soft text-primary-dark font-semibold' : 'text-muted hover:text-primary-dark hover:bg-primary-soft/60' }}"
                            >
                                <svg class="w-5 h-5 {{ request()->routeIs('profile.*') ? 'text-primary' : 'text-muted' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                                <span>Profil Saya</span>
                            </a>
                        </nav>
                    </div>

                    <!-- Master Data Disabled -->
                    <div>
                        <div class="flex items-center justify-between px-3 mb-2">
                            <span class="text-[11px] font-bold uppercase tracking-wider text-muted">Master Data</span>
                            <span class="text-[10px] font-medium px-1.5 py-0.5 rounded-md bg-background text-muted">Phase 2</span>
                        </div>
                        <div class="space-y-1">
                            <div class="flex items-center justify-between px-3.5 py-2 rounded-xl text-sm text-muted bg-background/70">
                                <span>Akademik</span>
                                <span class="text-[10px]">Segera</span>
                            </div>
                            <div class="flex items-center justify-between px-3.5 py-2 rounded-xl text-sm text-muted bg-background/70">
                                <span>Pembelajaran</span>
                                <span class="text-[10px]">Segera</span>
                            </div>
                            <div class="flex items-center justify-between px-3.5 py-2 rounded-xl text-sm text-muted bg-background/70">
                                <span>Manajemen</span>
                                <span class="text-[10px]">Segera</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Logout in Drawer -->
                <div class="p-4 border-t border-border">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button
                            type="submit"
                            class="w-full flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl text-sm font-medium text-accent bg-accent-soft hover:bg-accent-soft/80 transition"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                            </svg>
                            <span>Keluar Sistem</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

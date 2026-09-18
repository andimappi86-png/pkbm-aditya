<x-app-layout>
    <x-slot name="title">Dashboard Siswa</x-slot>

    <div class="max-w-7xl mx-auto space-y-6">
        <!-- Welcome Card -->
        <div class="card p-6 lg:p-8 bg-gradient-to-br from-surface via-surface to-primary-soft/40">
            <div class="flex items-start gap-6">
                <div class="flex-shrink-0">
                    <x-avatar :src="auth()->user()->avatarUrl" :alt="auth()->user()->name" size="xl" />
                </div>
                <div class="flex-1">
                    <h1 class="text-2xl lg:text-3xl font-bold text-navy mb-2">
                        Selamat datang, {{ auth()->user()->name }}
                    </h1>
                    <p class="text-muted mb-4">
                        Anda masuk sebagai <span class="font-semibold text-primary-dark">Peserta Didik</span>
                    </p>
                    <div class="flex items-center gap-4 text-sm text-muted">
                        <div class="flex items-center gap-2">
                            <x-icon name="calendar" class="w-4 h-4" />
                            <span>{{ now()->locale('id')->isoFormat('dddd, D MMMM Y') }}</span>
                        </div>
                    </div>
                </div>
                <div class="hidden lg:block flex-shrink-0">
                    <img src="{{ asset('images/logo-pkbm-aditya.png') }}" alt="Logo" class="w-20 h-20 opacity-20">
                </div>
            </div>
        </div>

        <!-- Role Info -->
        <div class="card p-6">
            <div class="flex items-start gap-4">
                <div class="flex-shrink-0">
                    <div class="w-12 h-12 rounded-xl bg-primary-soft text-primary-dark flex items-center justify-center">
                        <x-icon name="info-circle" class="w-6 h-6" />
                    </div>
                </div>
                <div class="flex-1">
                    <h3 class="text-lg font-semibold text-navy mb-2">Dashboard Pembelajaran Saya</h3>
                    <p class="text-muted text-sm leading-relaxed mb-3">
                        Sebagai Peserta Didik, Anda dapat mengakses materi pembelajaran, tugas, kuis, ujian, 
                        melihat jadwal, absensi, dan nilai Anda.
                    </p>
                    <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg bg-primary-soft text-primary-dark text-sm font-medium">
                        <x-icon name="check-circle" class="w-4 h-4" />
                        <span>Own Data Only</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stats Grid -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            <x-stat-card 
                title="Materi Belajar" 
                :value="$stats['total_materi']" 
                variant="primary" 
                icon="<path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253' />"
            />
            <x-stat-card 
                title="Tugas Terkumpul" 
                :value="$stats['tugas_selesai'] . ' / ' . $stats['total_tugas']" 
                variant="primary" 
                icon="<path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2' />"
            />
            <x-stat-card 
                title="Persentase Kehadiran" 
                :value="$stats['kehadiran'] . '%'" 
                variant="primary" 
                icon="<path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z' />"
            />
        </div>

        <!-- My Info -->
        <div class="grid md:grid-cols-2 gap-6">
            <!-- My Program -->
            <div class="card p-6">
                <h3 class="text-lg font-semibold text-navy mb-4 flex items-center gap-2">
                    <x-icon name="package" class="w-5 h-5 text-primary" />
                    Program Saya
                </h3>
                
                @if($myProgram)
                    <div class="flex items-center gap-4 p-4 rounded-xl bg-primary-soft border border-primary/20">
                        <div class="w-12 h-12 rounded-xl bg-primary text-white flex items-center justify-center">
                            <x-icon name="book" class="w-6 h-6" />
                        </div>
                        <div>
                            <div class="font-semibold text-primary-dark">{{ $myProgram->nama }}</div>
                            <div class="text-sm text-muted">{{ $myProgram->deskripsi }}</div>
                        </div>
                    </div>
                @else
                    <x-empty-state 
                        icon="package"
                        title="Belum Terdaftar di Program"
                        description="Data program akan tersedia setelah Anda terdaftar sebagai peserta didik di Phase 3."
                    />
                @endif
            </div>

            <!-- My Classroom -->
            <div class="card p-6">
                <h3 class="text-lg font-semibold text-navy mb-4 flex items-center gap-2">
                    <x-icon name="layers" class="w-5 h-5 text-primary" />
                    Kelas Saya
                </h3>
                
                @if($myClassroom)
                    <div class="flex items-center gap-4 p-4 rounded-xl bg-primary-soft border border-primary/20">
                        <div class="w-12 h-12 rounded-xl bg-primary text-white flex items-center justify-center">
                            <x-icon name="users" class="w-6 h-6" />
                        </div>
                        <div>
                            <div class="font-semibold text-primary-dark">{{ $myClassroom->nama_rombel }}</div>
                            <div class="text-sm text-muted">Rombel Aktif</div>
                        </div>
                    </div>
                @else
                    <x-empty-state 
                        icon="layers"
                        title="Belum Masuk Rombel"
                        description="Data kelas akan tersedia setelah Anda ditugaskan ke rombel di Phase 3."
                    />
                @endif
            </div>
        </div>

        <!-- Quick Access -->
        <div class="card p-6">
            <h3 class="text-lg font-semibold text-navy mb-4 flex items-center gap-2">
                <x-icon name="book-open" class="w-5 h-5 text-primary" />
                Akses Cepat
            </h3>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                @can('materials.view')
                <a href="{{ route('materi.index') }}" class="flex flex-col items-center gap-2 p-4 rounded-xl border border-border hover:border-primary/50 hover:bg-primary-soft transition-colors cursor-pointer">
                    <div class="w-10 h-10 rounded-xl bg-primary-soft text-primary-dark flex items-center justify-center">
                        <x-icon name="book-open" class="w-5 h-5" />
                    </div>
                    <span class="text-sm font-medium text-navy">Materi</span>
                </a>
                @endcan

                @can('assignments.view')
                <a href="{{ route('tugas.index') }}" class="flex flex-col items-center gap-2 p-4 rounded-xl border border-border hover:border-primary/50 hover:bg-primary-soft transition-colors cursor-pointer">
                    <div class="w-10 h-10 rounded-xl bg-primary-soft text-primary-dark flex items-center justify-center">
                        <x-icon name="clipboard" class="w-5 h-5" />
                    </div>
                    <span class="text-sm font-medium text-navy">Tugas</span>
                </a>
                @endcan

                @can('grades.view')
                <a href="{{ route('nilai.index') }}" class="flex flex-col items-center gap-2 p-4 rounded-xl border border-border hover:border-primary/50 hover:bg-primary-soft transition-colors cursor-pointer">
                    <div class="w-10 h-10 rounded-xl bg-primary-soft text-primary-dark flex items-center justify-center">
                        <x-icon name="chart-bar" class="w-5 h-5" />
                    </div>
                    <span class="text-sm font-medium text-navy">Nilai</span>
                </a>
                @endcan

                @can('schedules.view')
                <a href="{{ route('jadwal.index') }}" class="flex flex-col items-center gap-2 p-4 rounded-xl border border-border hover:border-primary/50 hover:bg-primary-soft transition-colors cursor-pointer">
                    <div class="w-10 h-10 rounded-xl bg-primary-soft text-primary-dark flex items-center justify-center">
                        <x-icon name="calendar" class="w-5 h-5" />
                    </div>
                    <span class="text-sm font-medium text-navy">Jadwal</span>
                </a>
                @endcan
            </div>
        </div>
    </div>
</x-app-layout>
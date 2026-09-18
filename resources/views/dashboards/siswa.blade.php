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
                            <div class="font-semibold text-primary-dark">{{ $myProgram->name }}</div>
                            <div class="text-sm text-muted">{{ $myProgram->level }}</div>
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
                            <div class="font-semibold text-primary-dark">{{ $myClassroom->name }}</div>
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
                <button class="flex flex-col items-center gap-2 p-4 rounded-xl border border-border hover:border-primary/50 hover:bg-primary-soft transition-colors" disabled>
                    <div class="w-10 h-10 rounded-xl bg-primary-soft text-primary-dark flex items-center justify-center">
                        <x-icon name="book-open" class="w-5 h-5" />
                    </div>
                    <span class="text-sm font-medium text-navy">Materi</span>
                    <span class="text-xs text-muted">Phase 5</span>
                </button>
                @endcan

                @can('assignments.view')
                <button class="flex flex-col items-center gap-2 p-4 rounded-xl border border-border hover:border-primary/50 hover:bg-primary-soft transition-colors" disabled>
                    <div class="w-10 h-10 rounded-xl bg-primary-soft text-primary-dark flex items-center justify-center">
                        <x-icon name="clipboard" class="w-5 h-5" />
                    </div>
                    <span class="text-sm font-medium text-navy">Tugas</span>
                    <span class="text-xs text-muted">Phase 5</span>
                </button>
                @endcan

                @can('grades.view')
                <button class="flex flex-col items-center gap-2 p-4 rounded-xl border border-border hover:border-primary/50 hover:bg-primary-soft transition-colors" disabled>
                    <div class="w-10 h-10 rounded-xl bg-primary-soft text-primary-dark flex items-center justify-center">
                        <x-icon name="chart-bar" class="w-5 h-5" />
                    </div>
                    <span class="text-sm font-medium text-navy">Nilai</span>
                    <span class="text-xs text-muted">Phase 4</span>
                </button>
                @endcan

                @can('schedules.view')
                <button class="flex flex-col items-center gap-2 p-4 rounded-xl border border-border hover:border-primary/50 hover:bg-primary-soft transition-colors" disabled>
                    <div class="w-10 h-10 rounded-xl bg-primary-soft text-primary-dark flex items-center justify-center">
                        <x-icon name="calendar" class="w-5 h-5" />
                    </div>
                    <span class="text-sm font-medium text-navy">Jadwal</span>
                    <span class="text-xs text-muted">Phase 4</span>
                </button>
                @endcan
            </div>
        </div>
    </div>
</x-app-layout>
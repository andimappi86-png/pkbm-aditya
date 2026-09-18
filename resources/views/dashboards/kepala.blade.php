<x-app-layout>
    <x-slot name="title">Dashboard Kepala PKBM</x-slot>

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
                        Anda masuk sebagai <span class="font-semibold text-primary-dark">Kepala PKBM</span>
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
                    <h3 class="text-lg font-semibold text-navy mb-2">Dashboard Pengendalian Operasional</h3>
                    <p class="text-muted text-sm leading-relaxed mb-3">
                        Sebagai Kepala PKBM, Anda memiliki kontrol penuh terhadap seluruh operasional PKBM Aditya. 
                        Anda dapat mengelola data peserta didik, tutor, rombel, akademik, dan sistem.
                    </p>
                    <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg bg-primary-soft text-primary-dark text-sm font-medium">
                        <x-icon name="check-circle" class="w-4 h-4" />
                        <span>Full Operational Access</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stats Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <x-stat-card 
                title="Peserta Didik" 
                :value="$totalStudents" 
                variant="primary" 
            />
            <x-stat-card 
                title="Tutor" 
                :value="$totalTutors" 
                variant="primary" 
            />
            <x-stat-card 
                title="Rombel" 
                :value="$totalClassrooms" 
                variant="primary" 
            />
            <x-stat-card 
                title="Program" 
                :value="$totalPrograms" 
                variant="primary" 
            />
        </div>

        <!-- Quick Actions -->
        <div class="card p-6">
            <h3 class="text-lg font-semibold text-navy mb-4 flex items-center gap-2">
                <x-icon name="layers" class="w-5 h-5 text-primary" />
                Aksi Cepat
            </h3>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                @can('students.manage')
                <a href="{{ route('peserta-didik.index') }}" class="flex flex-col items-center gap-2 p-4 rounded-xl border border-border hover:border-primary/50 hover:bg-primary-soft transition-colors cursor-pointer">
                    <div class="w-10 h-10 rounded-xl bg-primary-soft text-primary-dark flex items-center justify-center">
                        <x-icon name="users" class="w-5 h-5" />
                    </div>
                    <span class="text-sm font-medium text-navy">Kelola Siswa</span>
                </a>
                @endcan

                @can('tutors.manage')
                <button class="flex flex-col items-center gap-2 p-4 rounded-xl border border-border hover:border-primary/50 hover:bg-primary-soft transition-colors" disabled>
                    <div class="w-10 h-10 rounded-xl bg-primary-soft text-primary-dark flex items-center justify-center">
                        <x-icon name="user-check" class="w-5 h-5" />
                    </div>
                    <span class="text-sm font-medium text-navy">Kelola Tutor</span>
                    <span class="text-xs text-muted">Phase 3</span>
                </button>
                @endcan

                @can('classrooms.manage')
                <button class="flex flex-col items-center gap-2 p-4 rounded-xl border border-border hover:border-primary/50 hover:bg-primary-soft transition-colors" disabled>
                    <div class="w-10 h-10 rounded-xl bg-primary-soft text-primary-dark flex items-center justify-center">
                        <x-icon name="layers" class="w-5 h-5" />
                    </div>
                    <span class="text-sm font-medium text-navy">Kelola Rombel</span>
                    <span class="text-xs text-muted">Phase 3</span>
                </button>
                @endcan

                @can('reports.view')
                <button class="flex flex-col items-center gap-2 p-4 rounded-xl border border-border hover:border-primary/50 hover:bg-primary-soft transition-colors" disabled>
                    <div class="w-10 h-10 rounded-xl bg-primary-soft text-primary-dark flex items-center justify-center">
                        <x-icon name="chart-bar" class="w-5 h-5" />
                    </div>
                    <span class="text-sm font-medium text-navy">Lihat Laporan</span>
                    <span class="text-xs text-muted">Phase 6</span>
                </button>
                @endcan
            </div>
        </div>
    </div>
</x-app-layout>
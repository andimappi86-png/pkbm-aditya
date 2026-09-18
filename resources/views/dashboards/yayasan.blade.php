<x-app-layout>
    <x-slot name="title">Dashboard Ketua Yayasan</x-slot>

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
                        Anda masuk sebagai <span class="font-semibold text-primary-dark">Ketua Yayasan</span>
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
                    <h3 class="text-lg font-semibold text-navy mb-2">Dashboard Monitoring & Pengawasan</h3>
                    <p class="text-muted text-sm leading-relaxed mb-3">
                        Sebagai Ketua Yayasan, Anda memiliki akses monitoring untuk seluruh data PKBM Aditya. 
                        Dashboard ini menampilkan ringkasan dan laporan institusional.
                    </p>
                    <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg bg-primary-soft text-primary-dark text-sm font-medium">
                        <x-icon name="check-circle" class="w-4 h-4" />
                        <span>Read-Only Access</span>
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
                icon="users"
            />
            <x-stat-card 
                title="Tutor" 
                :value="$totalTutors" 
                variant="primary"
                icon="user-check" 
            />
            <x-stat-card 
                title="Rombel" 
                :value="$totalClassrooms" 
                variant="primary" 
                icon="layers"
            />
            <x-stat-card 
                title="Program" 
                :value="$totalPrograms" 
                variant="primary" 
                icon="book"
            />
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Academic Overview -->
            <div class="card p-6">
                <h3 class="text-lg font-semibold text-navy mb-4">Ringkasan Akademik</h3>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div class="p-4 rounded-xl border border-border bg-surface text-center">
                        <div class="text-2xl font-bold text-primary">{{ number_format($avgGrade, 1) }}</div>
                        <div class="text-sm text-muted">Rata-rata Nilai</div>
                    </div>
                    <div class="p-4 rounded-xl border border-border bg-surface text-center">
                        <div class="text-2xl font-bold text-primary">{{ $attendanceRate }}%</div>
                        <div class="text-sm text-muted">Tingkat Kehadiran</div>
                    </div>
                    <div class="p-4 rounded-xl border border-border bg-surface text-center">
                        <div class="text-2xl font-bold text-primary">{{ $ppdbStats['total'] }}</div>
                        <div class="text-sm text-muted">Total PPDB</div>
                    </div>
                    <div class="p-4 rounded-xl border border-border bg-surface text-center">
                        <div class="text-2xl font-bold text-primary">{{ $ppdbStats['diterima'] }}</div>
                        <div class="text-sm text-muted">PPDB Diterima</div>
                    </div>
                </div>
            </div>

            <!-- Program Breakdown -->
            <div class="card p-6">
                <h3 class="text-lg font-semibold text-navy mb-4">Sebaran per Program</h3>
                <div class="space-y-4">
                    @foreach($programStats as $program)
                    <div>
                        <div class="flex justify-between text-sm mb-1">
                            <span class="font-medium text-navy">{{ $program->nama }}</span>
                            <span class="text-muted">{{ $program->students_count }} Siswa</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="bg-primary rounded-full h-2" style="width: {{ $totalStudents > 0 ? ($program->students_count / $totalStudents) * 100 : 0 }}%"></div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Monitoring Summary -->
        <div class="card p-6">
            <h3 class="text-lg font-semibold text-navy mb-4">Ringkasan Monitoring</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="p-4 rounded-xl border border-border bg-surface text-center">
                    <div class="text-2xl font-bold text-primary">{{ $ppdbStats['pending'] }}</div>
                    <div class="text-sm text-muted">PPDB Menunggu</div>
                </div>
                <div class="p-4 rounded-xl border border-border bg-surface text-center">
                    <div class="text-2xl font-bold text-primary">{{ number_format($avgGrade, 1) }}</div>
                    <div class="text-sm text-muted">Rata-rata Nilai Global</div>
                </div>
                <div class="p-4 rounded-xl border border-border bg-surface text-center">
                    <div class="text-2xl font-bold text-primary">{{ $attendanceRate }}%</div>
                    <div class="text-sm text-muted">Kehadiran Global</div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
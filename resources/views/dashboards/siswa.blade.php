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
        <div class="grid grid-cols-2 lg:grid-cols-5 gap-4">
            <x-stat-card 
                title="Materi Belajar" 
                :value="$stats['total_materi']" 
                variant="primary" 
                icon="book-open"
            />
            <x-stat-card 
                title="Tugas Terkumpul" 
                :value="$stats['tugas_selesai'] . ' / ' . $stats['total_tugas']" 
                variant="primary" 
                icon="clipboard"
            />
            <x-stat-card 
                title="Kuis Diikuti" 
                :value="$stats['kuis_diikuti'] . ' / ' . $stats['total_kuis']" 
                variant="primary" 
                icon="lightning-bolt"
            />
            <x-stat-card 
                title="Rata-rata Nilai" 
                :value="number_format($stats['rata_rata_nilai'], 1)" 
                variant="primary" 
                icon="chart-bar"
            />
            <x-stat-card 
                title="Kehadiran" 
                :value="$stats['kehadiran'] . '%'" 
                variant="primary" 
                icon="calendar"
            />
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Left Column: Info & Links -->
            <div class="lg:col-span-2 space-y-6">
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
                                description="Anda belum terdaftar."
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
                                description="Anda belum dimasukkan ke kelas."
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
            
            <!-- Right Column -->
            <div class="space-y-6">
                <!-- Recent Announcements -->
                <div class="card p-6">
                    <h3 class="text-lg font-semibold text-navy mb-4 flex items-center justify-between">
                        Pengumuman
                        <a href="{{ route('announcements.index') }}" class="text-sm text-primary hover:underline">Semua</a>
                    </h3>
                    @if($recentAnnouncements->isNotEmpty())
                        <div class="space-y-3">
                            @foreach($recentAnnouncements as $announcement)
                            <a href="{{ route('announcements.show', $announcement) }}" class="block p-3 rounded-lg border border-border hover:bg-primary-soft transition-colors">
                                <div class="flex items-center justify-between">
                                    <span class="font-medium text-navy">{{ $announcement->judul }}</span>
                                    <span class="text-xs px-2 py-1 bg-primary-soft text-primary-dark rounded">{{ $announcement->kategori }}</span>
                                </div>
                                <div class="text-sm text-muted mt-1">{{ $announcement->published_at->format('d M Y') }}</div>
                            </a>
                            @endforeach
                        </div>
                    @else
                        <p class="text-muted text-center py-4">Tidak ada pengumuman</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
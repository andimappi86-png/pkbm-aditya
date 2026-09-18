<x-app-layout>
    
    <x-slot name="title">Dashboard Wali Kelas</x-slot>

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
                        Anda masuk sebagai <span class="font-semibold text-primary-dark">Wali Kelas</span>
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
                    <h3 class="text-lg font-semibold text-navy mb-2">Dashboard Pengelolaan Kelas</h3>
                    <p class="text-muted text-sm leading-relaxed mb-3">
                        Sebagai Wali Kelas, Anda dapat mengelola rombel yang ditugaskan kepada Anda. 
                        Anda dapat melihat dan memantau perkembangan siswa, absensi, dan perkembangan akademik kelas Anda.
                    </p>
                    <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg bg-primary-soft text-primary-dark text-sm font-medium">
                        <x-icon name="check-circle" class="w-4 h-4" />
                        <span>Classroom Scoped Access</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stats Grid -->
        <div class="grid grid-cols-2 lg:grid-cols-6 gap-4">
            <x-stat-card 
                title="Total Siswa" 
                :value="$stats['total_siswa']" 
                variant="primary" 
            />
            <x-stat-card 
                title="Rombel" 
                :value="$stats['total_rombel']" 
                variant="primary" 
            />
            <x-stat-card 
                title="Materi" 
                :value="$stats['total_materi']" 
                variant="primary" 
            />
            <x-stat-card 
                title="Tugas" 
                :value="$stats['total_tugas']" 
                variant="primary" 
            />
            <x-stat-card 
                title="Kuis" 
                :value="$stats['total_kuis']" 
                variant="primary" 
            />
            <x-stat-card 
                title="Kehadiran Rata-rata" 
                :value="$stats['kehadiran_rata'] . '%'" 
                variant="primary" 
            />
        </div>

        <!-- Academic Performance -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="card p-6">
                <h3 class="text-lg font-semibold text-navy mb-4">Performa Akademik Kelas</h3>
                <div class="grid grid-cols-2 gap-4">
                    <div class="p-4 rounded-xl border border-border bg-surface text-center">
                        <div class="text-3xl font-bold text-primary">{{ number_format($stats['rata_rata_nilai'], 1) }}</div>
                        <div class="text-sm text-muted">Rata-rata Nilai</div>
                    </div>
                    <div class="p-4 rounded-xl border border-border bg-surface text-center">
                        <div class="text-3xl font-bold text-primary">{{ $stats['kehadiran_rata'] }}%</div>
                        <div class="text-sm text-muted">Kehadiran</div>
                    </div>
                </div>
            </div>

            <!-- Recent Announcements -->
            <div class="card p-6">
                <h3 class="text-lg font-semibold text-navy mb-4 flex items-center justify-between">
                    Pengumuman Terbaru
                    <a href="{{ route('announcements.index') }}" class="text-sm text-primary hover:underline">Lihat Semua</a>
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

        <!-- My Classrooms -->
        <div class="card p-6">
            <h3 class="text-lg font-semibold text-navy mb-4 flex items-center gap-2">
                <x-icon name="layers" class="w-5 h-5 text-primary" />
                Rombel Saya
            </h3>
            
            @if(count($myClassrooms) > 0)
                <div class="space-y-3">
                    @foreach($myClassrooms as $classroom)
                    <div class="flex items-center justify-between p-4 rounded-xl border border-border hover:border-primary/50 hover:bg-primary-soft transition-colors">
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 rounded-xl bg-primary-soft text-primary-dark flex items-center justify-center">
                                <x-icon name="users" class="w-6 h-6" />
                            </div>
                            <div>
                                <div class="font-semibold text-navy">{{ $classroom->nama_rombel }}</div>
                                <div class="text-sm text-muted">{{ $classroom->students_count }} Siswa</div>
                            </div>
                        </div>
                        <a href="{{ route('rombel.index') }}" class="btn-secondary">
                            <x-icon name="folder" class="w-4 h-4" /> Kelola Kelas
                        </a>
                    </div>
                    @endforeach
                </div>
            @else
                <x-empty-state 
                    icon="layers"
                    title="Belum Ada Penugasan Kelas"
                    description="Anda belum ditugaskan sebagai wali kelas untuk rombel mana pun. Penugasan akan dilakukan di Phase 3."
                />
            @endif
        </div>
    </div>
</x-app-layout>
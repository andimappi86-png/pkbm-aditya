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
                    <h1 class="text-2xl lg:text-3xl font-bold text-text mb-2">
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
                    <h3 class="text-lg font-semibold text-text mb-2">Dashboard Pengelolaan Kelas</h3>
                    <p class="text-muted text-sm leading-relaxed mb-3">
                        Sebagai Wali Kelas, Anda dapat mengelola rombel yang ditugaskan kepada Anda. 
                        Anda dapat melihat dan memantau perkembangan siswa, absensi, dan perkembangan akademik kelas Anda.
                    </p>
                    <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg bg-primary-soft text-primary-dark text-sm font-medium">
                        <x-icon name="check-circle" class="w-4 h-4" />
                        <span>Classroom Scoped Access</span>
                    </div>

                    <div class="mt-4 pt-3 border-t border-slate-100 flex flex-wrap items-center gap-3">
                        <a href="{{ route('peserta-didik.index') }}"
                           class="bg-[#14532D] hover:bg-[#0D2618] text-white rounded-xl px-4 py-2 text-xs sm:text-sm font-semibold inline-flex items-center gap-2 whitespace-nowrap shadow-xs transition-colors">
                            <x-icon name="user-plus" class="w-4 h-4" />
                            <span>+ Tambah Siswa</span>
                        </a>
                        <a href="{{ route('peserta-didik.index') }}"
                           class="bg-white hover:bg-slate-50 text-slate-700 border border-slate-300 rounded-xl px-4 py-2 text-xs sm:text-sm font-semibold inline-flex items-center gap-2 whitespace-nowrap shadow-xs transition-colors">
                            <x-icon name="users" class="w-4 h-4 text-slate-500" />
                            <span>Lihat Anggota Kelas</span>
                        </a>
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
                icon="users"
            />
            <x-stat-card 
                title="Rombel" 
                :value="$stats['total_rombel']" 
                variant="primary" 
                icon="layers"
            />
            <x-stat-card 
                title="Materi" 
                :value="$stats['total_materi']" 
                variant="primary" 
                icon="book-open"
            />
            <x-stat-card 
                title="Tugas" 
                :value="$stats['total_tugas']" 
                variant="primary" 
                icon="clipboard"
            />
            <x-stat-card 
                title="Kuis" 
                :value="$stats['total_kuis']" 
                variant="primary" 
                icon="lightning-bolt"
            />
            <x-stat-card 
                title="Kehadiran Rata-rata" 
                :value="$stats['kehadiran_rata'] . '%'" 
                variant="primary" 
                icon="calendar"
            />
        </div>

        <!-- Academic Performance & Attendance Report -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="card p-6 border border-slate-200 shadow-xs">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-base font-semibold text-text flex items-center gap-2">
                        <x-icon name="chart-bar" class="w-5 h-5 text-primary" />
                        Performa Akademik & Rekap Presensi
                    </h3>
                    <a href="{{ route('rekap.index') }}" class="text-xs text-primary hover:underline font-medium inline-flex items-center gap-1">
                        <span>Laporan Rekap</span>
                        <x-icon name="arrow-right" class="w-3.5 h-3.5" />
                    </a>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                    <div class="p-3 rounded-lg border border-slate-200 bg-slate-50/50 text-center col-span-2 sm:col-span-1">
                        <div class="text-2xl font-bold text-primary">{{ $stats['kehadiran_rata'] }}%</div>
                        <div class="text-xs font-medium text-slate-500 mt-0.5">Kehadiran Rata-rata</div>
                    </div>
                    <div class="p-3 rounded-lg border border-slate-200 bg-white text-center">
                        <div class="text-xl font-bold text-emerald-600">{{ $stats['presensi_hadir'] }}</div>
                        <div class="text-xs font-medium text-slate-500 mt-0.5">Hadir</div>
                    </div>
                    <div class="p-3 rounded-lg border border-slate-200 bg-white text-center">
                        <div class="text-xl font-bold text-blue-600">{{ $stats['presensi_izin'] }}</div>
                        <div class="text-xs font-medium text-slate-500 mt-0.5">Izin</div>
                    </div>
                    <div class="p-3 rounded-lg border border-slate-200 bg-white text-center">
                        <div class="text-xl font-bold text-amber-600">{{ $stats['presensi_sakit'] }}</div>
                        <div class="text-xs font-medium text-slate-500 mt-0.5">Sakit</div>
                    </div>
                    <div class="p-3 rounded-lg border border-slate-200 bg-white text-center">
                        <div class="text-xl font-bold text-rose-600">{{ $stats['presensi_alpa'] }}</div>
                        <div class="text-xs font-medium text-slate-500 mt-0.5">Alpa</div>
                    </div>
                    <div class="p-3 rounded-lg border border-slate-200 bg-white text-center">
                        <div class="text-xl font-bold text-slate-700">{{ $stats['presensi_total'] }}</div>
                        <div class="text-xs font-medium text-slate-500 mt-0.5">Total Sesi Dicatat</div>
                    </div>
                </div>

                @if($stats['presensi_total'] === 0)
                    <p class="text-xs text-slate-500 mt-3 text-center bg-slate-50 py-2 rounded-md border border-slate-100">
                        Belum ada pencatatan presensi pada kelas binaan Anda. Kehadiran dihitung murni dari absensi aktual.
                    </p>
                @endif
            </div>

            <!-- Recent Announcements -->
            <div class="card p-6 border border-slate-200 shadow-xs">
                <h3 class="text-base font-semibold text-text mb-4 flex items-center justify-between">
                    <span>Pengumuman Terbaru</span>
                    <a href="{{ route('announcements.index') }}" class="text-xs text-primary hover:underline font-medium">Lihat Semua</a>
                </h3>
                @if($recentAnnouncements->isNotEmpty())
                    <div class="space-y-3">
                        @foreach($recentAnnouncements as $announcement)
                        <a href="{{ route('announcements.show', $announcement) }}" class="block p-3 rounded-lg border border-slate-200 hover:bg-slate-50 transition-colors">
                            <div class="flex items-center justify-between gap-2">
                                <span class="font-medium text-text text-sm truncate">{{ $announcement->judul }}</span>
                                <span class="text-xs px-2 py-0.5 bg-primary-soft text-primary-dark rounded font-medium shrink-0">{{ $announcement->kategori }}</span>
                            </div>
                            <div class="text-xs text-muted mt-1">{{ $announcement->published_at->format('d M Y') }}</div>
                        </a>
                        @endforeach
                    </div>
                @else
                    <p class="text-muted text-center py-6 text-sm">Tidak ada pengumuman terbaru</p>
                @endif
            </div>
        </div>

        <!-- My Classrooms -->
        <div class="card p-6 border border-slate-200 shadow-xs">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="text-base font-semibold text-text flex items-center gap-2">
                        <x-icon name="layers" class="w-5 h-5 text-primary" />
                        Rombel & Anggota Kelas Saya
                    </h3>
                    <p class="text-xs text-muted mt-0.5">Kelola data peserta didik dan akses laporan akademik per rombel.</p>
                </div>
            </div>
            
            @if(count($myClassrooms) > 0)
                <div class="space-y-3">
                    @foreach($myClassrooms as $classroom)
                    <div class="flex flex-col md:flex-row md:items-center justify-between p-4 rounded-xl border border-slate-200 hover:border-primary/40 bg-white transition-all gap-4">
                        <div class="flex items-center gap-3.5">
                            <div class="w-11 h-11 rounded-lg bg-primary-soft text-primary-dark flex items-center justify-center shrink-0">
                                <x-icon name="users" class="w-5 h-5" />
                            </div>
                            <div>
                                <div class="font-semibold text-text text-sm md:text-base">{{ $classroom->nama_rombel }}</div>
                                <div class="text-xs text-muted mt-0.5">
                                    {{ $classroom->program?->nama ?? 'Program Belajar' }} &bull; {{ $classroom->academicYear?->nama ?? 'Tahun Ajaran Aktif' }}
                                </div>
                                <div class="mt-1">
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-xs font-medium bg-slate-100 text-slate-700 border border-slate-200">
                                        <x-icon name="user" class="w-3 h-3 text-slate-400" />
                                        <span>{{ $classroom->students_count }} Siswa Terdaftar</span>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="flex items-center flex-wrap gap-2 pt-2 md:pt-0 border-t md:border-t-0 border-slate-100">
                            <a href="{{ route('rombel.show', $classroom) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium rounded-lg bg-primary text-white hover:bg-primary-dark transition shadow-xs" title="Lihat dan kelola anggota rombel {{ $classroom->nama_rombel }}">
                                <x-icon name="users" class="w-3.5 h-3.5" />
                                <span>Detail Anggota ({{ $classroom->students_count }})</span>
                            </a>
                            <a href="{{ route('rekap.index', ['classroom_id' => $classroom->id]) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium rounded-lg border border-slate-200 bg-white text-slate-700 hover:bg-slate-50 transition" title="Buka rekap akademik rombel {{ $classroom->nama_rombel }}">
                                <x-icon name="chart-bar" class="w-3.5 h-3.5 text-slate-500" />
                                <span>Laporan Akademik</span>
                            </a>
                            <a href="{{ route('rombel.show', $classroom) }}" class="btn-secondary text-xs">
                                <x-icon name="folder" class="w-3.5 h-3.5" />
                                <span>Kelola Kelas</span>
                            </a>
                        </div>
                    </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-8">
                    <x-icon name="layers" class="w-12 h-12 text-muted mx-auto mb-4" />
                    <h4 class="font-medium text-text mb-1">Belum Ada Penugasan Kelas</h4>
                    <p class="text-muted text-sm">Anda belum ditugaskan sebagai wali kelas untuk rombel mana pun.</p>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
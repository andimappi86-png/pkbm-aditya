<x-app-layout>
    <x-slot name="title">Dashboard Tutor & Wali Kelas</x-slot>

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
                        Anda masuk dengan peran ganda: <span class="font-semibold text-primary-dark">Tutor</span> &amp; <span class="font-semibold text-primary-dark">Wali Kelas</span>
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

        <!-- Role Scope Notice -->
        <div class="card p-6">
            <div class="flex items-start gap-4">
                <div class="flex-shrink-0">
                    <div class="w-12 h-12 rounded-xl bg-primary-soft text-primary-dark flex items-center justify-center">
                        <x-icon name="info-circle" class="w-6 h-6" />
                    </div>
                </div>
                <div class="flex-1">
                    <h3 class="text-lg font-semibold text-navy mb-2">Dashboard Gabungan Akademik &amp; Perwalian</h3>
                    <p class="text-muted text-sm leading-relaxed mb-3">
                        Halaman ini mengintegrasikan ringkasan kegiatan pengajaran mata pelajaran Anda sebagai Tutor sekaligus monitoring perkembangan peserta didik pada rombel yang Anda bimbing sebagai Wali Kelas.
                    </p>
                    <div class="flex flex-wrap gap-2">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg bg-primary-soft text-primary-dark text-xs font-semibold">
                            <x-icon name="check-circle" class="w-3.5 h-3.5" />
                            Tutor: Penugasan Mengajar
                        </span>
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg bg-emerald-50 text-emerald-700 text-xs font-semibold">
                            <x-icon name="check-circle" class="w-3.5 h-3.5" />
                            Wali Kelas: Rombel Binaan
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- SECTION 1: Ringkasan Pengajaran (Tutor) -->
        <div>
            <div class="flex items-center justify-between mb-3">
                <h2 class="text-base font-bold text-navy uppercase tracking-wider flex items-center gap-2">
                    <x-icon name="book" class="w-5 h-5 text-primary" />
                    Tanggung Jawab Pengajaran (Tutor)
                </h2>
                <span class="text-xs text-muted">Berdasarkan jadwal &amp; penugasan mengajar aktif</span>
            </div>
            <div class="grid grid-cols-2 lg:grid-cols-5 gap-4">
                <x-stat-card
                    title="Siswa Diajar"
                    :value="$tutorStats['total_siswa_ajar']"
                    variant="primary"
                    icon="users"
                />
                <x-stat-card
                    title="Materi Saya"
                    :value="$tutorStats['total_materi']"
                    variant="primary"
                    icon="book-open"
                />
                <x-stat-card
                    title="Tugas Dibuat"
                    :value="$tutorStats['total_tugas']"
                    variant="primary"
                    icon="clipboard"
                />
                <x-stat-card
                    title="Belum Dinilai"
                    :value="$tutorStats['submission_belum_dinilai']"
                    variant="warning"
                    icon="exclamation"
                />
                <x-stat-card
                    title="Kuis &amp; Ujian"
                    :value="$tutorStats['total_kuis']"
                    variant="primary"
                    icon="lightning-bolt"
                />
            </div>
        </div>

        <!-- SECTION 2: Ringkasan Rombel Binaan (Wali Kelas) -->
        <div>
            <div class="flex items-center justify-between mb-3">
                <h2 class="text-base font-bold text-navy uppercase tracking-wider flex items-center gap-2">
                    <x-icon name="layers" class="w-5 h-5 text-emerald-600" />
                    Tanggung Jawab Binaan (Wali Kelas)
                </h2>
                <span class="text-xs text-muted">Berdasarkan SK penetapan wali kelas</span>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <x-stat-card
                    title="Total Siswa Binaan"
                    :value="$waliStats['total_siswa_binaan']"
                    variant="primary"
                    icon="users"
                />
                <x-stat-card
                    title="Rombel Binaan"
                    :value="$waliStats['total_rombel_binaan']"
                    variant="primary"
                    icon="layers"
                />
                <x-stat-card
                    title="Kehadiran Rata-rata"
                    :value="$waliStats['kehadiran_rata'] . '%'"
                    variant="primary"
                    icon="calendar"
                />
            </div>
        </div>

        <!-- Detail Grid: Kelas Ajar, Rombel Binaan, dan Pengumuman -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Kolom 1: Mata Pelajaran & Kelas Ajar -->
            <div class="card p-6 space-y-4">
                <h3 class="text-base font-semibold text-navy flex items-center gap-2">
                    <x-icon name="book-open" class="w-5 h-5 text-primary" />
                    Kelas Mengajar Saya
                </h3>

                @if($myClasses->isNotEmpty())
                    <div class="space-y-3">
                        @foreach($myClasses as $class)
                        <div class="flex items-center justify-between p-3 rounded-xl border border-border">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-lg bg-primary-soft text-primary-dark flex items-center justify-center">
                                    <x-icon name="layers" class="w-4 h-4" />
                                </div>
                                <div>
                                    <div class="font-semibold text-navy text-sm">{{ $class->nama_rombel }}</div>
                                    <div class="text-xs text-muted">{{ $class->students_count }} Siswa</div>
                                </div>
                            </div>
                            <span class="text-[11px] font-semibold text-primary px-2 py-0.5 rounded bg-primary-soft">Diajar</span>
                        </div>
                        @endforeach
                    </div>
                @else
                    <x-empty-state
                        icon="layers"
                        title="Belum Ada Kelas Mengajar"
                        description="Anda belum memiliki penugasan mengajar aktif."
                    />
                @endif

                @if($mySubjects->isNotEmpty())
                    <div class="pt-3 border-t border-border">
                        <div class="text-xs font-semibold text-muted uppercase tracking-wider mb-2">Mata Pelajaran Diampu</div>
                        <div class="flex flex-wrap gap-1.5">
                            @foreach($mySubjects as $sub)
                                <span class="text-xs font-medium px-2.5 py-1 rounded-md bg-surface border border-border text-navy">
                                    {{ $sub->nama }}
                                </span>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>

            <!-- Kolom 2: Rombel Binaan Saya (Wali Kelas) -->
            <div class="card p-6 space-y-4">
                <h3 class="text-base font-semibold text-navy flex items-center gap-2">
                    <x-icon name="users" class="w-5 h-5 text-emerald-600" />
                    Rombel Binaan (Wali Kelas)
                </h3>

                @if($myClassrooms->isNotEmpty())
                    <div class="space-y-3">
                        @foreach($myClassrooms as $classroom)
                        <div class="flex items-center justify-between p-3 rounded-xl border border-border hover:border-emerald-300 transition-colors">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-lg bg-emerald-50 text-emerald-700 flex items-center justify-center">
                                    <x-icon name="users" class="w-4 h-4" />
                                </div>
                                <div>
                                    <div class="font-semibold text-navy text-sm">{{ $classroom->nama_rombel }}</div>
                                    <div class="text-xs text-muted">{{ $classroom->students_count }} Siswa Binaan</div>
                                </div>
                            </div>
                            <a href="{{ route('rekap.index') }}" class="text-xs text-emerald-700 hover:underline font-semibold">
                                Rekap &rarr;
                            </a>
                        </div>
                        @endforeach
                    </div>
                @else
                    <x-empty-state
                        icon="users"
                        title="Belum Ada Rombel Binaan"
                        description="Anda belum ditugaskan sebagai wali kelas pada rombel aktif."
                    />
                @endif
            </div>

            <!-- Kolom 3: Pengumuman Terkait -->
            <div class="card p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-base font-semibold text-navy flex items-center gap-2">
                        <x-icon name="megaphone" class="w-5 h-5 text-primary" />
                        Pengumuman
                    </h3>
                    <a href="{{ route('announcements.index') }}" class="text-xs text-primary hover:underline">Lihat Semua</a>
                </div>
                @if($recentAnnouncements->isNotEmpty())
                    <div class="space-y-3">
                        @foreach($recentAnnouncements as $announcement)
                        <a href="{{ route('announcements.show', $announcement) }}" class="block p-3 rounded-lg border border-border hover:bg-primary-soft transition-colors">
                            <div class="flex items-center justify-between gap-2">
                                <span class="font-medium text-navy text-sm truncate">{{ $announcement->judul }}</span>
                                <span class="text-[10px] px-1.5 py-0.5 bg-primary-soft text-primary-dark rounded shrink-0">{{ $announcement->kategori }}</span>
                            </div>
                            <div class="text-xs text-muted mt-1">{{ $announcement->published_at?->format('d M Y') }}</div>
                        </a>
                        @endforeach
                    </div>
                @else
                    <p class="text-muted text-center py-6 text-sm">Tidak ada pengumuman terbaru</p>
                @endif
            </div>
        </div>

        <!-- Quick Access Terpadu -->
        <div class="card p-6">
            <h3 class="text-base font-semibold text-navy mb-4 flex items-center gap-2">
                <x-icon name="lightning-bolt" class="w-5 h-5 text-primary" />
                Akses Cepat Layanan (Tutor &amp; Wali Kelas)
            </h3>
            <div class="grid grid-cols-2 md:grid-cols-6 gap-3">
                <a href="{{ route('materi.index') }}" class="flex flex-col items-center gap-2 p-3 rounded-xl border border-border hover:border-primary/50 hover:bg-primary-soft transition-colors text-center">
                    <div class="w-9 h-9 rounded-lg bg-primary-soft text-primary-dark flex items-center justify-center">
                        <x-icon name="book-open" class="w-5 h-5" />
                    </div>
                    <span class="text-xs font-semibold text-navy">Materi</span>
                </a>

                <a href="{{ route('tugas.index') }}" class="flex flex-col items-center gap-2 p-3 rounded-xl border border-border hover:border-primary/50 hover:bg-primary-soft transition-colors text-center">
                    <div class="w-9 h-9 rounded-lg bg-primary-soft text-primary-dark flex items-center justify-center">
                        <x-icon name="clipboard" class="w-5 h-5" />
                    </div>
                    <span class="text-xs font-semibold text-navy">Tugas</span>
                </a>

                <a href="{{ route('quiz.index') }}" class="flex flex-col items-center gap-2 p-3 rounded-xl border border-border hover:border-primary/50 hover:bg-primary-soft transition-colors text-center">
                    <div class="w-9 h-9 rounded-lg bg-primary-soft text-primary-dark flex items-center justify-center">
                        <x-icon name="lightning-bolt" class="w-5 h-5" />
                    </div>
                    <span class="text-xs font-semibold text-navy">Kuis &amp; Ujian</span>
                </a>

                <a href="{{ route('nilai.index') }}" class="flex flex-col items-center gap-2 p-3 rounded-xl border border-border hover:border-primary/50 hover:bg-primary-soft transition-colors text-center">
                    <div class="w-9 h-9 rounded-lg bg-primary-soft text-primary-dark flex items-center justify-center">
                        <x-icon name="chart-bar" class="w-5 h-5" />
                    </div>
                    <span class="text-xs font-semibold text-navy">Penilaian</span>
                </a>

                <a href="{{ route('absensi.index') }}" class="flex flex-col items-center gap-2 p-3 rounded-xl border border-border hover:border-primary/50 hover:bg-primary-soft transition-colors text-center">
                    <div class="w-9 h-9 rounded-lg bg-primary-soft text-primary-dark flex items-center justify-center">
                        <x-icon name="calendar" class="w-5 h-5" />
                    </div>
                    <span class="text-xs font-semibold text-navy">Absensi</span>
                </a>

                <a href="{{ route('rekap.index') }}" class="flex flex-col items-center gap-2 p-3 rounded-xl border border-border hover:border-emerald-400 hover:bg-emerald-50 transition-colors text-center">
                    <div class="w-9 h-9 rounded-lg bg-emerald-50 text-emerald-700 flex items-center justify-center">
                        <x-icon name="document-text" class="w-5 h-5" />
                    </div>
                    <span class="text-xs font-semibold text-navy">Rekap &amp; Rapor</span>
                </a>
            </div>
        </div>
    </div>
</x-app-layout>

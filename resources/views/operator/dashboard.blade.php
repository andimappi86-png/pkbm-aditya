<x-app-layout title="Dashboard Operator">
    <div class="space-y-6">
        <!-- Welcome Card -->
        <div class="rounded-2xl p-6 md:p-8 bg-white border border-[#D9E2E5] shadow-sm">
            <div class="flex flex-col md:flex-row items-start md:items-center gap-6">
                <div class="flex-shrink-0">
                    <div class="w-16 h-16 rounded-2xl bg-[#E8F5F4] flex items-center justify-center">
                        <x-icon name="home" class="w-8 h-8 text-[#167C80]" />
                    </div>
                </div>
                <div class="flex-1 min-w-0">
                    <h2 class="text-2xl md:text-3xl font-bold text-[#172B3A] mb-1 truncate">
                        Selamat datang, {{ auth()->user()->name }}
                    </h2>
                    <p class="text-[#64748B]">
                        Anda masuk sebagai <span class="font-semibold text-[#167C80]">Operator Sistem</span>
                    </p>
                </div>
                <div class="flex flex-col gap-2 w-full md:w-auto">
                    <a href="{{ route('users.create') }}" class="btn-primary justify-center text-sm">
                        <x-icon name="plus" class="w-4 h-4" />
                        Buat Akun Pengguna
                    </a>
                    <a href="{{ route('peserta-didik.link-accounts') }}" class="btn-secondary justify-center text-sm">
                        <x-icon name="link" class="w-4 h-4" />
                        Penautan Akun Siswa
                    </a>
                </div>
            </div>
        </div>

        <!-- KPI Stats Grid -->
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4">
            <x-stat-card title="Peserta Didik" :value="$stats['students']" variant="primary" icon="users" />
            <x-stat-card title="Tutor" :value="$stats['tutors']" variant="primary" icon="user-group" />
            <x-stat-card title="Rombel" :value="$stats['classrooms']" variant="primary" icon="layers" />
            <x-stat-card title="Mata Pelajaran" :value="$stats['subjects']" variant="primary" icon="book" />
            <x-stat-card title="Jadwal" :value="$stats['schedules']" variant="primary" icon="calendar" />

            <x-stat-card title="Absensi" :value="$stats['attendance']" variant="primary" icon="clipboard-check" />
            <x-stat-card title="Nilai" :value="$stats['grades']" variant="primary" icon="chart-bar" />
            <x-stat-card title="Tugas" :value="$stats['assignments']" variant="primary" icon="clipboard" />
            <x-stat-card title="Kuis" :value="$stats['quizzes']" variant="primary" icon="lightning-bolt" />
            <x-stat-card title="Materi" :value="$stats['materials']" variant="primary" icon="book-open" />

            <x-stat-card title="Dokumen" :value="$stats['documents']" variant="accent" icon="file-text" />
            <x-stat-card title="Surat" :value="$stats['letters']" variant="accent" icon="envelope" />
            <x-stat-card title="Pengumuman" :value="$stats['announcements']" variant="accent" icon="megaphone" />
        </div>

        <!-- Operational Indicators -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <a href="{{ route('users.index', ['status' => 'inactive']) }}" class="rounded-2xl p-4 bg-white border border-[#D9E2E5] shadow-sm hover:border-[#167C80]/40 transition-colors group">
                <div class="flex items-start justify-between gap-2 mb-2">
                    <span class="text-xs font-semibold uppercase tracking-wider text-muted">Akun Nonaktif</span>
                    <x-icon name="x-circle" class="w-4 h-4 text-muted group-hover:text-primary" />
                </div>
                <div class="text-2xl font-bold text-[#172B3A]">{{ $ops['inactive_accounts'] }}</div>
                <p class="text-xs text-muted mt-1">Perlu aktivasi ulang</p>
            </a>

            <a href="{{ route('peserta-didik.link-accounts') }}" class="rounded-2xl p-4 bg-white border border-[#D9E2E5] shadow-sm hover:border-[#167C80]/40 transition-colors group">
                <div class="flex items-start justify-between gap-2 mb-2">
                    <span class="text-xs font-semibold uppercase tracking-wider text-muted">Akun Siswa Belum Tertaut</span>
                    <x-icon name="link" class="w-4 h-4 text-muted group-hover:text-primary" />
                </div>
                <div class="text-2xl font-bold text-[#172B3A]">{{ $ops['unlinked_student_accounts'] }}</div>
                <p class="text-xs text-muted mt-1">Perlu penautan ke profil siswa</p>
            </a>

            <a href="{{ route('users.index') }}" class="rounded-2xl p-4 bg-white border border-[#D9E2E5] shadow-sm hover:border-[#167C80]/40 transition-colors group">
                <div class="flex items-start justify-between gap-2 mb-2">
                    <span class="text-xs font-semibold uppercase tracking-wider text-muted">Total Akun</span>
                    <x-icon name="users" class="w-4 h-4 text-muted group-hover:text-primary" />
                </div>
                <div class="text-2xl font-bold text-[#172B3A]">{{ $ops['total_users'] }}</div>
                <p class="text-xs text-muted mt-1">{{ $rolesCount }} role tersedia</p>
            </a>

            <a href="{{ route('roles.index') }}" class="rounded-2xl p-4 bg-white border border-[#D9E2E5] shadow-sm hover:border-[#167C80]/40 transition-colors group">
                <div class="flex items-start justify-between gap-2 mb-2">
                    <span class="text-xs font-semibold uppercase tracking-wider text-muted">Total Permission</span>
                    <x-icon name="key" class="w-4 h-4 text-muted group-hover:text-primary" />
                </div>
                <div class="text-2xl font-bold text-[#172B3A]">{{ $ops['total_permissions'] }}</div>
                <p class="text-xs text-muted mt-1">{{ $rolesCount }} role terdaftar</p>
            </a>
        </div>

        <!-- Quick Actions -->
        <div class="rounded-2xl p-6 bg-white border border-[#D9E2E5] shadow-sm">
            <h3 class="text-sm font-bold text-navy uppercase tracking-wider mb-4">Pintasan Kerja</h3>
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3">
                <a href="{{ route('peserta-didik.create') }}" class="flex items-center gap-3 p-3 rounded-xl border border-border hover:bg-primary-soft/30 transition-colors group">
                    <div class="w-9 h-9 rounded-lg bg-primary-soft text-primary-dark flex items-center justify-center shrink-0">
                        <x-icon name="users" class="w-4 h-4" />
                    </div>
                    <span class="text-sm font-medium text-navy group-hover:text-primary-dark">Tambah Peserta Didik</span>
                </a>
                <a href="{{ route('tutors.create') }}" class="flex items-center gap-3 p-3 rounded-xl border border-border hover:bg-primary-soft/30 transition-colors group">
                    <div class="w-9 h-9 rounded-lg bg-primary-soft text-primary-dark flex items-center justify-center shrink-0">
                        <x-icon name="user-group" class="w-4 h-4" />
                    </div>
                    <span class="font-medium text-sm text-navy group-hover:text-primary-dark transition-colors">Tambah Tutor</span>
                </a>
                <a href="{{ route('rombel.create') }}" class="flex items-center gap-3 p-3 rounded-xl border border-border hover:bg-primary-soft/30 transition-colors group">
                    <div class="w-9 h-9 rounded-lg bg-primary-soft text-primary-dark flex items-center justify-center shrink-0">
                        <x-icon name="layers" class="w-4 h-4" />
                    </div>
                    <span class="font-medium text-sm text-navy group-hover:text-primary-dark transition-colors">Tambah Rombel</span>
                </a>
                <a href="{{ route('announcements.create') }}" class="flex items-center gap-3 p-3 rounded-xl border border-border hover:bg-primary-soft/30 transition-colors group">
                    <div class="w-9 h-9 rounded-lg bg-primary-soft text-primary-dark flex items-center justify-center shrink-0">
                        <x-icon name="megaphone" class="w-4 h-4" />
                    </div>
                    <span class="font-medium text-sm text-navy group-hover:text-primary-dark transition-colors">Buat Pengumuman</span>
                </a>
                <a href="{{ route('users.create') }}" class="flex items-center gap-3 p-3 rounded-xl border border-border hover:bg-primary-soft/30 transition-colors group">
                    <div class="w-9 h-9 rounded-lg bg-primary-soft text-primary-dark flex items-center justify-center shrink-0">
                        <x-icon name="plus" class="w-4 h-4" />
                    </div>
                    <span class="font-medium text-sm text-navy group-hover:text-primary-dark transition-colors">Buat Akun Pengguna</span>
                </a>
                <a href="{{ route('roles.index') }}" class="flex items-center gap-3 p-3 rounded-xl border border-border hover:bg-primary-soft/30 transition-colors group">
                    <div class="w-9 h-9 rounded-lg bg-primary-soft text-primary-dark flex items-center justify-center shrink-0">
                        <x-icon name="user-check" class="w-4 h-4" />
                    </div>
                    <span class="font-medium text-sm text-navy group-hover:text-primary-dark transition-colors">Kelola Role & Permission</span>
                </a>
                <a href="{{ route('tugas.index') }}" class="flex items-center gap-3 p-3 rounded-xl border border-border hover:bg-primary-soft/30 transition-colors group">
                    <div class="w-9 h-9 rounded-lg bg-primary-soft text-primary-dark flex items-center justify-center shrink-0">
                        <x-icon name="clipboard" class="w-4 h-4" />
                    </div>
                    <span class="font-medium text-sm text-navy group-hover:text-primary-dark transition-colors">Modul Tugas</span>
                </a>
                <a href="{{ route('operator.import.index') }}" class="flex items-center gap-3 p-3 rounded-xl border border-border hover:bg-primary-soft/30 transition-colors group">
                    <div class="w-9 h-9 rounded-lg bg-primary-soft text-primary-dark flex items-center justify-center shrink-0">
                        <x-icon name="upload" class="w-4 h-4" />
                    </div>
                    <span class="font-medium text-sm text-navy group-hover:text-primary-dark transition-colors">Import Data</span>
                </a>
            </div>
        </div>

        <!-- Announcements -->
        <div class="grid grid-cols-1 gap-6">
            <!-- Announcements -->
            <div class="rounded-2xl p-6 bg-white border border-[#D9E2E5] shadow-sm">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-sm font-bold text-navy uppercase tracking-wider">Pengumuman Terbaru</h3>
                    <a href="{{ route('announcements.index') }}" class="text-xs text-primary-dark hover:underline">Lihat semua →</a>
                </div>
                @forelse($recentAnnouncements as $announcement)
                    <div class="flex items-start gap-3 py-2.5 border-b border-border last:border-b-0">
                        <div class="w-8 h-8 rounded-lg bg-primary-soft text-primary-dark flex items-center justify-center shrink-0">
                            <x-icon name="megaphone" class="w-4 h-4" />
                        </div>
                        <div class="flex-1 min-w-0">
                            <a href="{{ route('announcements.show', $announcement) }}" class="text-sm font-medium text-navy hover:text-primary-dark line-clamp-1">
                                {{ $announcement->judul ?? $announcement->title ?? 'Pengumuman' }}
                            </a>
                            <p class="text-xs text-muted">{{ $announcement->created_at->diffForHumans() }}</p>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-6 text-muted text-sm border-2 border-dashed border-border rounded-xl">
                        <p>Belum ada pengumuman terbaru.</p>
                        <a href="{{ route('announcements.create') }}" class="text-primary-dark hover:underline mt-1 inline-block">Buat pengumuman pertama →</a>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>
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
                    <h1 class="text-2xl lg:text-3xl font-bold text-text mb-2">
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
                    <h3 class="text-lg font-semibold text-text mb-2">Dashboard Pengendalian Operasional</h3>
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
                icon="users"
            />
            <x-stat-card 
                title="Tutor" 
                :value="$totalTutors" 
                variant="primary"
                icon="user-group" 
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

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Left Column -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Academic Overview -->
                <div class="card p-6">
                    <h3 class="text-lg font-semibold text-text mb-4">Ringkasan Operasional</h3>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <div class="p-4 rounded-xl border border-gray-200 bg-surface text-center">
                            <div class="text-2xl font-bold text-primary">{{ $attendanceRate }}%</div>
                            <div class="text-sm text-muted">Tingkat Kehadiran</div>
                        </div>
                        <div class="p-4 rounded-xl border border-gray-200 bg-surface text-center">
                            <div class="text-2xl font-bold text-primary">{{ $ppdbStats['total'] }}</div>
                            <div class="text-sm text-muted">Total PPDB</div>
                        </div>
                        <div class="p-4 rounded-xl border border-gray-200 bg-surface text-center">
                            <div class="text-2xl font-bold text-primary">{{ $documentsCount }}</div>
                            <div class="text-sm text-muted">Total Dokumen</div>
                        </div>
                        <div class="p-4 rounded-xl border border-gray-200 bg-surface text-center">
                            <div class="text-2xl font-bold text-primary">{{ $lettersCount }}</div>
                            <div class="text-sm text-muted">Total Surat</div>
                        </div>
                    </div>
                </div>

                <!-- Program Breakdown -->
                <div class="card p-6">
                    <h3 class="text-lg font-semibold text-text mb-4">Sebaran per Program</h3>
                    <div class="space-y-4">
                        @foreach($programStats as $program)
                        <div>
                            <div class="flex justify-between text-sm mb-1">
                                <span class="font-medium text-text">{{ $program->nama }}</span>
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

            <!-- Right Column -->
            <div class="space-y-6">
                <!-- Administrative Stats -->
                <div class="card p-6">
                    <h3 class="text-lg font-semibold text-text mb-4">Administrasi & PPDB</h3>
                    <div class="space-y-4">
                        <div class="flex justify-between items-center p-3 rounded-lg border border-gray-200">
                            <span class="text-sm font-medium text-text">PPDB Menunggu</span>
                            <span class="px-2 py-1 bg-yellow-100 text-yellow-800 rounded text-xs font-bold">{{ $ppdbStats['pending'] }}</span>
                        </div>
                        <div class="flex justify-between items-center p-3 rounded-lg border border-gray-200">
                            <span class="text-sm font-medium text-text">PPDB Diterima</span>
                            <span class="px-2 py-1 bg-green-100 text-green-800 rounded text-xs font-bold">{{ $ppdbStats['diterima'] }}</span>
                        </div>
                        <div class="flex justify-between items-center p-3 rounded-lg border border-gray-200">
                            <span class="text-sm font-medium text-text">Dokumen Menunggu Verifikasi</span>
                            <span class="px-2 py-1 bg-yellow-100 text-yellow-800 rounded text-xs font-bold">{{ $pendingDocuments }}</span>
                        </div>
                        <div class="flex justify-between items-center p-3 rounded-lg border border-gray-200">
                            <span class="text-sm font-medium text-text">Total Pengumuman Aktif</span>
                            <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded text-xs font-bold">{{ $activeAnnouncements }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="card p-6">
            <h3 class="text-lg font-semibold text-text mb-4 flex items-center gap-2">
                <x-icon name="layers" class="w-5 h-5 text-primary" />
                Aksi Cepat
            </h3>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                @can('students.manage')
                <a href="{{ route('peserta-didik.index') }}" class="flex flex-col items-center gap-2 p-4 rounded-xl border border-gray-200 hover:border-primary/50 hover:bg-primary-soft transition-colors cursor-pointer">
                    <div class="w-10 h-10 rounded-xl bg-primary-soft text-primary-dark flex items-center justify-center">
                        <x-icon name="users" class="w-5 h-5" />
                    </div>
                    <span class="text-sm font-medium text-text">Kelola Siswa</span>
                </a>
                @endcan

                @can('tutors.manage')
                <a href="{{ route('tutors.index') }}" class="flex flex-col items-center gap-2 p-4 rounded-xl border border-gray-200 hover:border-primary/50 hover:bg-primary-soft transition-colors cursor-pointer">
                    <div class="w-10 h-10 rounded-xl bg-primary-soft text-primary-dark flex items-center justify-center">
                        <x-icon name="user-group" class="w-5 h-5" />
                    </div>
                    <span class="text-sm font-medium text-text">Kelola Tutor</span>
                </a>
                @endcan

                @can('classrooms.manage')
                <a href="{{ route('rombel.index') }}" class="flex flex-col items-center gap-2 p-4 rounded-xl border border-gray-200 hover:border-primary/50 hover:bg-primary-soft transition-colors cursor-pointer">
                    <div class="w-10 h-10 rounded-xl bg-primary-soft text-primary-dark flex items-center justify-center">
                        <x-icon name="layers" class="w-5 h-5" />
                    </div>
                    <span class="text-sm font-medium text-text">Kelola Rombel</span>
                </a>
                @endcan

                @can('reports.view')
                <a href="{{ route('rekap.index') }}" class="flex flex-col items-center gap-2 p-4 rounded-xl border border-gray-200 hover:border-primary/50 hover:bg-primary-soft transition-colors cursor-pointer">
                    <div class="w-10 h-10 rounded-xl bg-primary-soft text-primary-dark flex items-center justify-center">
                        <x-icon name="chart-bar" class="w-5 h-5" />
                    </div>
                    <span class="text-sm font-medium text-text">Lihat Laporan</span>
                </a>
                @endcan
            </div>
        </div>
    </div>
</x-app-layout>
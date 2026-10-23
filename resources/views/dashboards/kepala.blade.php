<x-app-layout>
    <x-slot name="title">Dashboard Kepala PKBM</x-slot>

    <div class="max-w-7xl mx-auto space-y-6">
        <!-- Welcome Card -->
        <div class="card p-6 lg:p-8 bg-white border border-slate-200 rounded-xl shadow-sm">
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
                <div class="card p-6 bg-white border border-slate-200 rounded-xl shadow-sm">
                    <h3 class="text-lg font-semibold text-navy mb-4">Ringkasan Operasional</h3>
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                        <div class="p-4 rounded-xl border border-slate-100 bg-slate-50 text-center">
                            <div class="text-2xl font-bold text-primary-dark">{{ $attendanceRate }}%</div>
                            <div class="text-sm text-muted">Kehadiran</div>
                        </div>
                        <div class="p-4 rounded-xl border border-slate-100 bg-slate-50 text-center">
                            <div class="text-2xl font-bold text-primary-dark">{{ $documentsCount }}</div>
                            <div class="text-sm text-muted">Dokumen</div>
                        </div>
                        <div class="p-4 rounded-xl border border-slate-100 bg-slate-50 text-center">
                            <div class="text-2xl font-bold text-primary-dark">{{ $lettersCount }}</div>
                            <div class="text-sm text-muted">Surat</div>
                        </div>
                    </div>
                </div>

                <!-- Program Breakdown -->
                <div class="card p-6 bg-white border border-slate-200 rounded-xl shadow-sm">
                    <h3 class="text-lg font-semibold text-navy mb-4">Sebaran per Program</h3>
                    <div class="space-y-4">
                        @foreach($programStats as $program)
                        <div>
                            <div class="flex justify-between text-sm mb-1">
                                <span class="font-medium text-navy">{{ $program->nama }}</span>
                                <span class="text-muted">{{ $program->students_count }} Siswa</span>
                            </div>
                            <div class="w-full bg-slate-100 rounded-full h-2">
                                <div class="bg-primary rounded-full h-2" style="width: {{ $totalStudents > 0 ? ($program->students_count / $totalStudents) * 100 : 0 }}%"></div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Right Column -->
            <div class="space-y-6">
                <!-- Academic Context & Admin Stats -->
                <div class="card p-6 bg-white border border-slate-200 rounded-xl shadow-sm">
                    <h3 class="text-lg font-semibold text-navy mb-4">Tahun Ajaran & Semester</h3>
                    <div class="space-y-3">
                        @if($academicYear)
                        <div class="flex justify-between items-center p-3 rounded-lg bg-slate-50 border border-slate-100">
                            <span class="text-sm text-muted">Tahun Ajaran Aktif</span>
                            <span class="font-semibold text-navy text-sm">{{ $academicYear->nama }}</span>
                        </div>
                        @endif
                        @if($semester)
                        <div class="flex justify-between items-center p-3 rounded-lg bg-slate-50 border border-slate-100">
                            <span class="text-sm text-muted">Semester Aktif</span>
                            <span class="font-semibold text-navy text-sm">{{ $semester->nama_semester }}</span>
                        </div>
                        @endif
                        <div class="flex justify-between items-center p-3 rounded-lg bg-slate-50 border border-slate-100">
                            <span class="text-sm text-muted">Mata Pelajaran</span>
                            <span class="font-semibold text-navy text-sm">{{ $totalSubjects }}</span>
                        </div>
                    </div>
                </div>

                <div class="card p-6 bg-white border border-slate-200 rounded-xl shadow-sm">
                    <h3 class="text-lg font-semibold text-navy mb-4">Administrasi</h3>
                    <div class="space-y-3">
                        <div class="flex justify-between items-center p-3 rounded-lg border border-slate-100">
                            <span class="text-sm font-medium text-navy">Dokumen Menunggu Verifikasi</span>
                            <span class="px-2 py-1 bg-amber-50 text-amber-700 rounded text-xs font-bold">{{ $pendingDocuments }}</span>
                        </div>
                        <div class="flex justify-between items-center p-3 rounded-lg border border-slate-100">
                            <span class="text-sm font-medium text-navy">Total Pengumuman Aktif</span>
                            <span class="px-2 py-1 bg-sky-50 text-sky-700 rounded text-xs font-bold">{{ $activeAnnouncements }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="card p-6 bg-white border border-slate-200 rounded-xl shadow-sm">
            <h3 class="text-lg font-semibold text-navy mb-4 flex items-center gap-2">
                <x-icon name="layers" class="w-5 h-5 text-primary" />
                Aksi Cepat
            </h3>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                @can('students.manage')
                <a href="{{ route('peserta-didik.index') }}" class="flex flex-col items-center gap-2 p-4 rounded-xl border border-slate-200 hover:border-primary hover:bg-primary-soft/40 transition-colors cursor-pointer text-center">
                    <div class="w-10 h-10 rounded-xl bg-primary-soft text-primary-dark flex items-center justify-center">
                        <x-icon name="users" class="w-5 h-5" />
                    </div>
                    <span class="text-sm font-medium text-navy">Kelola Peserta Didik</span>
                </a>
                @endcan

                @can('tutors.manage')
                <a href="{{ route('tutors.index') }}" class="flex flex-col items-center gap-2 p-4 rounded-xl border border-slate-200 hover:border-primary hover:bg-primary-soft/40 transition-colors cursor-pointer text-center">
                    <div class="w-10 h-10 rounded-xl bg-primary-soft text-primary-dark flex items-center justify-center">
                        <x-icon name="user-group" class="w-5 h-5" />
                    </div>
                    <span class="text-sm font-medium text-navy">Kelola Tutor</span>
                </a>
                @endcan

                @can('classrooms.manage')
                <a href="{{ route('rombel.index') }}" class="flex flex-col items-center gap-2 p-4 rounded-xl border border-slate-200 hover:border-primary hover:bg-primary-soft/40 transition-colors cursor-pointer text-center">
                    <div class="w-10 h-10 rounded-xl bg-primary-soft text-primary-dark flex items-center justify-center">
                        <x-icon name="layers" class="w-5 h-5" />
                    </div>
                    <span class="text-sm font-medium text-navy">Kelola Rombel</span>
                </a>
                @endcan

                @can('reports.view')
                <a href="{{ route('rekap.index') }}" class="flex flex-col items-center gap-2 p-4 rounded-xl border border-slate-200 hover:border-primary hover:bg-primary-soft/40 transition-colors cursor-pointer text-center">
                    <div class="w-10 h-10 rounded-xl bg-primary-soft text-primary-dark flex items-center justify-center">
                        <x-icon name="chart-bar" class="w-5 h-5" />
                    </div>
                    <span class="text-sm font-medium text-navy">Lihat Laporan</span>
                </a>
                @endcan
            </div>
        </div>
    </div>
</x-app-layout>

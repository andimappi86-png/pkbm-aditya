<x-app-layout>
    <x-slot name="title">Ruang Belajar Saya</x-slot>

    <div class="max-w-7xl mx-auto space-y-8">
        
        <!-- 1. WELCOME SECTION -->
        <div class="bg-surface rounded-2xl border border-border p-6 lg:p-8 flex flex-col md:flex-row md:items-center justify-between gap-6 shadow-sm">
            <div class="flex items-start md:items-center gap-5">
                <div class="w-16 h-16 rounded-full bg-primary-soft text-primary flex items-center justify-center text-2xl font-bold border-2 border-white shadow-sm shrink-0">
                    {{ substr(auth()->user()->name, 0, 1) }}
                </div>
                <div>
                    <h1 class="text-2xl lg:text-3xl font-bold text-navy mb-1">Selamat datang kembali, {{ auth()->user()->name }}</h1>
                    <p class="text-muted text-sm md:text-base">
                        Lanjutkan pembelajaranmu hari ini. <span class="hidden md:inline">&bull; {{ now()->locale('id')->isoFormat('dddd, D MMMM Y') }}</span>
                    </p>
                    @if($myClassroom)
                        <div class="mt-3 flex flex-wrap items-center gap-2">
                            <span class="px-2.5 py-1 rounded bg-primary text-white text-[10px] font-bold uppercase tracking-wider">Peserta Didik</span>
                            <span class="px-2.5 py-1 rounded bg-gray-100 text-gray-700 border border-border text-[10px] font-bold uppercase tracking-wider">
                                {{ $myClassroom->program->nama ?? 'Program' }} &bull; {{ $myClassroom->nama_rombel }}
                            </span>
                            @if($myClassroom->academicYear)
                                <span class="px-2.5 py-1 rounded bg-gray-100 text-gray-700 border border-border text-[10px] font-bold uppercase tracking-wider">
                                    {{ $myClassroom->academicYear->nama_ajaran }}
                                </span>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
            <div class="shrink-0 w-full md:w-auto">
                <a href="{{ route('kelas-saya.index') }}" class="w-full inline-flex items-center justify-center px-6 py-3 bg-primary text-white text-sm font-semibold rounded-xl hover:bg-primary-dark transition-all shadow-sm">
                    Lihat Kelas Saya
                </a>
            </div>
        </div>

        <!-- 2. HARI INI & RINGKASAN PEMBELAJARAN -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Hari Ini -->
            <div>
                <h2 class="text-lg font-bold text-navy mb-4 flex items-center gap-2">
                    <i data-lucide="sun" class="w-5 h-5 text-amber-500"></i> Hari Ini
                </h2>
                <div class="bg-surface border border-border rounded-xl p-5 shadow-sm space-y-4">
                    <div class="flex items-center gap-4">
                        <div class="w-10 h-10 rounded-lg bg-primary-soft text-primary flex items-center justify-center shrink-0">
                            <i data-lucide="calendar" class="w-5 h-5"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-[10px] font-bold text-muted uppercase tracking-wider">Jadwal Berikutnya</p>
                            @if($upcomingSchedules->first())
                                <p class="text-sm font-bold text-navy truncate">{{ $upcomingSchedules->first()->jam_mulai ? $upcomingSchedules->first()->jam_mulai->format('H:i') : '--' }} - {{ $upcomingSchedules->first()->penugasanMengajar->subject->nama }}</p>
                                <p class="text-xs text-muted truncate">Tutor: {{ $upcomingSchedules->first()->penugasanMengajar->tutor->user->name ?? $upcomingSchedules->first()->penugasanMengajar->tutor->nama_lengkap }}</p>
                            @else
                                <p class="text-sm font-medium text-navy">Tidak ada kelas.</p>
                            @endif
                        </div>
                    </div>
                    <div class="flex items-center gap-4 border-t border-border pt-4">
                        <div class="w-10 h-10 rounded-lg bg-red-50 text-red-600 flex items-center justify-center shrink-0">
                            <i data-lucide="clipboard-list" class="w-5 h-5"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-[10px] font-bold text-muted uppercase tracking-wider">Tugas Terdekat</p>
                            @if($upcomingAssignments->first())
                                <p class="text-sm font-bold text-navy truncate"><a href="{{ route('tugas.show', $upcomingAssignments->first()) }}" class="hover:text-primary transition-colors">{{ $upcomingAssignments->first()->judul }}</a></p>
                                <p class="text-xs text-accent font-medium truncate">Deadline: {{ $upcomingAssignments->first()->deadline ? $upcomingAssignments->first()->deadline->diffForHumans() : 'Segera' }}</p>
                            @else
                                <p class="text-sm font-medium text-navy">Semua tugas selesai.</p>
                            @endif
                        </div>
                    </div>
                    <div class="flex items-center gap-4 border-t border-border pt-4">
                        <div class="w-10 h-10 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center shrink-0">
                            <i data-lucide="help-circle" class="w-5 h-5"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-[10px] font-bold text-muted uppercase tracking-wider">Quiz Tersedia</p>
                            @if($upcomingQuizzes->first())
                                <p class="text-sm font-bold text-navy truncate"><a href="{{ route('quiz.show', $upcomingQuizzes->first()) }}" class="hover:text-primary transition-colors">{{ $upcomingQuizzes->first()->judul }}</a></p>
                                <p class="text-xs text-muted truncate">{{ $upcomingQuizzes->first()->durasi_menit }} menit &bull; {{ $upcomingQuizzes->first()->penugasanMengajar->subject->nama }}</p>
                            @else
                                <p class="text-sm font-medium text-navy">Tidak ada kuis aktif.</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Ringkasan Pembelajaran -->
            <div>
                <h2 class="text-lg font-bold text-navy mb-4 flex items-center gap-2">
                    <i data-lucide="bar-chart-2" class="w-5 h-5 text-primary"></i> Ringkasan Pembelajaran
                </h2>
                <div class="grid grid-cols-2 gap-4">
                    <div class="bg-surface border border-border rounded-xl p-5 shadow-sm text-center">
                        <p class="text-[10px] font-bold text-muted uppercase tracking-wider mb-1">Materi Belajar</p>
                        <p class="text-3xl font-extrabold text-navy">{{ $stats['total_materi'] }}</p>
                        <p class="text-xs text-muted mt-1">tersedia</p>
                    </div>
                    <div class="bg-surface border border-border rounded-xl p-5 shadow-sm text-center">
                        <p class="text-[10px] font-bold text-muted uppercase tracking-wider mb-1">Tugas</p>
                        <p class="text-3xl font-extrabold text-navy">{{ $stats['total_tugas'] - $stats['tugas_selesai'] }}</p>
                        <p class="text-xs text-muted mt-1">belum selesai</p>
                    </div>
                    <div class="bg-surface border border-border rounded-xl p-5 shadow-sm text-center">
                        <p class="text-[10px] font-bold text-muted uppercase tracking-wider mb-1">Quiz Tersedia</p>
                        <p class="text-3xl font-extrabold text-navy">{{ $stats['total_kuis'] - $stats['kuis_diikuti'] }}</p>
                        <p class="text-xs text-muted mt-1">belum dikerjakan</p>
                    </div>
                    <div class="bg-surface border border-border rounded-xl p-5 shadow-sm text-center">
                        <p class="text-[10px] font-bold text-muted uppercase tracking-wider mb-1">Kehadiran</p>
                        <p class="text-3xl font-extrabold text-primary">{{ $stats['kehadiran'] }}%</p>
                        <p class="text-xs text-muted mt-1">persentase</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- 3. LANJUTKAN BELAJAR -->
        <div>
            <h2 class="text-lg font-bold text-navy mb-4 flex items-center gap-2">
                <i data-lucide="play-circle" class="w-5 h-5 text-primary"></i> Lanjutkan Belajar
            </h2>
            @if($penugasans->count() > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($penugasans->take(3) as $p)
                        @php
                            $pTasks = $p->assignments->count();
                            $pDone = \App\Models\Submission::where('student_id', auth()->user()->student->id ?? 0)
                                    ->whereIn('assignment_id', $p->assignments->pluck('id'))->count();
                            $pct = $pTasks > 0 ? round(($pDone / $pTasks) * 100) : 0;
                            $latestMaterial = $p->materials->first();
                            $nextTask = $p->assignments->first();
                        @endphp
                        <div class="bg-surface border border-border rounded-xl p-5 hover:shadow-soft transition-all flex flex-col">
                            <div class="mb-4">
                                <h3 class="font-bold text-navy line-clamp-1">{{ $p->subject->nama }}</h3>
                                <p class="text-xs text-muted mt-1">Tutor: {{ $p->tutor->user->name ?? $p->tutor->nama_lengkap }} &bull; {{ $p->classroom->nama_rombel }}</p>
                            </div>
                            
                            <div class="mb-4">
                                <div class="flex justify-between text-xs font-bold mb-1.5">
                                    <span class="text-muted">Progress</span>
                                    <span class="text-primary">{{ $pct }}%</span>
                                </div>
                                <div class="h-1.5 w-full bg-border rounded-full overflow-hidden">
                                    <div class="h-full bg-primary rounded-full transition-all" style="width: {{ $pct }}%"></div>
                                </div>
                            </div>

                            <div class="space-y-3 mb-5 flex-1">
                                @if($latestMaterial)
                                    <div class="text-xs">
                                        <span class="text-muted block mb-0.5">Materi terakhir:</span>
                                        <span class="font-medium text-navy line-clamp-1">{{ $latestMaterial->judul }}</span>
                                    </div>
                                @endif
                                @if($nextTask)
                                    <div class="text-xs">
                                        <span class="text-muted block mb-0.5">Aktivitas berikutnya:</span>
                                        <span class="font-medium text-accent line-clamp-1">Tugas: {{ $nextTask->judul }}</span>
                                    </div>
                                @endif
                            </div>

                            <a href="{{ route('kelas-saya.show', $p) }}" class="w-full inline-flex items-center justify-center px-4 py-2.5 bg-primary-soft text-primary-dark hover:bg-primary hover:text-white font-bold text-xs rounded-lg transition-colors">
                                Lanjutkan Belajar
                            </a>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="bg-surface border border-border rounded-xl p-8 text-center text-muted">
                    <p class="text-sm font-medium">Anda belum ditempatkan pada rombel atau kelas aktif.</p>
                </div>
            @endif
        </div>

        <!-- TWO COLUMN LAYOUT: MAIN & SIDEBAR -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            <!-- LEFT CONTENT (Col 2) -->
            <div class="lg:col-span-2 space-y-8">
                
                <!-- TUGAS YANG PERLU DISELESAIKAN -->
                <div>
                    <h2 class="text-lg font-bold text-navy mb-4 flex items-center gap-2">
                        <i data-lucide="clipboard-list" class="w-5 h-5 text-accent"></i> Tugas yang Perlu Diselesaikan
                    </h2>
                    @if($upcomingAssignments->count() > 0)
                        <div class="bg-surface border border-border rounded-xl overflow-hidden shadow-sm">
                            <div class="divide-y divide-border">
                                @foreach($upcomingAssignments->take(4) as $assignment)
                                    <div class="p-4 sm:p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4 hover:bg-background/50 transition-colors">
                                        <div class="flex-1 min-w-0">
                                            <h4 class="font-bold text-navy text-sm mb-1"><a href="{{ route('tugas.show', $assignment) }}" class="hover:text-primary transition-colors">{{ $assignment->judul }}</a></h4>
                                            <div class="flex flex-wrap items-center gap-2 text-xs text-muted">
                                                <span>{{ $assignment->penugasanMengajar->subject->nama }}</span>
                                                <span>&bull;</span>
                                                <span class="flex items-center gap-1 text-accent font-medium">
                                                    <i data-lucide="clock" class="w-3 h-3"></i> 
                                                    {{ $assignment->deadline ? $assignment->deadline->format('d M Y, H:i') : 'Tanpa batas' }}
                                                </span>
                                            </div>
                                        </div>
                                        <div class="shrink-0 flex items-center gap-3">
                                            <span class="px-2.5 py-1 rounded bg-red-50 border border-red-100 text-red-600 text-[10px] font-bold uppercase tracking-wider">
                                                Belum Dikerjakan
                                            </span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @else
                        <div class="bg-surface border border-border rounded-xl p-8 text-center text-muted">
                            <p class="text-sm font-medium">Semua tugas sudah selesai. Pertahankan kerjamu!</p>
                        </div>
                    @endif
                </div>

                <!-- QUIZ & UJIAN -->
                <div>
                    <h2 class="text-lg font-bold text-navy mb-4 flex items-center gap-2">
                        <i data-lucide="help-circle" class="w-5 h-5 text-primary"></i> Quiz & Ujian
                    </h2>
                    @if($upcomingQuizzes->count() > 0)
                        <div class="grid sm:grid-cols-2 gap-4">
                            @foreach($upcomingQuizzes as $quiz)
                                <div class="bg-surface border border-border rounded-xl p-5 hover:shadow-soft transition-shadow flex flex-col">
                                    <div class="flex items-center gap-2 mb-2">
                                        <span class="px-2 py-0.5 rounded bg-amber-100 text-amber-800 text-[10px] font-bold uppercase tracking-wider">Tersedia</span>
                                    </div>
                                    <h4 class="font-bold text-navy text-sm mb-1"><a href="{{ route('quiz.show', $quiz) }}" class="hover:text-primary transition-colors">{{ $quiz->judul }}</a></h4>
                                    <p class="text-xs text-muted mb-4">{{ $quiz->penugasanMengajar->subject->nama }}</p>
                                    
                                    <div class="flex items-center gap-4 text-[11px] font-medium text-muted mb-5">
                                        <span class="flex items-center gap-1"><i data-lucide="file-question" class="w-3.5 h-3.5"></i> {{ $quiz->questions_count ?? 10 }} soal</span>
                                        <span class="flex items-center gap-1"><i data-lucide="clock" class="w-3.5 h-3.5"></i> {{ $quiz->durasi_menit }} mnt</span>
                                    </div>
                                    
                                    <a href="{{ route('quiz.show', $quiz) }}" class="mt-auto block text-center w-full px-4 py-2 bg-surface border border-border text-navy text-xs font-bold rounded-lg hover:bg-primary-soft hover:text-primary-dark transition-colors">
                                        Mulai Quiz
                                    </a>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="bg-surface border border-border rounded-xl p-8 text-center text-muted">
                            <p class="text-sm font-medium">Belum ada quiz yang tersedia.</p>
                        </div>
                    @endif
                </div>

                <!-- JADWAL HARI INI -->
                <div>
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-lg font-bold text-navy flex items-center gap-2">
                            <i data-lucide="calendar" class="w-5 h-5 text-primary"></i> Jadwal Berikutnya
                        </h2>
                        <a href="{{ route('jadwal.index') }}" class="text-xs font-bold text-primary hover:underline">Lihat Semua Jadwal</a>
                    </div>
                    @if($upcomingSchedules->count() > 0)
                        <div class="bg-surface border border-border rounded-xl p-6 relative shadow-sm">
                            <!-- Vertical Line -->
                            <div class="absolute left-[39px] top-8 bottom-8 w-px bg-border"></div>
                            
                            <div class="space-y-6">
                                @foreach($upcomingSchedules as $schedule)
                                    <div class="flex gap-4 relative z-10">
                                        <div class="w-14 text-right pt-0.5 shrink-0">
                                            <span class="text-[10px] font-bold text-muted uppercase block">{{ substr($schedule->hari, 0, 3) }}</span>
                                            <span class="text-sm font-bold text-navy block">{{ $schedule->jam_mulai ? $schedule->jam_mulai->format('H:i') : '--' }}</span>
                                        </div>
                                        <div class="w-3 h-3 rounded-full bg-primary ring-4 ring-surface mt-2 shrink-0"></div>
                                        <div class="flex-1 pb-2">
                                            <h4 class="font-bold text-navy text-sm">{{ $schedule->penugasanMengajar->subject->nama }}</h4>
                                            <p class="text-xs text-muted mt-0.5">Tutor: {{ $schedule->penugasanMengajar->tutor->user->name ?? $schedule->penugasanMengajar->tutor->nama_lengkap }}</p>
                                            <p class="text-[11px] text-muted flex items-center gap-1 mt-1.5"><i data-lucide="map-pin" class="w-3 h-3"></i> {{ $schedule->ruangan ?? 'Daring' }}</p>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @else
                        <div class="bg-surface border border-border rounded-xl p-8 text-center text-muted">
                            <p class="text-sm font-medium">Belum ada jadwal pembelajaran.</p>
                        </div>
                    @endif
                </div>

                <!-- MATERI TERBARU -->
                <div>
                    <h2 class="text-lg font-bold text-navy mb-4 flex items-center gap-2">
                        <i data-lucide="folder-open" class="w-5 h-5 text-primary"></i> Materi Terbaru
                    </h2>
                    @if($recentMaterials->count() > 0)
                        <div class="grid sm:grid-cols-2 gap-4">
                            @foreach($recentMaterials as $material)
                                <div class="bg-surface border border-border rounded-xl p-4 flex items-start gap-4 hover:shadow-soft transition-shadow">
                                    <div class="w-10 h-10 rounded-lg {{ $material->tipe === 'pdf' ? 'bg-red-50 text-red-600' : 'bg-primary-soft text-primary' }} flex items-center justify-center shrink-0">
                                        <i data-lucide="{{ $material->tipe === 'video' ? 'youtube' : 'file-text' }}" class="w-5 h-5"></i>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <h4 class="font-bold text-navy text-sm line-clamp-1 mb-1"><a href="{{ route('materi.show', $material) }}" class="hover:text-primary transition-colors">{{ $material->judul }}</a></h4>
                                        <p class="text-[11px] text-muted truncate mb-1">{{ $material->penugasanMengajar->subject->nama }}</p>
                                        <p class="text-[10px] text-muted">{{ $material->created_at->diffForHumans() }}</p>
                                    </div>
                                    <div class="shrink-0">
                                        <a href="{{ route('materi.show', $material) }}" class="p-2 text-muted hover:text-primary transition-colors inline-block" title="Buka Materi">
                                            <i data-lucide="external-link" class="w-4 h-4"></i>
                                        </a>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="bg-surface border border-border rounded-xl p-8 text-center text-muted">
                            <p class="text-sm font-medium">Belum ada materi baru untuk kelasmu.</p>
                        </div>
                    @endif
                </div>

            </div>

            <!-- RIGHT SIDEBAR (Col 1) -->
            <div class="space-y-8">
                
                <!-- PROGRESS PEMBELAJARAN -->
                <div class="bg-surface border border-border rounded-xl p-5 shadow-sm">
                    <h3 class="text-sm font-bold text-navy mb-4">Progress Pembelajaran</h3>
                    @if($penugasans->count() > 0)
                        <div class="space-y-5">
                            @foreach($penugasans->take(4) as $p)
                                @php
                                    $pTasks = $p->assignments->count();
                                    $pDone = \App\Models\Submission::where('student_id', auth()->user()->student->id ?? 0)
                                            ->whereIn('assignment_id', $p->assignments->pluck('id'))->count();
                                    $pct = $pTasks > 0 ? round(($pDone / $pTasks) * 100) : 0;
                                @endphp
                                <div>
                                    <div class="flex justify-between items-end mb-2">
                                        <span class="font-bold text-navy text-xs truncate pr-2">{{ $p->subject->nama }}</span>
                                        <span class="font-bold text-primary text-[10px] shrink-0">{{ $pct }}%</span>
                                    </div>
                                    <div class="h-1.5 w-full bg-border rounded-full overflow-hidden">
                                        <div class="h-full bg-primary rounded-full transition-all" style="width: {{ $pct }}%"></div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-xs text-muted text-center py-2">Tidak ada data progress.</p>
                    @endif
                </div>

                <!-- KEHADIRAN SAYA -->
                <div class="bg-surface border border-border rounded-xl p-5 shadow-sm">
                    <h3 class="text-sm font-bold text-navy mb-4">Kehadiran Saya</h3>
                    <div class="flex items-center gap-4 mb-5">
                        <!-- Simple Ring/Donut visualization via CSS -->
                        <div class="relative w-16 h-16 shrink-0 flex items-center justify-center rounded-full bg-border" style="background: conic-gradient(#167C80 {{ $stats['kehadiran'] }}%, #D9E2E5 0);">
                            <div class="absolute inset-1 bg-surface rounded-full flex items-center justify-center">
                                <span class="text-sm font-extrabold text-navy">{{ $stats['kehadiran'] }}%</span>
                            </div>
                        </div>
                        <div class="flex-1 text-xs text-muted">
                            <p>Persentase kehadiran pada seluruh sesi tatap muka & daring.</p>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-3 text-center">
                        <div class="bg-background border border-border rounded-lg py-2 px-1">
                            <div class="text-base font-bold text-primary mb-0.5">{{ $hadirCount ?? 0 }}</div>
                            <div class="text-[10px] font-bold text-muted uppercase tracking-wider">Hadir</div>
                        </div>
                        <div class="bg-background border border-border rounded-lg py-2 px-1">
                            <div class="text-base font-bold text-amber-500 mb-0.5">{{ $izinCount ?? 0 }}</div>
                            <div class="text-[10px] font-bold text-muted uppercase tracking-wider">Izin</div>
                        </div>
                        <div class="bg-background border border-border rounded-lg py-2 px-1">
                            <div class="text-base font-bold text-blue-500 mb-0.5">{{ $sakitCount ?? 0 }}</div>
                            <div class="text-[10px] font-bold text-muted uppercase tracking-wider">Sakit</div>
                        </div>
                        <div class="bg-background border border-border rounded-lg py-2 px-1">
                            <div class="text-base font-bold text-red-500 mb-0.5">{{ $alpaCount ?? 0 }}</div>
                            <div class="text-[10px] font-bold text-muted uppercase tracking-wider">Alpa</div>
                        </div>
                    </div>
                </div>

                <!-- NILAI TERBARU -->
                <div class="bg-surface border border-border rounded-xl p-5 shadow-sm">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-sm font-bold text-navy">Nilai Terbaru</h3>
                        <a href="{{ route('nilai.index') }}" class="text-[10px] font-bold text-primary hover:underline uppercase">Lihat Semua</a>
                    </div>
                    @if($recentGrades->count() > 0)
                        <div class="space-y-3">
                            @foreach($recentGrades as $grade)
                                <div class="flex items-center justify-between gap-3 p-3 bg-background border border-border rounded-lg hover:border-primary/30 transition-colors">
                                    <div class="min-w-0 flex-1">
                                        <h4 class="font-bold text-navy text-xs line-clamp-1 mb-0.5">{{ $grade->penugasanMengajar->subject->nama }}</h4>
                                        <p class="text-[10px] text-muted truncate">{{ $grade->komponen ?? 'Tugas / Evaluasi' }}</p>
                                    </div>
                                    <div class="text-right shrink-0">
                                        <div class="font-extrabold text-navy">{{ rtrim(rtrim(number_format($grade->nilai_akhir, 2, '.', ''), '0'), '.') }}</div>
                                        <div class="text-[9px] font-bold uppercase tracking-wider {{ $grade->nilai_akhir >= 70 ? 'text-primary' : 'text-accent' }} mt-0.5">
                                            {{ $grade->nilai_akhir >= 70 ? 'Baik' : 'Remedial' }}
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-4 text-muted">
                            <p class="text-xs font-medium">Belum ada nilai yang dipublikasikan.</p>
                        </div>
                    @endif
                </div>

                <!-- PENGUMUMAN -->
                <div class="bg-surface border border-border rounded-xl p-5 shadow-sm">
                    <h3 class="text-sm font-bold text-navy mb-4">Pengumuman</h3>
                    @if($recentAnnouncements->count() > 0)
                        <div class="space-y-4">
                            @foreach($recentAnnouncements as $announcement)
                                <div>
                                    <div class="flex items-center justify-between mb-1">
                                        <h4 class="font-bold text-navy text-sm line-clamp-1 flex-1"><a href="{{ route('announcements.show', $announcement) }}" class="hover:text-primary transition-colors">{{ $announcement->judul }}</a></h4>
                                    </div>
                                    <p class="text-[11px] text-muted line-clamp-2 mb-1">{{ strip_tags($announcement->konten) }}</p>
                                    <p class="text-[10px] text-primary font-medium">{{ $announcement->published_at->format('d M Y') }}</p>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-4 text-muted">
                            <p class="text-xs font-medium">Belum ada pengumuman untukmu.</p>
                        </div>
                    @endif
                </div>

                <!-- FORUM DISKUSI TERBARU -->
                <div class="bg-surface border border-border rounded-xl p-5 shadow-sm">
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="text-sm font-bold text-navy">Diskusi Terbaru</h3>
                        <a href="{{ route('forum.index') }}" class="text-[10px] font-bold text-primary hover:underline uppercase">Buka Forum</a>
                    </div>
                    @if($recentForums->count() > 0)
                        <div class="space-y-3">
                            @foreach($recentForums as $forum)
                                <div class="bg-background border border-border rounded-lg p-3 hover:border-primary/30 transition-colors">
                                    <h4 class="font-bold text-navy text-xs line-clamp-1 mb-1"><a href="{{ route('forum.show', $forum) }}" class="hover:text-primary transition-colors">{{ $forum->judul }}</a></h4>
                                    <div class="flex items-center justify-between text-[10px] text-muted">
                                        <span class="truncate pr-2">{{ $forum->penugasanMengajar->subject->nama }}</span>
                                        <span class="shrink-0 flex items-center gap-1 font-medium"><i data-lucide="message-square" class="w-3 h-3"></i> {{ $forum->comments_count }} balasan</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-4 text-muted">
                            <p class="text-xs font-medium">Belum ada diskusi kelas.</p>
                        </div>
                    @endif
                </div>

                <!-- PERLU BANTUAN -->
                <div class="bg-primary-soft border border-primary/10 rounded-xl p-5 text-center">
                    <h3 class="text-sm font-bold text-primary-dark mb-2">Perlu Bantuan?</h3>
                    <p class="text-xs text-primary-dark/80 leading-relaxed mb-4">Hubungi tutor atau wali kelas melalui ruang diskusi pembelajaran.</p>
                    <a href="{{ route('forum.index') }}" class="inline-block px-4 py-2 bg-primary text-white text-xs font-bold rounded-lg hover:bg-primary-dark transition-colors shadow-sm">
                        Lihat Forum Diskusi
                    </a>
                </div>

            </div>
        </div>
    </div>
</x-app-layout>
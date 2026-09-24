<x-app-layout>
    <x-slot name="title">{{ $penugasanMengajar->subject->nama }} - Detail Kelas</x-slot>

    <div class="max-w-7xl mx-auto space-y-6">
        <x-detail-kelas-header :penugasan="$penugasanMengajar" :activeTab="'overview'" />

        <!-- 2-Column Content Layout -->
        <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
            
            <!-- Kiri: Main Content -->
            <div class="xl:col-span-2 space-y-6">
                <!-- Deskripsi Pembelajaran -->
                <div class="card p-6 border border-border">
                    <h3 class="text-lg font-bold text-navy mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        Deskripsi & Orientasi Pembelajaran
                    </h3>
                    <p class="text-muted text-sm leading-relaxed mb-6">
                        Mata pelajaran ini dirancang untuk memberikan pemahaman aplikatif mengenai materi sesuai standar kurikulum paket pendidikan kesetaraan PKBM Aditya. Modul menekankan pada daya nalar kompetensi praktis dan kemandirian belajar non-formal.
                    </p>
                    <div class="grid sm:grid-cols-3 gap-4">
                        <div class="bg-background rounded-xl p-4 border border-border">
                            <span class="block text-xs font-bold text-muted uppercase tracking-wider mb-1">Capaian Unit</span>
                            <span class="text-sm font-medium text-navy">Penguasaan Kompetensi Dasar Akademik</span>
                        </div>
                        <div class="bg-background rounded-xl p-4 border border-border">
                            <span class="block text-xs font-bold text-muted uppercase tracking-wider mb-1">Model Evaluasi</span>
                            <span class="text-sm font-medium text-navy">Portofolio, Penugasan & Ujian Modul</span>
                        </div>
                        <div class="bg-background rounded-xl p-4 border border-border">
                            <span class="block text-xs font-bold text-muted uppercase tracking-wider mb-1">Standar Modul</span>
                            <span class="text-sm font-medium text-navy">KTSP Kesetaraan PKBM</span>
                        </div>
                    </div>
                </div>

                <!-- Bahan Ajar Terbaru -->
                <div class="card p-6 border border-border">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-bold text-navy flex items-center gap-2">
                            <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" /></svg>
                            Bahan Ajar & Modul Terbaru
                        </h3>
                        <a href="{{ route('materi.index', ['penugasan_mengajar_id' => $penugasanMengajar->id]) }}" class="text-sm font-semibold text-primary hover:text-primary-dark transition-colors">Lihat Semua</a>
                    </div>
                    
                    @if($penugasanMengajar->materials->count() > 0)
                        <div class="space-y-4">
                            @foreach($penugasanMengajar->materials as $materi)
                                <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 p-4 rounded-xl border border-border bg-background/30 hover:bg-surface hover:shadow-soft transition-all">
                                    <div class="flex items-start gap-4">
                                        <div class="w-10 h-10 rounded-lg {{ $materi->tipe === 'pdf' ? 'bg-red-50 text-red-600' : 'bg-primary-soft text-primary' }} flex items-center justify-center shrink-0">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" /></svg>
                                        </div>
                                        <div>
                                            <h4 class="text-sm font-bold text-navy line-clamp-1">{{ $materi->judul }}</h4>
                                            <p class="text-xs text-muted mt-1">{{ $materi->deskripsi ?? 'Modul pembelajaran' }}</p>
                                        </div>
                                    </div>
                                    <a href="{{ route('materi.show', $materi) }}" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-4 py-2 bg-surface border border-border rounded-lg text-sm font-medium text-navy hover:bg-background hover:text-primary transition-colors shrink-0">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" /></svg>
                                        Buka
                                    </a>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <x-empty-state title="Belum ada bahan ajar" description="Tutor belum mengunggah materi untuk kelas ini." icon="document" />
                    @endif
                </div>

                <!-- Diskusi Terbaru -->
                <div class="card p-6 border border-border">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-bold text-navy flex items-center gap-2">
                            <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a1.994 1.994 0 01-1.414-.586m0 0L11 14h4a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2v4l.586-.586z" /></svg>
                            Diskusi Kelas Terkini
                        </h3>
                        <a href="{{ route('forum.index', ['penugasan_mengajar_id' => $penugasanMengajar->id]) }}" class="text-sm font-semibold text-primary hover:text-primary-dark transition-colors">Buka Ruang Diskusi</a>
                    </div>

                    @if($penugasanMengajar->forums->count() > 0)
                        <div class="space-y-4">
                            @foreach($penugasanMengajar->forums as $forum)
                                <div class="bg-background rounded-xl p-4 border border-border hover:border-primary/50 transition-colors">
                                    <div class="flex items-start justify-between mb-2">
                                        <div class="flex items-center gap-2">
                                            <div class="w-6 h-6 rounded-full bg-primary text-white flex items-center justify-center text-xs font-bold">
                                                {{ substr($forum->user->name ?? 'A', 0, 1) }}
                                            </div>
                                            <span class="text-sm font-medium text-navy">{{ $forum->user->name ?? 'Pengguna' }}</span>
                                            <span class="text-xs text-muted">&bull; {{ $forum->created_at->diffForHumans() }}</span>
                                        </div>
                                    </div>
                                    <h4 class="font-bold text-navy text-sm mb-1">{{ $forum->judul }}</h4>
                                    <p class="text-sm text-muted line-clamp-2 mb-3">{{ $forum->isi }}</p>
                                    <div class="flex items-center justify-between">
                                        <span class="text-xs font-medium text-primary bg-primary-soft px-2 py-1 rounded-md">{{ $forum->comments_count ?? 0 }} Balasan</span>
                                        <a href="{{ route('forum.show', $forum) }}" class="text-xs font-semibold text-navy hover:text-primary transition-colors">Ikut Diskusi &rarr;</a>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <x-empty-state title="Belum ada diskusi" description="Mulai percakapan pertama Anda di kelas ini." icon="chat" />
                    @endif
                </div>
            </div>

            <!-- Kanan: Sidebar & Agenda -->
            <div class="space-y-6">
                
                <!-- Tugas Pending -->
                <div class="card p-0 border border-border overflow-hidden">
                    <div class="bg-red-50 p-4 border-b border-red-100 flex items-center justify-between">
                        <h3 class="text-sm font-bold text-red-700 flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            TUGAS & EVALUASI
                        </h3>
                        <span class="px-2 py-1 bg-red-100 text-red-700 rounded-md text-xs font-bold">{{ $penugasanMengajar->assignments->count() }} PENDING</span>
                    </div>
                    
                    @if($penugasanMengajar->assignments->count() > 0)
                        <div class="divide-y divide-border">
                            @foreach($penugasanMengajar->assignments as $assignment)
                                <div class="p-4 hover:bg-background/50 transition-colors">
                                    <div class="flex items-center gap-2 mb-2">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider {{ $assignment->type === 'ujian' ? 'bg-red-100 text-red-700' : 'bg-primary-soft text-primary-dark' }}">
                                            Batas: {{ $assignment->due_date ? $assignment->due_date->format('d M, H:i') : 'Tidak ada' }}
                                        </span>
                                    </div>
                                    <h4 class="text-sm font-bold text-navy line-clamp-2 mb-1">{{ $assignment->judul }}</h4>
                                    <p class="text-xs text-muted mb-3">{{ $assignment->type === 'pilihan_ganda' ? 'Pilihan Ganda' : 'Esai/Unggah Berkas' }}</p>
                                    
                                    <div class="flex items-center justify-between mt-3">
                                        <span class="text-xs font-medium text-red-600">Belum Dikerjakan</span>
                                        <a href="{{ route('tugas.show', $assignment) }}" class="px-3 py-1.5 bg-primary hover:bg-primary-dark text-white rounded-md text-xs font-bold transition-colors">Mulai &rarr;</a>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="p-6 text-center">
                            <p class="text-sm text-muted">Tidak ada tugas mendesak.</p>
                        </div>
                    @endif
                </div>

                <!-- Konsultasi Tutor -->
                <div class="card p-6 border border-border">
                    <h3 class="text-sm font-bold text-navy mb-3 flex items-center gap-2">
                        <svg class="w-4 h-4 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z" /></svg>
                        Pusat Konsultasi Tutor
                    </h3>
                    <p class="text-xs text-muted leading-relaxed mb-4">
                        Peserta didik berhak memperoleh sesi bimbingan tambahan secara mandiri. Silakan jadwalkan konsultasi dengan {{ $penugasanMengajar->tutor->nama_lengkap }}.
                    </p>
                    <a href="mailto:{{ $penugasanMengajar->tutor->user->email ?? '#' }}" class="w-full inline-flex items-center justify-center gap-2 px-4 py-2 bg-primary-soft text-primary-dark rounded-lg text-xs font-bold hover:bg-primary/20 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
                        Kirim Pesan ke Tutor
                    </a>
                </div>

            </div>
        </div>
    </div>
</x-app-layout>
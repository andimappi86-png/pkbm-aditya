<x-app-layout>
    <x-slot name="title">Detail Quiz: {{ $quiz->judul }}</x-slot>

    <div class="max-w-4xl mx-auto space-y-6">
        <x-breadcrumb :items="[['label' => 'Pembelajaran'], ['label' => 'Kuis & Ujian', 'href' => route('quiz.index')], ['label' => $quiz->judul]]" />

        <div class="card p-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
                <div>
                    <h1 class="text-2xl font-bold text-text">{{ $quiz->judul }}</h1>
                    <p class="text-muted mt-1">{{ $quiz->deskripsi }}</p>
                </div>
                <div class="flex flex-wrap gap-3">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded text-xs font-medium {{ $quiz->getStatusBadgeClass() }}">
                        {{ $quiz->getStatusLabel() }}
                    </span>
                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        {{ $quiz->durasi_menit }} Menit
                    </span>
                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded text-xs font-medium bg-primary-soft text-primary">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg>
                        {{ $quiz->penugasanMengajar->subject->nama }}
                    </span>
                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                        Kelas {{ $quiz->penugasanMengajar->classroom->nama_rombel }}
                    </span>
                </div>
            </div>

            {{-- Schedule Info --}}
            @if($quiz->buka_waktu || $quiz->tutup_waktu)
            <div class="mb-6 p-4 bg-gray-50 rounded-xl border border-border">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @if($quiz->buka_waktu)
                    <div>
                        <p class="text-xs text-muted uppercase tracking-wider">Waktu Mulai</p>
                        <p class="font-medium text-text">{{ $quiz->buka_waktu->format('d M Y, H:i') }}</p>
                    </div>
                    @endif
                    @if($quiz->tutup_waktu)
                    <div>
                        <p class="text-xs text-muted uppercase tracking-wider">Waktu Selesai</p>
                        <p class="font-medium text-text">{{ $quiz->tutup_waktu->format('d M Y, H:i') }}</p>
                    </div>
                    @endif
                </div>
            </div>
            @endif

            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                <div class="card p-4 text-center">
                    <div class="text-3xl font-bold text-primary">{{ $quiz->questions->count() }}</div>
                    <div class="text-sm text-muted">Total Soal</div>
                </div>
                <div class="card p-4 text-center">
                    <div class="text-3xl font-bold text-success">{{ $quiz->questions->sum('bobot') }}</div>
                    <div class="text-sm text-muted">Total Bobot</div>
                </div>
                <div class="card p-4 text-center">
                    <div class="text-3xl font-bold text-warning">{{ $attemptStats['total'] }}</div>
                    <div class="text-sm text-muted">Total Percobaan</div>
                </div>
                <div class="card p-4 text-center">
                    <div class="text-3xl font-bold text-info">{{ $attemptStats['completed'] }}</div>
                    <div class="text-sm text-muted">Selesai</div>
                </div>
            </div>

            <div class="border-t border-border pt-6 mb-6">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-4">
                    <h2 class="text-lg font-semibold text-text">Daftar Soal</h2>
                    <div class="flex flex-wrap gap-2">
                        @can('quizzes.manage', $quiz)
                        <a href="{{ route('quiz.questions.create', $quiz) }}" class="btn-primary">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                            Tambah Soal
                        </a>
                        @if($quiz->status_aktif)
                            <form action="{{ route('quiz.archive', $quiz) }}" method="POST" class="inline" onsubmit="return confirm('Arsipkan quiz ini?');">
                                @csrf
                                <button type="submit" class="btn-secondary">Arsipkan</button>
                            </form>
                        @else
                            <form action="{{ route('quiz.publish', $quiz) }}" method="POST" class="inline" onsubmit="return confirm('Terbitkan quiz ini?');">
                                @csrf
                                <button type="submit" class="btn-success">Terbitkan</button>
                            </form>
                        @endif
                        <a href="{{ route('quiz.preview', $quiz) }}" class="btn-info" target="_blank">Pratinjau</a>
                        <a href="{{ route('quiz.recap', $quiz) }}" class="btn-secondary">Rekap Nilai</a>
                        <a href="{{ route('quiz.essay-grade', $quiz) }}" class="btn-warning">Penilaian Esai</a>
                        @endcan
                    </div>
                </div>

                @if($quiz->questions->isNotEmpty())
                <div class="space-y-4">
                    @foreach($quiz->questions as $question)
                    <div class="card p-4">
                        <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3 mb-3">
                            <div class="flex-1">
                                <span class="badge badge-secondary mb-2">{{ ucfirst(str_replace('_', ' ', $question->tipe)) }}</span>
                                <p class="font-medium text-text">{{ $question->pertanyaan }}</p>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="badge badge-primary">{{ $question->bobot }} Poin</span>
                                @can('quizzes.manage', $quiz)
                                <a href="{{ route('quiz.questions.edit', [$quiz, $question]) }}" class="text-primary hover:underline text-sm">Edit</a>
                                <form action="{{ route('quiz.questions.destroy', [$quiz, $question]) }}" method="POST" class="inline" onsubmit="return confirm('Hapus soal ini?');">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:underline text-sm">Hapus</button>
                                </form>
                                @endcan
                            </div>
                        </div>

                        @if($question->tipe === 'pilihan_ganda' && $question->options->isNotEmpty())
                        <div class="ml-4 pl-4 border-l-2 border-border space-y-2">
                            @foreach($question->options as $index => $option)
                            <div class="flex items-center gap-3 p-2 rounded-lg {{ $option->is_benar ? 'bg-green-50 border border-green-200' : 'bg-gray-50' }}">
                                <span class="w-6 h-6 flex items-center justify-center rounded-full border border-primary/20 text-primary text-sm font-medium">
                                    {{ chr(65 + $index) }}
                                </span>
                                <span class="flex-1 text-sm text-text">{{ $option->teks }}</span>
                                @if($option->is_benar)
                                <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                <span class="text-xs text-green-700 font-medium">Benar</span>
                                @endif
                            </div>
                            @endforeach
                        </div>
                        @elseif($question->tipe === 'esai')
                        <div class="ml-4 pl-4 border-l-2 border-border py-2">
                            <p class="text-sm text-muted italic">Soal esai - Peserta Didik menjawab secara tertulis. Bobot: {{ $question->bobot }}</p>
                        </div>
                        @endif
                    </div>
                    @endforeach
                </div>
                @else
                <div class="text-center py-8 text-muted">
                    <svg class="mx-auto w-12 h-12 text-muted mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <p class="text-sm">Belum ada soal. Klik "Tambah Soal" untuk memulai.</p>
                </div>
                @endif
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t border-border">
                <a href="{{ route('quiz.index') }}" class="btn-secondary">Kembali</a>
                @can('quizzes.manage', $quiz)
                <a href="{{ route('quiz.edit', $quiz) }}" class="btn-primary">Edit Quiz</a>
                @endcan
            </div>
        </div>
    </div>
</x-app-layout>
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
                    <span class="badge {{ $quiz->status_aktif ? 'badge-success' : 'badge-warning' }}">
                        {{ $quiz->status_aktif ? 'Diterbitkan' : 'Draft' }}
                    </span>
                    <span class="badge badge-info flex items-center gap-1">
                        <x-icon name="clock" class="w-3 h-3" />
                        {{ $quiz->durasi_menit }} Menit
                    </span>
                    <span class="badge badge-primary flex items-center gap-1">
                        <x-icon name="layers" class="w-3 h-3" />
                        {{ $quiz->penugasanMengajar->subject->nama }}
                    </span>
                    <span class="badge badge-secondary flex items-center gap-1">
                        <x-icon name="users" class="w-3 h-3" />
                        Kelas {{ $quiz->penugasanMengajar->classroom->nama_rombel }}
                    </span>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                <div class="card p-4 text-center">
                    <div class="text-3xl font-bold text-primary">{{ $quiz->questions->count() }}</div>
                    <div class="text-sm text-muted">Total Soal</div>
                </div>
                <div class="card p-4 text-center">
                    <div class="text-3xl font-bold text-success">{{ $quiz->questions->sum('bobot') }}</div>
                    <div class="text-sm text-muted">Total Skor</div>
                </div>
                <div class="card p-4 text-center">
                    <div class="text-3xl font-bold text-warning">{{ $quiz->attempts->count() }}</div>
                    <div class="text-sm text-muted">Jumlah Percobaan</div>
                </div>
            </div>

            <div class="border-t border-border pt-6 mb-6">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-4">
                    <h2 class="text-lg font-semibold text-text">Daftar Soal</h2>
                    @can('materials.manage')
                    <a href="{{ route('quiz.questions.create', $quiz) }}" class="btn-primary">
                        <x-icon name="plus" class="w-4 h-4 mr-2" />
                        Tambah Soal
                    </a>
                    @endcan
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
                                @can('materials.manage')
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
                                <x-icon name="check-circle" class="w-5 h-5 text-green-600" />
                                <span class="text-xs text-green-700 font-medium">Benar</span>
                                @endif
                            </div>
                            @endforeach
                        </div>
                        @elseif($question->tipe === 'esai')
                        <div class="ml-4 pl-4 border-l-2 border-border py-2">
                            <p class="text-sm text-muted italic">Soal esai - Peserta Didik menjawab secara tertulis.</p>
                        </div>
                        @endif
                    </div>
                    @endforeach
                </div>
                @else
                <div class="text-center py-8 text-muted">
                    <x-icon name="clipboard-list" class="mx-auto w-12 h-12 text-muted mb-3" />
                    <p class="text-sm">Belum ada soal. Klik "Tambah Soal" untuk memulai.</p>
                </div>
                @endif
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t border-border">
                <a href="{{ route('quiz.index') }}" class="btn-secondary">Kembali</a>
                @can('materials.manage')
                <a href="{{ route('quiz.edit', $quiz) }}" class="btn-primary">Edit Quiz</a>
                @endcan
            </div>
        </div>
    </div>
</x-app-layout>
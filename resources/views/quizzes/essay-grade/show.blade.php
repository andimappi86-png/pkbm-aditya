<x-app-layout>
    <x-slot name="title">Penilaian Esai - {{ $quiz->judul }} - {{ $attempt->user->name }}</x-slot>

    <div class="max-w-4xl mx-auto space-y-6">
        <x-breadcrumb :items="[['label' => 'Pembelajaran'], ['label' => 'Kuis & Ujian', 'href' => route('quiz.index')], ['label' => $quiz->judul, 'href' => route('quiz.show', $quiz)], ['label' => 'Penilaian Esai', 'href' => route('quiz.essay-grade', $quiz)], ['label' => $attempt->user->name]]" />

        <div class="card p-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
                <div>
                    <h1 class="text-2xl font-bold text-text">{{ $quiz->judul }}</h1>
                    <div class="flex flex-wrap items-center gap-3 mt-2 text-sm">
                        <span class="font-medium text-text">{{ $attempt->user->name }}</span>
                        <span class="text-muted">•</span>
                        <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-primary-soft text-primary">Percobaan #{{ $attempt->attempt_number }}</span>
                        <span class="text-muted">•</span>
                        @if($attempt->status === 'dikerjakan')
                        <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-yellow-100 text-yellow-800">Dikerjakan</span>
                        @elseif($attempt->status === 'diserahkan')
                        <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-blue-100 text-blue-800">Diserahkan</span>
                        @elseif($attempt->status === 'dinilai')
                        <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-green-100 text-green-800">Dinilai</span>
                        @elseif($attempt->status === 'otomatis')
                        <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-purple-100 text-purple-800">Otomatis</span>
                        @else
                        <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-gray-100 text-gray-800">{{ ucfirst($attempt->status) }}</span>
                        @endif
                    </div>
                </div>
                <a href="{{ route('quiz.essay-grade', $quiz) }}" class="btn-secondary">Kembali ke Daftar</a>
            </div>

            @if($essayAnswers->isEmpty())
            <div class="text-center py-12 border-2 border-dashed border-border rounded-xl">
                <svg class="mx-auto w-16 h-16 text-muted mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <h3 class="text-lg font-medium text-navy mb-2">Tidak Ada Jawaban Esai</h3>
                <p class="text-muted">Peserta didik belum menjawab soal esai atau quiz tidak memiliki soal esai.</p>
            </div>
            @else
            <form method="POST" action="{{ route('quiz.essay-grade.grade', [$quiz, $attempt]) }}" class="space-y-6">
                @csrf
                @method('PUT')

                @foreach($essayAnswers as $answer)
                <div class="card p-5 border border-border">
                    <div class="mb-4">
                        <div class="flex items-center gap-2 mb-2">
                            <span class="badge badge-warning">Esai</span>
                            <span class="badge badge-primary">{{ $answer->question->bobot }} Poin Maksimal</span>
                            @if($answer->is_graded)
                            <span class="badge badge-success">Sudah Dinilai</span>
                            @else
                            <span class="badge badge-yellow">Belum Dinilai</span>
                            @endif
                        </div>
                        <p class="text-text">{{ $answer->question->pertanyaan }}</p>
                    </div>

                    @if($answer->question->rubrik)
                    <div class="mb-4 p-3 bg-gray-50 rounded-lg border border-border">
                        <p class="text-xs text-muted uppercase tracking-wider mb-1">Rubrik / Pedoman Penilaian</p>
                        <p class="text-sm text-text">{{ $answer->question->rubrik }}</p>
                    </div>
                    @endif

                    @if($answer->question->pembahasan)
                    <div class="mb-4 p-3 bg-primary-soft/50 rounded-lg border border-primary/20">
                        <p class="text-xs text-primary uppercase tracking-wider mb-1">Pembahasan (Untuk Referensi Tutor)</p>
                        <p class="text-sm text-primary">{{ $answer->question->pembahasan }}</p>
                    </div>
                    @endif

                    <div class="mb-4">
                        <label class="label">Jawaban Peserta Didik</label>
                        <div class="p-4 bg-gray-50 rounded-lg border border-border min-h-[100px] whitespace-pre-wrap text-text">
                            {{ $answer->answer_text ?: '<span class="text-muted italic">Tidak ada jawaban</span>' }}
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="nilai_{{ $answer->id }}" class="label">Nilai <span class="text-red-500">*</span></label>
                            <input type="number" name="nilai[{{ $answer->id }}]" id="nilai_{{ $answer->id }}" class="input" step="0.1" min="0" max="{{ $answer->question->bobot }}" value="{{ $answer->nilai ?? '' }}" required>
                            <p class="text-xs text-muted mt-1">Maksimal: {{ $answer->question->bobot }}</p>
                        </div>

                        <div>
                            <label for="catatan_{{ $answer->id }}" class="label">Catatan / Umpan Balik</label>
                            <textarea name="catatan[{{ $answer->id }}]" id="catatan_{{ $answer->id }}" class="input" rows="3">{{ $answer->catatan ?? '' }}</textarea>
                        </div>
                    </div>

                    <input type="hidden" name="question_ids[]" value="{{ $answer->question_id }}">
                </div>
                @endforeach

                <div class="flex justify-end gap-3 pt-4 border-t border-border">
                    <a href="{{ route('quiz.essay-grade', $quiz) }}" class="btn-secondary">Batal</a>
                    <button type="submit" class="btn-primary">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                        Simpan Penilaian
                    </button>
                </div>
            </form>
            @endif
        </div>
    </div>
</x-app-layout>
<x-app-layout>
    <x-slot name="title">Hasil Kuis: {{ $quiz->judul }}</x-slot>

    <div class="max-w-3xl mx-auto space-y-6">
        <x-breadcrumb :items="[['label' => 'Pembelajaran'], ['label' => 'Kuis & Ujian', 'href' => route('quiz.index')], ['label' => 'Hasil Kuis']]" />

        {{-- Score Summary --}}
        <div class="card p-8 text-center">
            <div class="inline-flex items-center justify-center w-20 h-20 rounded-full {{ $attempt->nilai >= ($quiz->kkm ?? 70) ? 'bg-success/10 text-success' : 'bg-danger/10 text-danger' }} mb-4">
                <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
            </div>

            <h1 class="text-2xl font-bold text-text mb-2">Kuis Selesai!</h1>
            <p class="text-muted mb-6">Anda telah menyelesaikan kuis <strong>{{ $quiz->judul }}</strong>.</p>

            <div class="inline-block p-6 rounded-2xl bg-gray-50 border border-border">
                <div class="text-sm font-semibold text-muted uppercase tracking-wider mb-2">Nilai Akhir</div>
                <div class="text-5xl font-black {{ $attempt->nilai >= ($quiz->kkm ?? 70) ? 'text-success' : 'text-danger' }}">
                    {{ number_format($attempt->nilai, 1) }}
                </div>
                <div class="text-sm text-muted mt-2">
                    @if($quiz->kkm)
                    KKM: {{ $quiz->kkm }} •
                    @endif
                    dari skala {{ $quiz->questions->sum('bobot') }}
                </div>
                @if($quiz->kkm)
                <div class="mt-3 inline-flex items-center px-3 py-1.5 rounded-full text-sm font-medium {{ $attempt->nilai >= $quiz->kkm ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                    {{ $attempt->nilai >= $quiz->kkm ? 'LULUS' : 'TIDAK LULUS' }}
                </div>
                @endif
            </div>
        </div>

        {{-- Time Info --}}
        <div class="grid grid-cols-2 gap-4">
            <div class="card p-4 flex flex-col items-center justify-center text-center">
                <span class="text-muted text-sm mb-1">Mulai</span>
                <span class="font-medium text-text">{{ $attempt->mulai_pada->format('d M Y, H:i') }}</span>
            </div>
            <div class="card p-4 flex flex-col items-center justify-center text-center">
                <span class="text-muted text-sm mb-1">Selesai</span>
                <span class="font-medium text-text">{{ $attempt->selesai_pada->format('d M Y, H:i') }}</span>
            </div>
        </div>

        {{-- Detail Results (if allowed) --}}
        @if($quiz->tampilkan_nilai !== 'never' && ($attempt->status === 'dinilai' || $quiz->tampilkan_nilai === 'after_submit'))
        <div class="card">
            <div class="p-4 border-b border-border">
                <h2 class="text-lg font-semibold text-text">Detail Jawaban</h2>
            </div>
            <div class="divide-y divide-border">
                @foreach($attempt->answers()->with(['question', 'option'])->get() as $index => $answer)
                <div class="p-4">
                    <div class="flex items-start gap-3 mb-3">
                        <span class="w-7 h-7 flex items-center justify-center rounded-full bg-primary-soft text-primary font-bold shrink-0">{{ $index + 1 }}</span>
                        <div class="flex-1">
                            <p class="font-medium text-text">{{ $answer->question->pertanyaan }}</p>
                            <span class="text-xs text-muted">{{ ucfirst(str_replace('_', ' ', $answer->question->tipe)) }} • {{ $answer->question->bobot }} Poin</span>
                        </div>
                    </div>

                    @if($answer->question->tipe === 'pilihan_ganda')
                    <div class="space-y-2 ml-10">
                        @foreach($answer->question->options as $option)
                        <div class="flex items-center gap-3 p-2 rounded-lg {{ $option->id == $answer->option_id ? ($option->is_benar ? 'bg-green-50 border-green-200' : 'bg-red-50 border-red-200') : ($option->is_benar ? 'bg-green-50 border-green-200' : 'bg-gray-50') }} border">
                            <span class="w-6 h-6 flex items-center justify-center rounded-full border border-primary/20 text-primary text-sm font-medium">{{ chr(65 + $loop->index) }}</span>
                            <span class="flex-1 text-sm text-text">{{ $option->teks }}</span>
                            @if($option->id == $answer->option_id)
                            @if($option->is_benar)
                            <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                            <span class="text-xs text-green-700 font-medium">Jawaban Anda (Benar)</span>
                            @else
                            <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                            <span class="text-xs text-red-700 font-medium">Jawaban Anda (Salah)</span>
                            @endif
                            @elseif($option->is_benar)
                            @if($quiz->tampilkan_jawaban !== 'never')
                            <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                            <span class="text-xs text-green-700 font-medium">Kunci Jawaban</span>
                            @endif
                            @endif
                        </div>
                        @endforeach
                    </div>

                    @elseif($answer->question->tipe === 'benar_salah')
                    <div class="flex gap-4 ml-10">
                        <div class="flex items-center gap-2 p-2 rounded-lg border {{ $answer->answer_text === 'true' ? ($answer->question->kunci_benar_salah ? 'border-green-300 bg-green-50' : 'border-red-300 bg-red-50') : ($answer->question->kunci_benar_salah ? 'border-green-300 bg-green-50' : 'border-gray-200') }}">
                            <span class="font-medium text-text">Benar</span>
                            @if($answer->question->kunci_benar_salah && $quiz->tampilkan_jawaban !== 'never')
                            <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                            @endif
                        </div>
                        <div class="flex items-center gap-2 p-2 rounded-lg border {{ $answer->answer_text === 'false' ? (!$answer->question->kunci_benar_salah ? 'border-green-300 bg-green-50' : 'border-red-300 bg-red-50') : (!$answer->question->kunci_benar_salah ? 'border-green-300 bg-green-50' : 'border-gray-200') }}">
                            <span class="font-medium text-text">Salah</span>
                            @if(!$answer->question->kunci_benar_salah && $quiz->tampilkan_jawaban !== 'never')
                            <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                            @endif
                        </div>
                    </div>

                    @elseif($answer->question->tipe === 'isian_singkat')
                    <div class="ml-10 space-y-2">
                        <div class="p-2 rounded-lg border {{ $answer->is_correct ? 'bg-green-50 border-green-200' : 'bg-red-50 border-red-200' }}">
                            <p class="text-sm"><strong>Jawaban Anda:</strong> {{ $answer->answer_text ?: '<span class="text-muted italic">(kosong)</span>' }}</p>
                        </div>
                        @if($quiz->tampilkan_jawaban !== 'never')
                        <div class="p-2 rounded-lg border bg-blue-50 border-blue-200">
                            <p class="text-sm"><strong>Kunci Jawaban:</strong> {{ $answer->question->kunci_jawaban }}</p>
                        </div>
                        @endif
                    </div>

                    @elseif($answer->question->tipe === 'esai')
                    <div class="ml-10 space-y-2">
                        <div class="p-3 rounded-lg border bg-gray-50">
                            <p class="text-sm"><strong>Jawaban Anda:</strong></p>
                            <p class="whitespace-pre-wrap mt-1">{{ $answer->answer_text ?: '<span class="text-muted italic">(kosong)</span>' }}</p>
                        </div>
                        @if($answer->is_graded)
                        <div class="p-3 rounded-lg border bg-green-50 border-green-200">
                            <p class="text-sm"><strong>Nilai:</strong> {{ $answer->nilai }}/{{ $answer->question->bobot }}</p>
                            @if($answer->catatan)
                            <p class="text-sm mt-1"><strong>Catatan Tutor:</strong> {{ $answer->catatan }}</p>
                            @endif
                        </div>
                        @else
                        <div class="p-3 rounded-lg border bg-yellow-50 border-yellow-200">
                            <p class="text-sm text-yellow-800"><strong>Belum Dinilai</strong> - Menunggu penilaian tutor.</p>
                        </div>
                        @endif
                    </div>
                    @endif

                    @if($answer->question->pembahasan && $quiz->tampilkan_jawaban !== 'never')
                    <div class="mt-3 p-3 rounded-lg border bg-primary-soft/50 border-primary/20">
                        <p class="text-xs text-primary uppercase tracking-wider mb-1">Pembahasan</p>
                        <p class="text-sm text-primary">{{ $answer->question->pembahasan }}</p>
                    </div>
                    @endif
                </div>
                @endforeach
            </div>
        </div>
        @elseif($quiz->tampilkan_nilai === 'after_grading' && $attempt->status !== 'dinilai')
        <div class="card p-6 text-center border border-yellow-200 bg-yellow-50">
            <svg class="mx-auto w-12 h-12 text-yellow-600 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
            <h3 class="text-lg font-medium text-yellow-900 mb-2">Nilai Belum Tersedia</h3>
            <p class="text-yellow-800">Quiz ini mengandung soal esai yang belum selesai dinilai. Nilai akhir akan ditampilkan setelah semua soal esai dinilai oleh tutor.</p>
        </div>
        @endif

        <div class="flex justify-center pt-4">
            <a href="{{ route('quiz.index') }}" class="btn-primary">
                Kembali ke Daftar Kuis
            </a>
        </div>
    </div>
</x-app-layout>
<x-app-layout>
    <x-slot name="title">Pratinjau Quiz - {{ $quiz->judul }}</x-slot>

    <div class="max-w-4xl mx-auto space-y-6">
        <div class="mb-4">
            <div class="flex items-center gap-3 bg-yellow-50 border border-yellow-200 rounded-xl p-4">
                <svg class="w-6 h-6 text-yellow-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                <div>
                    <p class="font-medium text-yellow-900">Mode Pratinjau</p>
                    <p class="text-sm text-yellow-800">Ini hanya simulasi. Jawaban tidak akan tersimpan dan timer tidak berjalan. Gunakan untuk memeriksa tampilan soal dan alur pengerjaan.</p>
                </div>
            </div>
        </div>

        <x-breadcrumb :items="[['label' => 'Pembelajaran'], ['label' => 'Kuis & Ujian', 'href' => route('quiz.index')], ['label' => $quiz->judul, 'href' => route('quiz.show', $quiz)], ['label' => 'Pratinjau']]" />

        <div class="card p-6">
            <div class="mb-6">
                <h1 class="text-2xl font-bold text-text">{{ $quiz->judul }}</h1>
                @if($quiz->deskripsi)
                <p class="text-muted mt-2">{{ $quiz->deskripsi }}</p>
                @endif
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6 p-4 bg-gray-50 rounded-xl border border-border">
                <div class="text-center">
                    <div class="text-2xl font-bold text-primary">{{ $questions->count() }}</div>
                    <div class="text-sm text-muted">Total Soal</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-bold text-success">{{ $questions->sum('bobot') }}</div>
                    <div class="text-sm text-muted">Total Bobot</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-bold text-warning">{{ $quiz->durasi_menit }} Menit</div>
                    <div class="text-sm text-muted">Durasi</div>
                </div>
            </div>

            @if($questions->isNotEmpty())
            <div class="space-y-6">
                @foreach($questions as $index => $question)
                <div class="card p-5 border border-border">
                    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3 mb-4">
                        <div class="flex items-center gap-3">
                            <span class="w-8 h-8 flex items-center justify-center rounded-full bg-primary-soft text-primary font-bold">{{ $index + 1 }}</span>
                            <div>
                                <span class="badge badge-secondary mb-1">{{ ucfirst(str_replace('_', ' ', $question->tipe)) }}</span>
                                <p class="font-medium text-text">{{ $question->pertanyaan }}</p>
                            </div>
                        </div>
                        <span class="badge badge-primary shrink-0">{{ $question->bobot }} Poin</span>
                    </div>

                    @if($question->tipe === 'pilihan_ganda')
                    <div class="space-y-2 ml-11">
                        @foreach($question->options as $optIndex => $option)
                        <label class="flex items-center gap-3 p-3 rounded-lg border border-border hover:bg-gray-50 cursor-pointer transition-colors {{ $option->is_benar ? 'border-green-300 bg-green-50' : '' }}">
                            <input type="radio" name="preview_{{ $question->id }}" class="w-5 h-5 text-primary border-border focus:ring-primary" disabled>
                            <span class="w-7 h-7 flex items-center justify-center rounded-full border border-primary/20 text-primary text-sm font-medium">{{ chr(65 + $optIndex) }}</span>
                            <span class="flex-1 text-text">{{ $option->teks }}</span>
                            @if($option->is_benar)
                            <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                            <span class="text-xs text-green-700 font-medium">Kunci</span>
                            @endif
                        </label>
                        @endforeach
                    </div>

                    @elseif($question->tipe === 'benar_salah')
                    <div class="flex gap-4 ml-11">
                        <label class="flex items-center gap-2 p-3 rounded-lg border border-border cursor-pointer {{ $question->kunci_benar_salah ? 'border-green-300 bg-green-50' : 'border-red-300 bg-red-50' }}">
                            <input type="radio" name="preview_{{ $question->id }}" class="w-5 h-5 text-primary border-border focus:ring-primary" disabled>
                            <span class="text-text">Benar</span>
                            @if($question->kunci_benar_salah)
                            <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                            @endif
                        </label>
                        <label class="flex items-center gap-2 p-3 rounded-lg border border-border cursor-pointer {{ ! $question->kunci_benar_salah ? 'border-green-300 bg-green-50' : 'border-red-300 bg-red-50' }}">
                            <input type="radio" name="preview_{{ $question->id }}" class="w-5 h-5 text-primary border-border focus:ring-primary" disabled>
                            <span class="text-text">Salah</span>
                            @if(! $question->kunci_benar_salah)
                            <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                            @endif
                        </label>
                    </div>

                    @elseif($question->tipe === 'isian_singkat')
                    <div class="ml-11">
                        <div class="p-3 bg-gray-50 rounded-lg border border-border">
                            <p class="text-sm text-muted mb-1">Kunci Jawaban: <span class="text-text font-mono">{{ $question->kunci_jawaban }}</span></p>
                            @if($question->case_sensitive)
                            <p class="text-xs text-muted">Case sensitive</p>
                            @endif
                        </div>
                    </div>

                    @elseif($question->tipe === 'esai')
                    <div class="ml-11">
                        <div class="p-3 bg-gray-50 rounded-lg border border-border">
                            <p class="text-sm text-muted mb-1">Soal esai - Peserta didik menjawab secara tertulis</p>
                            <p class="text-sm text-text">Bobot maksimal: {{ $question->bobot }}</p>
                            @if($question->rubrik)
                            <div class="mt-2 p-2 bg-yellow-50 rounded border border-yellow-200">
                                <p class="text-xs text-yellow-900 uppercase tracking-wider mb-1">Rubrik Penilaian</p>
                                <p class="text-sm text-yellow-800">{{ $question->rubrik }}</p>
                            </div>
                            @endif
                            @if($question->pembahasan)
                            <div class="mt-2 p-2 bg-primary-soft/50 rounded border border-primary/20">
                                <p class="text-xs text-primary uppercase tracking-wider mb-1">Pembahasan</p>
                                <p class="text-sm text-primary">{{ $question->pembahasan }}</p>
                            </div>
                            @endif
                        </div>
                    </div>
                    @endif
                </div>
                @endforeach
            </div>
            @else
            <div class="text-center py-12 text-muted">
                <svg class="mx-auto w-16 h-16 text-muted mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <p class="text-sm">Belum ada soal untuk ditampilkan.</p>
            </div>
            @endif

            <div class="flex justify-end gap-3 pt-6 border-t border-border">
                <a href="{{ route('quiz.show', $quiz) }}" class="btn-primary">Kembali ke Detail</a>
            </div>
        </div>
    </div>
</x-app-layout>
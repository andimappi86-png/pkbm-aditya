<x-app-layout>
    <x-slot name="title">Kuis: {{ $quiz->judul }}</x-slot>

    <div class="max-w-4xl mx-auto" x-data="quizAttempt()">
        <div class="card p-6 mb-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-text">{{ $quiz->judul }}</h1>
                    <p class="text-muted mt-1">{{ $quiz->deskripsi }}</p>
                </div>
                <div class="card bg-primary-soft border-primary/20 p-4 text-center min-w-[120px]">
                    <div class="text-sm font-semibold text-primary-dark uppercase tracking-wider mb-1">Sisa Waktu</div>
                    <div class="text-2xl font-bold text-primary" x-text="formatTime(timeLeft)">--:--</div>
                </div>
            </div>
        </div>

        <form id="quizForm" action="{{ route('quiz.attempts.submit', [$quiz, $attempt]) }}" method="POST">
            @csrf
            
            <div class="space-y-6">
                @foreach($quiz->questions as $index => $question)
                <div class="card p-6" id="q{{ $question->id }}">
                    <h3 class="text-lg font-medium text-text mb-4">
                        <span class="text-muted mr-2">{{ $index + 1 }}.</span>
                        {{ $question->pertanyaan }}
                    </h3>

                    @if($question->tipe === 'pilihan_ganda')
                        <div class="space-y-3 pl-6">
                            @foreach($question->options->shuffle() as $option)
                                <label class="flex items-center gap-3 p-3 rounded-lg border border-border hover:bg-gray-50 cursor-pointer transition-colors">
                                    <input type="radio" 
                                           name="answers[{{ $question->id }}]" 
                                           value="{{ $option->id }}" 
                                           class="w-4 h-4 text-primary focus:ring-primary border-gray-300"
                                           @change="saveAnswer('{{ $question->id }}', '{{ $option->id }}')"
                                           {{ $attempt->answers->where('question_id', $question->id)->where('option_id', $option->id)->count() > 0 ? 'checked' : '' }}>
                                    <span class="text-text">{{ $option->teks }}</span>
                                </label>
                            @endforeach
                        </div>
                    @else
                        <textarea name="answers[{{ $question->id }}]" class="input" rows="4" placeholder="Tuliskan jawaban Anda di sini..." disabled></textarea>
                        <p class="text-xs text-muted mt-2">Soal esai belum didukung untuk saat ini.</p>
                    @endif
                </div>
                @endforeach
            </div>

            <div class="mt-8 flex justify-end">
                <button type="submit" class="btn-primary" onclick="return confirm('Apakah Anda yakin ingin menyelesaikan kuis ini? Jawaban tidak dapat diubah setelah disubmit.')">
                    <x-icon name="check-circle" class="w-5 h-5 mr-2" />
                    Selesai & Kumpulkan
                </button>
            </div>
        </form>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('quizAttempt', () => ({
                timeLeft: 0,
                duration: {{ $quiz->durasi_menit }},
                startTime: new Date('{{ $attempt->mulai_waktu->toISOString() }}').getTime(),
                
                init() {
                    this.updateTimeLeft();
                    setInterval(() => {
                        this.updateTimeLeft();
                    }, 1000);
                },
                
                updateTimeLeft() {
                    const now = new Date().getTime();
                    const end = this.startTime + (this.duration * 60 * 1000);
                    const diff = end - now;
                    
                    if (diff <= 0) {
                        this.timeLeft = 0;
                        document.getElementById('quizForm').submit();
                    } else {
                        this.timeLeft = Math.floor(diff / 1000);
                    }
                },
                
                formatTime(seconds) {
                    const m = Math.floor(seconds / 60);
                    const s = seconds % 60;
                    return `${m.toString().padStart(2, '0')}:${s.toString().padStart(2, '0')}`;
                },

                saveAnswer(qId, optId) {
                    // Auto-save silently
                    const formData = new FormData();
                    formData.append('_token', '{{ csrf_token() }}');
                    formData.append(`answers[${qId}]`, optId);
                    
                    fetch('{{ route("quiz.attempts.answer", [$quiz, $attempt]) }}', {
                        method: 'POST',
                        body: formData
                    }).catch(err => console.error(err));
                }
            }))
        })
    </script>
    @endpush
</x-app-layout>
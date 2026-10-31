<x-app-layout>
    <x-slot name="title">Kuis: {{ $quiz->judul }}</x-slot>

    <div class="max-w-4xl mx-auto" x-data="quizAttempt()">
        {{-- Header with Timer --}}
        <div class="card p-4 mb-4 border border-border {{ $timeLeft <= 300 ? 'border-red-300 bg-red-50' : '' }}">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-xl font-bold text-text">{{ $quiz->judul }}</h1>
                    <p class="text-sm text-muted mt-1">{{ $quiz->deskripsi }}</p>
                </div>
                <div class="flex items-center gap-4">
                    <div class="card {{ $timeLeft <= 300 ? 'bg-red-50 border-red-200' : 'bg-primary-soft border-primary/20' }} px-5 py-3 text-center min-w-[140px]">
                        <div class="text-xs font-semibold uppercase tracking-wider {{ $timeLeft <= 300 ? 'text-red-700' : 'text-primary-dark' }} mb-1">Sisa Waktu</div>
                        <div class="text-3xl font-bold tabular-nums {{ $timeLeft <= 300 ? 'text-red-600' : 'text-primary' }}" x-text="formatTime(timeLeft)">{{ gmdate('i:s', $timeLeft) }}</div>
                    </div>
                    <div class="hidden sm:block w-48">
                        <div class="h-2 bg-gray-200 rounded-full overflow-hidden">
                            <div class="h-full bg-primary transition-all duration-300" x-bind:style="'width: ' + progressPercent + '%'" role="progressbar" aria-valuenow="50" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                        <p class="text-xs text-muted text-right mt-1" x-text="progressText">{{ $attempt->answers->count() }}/{{ $quiz->questions->count() }}</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Progress Sidebar (Mobile) --}}
        <div class="sm:hidden mb-4">
            <div class="card p-4">
                <div class="flex items-center justify-between mb-2">
                    <span class="font-medium text-text">Progress</span>
                    <span class="text-sm text-muted" x-text="progressText">{{ $attempt->answers->count() }}/{{ $quiz->questions->count() }}</span>
                </div>
                <div class="h-2 bg-gray-200 rounded-full overflow-hidden">
                    <div class="h-full bg-primary transition-all duration-300" x-bind:style="'width: ' + progressPercent + '%'"></div>
                </div>
            </div>
        </div>

        <form id="quizForm" action="{{ route('quiz.attempts.submit', [$quiz, $attempt]) }}" method="POST">
            @csrf

            {{-- Question Navigation Tabs --}}
            <div class="mb-4 overflow-x-auto pb-2" role="tablist" aria-label="Navigasi Soal">
                <div class="flex gap-1 min-w-max" x-ref="navTabs">
                    @foreach($quiz->questions as $index => $question)
                    <button type="button"
                            role="tab"
                            :class="[
                                'px-3 py-2 rounded-lg text-sm font-medium transition-colors',
                                currentQuestionIndex === {{ $index }} ? 'bg-primary text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200',
                                answeredQuestions.includes('{{ $question->id }}') && currentQuestionIndex !== {{ $index }} ? 'border-2 border-green-400' : ''
                            ]"
                            @click="goToQuestion({{ $index }})"
                            aria-selected="{{ $index === 0 ? 'true' : 'false' }}"
                            aria-controls="panel-{{ $question->id }}"
                            id="tab-{{ $question->id }}">
                        {{ $index + 1 }}
                        <span class="ml-1" x-show="answeredQuestions.includes('{{ $question->id }}')" aria-hidden="true">✓</span>
                    </button>
                    @endforeach
                </div>
            </div>

            <div class="space-y-6" role="tabpanel">
                @foreach($quiz->questions as $index => $question)
                <div class="card p-6 {{ $index !== 0 ? 'hidden' : '' }}" id="panel-{{ $question->id }}" role="tabpanel" aria-labelledby="tab-{{ $question->id }}">
                    <div class="flex items-center gap-3 mb-4">
                        <span class="badge badge-secondary">{{ ucfirst(str_replace('_', ' ', $question->tipe)) }}</span>
                        <span class="badge badge-primary">{{ $question->bobot }} Poin</span>
                    </div>

                    <h3 class="text-lg font-medium text-text mb-4">{{ $index + 1 }}. {{ $question->pertanyaan }}</h3>

                    @if($question->tipe === 'pilihan_ganda')
                    <div class="space-y-3">
                        @foreach($question->options->shuffle() as $option)
                        <label class="flex items-center gap-3 p-3 rounded-lg border border-border hover:bg-gray-50 cursor-pointer transition-colors"
                               :class="{ 'border-primary bg-primary-soft': selectedAnswers['{{ $question->id }}'] === '{{ $option->id }}' }">
                            <input type="radio"
                                   name="answers[{{ $question->id }}]"
                                   value="{{ $option->id }}"
                                   class="w-4 h-4 text-primary focus:ring-primary border-gray-300"
                                   @change="saveAnswer('{{ $question->id }}', '{{ $option->id }}')"
                                   {{ old("answers.$question->id") == $option->id ? 'checked' : '' }}>
                            <span class="text-text">{{ $option->teks }}</span>
                        </label>
                        @endforeach
                    </div>

                    @elseif($question->tipe === 'benar_salah')
                    <div class="flex gap-4">
                        <label class="flex-1 flex items-center justify-center gap-2 p-4 rounded-lg border-2 cursor-pointer transition-colors"
                               :class="{
                                   'border-green-400 bg-green-50': selectedAnswers['{{ $question->id }}'] === 'true',
                                   'border-red-400 bg-red-50': selectedAnswers['{{ $question->id }}'] === 'false',
                                   'border-gray-200': !selectedAnswers['{{ $question->id }}']
                               }"
                               @click="saveAnswer('{{ $question->id }}', 'true')">
                            <input type="radio" name="answers[{{ $question->id }}]" value="true" class="sr-only" @change="saveAnswer('{{ $question->id }}', 'true')" {{ old("answers.$question->id") == 'true' ? 'checked' : '' }}>
                            <span class="font-medium text-text">Benar</span>
                        </label>
                        <label class="flex-1 flex items-center justify-center gap-2 p-4 rounded-lg border-2 cursor-pointer transition-colors"
                               :class="{
                                   'border-green-400 bg-green-50': selectedAnswers['{{ $question->id }}'] === 'false',
                                   'border-red-400 bg-red-50': selectedAnswers['{{ $question->id }}'] === 'true',
                                   'border-gray-200': !selectedAnswers['{{ $question->id }}']
                               }"
                               @click="saveAnswer('{{ $question->id }}', 'false')">
                            <input type="radio" name="answers[{{ $question->id }}]" value="false" class="sr-only" @change="saveAnswer('{{ $question->id }}', 'false')" {{ old("answers.$question->id") == 'false' ? 'checked' : '' }}>
                            <span class="font-medium text-text">Salah</span>
                        </label>
                    </div>

                    @elseif($question->tipe === 'isian_singkat')
                    <input type="text"
                           name="answers[{{ $question->id }}]"
                           class="input"
                           placeholder="Ketik jawaban singkat Anda..."
                           x-model="textAnswers['{{ $question->id }}']"
                           @input.debounce.500="saveTextAnswer('{{ $question->id }}', textAnswers['{{ $question->id }}'])"
                           value="{{ old("answers.$question->id") }}">
                    <p class="text-xs text-muted mt-2">Jawaban akan disimpan otomatis.</p>

                    @elseif($question->tipe === 'esai')
                    <textarea name="answers[{{ $question->id }}]"
                              class="input min-h-[150px] resize-y"
                              placeholder="Tuliskan jawaban esai Anda di sini..."
                              x-model="textAnswers['{{ $question->id }}']"
                              @input.debounce.1000="saveTextAnswer('{{ $question->id }}', textAnswers['{{ $question->id }}'])">{{ old("answers.$question->id") }}</textarea>
                    <p class="text-xs text-muted mt-2">Jawaban esai akan dinilai manual oleh tutor. Disimpan otomatis setiap 1 detik setelah berhenti mengetik.</p>

                    @endif

                    {{-- Navigation Buttons --}}
                    <div class="flex justify-between mt-6 pt-4 border-t border-border">
                        @if($index > 0)
                        <button type="button" @click="goToQuestion({{ $index - 1 }})" class="btn-secondary">← Sebelumnya</button>
                        @else
                        <div class="w-24"></div>
                        @endif

                        @if($index < $quiz->questions->count() - 1)
                        <button type="button" @click="goToQuestion({{ $index + 1 }})" class="btn-primary">Selanjutnya →</button>
                        @else
                        <button type="button" @click="confirmSubmit" class="btn-success">Selesai & Kumpulkan</button>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
        </form>

        {{-- Auto-save indicator --}}
        <div class="fixed bottom-4 right-4 z-50 flex items-center gap-2 px-3 py-2 bg-gray-900 text-white text-sm rounded-xl shadow-lg opacity-0 transition-opacity duration-300" x-show="showSaveIndicator" x-transition:enter="opacity-100" x-transition:leave="opacity-0" id="saveIndicator">
            <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
            <span x-text="saveIndicatorText">Menyimpan...</span>
        </div>

        {{-- Submit Confirmation Modal --}}
        <div x-show="showSubmitModal" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4" @click.outside="showSubmitModal = false" role="dialog" aria-modal="true" aria-labelledby="modal-title">
            <div class="bg-white rounded-2xl shadow-xl max-w-md w-full p-6 transform transition-all" @click.outside="showSubmitModal = false">
                <h3 id="modal-title" class="text-lg font-bold text-text mb-2">Konfirmasi Pengumpulan</h3>
                <p class="text-muted mb-6">Apakah Anda yakin ingin menyelesaikan kuis ini? Jawaban tidak dapat diubah setelah disubmit.</p>
                <div class="flex justify-end gap-3">
                    <button type="button" @click="showSubmitModal = false" class="btn-secondary">Batal</button>
                    <button type="button" @click="submitQuiz" class="btn-primary">Ya, Kumpulkan</button>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('quizAttempt', () => ({
                timeLeft: 0,
                duration: {{ $quiz->durasi_menit ?? 60 }},
                startTime: new Date('{{ ($attempt->mulai_pada ?? now())->toISOString() }}').getTime(),
                serverEndTime: new Date('{{ ($attempt->waktu_habis ?? now()->addMinutes($quiz->durasi_menit ?? 60))->toISOString() }}').getTime(),
                currentQuestionIndex: 0,
                answeredQuestions: [],
                selectedAnswers: {},
                textAnswers: {},
                showSaveIndicator: false,
                saveIndicatorText: 'Menyimpan...',
                saveDebounce: null,
                showSubmitModal: false,
                submitting: false,

                init() {
                    this.updateTimeLeft();
                    this.loadSavedAnswers();
                    this.interval = setInterval(() => this.updateTimeLeft(), 1000);

                    // Load saved answers from server
                    this.fetchSavedAnswers();
                },

                async fetchSavedAnswers() {
                    try {
                        const res = await fetch('{{ route("quiz.attempts.answer", [$quiz, $attempt]) }}');
                        const data = await res.json();
                        if (data.answers) {
                            Object.entries(data.answers).forEach(([qId, answer]) => {
                                if (typeof answer === 'string' || typeof answer === 'number') {
                                    this.selectedAnswers[qId] = answer;
                                } else if (answer?.text) {
                                    this.textAnswers[qId] = answer.text;
                                }
                                if (!this.answeredQuestions.includes(qId)) {
                                    this.answeredQuestions.push(qId);
                                }
                            });
                            this.updateProgress();
                        }
                    } catch (err) {
                        console.error('Failed to load saved answers:', err);
                    }
                },

                loadSavedAnswers() {
                    // Load from DOM (old() values)
                    document.querySelectorAll('input[name^="answers["], textarea[name^="answers["]').forEach(el => {
                        const match = el.name.match(/answers\[(\d+)\]/);
                        if (match) {
                            const qId = match[1];
                            if (el.type === 'radio' && el.checked) {
                                this.selectedAnswers[qId] = el.value;
                                if (!this.answeredQuestions.includes(qId)) this.answeredQuestions.push(qId);
                            } else if (el.type === 'text' || el.tagName === 'TEXTAREA') {
                                this.textAnswers[qId] = el.value;
                                if (el.value && !this.answeredQuestions.includes(qId)) this.answeredQuestions.push(qId);
                            }
                        }
                    });
                    this.updateProgress();
                },

                updateTimeLeft() {
                    const now = new Date().getTime();
                    const diff = this.serverEndTime - now;

                    if (diff <= 0) {
                        this.timeLeft = 0;
                        this.autoSubmit();
                    } else {
                        this.timeLeft = Math.floor(diff / 1000);
                    }
                },

                formatTime(seconds) {
                    const m = Math.floor(seconds / 60);
                    const s = seconds % 60;
                    return `${m.toString().padStart(2, '0')}:${s.toString().padStart(2, '0')}`;
                },

                get progressPercent() {
                    const total = {{ $quiz->questions->count() }};
                    if (total === 0) return 0;
                    return Math.round((this.answeredQuestions.length / total) * 100);
                },

                get progressText() {
                    return `${this.answeredQuestions.length} / {{ $quiz->questions->count() }} soal terjawab`;
                },

                saveAnswer(qId, value) {
                    this.selectedAnswers[qId] = value;
                    if (!this.answeredQuestions.includes(qId)) {
                        this.answeredQuestions.push(qId);
                    }
                    this.updateProgress();
                    this.autoSave(qId, value);
                },

                saveTextAnswer(qId, value) {
                    this.textAnswers[qId] = value;
                    if (value && value.trim() && !this.answeredQuestions.includes(qId)) {
                        this.answeredQuestions.push(qId);
                    }
                    this.updateProgress();
                    this.autoSave(qId, value);
                },

                autoSave(qId, value) {
                    clearTimeout(this.saveDebounce);
                    this.showSaveIndicator = true;
                    this.saveIndicatorText = 'Menyimpan...';

                    this.saveDebounce = setTimeout(async () => {
                        const formData = new FormData();
                        formData.append('_token', '{{ csrf_token() }}');
                        formData.append(`answers[${qId}]`, value);

                        try {
                            const res = await fetch('{{ route("quiz.attempts.answer", [$quiz, $attempt]) }}', {
                                method: 'POST',
                                body: formData
                            });
                            if (res.ok) {
                                this.saveIndicatorText = 'Tersimpan';
                                setTimeout(() => { this.showSaveIndicator = false; }, 1500);
                            }
                        } catch (err) {
                            console.error('Auto-save failed:', err);
                            this.saveIndicatorText = 'Gagal menyimpan';
                            setTimeout(() => { this.showSaveIndicator = false; }, 2000);
                        }
                    }, 500);
                },

                updateProgress() {
                    // Update nav tab indicators
                    document.querySelectorAll('[role="tab"]').forEach(btn => {
                        const qId = btn.id.replace('tab-', '');
                        const check = btn.querySelector('span[aria-hidden]');
                        if (this.answeredQuestions.includes(qId)) {
                            if (!check) {
                                const span = document.createElement('span');
                                span.className = 'ml-1';
                                span.setAttribute('aria-hidden', 'true');
                                span.textContent = '✓';
                                btn.appendChild(span);
                            }
                        } else if (check) {
                            check.remove();
                        }
                    });
                },

                goToQuestion(index) {
                    // Hide all panels
                    document.querySelectorAll('[id^="panel-"]').forEach(p => p.classList.add('hidden'));

                    // Show target
                    const panels = document.querySelectorAll('[id^="panel-"]');
                    if (panels[index]) {
                        panels[index].classList.remove('hidden');
                    }

                    // Update tabs
                    document.querySelectorAll('[role="tab"]').forEach((btn, i) => {
                        if (i === index) {
                            btn.classList.remove('bg-gray-100', 'text-gray-700', 'hover:bg-gray-200');
                            btn.classList.add('bg-primary', 'text-white');
                            btn.setAttribute('aria-selected', 'true');
                        } else {
                            btn.classList.remove('bg-primary', 'text-white');
                            btn.classList.add('bg-gray-100', 'text-gray-700', 'hover:bg-gray-200');
                            btn.setAttribute('aria-selected', 'false');
                        }
                    });

                    this.currentQuestionIndex = index;
                },

                confirmSubmit() {
                    this.showSubmitModal = true;
                },

                async submitQuiz() {
                    if (this.submitting) return;
                    this.submitting = true;
                    this.showSubmitModal = false;

                    // Submit form
                    document.getElementById('quizForm').submit();
                },

                async autoSubmit() {
                    clearInterval(this.interval);
                    if (!this.submitting) {
                        this.submitting = true;
                        document.getElementById('quizForm').submit();
                    }
                }
            }))
        })
    </script>
    @endpush
</x-app-layout>
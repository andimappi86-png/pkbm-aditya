<x-app-layout>
    <x-slot name="title">Edit Soal</x-slot>

    <div class="max-w-3xl mx-auto">
        <x-breadcrumb :items="[['label' => 'Pembelajaran'], ['label' => 'Kuis & Ujian', 'href' => route('quiz.index')], ['label' => $question->quiz->judul, 'href' => route('quiz.show', $question->quiz)], ['label' => 'Edit Soal']]" />

        <div class="card p-6" x-data="{ tipe: '{{ old('tipe', $question->tipe) }}', correctAnswer: '{{ old('kunci_benar_salah', $question->kunci_benar_salah ? 'true' : 'false') }}' }">
            <form method="POST" action="{{ route('quiz.questions.update', [$question->quiz, $question]) }}" class="space-y-6">
                @csrf
                @method('PUT')

                <div>
                    <label class="label">Pertanyaan <span class="text-red-500">*</span></label>
                    <textarea name="pertanyaan" class="input @error('pertanyaan') input-error @enderror" rows="3" required>{{ old('pertanyaan', $question->pertanyaan) }}</textarea>
                    @error('pertanyaan')
                    <p class="input-error">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="label">Tipe Soal <span class="text-red-500">*</span></label>
                        <select name="tipe" class="input @error('tipe') input-error @enderror" x-model="tipe" required>
                            <option value="pilihan_ganda" {{ $question->tipe === 'pilihan_ganda' ? 'selected' : '' }}>Pilihan Ganda</option>
                            <option value="benar_salah" {{ $question->tipe === 'benar_salah' ? 'selected' : '' }}>Benar / Salah</option>
                            <option value="isian_singkat" {{ $question->tipe === 'isian_singkat' ? 'selected' : '' }}>Isian Singkat</option>
                            <option value="esai" {{ $question->tipe === 'esai' ? 'selected' : '' }}>Esai</option>
                        </select>
                        @error('tipe')
                        <p class="input-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="label">Bobot Skor <span class="text-red-500">*</span></label>
                        <input type="number" name="bobot" class="input @error('bobot') input-error @enderror" required min="1" value="{{ old('bobot', $question->bobot) }}">
                        @error('bobot')
                        <p class="input-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="label">Urutan</label>
                        <input type="number" name="urutan" class="input" min="0" value="{{ old('urutan', $question->urutan ?? 0) }}">
                    </div>
                </div>

                {{-- Pilihan Ganda --}}
                <div x-show="tipe === 'pilihan_ganda'" class="p-4 bg-gray-50 rounded-xl border border-border space-y-4" x-transition>
                    <h3 class="font-medium text-text mb-2 text-sm">Pilihan Jawaban (Minimal 2, tandai yang benar)</h3>

                    @php
                        $currentOptions = $question->options->toArray();
                        while (count($currentOptions) < 4) {
                            $currentOptions[] = ['teks' => '', 'is_benar' => 0];
                        }
                        $oldOptions = old('options', $currentOptions);
                    @endphp

                    @foreach($oldOptions as $index => $opt)
                    <div class="flex items-start gap-3">
                        <div class="pt-2">
                            <input type="radio" name="correct_option" value="{{ $index }}" {{ (isset($opt['is_benar']) && $opt['is_benar']) || ($index === 0 && !collect($oldOptions)->contains('is_benar', 1)) ? 'checked' : '' }} class="w-4 h-4 text-primary focus:ring-primary border-gray-300">
                        </div>
                        <div class="flex-1">
                            <div class="flex items-center">
                                <span class="w-8 h-10 flex items-center justify-center bg-gray-100 border border-r-0 border-border rounded-l-lg font-medium text-muted">
                                    {{ chr(65 + $index) }}
                                </span>
                                <input type="text" name="options[{{ $index }}][teks]" class="input rounded-l-none" value="{{ $opt['teks'] ?? '' }}" placeholder="Masukkan pilihan jawaban..." required>
                                <input type="hidden" name="options[{{ $index }}][is_benar]" value="0">
                            </div>
                        </div>
                    </div>
                    @endforeach

                    <p class="text-xs text-muted mt-2">Pilih radio button untuk menentukan jawaban yang benar. Minimal 2 opsi.</p>
                </div>

                {{-- Benar / Salah --}}
                <div x-show="tipe === 'benar_salah'" class="p-4 bg-gray-50 rounded-xl border border-border space-y-4" x-transition>
                    <h3 class="font-medium text-text mb-2 text-sm">Kunci Jawaban</h3>

                    <div class="flex gap-4">
                        <label class="flex-1 flex items-center justify-center gap-2 p-4 rounded-lg border-2 cursor-pointer"
                               :class="{ 'border-primary bg-primary-soft': correctAnswer === 'true' }"
                               @click="correctAnswer = 'true'">
                            <input type="radio" name="kunci_benar_salah" value="true" class="sr-only" x-model="correctAnswer" {{ $question->kunci_benar_salah ? 'checked' : '' }}>
                            <span class="font-medium text-text">Benar</span>
                        </label>
                        <label class="flex-1 flex items-center justify-center gap-2 p-4 rounded-lg border-2 cursor-pointer"
                               :class="{ 'border-primary bg-primary-soft': correctAnswer === 'false' }"
                               @click="correctAnswer = 'false'">
                            <input type="radio" name="kunci_benar_salah" value="false" class="sr-only" x-model="correctAnswer" {{ ! $question->kunci_benar_salah ? 'checked' : '' }}>
                            <span class="font-medium text-text">Salah</span>
                        </label>
                    </div>
                    <input type="hidden" name="kunci_benar_salah" x-model="correctAnswer">
                </div>

                {{-- Isian Singkat --}}
                <div x-show="tipe === 'isian_singkat'" class="p-4 bg-gray-50 rounded-xl border border-border space-y-4" x-transition>
                    <h3 class="font-medium text-text mb-2 text-sm">Kunci Jawaban</h3>

                    <div>
                        <label class="label">Jawaban yang Benar <span class="text-red-500">*</span></label>
                        <input type="text" name="kunci_jawaban" class="input @error('kunci_jawaban') input-error @enderror" required value="{{ old('kunci_jawaban', $question->kunci_jawaban) }}">
                        @error('kunci_jawaban')
                        <p class="input-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex items-center gap-2">
                        <input type="checkbox" name="case_sensitive" value="1" class="w-4 h-4 text-primary focus:ring-primary border-gray-300 rounded" {{ old('case_sensitive', $question->case_sensitive ?? false) ? 'checked' : '' }}>
                        <label class="text-sm text-text cursor-pointer">Case Sensitive (perbedaan huruf besar/kecil)</label>
                    </div>
                </div>

                {{-- Esai --}}
                <div x-show="tipe === 'esai'" class="p-4 bg-gray-50 rounded-xl border border-border space-y-4" x-transition>
                    <h3 class="font-medium text-text mb-2 text-sm">Pengaturan Esai</h3>

                    <div>
                        <label class="label">Rubrik / Pedoman Penilaian</label>
                        <textarea name="rubrik" class="input" rows="4" placeholder="Contoh: Penjelasan benar (5 poin), Contoh relevan (3 poin), Penulisan rapi (2 poin)">{{ old('rubrik', $question->rubrik) }}</textarea>
                        <p class="text-xs text-muted mt-1">Pedoman bagi tutor saat menilai jawaban esai.</p>
                    </div>

                    <div>
                        <label class="label">Kriteria Jawaban yang Diharapkan</label>
                        <textarea name="kriteria_jawaban" class="input" rows="3" placeholder="Kata kunci atau poin-poin utama yang harus ada di jawaban peserta didik.">{{ old('kriteria_jawaban', $question->kriteria_jawaban) }}</textarea>
                        <p class="text-xs text-muted mt-1">Opsional. Bisa digunakan untuk referensi penilaian atau pemeriksaan otomatis di masa depan.</p>
                    </div>

                    <div>
                        <label class="label">Pembahasan (Untuk Referensi Tutor)</label>
                        <textarea name="pembahasan" class="input" rows="3" placeholder="Penjelasan lengkap jawaban yang benar untuk referensi tutor saat menilai.">{{ old('pembahasan', $question->pembahasan) }}</textarea>
                        <p class="text-xs text-muted mt-1">Tidak ditampilkan ke peserta didik.</p>
                    </div>
                </div>

                <div class="flex justify-end gap-3 pt-4 border-t border-border">
                    <a href="{{ route('quiz.show', $question->quiz) }}" class="btn-secondary">Batal</a>
                    <button type="submit" class="btn-primary">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
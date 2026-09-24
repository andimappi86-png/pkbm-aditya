<x-app-layout>
    <x-slot name="title">Edit Soal</x-slot>

    <div class="max-w-3xl mx-auto">
        <x-breadcrumb :items="[['label' => 'Pembelajaran'], ['label' => 'Kuis & Ujian', 'href' => route('quiz.index')], ['label' => $question->quiz->judul, 'href' => route('quiz.show', $question->quiz)], ['label' => 'Edit Soal']]" />

        <div class="card p-6" x-data="{ tipe: '{{ old('tipe', $question->tipe) }}' }">
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

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="label">Tipe Soal <span class="text-red-500">*</span></label>
                        <select name="tipe" class="input @error('tipe') input-error @enderror" x-model="tipe" required>
                            <option value="pilihan_ganda">Pilihan Ganda</option>
                            <option value="esai">Esai</option>
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
                </div>

                <div x-show="tipe === 'pilihan_ganda'" class="p-4 bg-gray-50 rounded-xl border border-border space-y-4">
                    <h3 class="font-medium text-text mb-2 text-sm">Pilihan Jawaban (Tandai yang benar)</h3>
                    
                    @php
                        // Set up initial options
                        $defaultOptions = [
                            ['teks' => '', 'is_benar' => 1],
                            ['teks' => '', 'is_benar' => 0],
                            ['teks' => '', 'is_benar' => 0],
                            ['teks' => '', 'is_benar' => 0],
                        ];
                        
                        $currentOptions = $question->options->toArray();
                        
                        // Pad with empty options up to 4 if less than 4 exist
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
                                <input type="text" name="options[{{ $index }}][teks]" class="input rounded-l-none" value="{{ $opt['teks'] ?? '' }}" placeholder="Masukkan pilihan jawaban...">
                                <input type="hidden" name="options[{{ $index }}][is_benar]" value="0">
                            </div>
                        </div>
                    </div>
                    @endforeach
                    
                    <p class="text-xs text-muted mt-2">Pilih radio button untuk menentukan jawaban yang benar.</p>
                </div>

                <div class="flex justify-end gap-3 pt-4 border-t border-border">
                    <a href="{{ route('quiz.show', $question->quiz) }}" class="btn-secondary">Batal</a>
                    <button type="submit" class="btn-primary" onclick="document.querySelectorAll('input[type=radio][name=correct_option]').forEach(function(radio) { if(radio.checked) { document.querySelector('input[name=\'options['+radio.value+'][is_benar]\']').value = '1'; } })">
                        <x-icon name="save" class="w-4 h-4 mr-2" />
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
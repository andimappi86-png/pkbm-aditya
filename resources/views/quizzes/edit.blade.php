<x-app-layout>
    <x-slot name="title">Edit Quiz: {{ $quiz->judul }}</x-slot>

    <div class="max-w-3xl mx-auto">
        <x-breadcrumb :items="[['label' => 'Pembelajaran'], ['label' => 'Kuis & Ujian', 'href' => route('quiz.index')], ['label' => 'Edit: ' . $quiz->judul]]" />

        <div class="card p-6">
            <form method="POST" action="{{ route('quiz.update', $quiz) }}" class="space-y-6">
                @csrf
                @method('PUT')

                <div>
                    <label class="label">Judul Quiz <span class="text-red-500">*</span></label>
                    <input type="text" name="judul" class="input @error('judul') input-error @enderror" required value="{{ $quiz->judul }}">
                    @error('judul')
                    <p class="input-error">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="label">Deskripsi / Petunjuk</label>
                    <textarea name="deskripsi" class="input @error('deskripsi') input-error @enderror" rows="4">{{ $quiz->deskripsi }}</textarea>
                    @error('deskripsi')
                    <p class="input-error">{{ $message }}</p>
                    @enderror
                </div>



                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="label">Durasi (Menit) <span class="text-red-500">*</span></label>
                        <input type="number" name="durasi_menit" class="input @error('durasi_menit') input-error @enderror" required min="1" max="300" value="{{ $quiz->durasi_menit }}">
                        @error('durasi_menit')
                        <p class="input-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="label">Maksimal Percobaan <span class="text-red-500">*</span></label>
                        <input type="number" name="max_attempts" class="input @error('max_attempts') input-error @enderror" required min="1" max="10" value="{{ $quiz->max_attempts }}">
                        @error('max_attempts')
                        <p class="input-error">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="label">Waktu Mulai (Opsional)</label>
                        <input type="datetime-local" name="buka_waktu" class="input @error('buka_waktu') input-error @enderror" value="{{ $quiz->buka_waktu ? $quiz->buka_waktu->format('Y-m-d\TH:i') : '' }}">
                        <p class="text-xs text-muted mt-1">Kosongkan untuk langsung bisa dikerjakan saat diterbitkan.</p>
                        @error('buka_waktu')
                        <p class="input-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="label">Waktu Selesai (Opsional)</label>
                        <input type="datetime-local" name="tutup_waktu" class="input @error('tutup_waktu') input-error @enderror" value="{{ $quiz->tutup_waktu ? $quiz->tutup_waktu->format('Y-m-d\TH:i') : '' }}">
                        <p class="text-xs text-muted mt-1">Kosongkan untuk tidak ada batas waktu akhir.</p>
                        @error('tutup_waktu')
                        <p class="input-error">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="label">Pengacakan Soal</label>
                        <select name="acak_soal" class="input">
                            <option value="0" {{ ! $quiz->acak_soal ? 'selected' : '' }}>Tidak Acak</option>
                            <option value="1" {{ $quiz->acak_soal ? 'selected' : '' }}>Acak Urutan Soal</option>
                        </select>
                    </div>

                    <div>
                        <label class="label">Status</label>
                        <select name="status_aktif" class="input">
                            <option value="1" {{ $quiz->status_aktif ? 'selected' : '' }}>Aktif / Diterbitkan</option>
                            <option value="0" {{ ! $quiz->status_aktif ? 'selected' : '' }}>Draft</option>
                        </select>
                    </div>
                </div>

                <fieldset class="border border-border rounded-xl p-4 space-y-4">
                    <legend class="label text-base">Pengaturan Tampilan Hasil</legend>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="label">Tampilkan Jawaban</label>
                            <select name="tampilkan_jawaban" class="input" required>
                                <option value="never" {{ $quiz->tampilkan_jawaban === 'never' ? 'selected' : '' }}>Tidak Pernah</option>
                                <option value="after_submit" {{ $quiz->tampilkan_jawaban === 'after_submit' ? 'selected' : '' }}>Setelah Mengumpulkan</option>
                                <option value="after_deadline" {{ $quiz->tampilkan_jawaban === 'after_deadline' ? 'selected' : '' }}>Setelah Batas Waktu</option>
                            </select>
                        </div>

                        <div>
                            <label class="label">Tampilkan Nilai</label>
                            <select name="tampilkan_nilai" class="input" required>
                                <option value="never" {{ $quiz->tampilkan_nilai === 'never' ? 'selected' : '' }}>Tidak Pernah</option>
                                <option value="after_submit" {{ $quiz->tampilkan_nilai === 'after_submit' ? 'selected' : '' }}>Setelah Mengumpulkan</option>
                                <option value="after_grading" {{ $quiz->tampilkan_nilai === 'after_grading' ? 'selected' : '' }}>Setelah Dinilai (Termasuk Esai)</option>
                            </select>
                        </div>
                    </div>
                </fieldset>

                <div class="flex justify-end gap-3 pt-4 border-t border-border">
                    <a href="{{ route('quiz.show', $quiz) }}" class="btn-secondary">Batal</a>
                    <button type="submit" class="btn-primary">
                        <x-icon name="save" class="w-4 h-4 mr-2" />
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
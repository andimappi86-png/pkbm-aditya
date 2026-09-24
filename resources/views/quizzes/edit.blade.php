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

                <div>
                    <label class="label">Penugasan Mengajar <span class="text-red-500">*</span></label>
                    <select name="penugasan_mengajar_id" class="input @error('penugasan_mengajar_id') input-error @enderror" required>
                        <option value="">-- Pilih Penugasan --</option>
                        @foreach($penugasans as $penugasan)
                        <option value="{{ $penugasan->id }}" {{ $quiz->penugasan_mengajar_id == $penugasan->id ? 'selected' : '' }}>
                            {{ $penugasan->subject->nama }} - {{ $penugasan->classroom->nama_rombel }} ({{ $penugasan->classroom->kode_rombel }})
                        </option>
                        @endforeach
                    </select>
                    @error('penugasan_mengajar_id')
                    <p class="input-error">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="label">Durasi (Menit) <span class="text-red-500">*</span></label>
                        <input type="number" name="durasi_menit" class="input @error('durasi_menit') input-error @enderror" required min="1" value="{{ $quiz->durasi_menit }}">
                        @error('durasi_menit')
                        <p class="input-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="label">Status</label>
                        <select name="status_aktif" class="input">
                            <option value="1" {{ $quiz->status_aktif ? 'selected' : '' }}>Aktif / Diterbitkan</option>
                            <option value="0" {{ ! $quiz->status_aktif ? 'selected' : '' }}>Draft</option>
                        </select>
                    </div>
                </div>

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
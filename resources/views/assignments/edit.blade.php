<x-app-layout>
    <x-slot name="title">Edit Tugas/Quiz</x-slot>

    <div class="max-w-3xl mx-auto space-y-6">
        <x-breadcrumb :items="[['label' => 'Pembelajaran'], ['label' => 'Tugas & Quiz', 'url' => route('tugas.index')], ['label' => 'Edit']]" />

        <div class="bg-surface border border-border rounded-2xl p-6 shadow-sm">
            <form action="{{ route('tugas.update', $assignment) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="type" class="block text-sm font-medium text-navy mb-1.5">Jenis</label>
                        <select name="type" id="type" required class="block w-full rounded-xl border-border bg-background px-3 py-2 text-sm focus:border-primary focus:ring-primary">
                            <option value="tugas" {{ old('type', $assignment->type) == 'tugas' ? 'selected' : '' }}>Tugas</option>
                            <option value="quiz" {{ old('type', $assignment->type) == 'quiz' ? 'selected' : '' }}>Quiz</option>
                        </select>
                        @error('type') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="deadline" class="block text-sm font-medium text-navy mb-1.5">Batas Waktu (Deadline)</label>
                        <input type="datetime-local" name="deadline" id="deadline" value="{{ old('deadline', $assignment->deadline ? $assignment->deadline->format('Y-m-d\TH:i') : '') }}" class="block w-full rounded-xl border-border bg-background px-3 py-2 text-sm focus:border-primary focus:ring-primary">
                        @error('deadline') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div>
                    <label for="judul" class="block text-sm font-medium text-navy mb-1.5">Judul Tugas/Quiz</label>
                    <input type="text" name="judul" id="judul" value="{{ old('judul', $assignment->judul) }}" required class="block w-full rounded-xl border-border bg-background px-3 py-2 text-sm focus:border-primary focus:ring-primary">
                    @error('judul') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="deskripsi" class="block text-sm font-medium text-navy mb-1.5">Deskripsi Singkat</label>
                    <textarea name="deskripsi" id="deskripsi" rows="3" class="block w-full rounded-xl border-border bg-background px-3 py-2 text-sm focus:border-primary focus:ring-primary">{{ old('deskripsi', $assignment->deskripsi) }}</textarea>
                    @error('deskripsi') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="instruksi" class="block text-sm font-medium text-navy mb-1.5">Instruksi Lengkap</label>
                    <textarea name="instruksi" id="instruksi" rows="5" class="block w-full rounded-xl border-border bg-background px-3 py-2 text-sm focus:border-primary focus:ring-primary">{{ old('instruksi', $assignment->instruksi) }}</textarea>
                    @error('instruksi') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="file_materi" class="block text-sm font-medium text-navy mb-1.5">File Lampiran/Materi (Opsional)</label>
                    @if($assignment->file_materi)
                    <div class="mb-2 text-xs text-muted">
                        File sekarang: <a href="{{ Storage::url($assignment->file_materi) }}" target="_blank" class="text-primary hover:underline">Unduh Lampiran</a>
                    </div>
                    @endif
                    <input type="file" name="file_materi" id="file_materi" class="block w-full rounded-xl border-border bg-background px-3 py-2 text-sm focus:border-primary focus:ring-primary">
                    @error('file_materi') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div class="flex items-center gap-2">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', $assignment->is_active) ? 'checked' : '' }} class="h-4 w-4 rounded border-border text-primary focus:ring-primary">
                    <label for="is_active" class="text-sm font-medium text-navy">Publikasikan (Status Aktif)</label>
                </div>

                <div class="flex justify-end gap-3 pt-4 border-t border-border">
                    <a href="{{ route('tugas.index') }}" class="inline-flex items-center justify-center rounded-xl bg-white border border-border px-4 py-2 text-sm font-medium text-navy hover:bg-background transition-colors focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2 shadow-xs">
                        Batal
                    </a>
                    <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-primary px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-primary-dark transition-colors focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
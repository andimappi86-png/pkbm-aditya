<x-app-layout>
    <x-slot name="title">Tambah Materi Baru</x-slot>

    <div class="max-w-3xl mx-auto space-y-6">
        <x-breadcrumb :items="[['label' => 'Pembelajaran'], ['label' => 'Materi', 'url' => route('materi.index')], ['label' => 'Tambah']]" />

        <div class="bg-surface border border-border rounded-2xl p-6 shadow-sm">
            <form action="{{ route('materi.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                @csrf

                <div>
                    <label for="penugasan_mengajar_id" class="block text-sm font-medium text-navy mb-1.5">Penugasan Mengajar (Mata Pelajaran & Kelas)</label>
                    <select name="penugasan_mengajar_id" id="penugasan_mengajar_id" required class="block w-full rounded-xl border-border bg-background px-3 py-2 text-sm focus:border-primary focus:ring-primary">
                        <option value="">-- Pilih Penugasan --</option>
                        @foreach($penugasans as $p)
                            <option value="{{ $p->id }}" {{ old('penugasan_mengajar_id') == $p->id ? 'selected' : '' }}>
                                {{ $p->subject->nama }} - Kelas {{ $p->classroom->nama }}
                            </option>
                        @endforeach
                    </select>
                    @error('penugasan_mengajar_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="tipe" class="block text-sm font-medium text-navy mb-1.5">Tipe Materi</label>
                        <select name="tipe" id="tipe" required class="block w-full rounded-xl border-border bg-background px-3 py-2 text-sm focus:border-primary focus:ring-primary">
                            <option value="teks" {{ old('tipe') == 'teks' ? 'selected' : '' }}>Teks / Modul Bacaan</option>
                            <option value="pdf" {{ old('tipe') == 'pdf' ? 'selected' : '' }}>File PDF / Dokumen</option>
                            <option value="video" {{ old('tipe') == 'video' ? 'selected' : '' }}>Video / Link Streaming</option>
                            <option value="link" {{ old('tipe') == 'link' ? 'selected' : '' }}>Tautan Luar (Link)</option>
                        </select>
                        @error('tipe') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="urutan" class="block text-sm font-medium text-navy mb-1.5">Urutan Bab / Nomor</label>
                        <input type="number" min="1" name="urutan" id="urutan" value="{{ old('urutan', 1) }}" class="block w-full rounded-xl border-border bg-background px-3 py-2 text-sm focus:border-primary focus:ring-primary">
                        @error('urutan') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div>
                    <label for="judul" class="block text-sm font-medium text-navy mb-1.5">Judul Materi</label>
                    <input type="text" name="judul" id="judul" value="{{ old('judul') }}" required class="block w-full rounded-xl border-border bg-background px-3 py-2 text-sm focus:border-primary focus:ring-primary">
                    @error('judul') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="deskripsi" class="block text-sm font-medium text-navy mb-1.5">Deskripsi Singkat</label>
                    <textarea name="deskripsi" id="deskripsi" rows="2" class="block w-full rounded-xl border-border bg-background px-3 py-2 text-sm focus:border-primary focus:ring-primary">{{ old('deskripsi') }}</textarea>
                    @error('deskripsi') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="file_path" class="block text-sm font-medium text-navy mb-1.5">Unggah File (PDF, DOCX, PPTX, ZIP - Max 20MB)</label>
                    <input type="file" name="file_path" id="file_path" class="block w-full rounded-xl border-border bg-background px-3 py-2 text-sm focus:border-primary focus:ring-primary">
                    @error('file_path') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="video_url" class="block text-sm font-medium text-navy mb-1.5">URL Video / Tautan Luar</label>
                    <input type="url" name="video_url" id="video_url" value="{{ old('video_url') }}" placeholder="https://..." class="block w-full rounded-xl border-border bg-background px-3 py-2 text-sm focus:border-primary focus:ring-primary">
                    @error('video_url') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="isi_materi" class="block text-sm font-medium text-navy mb-1.5">Konten / Isi Teks Modul</label>
                    <textarea name="isi_materi" id="isi_materi" rows="6" placeholder="Tuliskan isi modul pembelajaran..." class="block w-full rounded-xl border-border bg-background px-3 py-2 text-sm focus:border-primary focus:ring-primary">{{ old('isi_materi') }}</textarea>
                    @error('isi_materi') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div class="flex items-center gap-2">
                    <input type="hidden" name="status_aktif" value="0">
                    <input type="checkbox" name="status_aktif" id="status_aktif" value="1" {{ old('status_aktif', true) ? 'checked' : '' }} class="h-4 w-4 rounded border-border text-primary focus:ring-primary">
                    <label for="status_aktif" class="text-sm font-medium text-navy">Publikasikan Sekarang (Status Aktif)</label>
                </div>

                <div class="flex justify-end gap-3 pt-4 border-t border-border">
                    <a href="{{ route('materi.index') }}" class="inline-flex items-center justify-center rounded-xl bg-white border border-border px-4 py-2 text-sm font-medium text-navy hover:bg-background transition shadow-xs">
                        Batal
                    </a>
                    <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-primary px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-primary-dark transition focus:ring-2 focus:ring-primary">
                        Simpan Materi
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
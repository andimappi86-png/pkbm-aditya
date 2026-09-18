<x-app-layout>
    <x-slot name="title">Input Nilai</x-slot>

    <div class="max-w-4xl mx-auto space-y-6">
        <x-breadcrumb :items="[['label' => 'Akademik'], ['label' => 'Nilai', 'url' => route('nilai.index')], ['label' => 'Input Nilai']]" />

        <div class="bg-surface border border-border rounded-2xl p-6 shadow-sm">
            <form action="{{ route('nilai.create') }}" method="GET" class="space-y-4 mb-8">
                <div>
                    <label for="penugasan_mengajar_id" class="block text-sm font-medium text-navy mb-1.5">Pilih Penugasan Mengajar (Mata Pelajaran & Kelas)</label>
                    <div class="flex gap-3">
                        <select name="penugasan_mengajar_id" id="penugasan_mengajar_id" class="block w-full rounded-xl border-border bg-background px-3 py-2 text-sm focus:border-primary focus:ring-primary">
                            <option value="">-- Pilih --</option>
                            @foreach($penugasans as $p)
                                <option value="{{ $p->id }}" {{ request('penugasan_mengajar_id') == $p->id ? 'selected' : '' }}>
                                    {{ $p->subject->nama }} - Kelas {{ $p->classroom->nama }} ({{ $p->tutor->nama_lengkap }})
                                </option>
                            @endforeach
                        </select>
                        <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-white border border-border px-4 py-2 text-sm font-medium text-navy hover:bg-background transition-colors focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2 shadow-xs">
                            Pilih
                        </button>
                    </div>
                </div>
            </form>

            @if($selectedPenugasan)
                <div class="border-t border-border pt-6">
                    <div class="mb-4 bg-background p-4 rounded-xl text-sm text-navy space-y-1">
                        <p><span class="font-semibold w-24 inline-block">Mata Pelajaran</span>: {{ $selectedPenugasan->subject->nama }}</p>
                        <p><span class="font-semibold w-24 inline-block">Rombel</span>: {{ $selectedPenugasan->classroom->nama }}</p>
                        <p><span class="font-semibold w-24 inline-block">Tutor</span>: {{ $selectedPenugasan->tutor->nama_lengkap }}</p>
                    </div>

                    <form action="{{ route('nilai.store') }}" method="POST" class="space-y-6">
                        @csrf
                        <input type="hidden" name="penugasan_mengajar_id" value="{{ $selectedPenugasan->id }}">

                        <div class="space-y-4">
                            <div>
                                <label for="student_id" class="block text-sm font-medium text-navy mb-1.5">Pilih Siswa</label>
                                <select name="student_id" id="student_id" required class="block w-full rounded-xl border-border bg-background px-3 py-2 text-sm focus:border-primary focus:ring-primary">
                                    <option value="">-- Pilih Siswa --</option>
                                    @foreach($students as $student)
                                        <option value="{{ $student->id }}" {{ old('student_id') == $student->id ? 'selected' : '' }}>
                                            {{ $student->nama_lengkap }} ({{ $student->nomor_induk }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('student_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-navy mb-1.5">Nilai Tugas</label>
                                    <input type="number" step="0.01" min="0" max="100" name="nilai_tugas" value="{{ old('nilai_tugas') }}" class="block w-full rounded-xl border-border bg-background px-3 py-2 text-sm focus:border-primary focus:ring-primary">
                                    @error('nilai_tugas') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-navy mb-1.5">Nilai UTS</label>
                                    <input type="number" step="0.01" min="0" max="100" name="nilai_uts" value="{{ old('nilai_uts') }}" class="block w-full rounded-xl border-border bg-background px-3 py-2 text-sm focus:border-primary focus:ring-primary">
                                    @error('nilai_uts') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-navy mb-1.5">Nilai UAS</label>
                                    <input type="number" step="0.01" min="0" max="100" name="nilai_uas" value="{{ old('nilai_uas') }}" class="block w-full rounded-xl border-border bg-background px-3 py-2 text-sm focus:border-primary focus:ring-primary">
                                    @error('nilai_uas') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-navy mb-1.5">Nilai Akhir (Manual)</label>
                                    <input type="number" step="0.01" min="0" max="100" name="nilai_akhir" value="{{ old('nilai_akhir') }}" placeholder="Auto (30-30-40)" class="block w-full rounded-xl border-border bg-background px-3 py-2 text-sm focus:border-primary focus:ring-primary">
                                    @error('nilai_akhir') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-navy mb-1.5">Catatan / Deskripsi Kompetensi</label>
                                <textarea name="catatan" rows="3" class="block w-full rounded-xl border-border bg-background px-3 py-2 text-sm focus:border-primary focus:ring-primary">{{ old('catatan') }}</textarea>
                                @error('catatan') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <div class="flex justify-end pt-4">
                            <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-primary px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-primary-dark transition-colors focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2">
                                Simpan Nilai
                            </button>
                        </div>
                    </form>
                </div>
            @else
                <div class="py-8 text-center text-muted">
                    <p>Silakan pilih penugasan mengajar terlebih dahulu.</p>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
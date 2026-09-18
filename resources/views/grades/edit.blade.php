<x-app-layout>
    <x-slot name="title">Edit Nilai</x-slot>

    <div class="max-w-2xl mx-auto space-y-6">
        <x-breadcrumb :items="[['label' => 'Akademik'], ['label' => 'Nilai', 'url' => route('nilai.index')], ['label' => 'Edit Nilai']]" />

        <div class="bg-surface border border-border rounded-2xl p-6 shadow-sm">
            <div class="mb-4 bg-background p-4 rounded-xl text-sm text-navy space-y-1">
                <p><span class="font-semibold w-24 inline-block">Siswa</span>: {{ $grade->student->nama_lengkap }} ({{ $grade->student->nomor_induk }})</p>
                <p><span class="font-semibold w-24 inline-block">Mata Pelajaran</span>: {{ $grade->penugasanMengajar->subject->nama }}</p>
                <p><span class="font-semibold w-24 inline-block">Rombel</span>: {{ $grade->penugasanMengajar->classroom->nama }}</p>
            </div>

            <form action="{{ route('nilai.update', $grade) }}" method="POST" class="space-y-4">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-navy mb-1.5">Nilai Tugas</label>
                        <input type="number" step="0.01" min="0" max="100" name="nilai_tugas" value="{{ old('nilai_tugas', $grade->nilai_tugas) }}" class="block w-full rounded-xl border-border bg-background px-3 py-2 text-sm focus:border-primary focus:ring-primary">
                        @error('nilai_tugas') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-navy mb-1.5">Nilai UTS</label>
                        <input type="number" step="0.01" min="0" max="100" name="nilai_uts" value="{{ old('nilai_uts', $grade->nilai_uts) }}" class="block w-full rounded-xl border-border bg-background px-3 py-2 text-sm focus:border-primary focus:ring-primary">
                        @error('nilai_uts') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-navy mb-1.5">Nilai UAS</label>
                        <input type="number" step="0.01" min="0" max="100" name="nilai_uas" value="{{ old('nilai_uas', $grade->nilai_uas) }}" class="block w-full rounded-xl border-border bg-background px-3 py-2 text-sm focus:border-primary focus:ring-primary">
                        @error('nilai_uas') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-navy mb-1.5">Nilai Akhir</label>
                    <input type="number" step="0.01" min="0" max="100" name="nilai_akhir" value="{{ old('nilai_akhir', $grade->nilai_akhir) }}" class="block w-full rounded-xl border-border bg-background px-3 py-2 text-sm focus:border-primary focus:ring-primary">
                    @error('nilai_akhir') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-navy mb-1.5">Catatan</label>
                    <textarea name="catatan" rows="3" class="block w-full rounded-xl border-border bg-background px-3 py-2 text-sm focus:border-primary focus:ring-primary">{{ old('catatan', $grade->catatan) }}</textarea>
                    @error('catatan') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div class="flex justify-between items-center pt-4">
                    <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-primary px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-primary-dark transition-colors focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
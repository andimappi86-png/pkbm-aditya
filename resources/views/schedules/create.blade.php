<x-app-layout>
    <x-slot name="title">Tambah Jadwal Pelajaran</x-slot>

    <div class="max-w-4xl mx-auto space-y-6">
        <x-breadcrumb :items="[['label' => 'Akademik'], ['label' => 'Jadwal Pelajaran', 'url' => route('jadwal.index')], ['label' => 'Tambah']]" />

        <div class="card p-6">
            <h3 class="text-lg font-semibold text-navy mb-6">Form Tambah Jadwal Pelajaran</h3>

            <form action="{{ route('jadwal.store') }}" method="POST" class="space-y-4">
                @csrf

                <div>
                    <label class="label">Penugasan Mengajar (Rombel - Mapel - Tutor) <span class="text-accent">*</span></label>
                    @if($penugasanMengajars->isEmpty())
                        <div class="p-4 mb-2 bg-amber-50 border border-amber-200 rounded-lg">
                            <p class="text-sm text-amber-700">
                                <strong>Belum ada penugasan mengajar yang valid!</strong> <br>
                                Jadwal pelajaran hanya dapat dibuat jika terdapat penugasan mengajar lengkap (Tutor, Mata Pelajaran, dan Rombel).
                                Silakan buat Penugasan Mengajar melalui menu <a href="{{ route('penugasan-mengajar.index') }}" class="font-bold underline hover:text-amber-900">Pengelolaan &gt; Penugasan Mengajar</a> terlebih dahulu.
                            </p>
                        </div>
                    @endif
                    <select name="penugasan_mengajar_id" class="input @error('penugasan_mengajar_id') input-error @enderror" required {{ $penugasanMengajars->isEmpty() ? 'disabled' : '' }}>
                        <option value="">-- Pilih Penugasan --</option>
                        @foreach($penugasanMengajars as $pm)
                            <option value="{{ $pm->id }}" {{ old('penugasan_mengajar_id') == $pm->id ? 'selected' : '' }}>
                                {{ $pm->classroom?->nama_rombel }} | {{ $pm->subject?->nama }} (Tutor: {{ $pm->tutor?->nama_lengkap }})
                            </option>
                        @endforeach
                    </select>
                    @error('penugasan_mengajar_id')
                        <p class="text-sm text-accent mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="label">Hari <span class="text-accent">*</span></label>
                        <select name="hari" class="input @error('hari') input-error @enderror" required>
                            <option value="">-- Pilih Hari --</option>
                            @foreach(['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'] as $h)
                                <option value="{{ $h }}" {{ old('hari') == $h ? 'selected' : '' }}>{{ $h }}</option>
                            @endforeach
                        </select>
                        @error('hari')
                            <p class="text-sm text-accent mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="label">Jam Mulai <span class="text-accent">*</span></label>
                        <input type="text" name="jam_mulai" pattern="([01]?[0-9]|2[0-3]):[0-5][0-9]" placeholder="HH:mm" value="{{ old('jam_mulai') }}" class="input @error('jam_mulai') input-error @enderror" required />
                        @error('jam_mulai')
                            <p class="text-sm text-accent mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="label">Jam Selesai <span class="text-accent">*</span></label>
                        <input type="text" name="jam_selesai" pattern="([01]?[0-9]|2[0-3]):[0-5][0-9]" placeholder="HH:mm" value="{{ old('jam_selesai') }}" class="input @error('jam_selesai') input-error @enderror" required />
                        @error('jam_selesai')
                            <p class="text-sm text-accent mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div>
                    <label class="label">Ruangan (Opsional)</label>
                    <input type="text" name="ruangan" value="{{ old('ruangan') }}" placeholder="Contoh: Ruang Kelas A / Lab" class="input @error('ruangan') input-error @enderror" />
                    @error('ruangan')
                        <p class="text-sm text-accent mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-center gap-2 pt-2">
                    <input type="checkbox" name="status_aktif" value="1" id="status_aktif" {{ old('status_aktif', 1) ? 'checked' : '' }} class="rounded border-border text-primary focus:ring-primary/20" />
                    <label for="status_aktif" class="text-sm font-medium text-navy cursor-pointer">Status Aktif</label>
                </div>

                <div class="flex items-center justify-end gap-3 pt-4 border-t border-border">
                    <a href="{{ route('jadwal.index') }}" class="btn-secondary">Batal</a>
                    <button type="submit" class="btn-primary">Simpan Jadwal</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>

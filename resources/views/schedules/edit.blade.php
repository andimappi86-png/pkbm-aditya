<x-app-layout>
    <x-slot name="title">Edit Jadwal Pelajaran</x-slot>

    <div class="max-w-4xl mx-auto space-y-6">
        <x-breadcrumb :items="[['label' => 'Akademik'], ['label' => 'Jadwal Pelajaran', 'url' => route('jadwal.index')], ['label' => 'Edit']]" />

        <div class="card p-6">
            <h3 class="text-lg font-semibold text-navy mb-6">Form Edit Jadwal Pelajaran</h3>

            <form action="{{ route('jadwal.update', $schedule) }}" method="POST" class="space-y-4">
                @csrf
                @method('PUT')

                <div>
                    <label class="label">Penugasan Mengajar (Rombel - Mapel - Tutor) <span class="text-accent">*</span></label>
                    <select name="penugasan_mengajar_id" class="input @error('penugasan_mengajar_id') input-error @enderror" required>
                        <option value="">-- Pilih Penugasan --</option>
                        @foreach($penugasanMengajars as $pm)
                            <option value="{{ $pm->id }}" {{ old('penugasan_mengajar_id', $schedule->penugasan_mengajar_id) == $pm->id ? 'selected' : '' }}>
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
                                <option value="{{ $h }}" {{ old('hari', $schedule->hari) == $h ? 'selected' : '' }}>{{ $h }}</option>
                            @endforeach
                        </select>
                        @error('hari')
                            <p class="text-sm text-accent mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="label">Jam Mulai <span class="text-accent">*</span></label>
                        <input type="time" name="jam_mulai" value="{{ old('jam_mulai', \Carbon\Carbon::parse($schedule->jam_mulai)->format('H:i')) }}" class="input @error('jam_mulai') input-error @enderror" required />
                        @error('jam_mulai')
                            <p class="text-sm text-accent mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="label">Jam Selesai <span class="text-accent">*</span></label>
                        <input type="time" name="jam_selesai" value="{{ old('jam_selesai', \Carbon\Carbon::parse($schedule->jam_selesai)->format('H:i')) }}" class="input @error('jam_selesai') input-error @enderror" required />
                        @error('jam_selesai')
                            <p class="text-sm text-accent mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div>
                    <label class="label">Ruangan (Opsional)</label>
                    <input type="text" name="ruangan" value="{{ old('ruangan', $schedule->ruangan) }}" placeholder="Contoh: Ruang Kelas A / Lab" class="input @error('ruangan') input-error @enderror" />
                    @error('ruangan')
                        <p class="text-sm text-accent mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-center gap-2 pt-2">
                    <input type="checkbox" name="status_aktif" value="1" id="status_aktif" {{ old('status_aktif', $schedule->status_aktif) ? 'checked' : '' }} class="rounded border-border text-primary focus:ring-primary/20" />
                    <label for="status_aktif" class="text-sm font-medium text-navy cursor-pointer">Status Aktif</label>
                </div>

                <div class="flex items-center justify-end gap-3 pt-4 border-t border-border">
                    <a href="{{ route('jadwal.index') }}" class="btn-secondary">Batal</a>
                    <button type="submit" class="btn-primary">Perbarui Jadwal</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>

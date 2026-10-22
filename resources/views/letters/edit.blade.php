<x-app-layout>
    <x-slot name="title">Edit Surat</x-slot>

    <div class="max-w-3xl mx-auto space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-navy">Edit Surat Keterangan</h2>
                @if($letter->nomor_surat)
                    <p class="text-sm text-muted mt-1">Nomor: {{ $letter->nomor_surat }}</p>
                @endif
            </div>
            <a href="{{ route('letters.show', $letter) }}" class="btn-secondary">Kembali</a>
        </div>

        <div class="card p-6">
            <form action="{{ route('letters.update', $letter) }}" method="POST" class="space-y-6">
                @csrf
                @method('PUT')

                <div>
                    <x-input-label for="student_id" value="Peserta Didik (Opsional - Kosongkan untuk surat umum)" />
                    <x-select id="student_id" name="student_id" class="w-full mt-1">
                        <option value="">- Pilih Peserta Didik -</option>
                        @foreach($students as $s)
                            <option value="{{ $s->id }}" {{ (string) old('student_id', $letter->student_id) === (string) $s->id ? 'selected' : '' }}>
                                {{ $s->nama_lengkap }} (NIS: {{ $s->nis }})
                            </option>
                        @endforeach
                    </x-select>
                    <x-input-error :messages="$errors->get('student_id')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="jenis_surat" value="Jenis Surat" />
                    <x-select id="jenis_surat" name="jenis_surat" class="w-full mt-1" required>
                        <option value="">Pilih Jenis Surat</option>
                        @foreach(\App\Models\Letter::jenisOptions() as $val => $label)
                            <option value="{{ $val }}" {{ old('jenis_surat', $letter->jenis_surat) === $val ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </x-select>
                    <x-input-error :messages="$errors->get('jenis_surat')" class="mt-2" />
                </div>

                <div class="grid md:grid-cols-2 gap-4">
                    <div>
                        <x-input-label for="tanggal" value="Tanggal Surat" />
                        <x-text-input id="tanggal" name="tanggal" type="date" class="w-full mt-1" value="{{ old('tanggal', $letter->tanggal?->format('Y-m-d')) }}" required />
                        <x-input-error :messages="$errors->get('tanggal')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="tujuan" value="Tujuan Surat" />
                        <x-text-input id="tujuan" name="tujuan" type="text" class="w-full mt-1" value="{{ old('tujuan', $letter->tujuan) }}" placeholder="Contoh: Keperluan Beasiswa / Pindah Sekolah" />
                        <x-input-error :messages="$errors->get('tujuan')" class="mt-2" />
                    </div>
                </div>

                <div>
                    <x-input-label for="penandatangan_nama" value="Nama Penandatangan" />
                    <x-text-input id="penandatangan_nama" name="penandatangan_nama" type="text" class="w-full mt-1" value="{{ old('penandatangan_nama', $letter->penandatangan_nama) }}" required />
                    <x-input-error :messages="$errors->get('penandatangan_nama')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="isi" value="Isi Surat / Keterangan" />
                    <textarea id="isi" name="isi" rows="6" required class="w-full mt-1 rounded-xl border-border focus:border-primary focus:ring focus:ring-primary/20 shadow-sm" placeholder="Tuliskan isi surat di sini...">{{ old('isi', $letter->isi) }}</textarea>
                    <x-input-error :messages="$errors->get('isi')" class="mt-2" />
                </div>

                <div class="flex items-center justify-end gap-3 pt-4 border-t border-border">
                    <a href="{{ route('letters.show', $letter) }}" class="btn-secondary">Batal</a>
                    <x-primary-button type="submit">Simpan Perubahan</x-primary-button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>

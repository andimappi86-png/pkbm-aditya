<x-app-layout>
    <x-slot name="title">Edit Peserta Didik</x-slot>

    <div class="max-w-4xl mx-auto space-y-6">
        <x-breadcrumb :items="[
            ['label' => 'Peserta Didik', 'url' => route('peserta-didik.index')],
            ['label' => 'Edit Data']
        ]" />

        <div class="card p-6">
            <form action="{{ route('peserta-didik.update', $peserta_didik->id) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                @csrf
                @method('PUT')
                
                <h3 class="text-lg font-semibold text-navy border-b pb-2">Identitas Utama</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <x-input name="nis" label="NIS *" :value="$peserta_didik->nis" required />
                    <x-input name="nisn" label="NISN" :value="$peserta_didik->nisn" />
                    <x-input name="nik" label="NIK *" :value="$peserta_didik->nik" required />
                    <x-input name="nama_lengkap" label="Nama Lengkap *" :value="$peserta_didik->nama_lengkap" required />
                    <x-input name="nama_panggilan" label="Nama Panggilan" :value="$peserta_didik->nama_panggilan" />
                    <x-select name="jenis_kelamin" label="Jenis Kelamin *" :options="['Laki-laki' => 'Laki-laki', 'Perempuan' => 'Perempuan']" :selected="$peserta_didik->jenis_kelamin" required />
                    <x-input name="tempat_lahir" label="Tempat Lahir" :value="$peserta_didik->tempat_lahir" />
                    <x-input type="date" name="tanggal_lahir" label="Tanggal Lahir" :value="$peserta_didik->tanggal_lahir?->format('Y-m-d')" />
                    <x-select name="agama" label="Agama" :options="['Islam'=>'Islam','Kristen'=>'Kristen','Katolik'=>'Katolik','Hindu'=>'Hindu','Buddha'=>'Buddha','Konghucu'=>'Konghucu']" :selected="$peserta_didik->agama" />
                    <x-select name="status_perkawinan" label="Status Perkawinan" :options="['Belum Kawin'=>'Belum Kawin','Kawin'=>'Kawin','Cerai Hidup'=>'Cerai Hidup','Cerai Mati'=>'Cerai Mati']" :selected="$peserta_didik->status_perkawinan" />
                </div>

                <h3 class="text-lg font-semibold text-navy border-b pb-2 mt-6">Data Pendidikan</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <x-select name="program_id" label="Program *" :options="$programs->pluck('nama', 'id')->toArray()" :selected="$peserta_didik->program_id" required />
                    <x-select name="tahun_ajaran_id" label="Tahun Ajaran Masuk *" :options="$academicYears->pluck('nama', 'id')->toArray()" :selected="$peserta_didik->tahun_ajaran_id" required />
                    <x-select name="rombel_id" label="Rombel / Kelas" :options="$classrooms->pluck('nama_rombel', 'id')->toArray()" :selected="$peserta_didik->rombel_id" />
                    <x-select name="status" label="Status *" :options="['aktif'=>'Aktif','lulus'=>'Lulus','keluar'=>'Keluar','pindah'=>'Pindah','nonaktif'=>'Nonaktif']" :selected="$peserta_didik->status" required />
                </div>

                <h3 class="text-lg font-semibold text-navy border-b pb-2 mt-6">Kontak & Alamat</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="md:col-span-2">
                        <x-textarea name="alamat" label="Alamat Lengkap *" :value="$peserta_didik->alamat" required />
                    </div>
                    <x-input name="rt" label="RT" :value="$peserta_didik->rt" />
                    <x-input name="rw" label="RW" :value="$peserta_didik->rw" />
                    <x-input name="kelurahan" label="Kelurahan / Desa" :value="$peserta_didik->kelurahan" />
                    <x-input name="kecamatan" label="Kecamatan" :value="$peserta_didik->kecamatan" />
                    <x-input name="kabupaten" label="Kabupaten / Kota" :value="$peserta_didik->kabupaten" />
                    <x-input name="provinsi" label="Provinsi" :value="$peserta_didik->provinsi" />
                    <x-input name="no_hp" label="No. Handphone" :value="$peserta_didik->no_hp" />
                    <x-input type="email" name="email" label="Email" :value="$peserta_didik->email" />
                </div>

                <h3 class="text-lg font-semibold text-navy border-b pb-2 mt-6">Data Keluarga (Opsional)</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <x-input name="nama_ayah" label="Nama Ayah" :value="$peserta_didik->nama_ayah" />
                    <x-input name="nama_ibu" label="Nama Ibu" :value="$peserta_didik->nama_ibu" />
                    <x-input name="nama_wali" label="Nama Wali" :value="$peserta_didik->nama_wali" />
                    <x-input name="no_hp_wali" label="No. HP Wali" :value="$peserta_didik->no_hp_wali" />
                    <div class="md:col-span-2">
                        <x-textarea name="alamat_wali" label="Alamat Wali" rows="2" :value="$peserta_didik->alamat_wali" />
                    </div>
                </div>

                <h3 class="text-lg font-semibold text-navy border-b pb-2 mt-6">Lainnya</h3>
                <div class="grid grid-cols-1 gap-4">
                    <div>
                        <label class="label">Foto Profil (Biarkan kosong jika tidak ingin mengubah)</label>
                        @if($peserta_didik->foto)
                        <div class="mb-2">
                            <img src="{{ asset('storage/' . $peserta_didik->foto) }}" alt="Foto" class="h-20 rounded">
                        </div>
                        @endif
                        <input type="file" name="foto" class="input p-2" accept="image/*">
                    </div>
                    <x-textarea name="catatan" label="Catatan Tambahan" rows="2" :value="$peserta_didik->catatan" />
                </div>

                <div class="flex justify-end gap-3 mt-6 pt-6 border-t">
                    <a href="{{ route('peserta-didik.index') }}" class="btn-secondary">Batal</a>
                    <button type="submit" class="btn-primary">Perbarui Data</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>

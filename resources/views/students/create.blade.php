<x-app-layout>
    <x-slot name="title">Tambah Peserta Didik</x-slot>

    <div class="max-w-4xl mx-auto space-y-6">
        <x-breadcrumb :items="[
            ['label' => 'Peserta Didik', 'url' => route('peserta-didik.index')],
            ['label' => 'Tambah Data']
        ]" />

        <div class="card p-6">
            <form action="{{ route('peserta-didik.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                @csrf
                
                <h3 class="text-lg font-semibold text-navy border-b pb-2">Identitas Utama</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <x-input name="nis" label="NIS *" required />
                    <x-input name="nisn" label="NISN" />
                    <x-input name="nik" label="NIK *" required />
                    <x-input name="nama_lengkap" label="Nama Lengkap *" required />
                    <x-input name="nama_panggilan" label="Nama Panggilan" />
                    <x-select name="jenis_kelamin" label="Jenis Kelamin *" :options="['Laki-laki' => 'Laki-laki', 'Perempuan' => 'Perempuan']" required />
                    <x-input name="tempat_lahir" label="Tempat Lahir" />
                    <x-input type="date" name="tanggal_lahir" label="Tanggal Lahir" />
                    <x-select name="agama" label="Agama" :options="['Islam'=>'Islam','Kristen'=>'Kristen','Katolik'=>'Katolik','Hindu'=>'Hindu','Buddha'=>'Buddha','Konghucu'=>'Konghucu']" />
                    <x-select name="status_perkawinan" label="Status Perkawinan" :options="['Belum Kawin'=>'Belum Kawin','Kawin'=>'Kawin','Cerai Hidup'=>'Cerai Hidup','Cerai Mati'=>'Cerai Mati']" />
                </div>

                <h3 class="text-lg font-semibold text-navy border-b pb-2 mt-6">Data Pendidikan</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <x-select name="program_id" label="Program *" :options="$programs->pluck('nama', 'id')->toArray()" required />
                    <x-select name="tahun_ajaran_id" label="Tahun Ajaran Masuk *" :options="$academicYears->pluck('nama', 'id')->toArray()" required />
                    <x-select name="rombel_id" label="Rombel / Kelas" :options="$classrooms->pluck('nama_rombel', 'id')->toArray()" />
                    <x-select name="status" label="Status *" :options="['aktif'=>'Aktif','lulus'=>'Lulus','keluar'=>'Keluar','pindah'=>'Pindah','nonaktif'=>'Nonaktif']" required />
                </div>

                <h3 class="text-lg font-semibold text-navy border-b pb-2 mt-6">Kontak & Alamat</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="md:col-span-2">
                        <x-textarea name="alamat" label="Alamat Lengkap *" required />
                    </div>
                    <x-input name="rt" label="RT" />
                    <x-input name="rw" label="RW" />
                    <x-input name="kelurahan" label="Kelurahan / Desa" />
                    <x-input name="kecamatan" label="Kecamatan" />
                    <x-input name="kabupaten" label="Kabupaten / Kota" />
                    <x-input name="provinsi" label="Provinsi" />
                    <x-input name="no_hp" label="No. Handphone" />
                    <x-input type="email" name="email" label="Email" />
                </div>

                <h3 class="text-lg font-semibold text-navy border-b pb-2 mt-6">Data Keluarga (Opsional)</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <x-input name="nama_ayah" label="Nama Ayah" />
                    <x-input name="nama_ibu" label="Nama Ibu" />
                    <x-input name="nama_wali" label="Nama Wali" />
                    <x-input name="no_hp_wali" label="No. HP Wali" />
                    <div class="md:col-span-2">
                        <x-textarea name="alamat_wali" label="Alamat Wali" rows="2" />
                    </div>
                </div>

                <h3 class="text-lg font-semibold text-navy border-b pb-2 mt-6">Lainnya</h3>
                <div class="grid grid-cols-1 gap-4">
                    <div>
                        <label class="label">Foto Profil</label>
                        <input type="file" name="foto" class="input p-2" accept="image/*">
                    </div>
                    <x-textarea name="catatan" label="Catatan Tambahan" rows="2" />
                </div>

                <div class="flex justify-end gap-3 mt-6 pt-6 border-t">
                    <a href="{{ route('peserta-didik.index') }}" class="btn-secondary">Batal</a>
                    <button type="submit" class="btn-primary">Simpan Data</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>

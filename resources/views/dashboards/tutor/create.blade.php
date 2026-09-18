<x-app-layout>
    <x-slot name="title">Tambah Tutor</x-slot>

    <div class="max-w-7xl mx-auto space-y-6">
        <x-breadcrumb :items="[
            ['label' => 'Data Tutor', 'url' => route('tutors.index')],
            ['label' => 'Tambah Data']
        ]" />

        <div class="card p-6">
            <form action="{{ route('tutors.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                @csrf
                
                <h3 class="text-lg font-semibold text-navy border-b pb-2">Identitas Utama</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <x-input name="nomor_induk" label="Nomor Induk *" required />
                    <x-input name="nik" label="NIK" />
                    <x-input name="nama_lengkap" label="Nama Lengkap *" required />
                    <x-input name="nama_panggilan" label="Nama Panggilan" />
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <x-select name="jenis_kelamin" label="Jenis Kelamin *" :options="['Laki-laki' => 'Laki-laki', 'Perempuan' => 'Perempuan']" required />
                    <x-input name="tempat_lahir" label="Tempat Lahir" />
                    <x-input type="date" name="tanggal_lahir" label="Tanggal Lahir" />
                    <x-select name="agama" label="Agama" :options="['Islam'=>'Islam','Kristen'=>'Kristen','Katolik'=>'Katolik','Hindu'=>'Hindu','Buddha'=>'Buddha','Konghucu'=>'Konghucu']" />
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <x-textarea name="alamat" label="Alamat Lengkap *" required />
                    <x-input name="no_hp" label="No. Handphone" />
                    <x-input type="email" name="email" label="Email" />
                    <x-input name="pendidikan_terakhir" label="Pendidikan Terakhir" />
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <x-input name="bidang_keahlian" label="Bidang Keahlian" />
                    <x-select name="status_tutor" label="Status *" :options="['aktif'=>'Aktif','tidak_aktif'=>'Tidak Aktif']" required />
                    <x-input name="tanggal_mulai_mengajar" label="Tanggal Mulai Mengajar" />
                    <div class="md:col-span-2">
                        <x-input name="foto" label="Foto Profil" type="file" accept="image/*" />
                    </div>
                </div>

                <div>
                    <x-textarea name="catatan" label="Catatan Tambahan" rows="3" />
                </div>

                <div class="flex justify-end gap-3 mt-6 pt-6 border-t">
                    <a href="{{ route('tutors.index') }}" class="btn-secondary">Batal</a>
                    <button type="submit" class="btn-primary">Simpan Data</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
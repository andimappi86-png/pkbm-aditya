<x-app-layout>
    <x-slot name="title">Edit Tutor</x-slot>

    <div class="max-w-7xl mx-auto space-y-6">
        <x-breadcrumb :items="[
            ['label' => 'Data Tutor', 'url' => route('tutors.index')],
            ['label' => 'Edit Data']
        ]" />

        <div class="card p-6">
            <form action="{{ route('tutors.update', $tutor->id) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                @csrf
                @method('PUT')
                
                <h3 class="text-lg font-semibold text-navy border-b pb-2">Identitas Utama</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <x-input name="nomor_induk" label="Nomor Induk *" :value="$tutor->nomor_induk" required />
                    <x-input name="nik" label="NIK" :value="$tutor->nik" />
                    <x-input name="nama_lengkap" label="Nama Lengkap *" :value="$tutor->nama_lengkap" required />
                    <x-input name="nama_panggilan" label="Nama Panggilan" :value="$tutor->nama_panggilan" />
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <x-select name="jenis_kelamin" label="Jenis Kelamin *" :options="['Laki-laki' => 'Laki-laki', 'Perempuan' => 'Perempuan']" :selected="$tutor->jenis_kelamin" required />
                    <x-input name="tempat_lahir" label="Tempat Lahir" :value="$tutor->tempat_lahir" />
                    <x-input type="date" name="tanggal_lahir" label="Tanggal Lahir" :value="$tutor->tanggal_lahir?->format('Y-m-d')" />
                    <x-select name="agama" label="Agama" :options="['Islam'=>'Islam','Kristen'=>'Kristen','Katolik'=>'Katolik','Hindu'=>'Hindu','Buddha'=>'Buddha','Konghucu'=>'Konghucu']" :selected="$tutor->agama" />
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <x-textarea name="alamat" label="Alamat Lengkap *" :value="$tutor->alamat" required />
                    <x-input name="no_hp" label="No. Handphone" :value="$tutor->no_hp" />
                    <x-input type="email" name="email" label="Email" :value="$tutor->email" />
                    <x-input name="pendidikan_terakhir" label="Pendidikan Terakhir" :value="$tutor->pendidikan_terakhir" />
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <x-input name="bidang_keahlian" label="Bidang Keahlian" :value="$tutor->bidang_keahlian" />
                    <x-select name="status_tutor" label="Status *" :options="['aktif'=>'Aktif','tidak_aktif'=>'Tidak Aktif']" :selected="$tutor->status_tutor" required />
                    <x-input name="tanggal_mulai_mengajar" label="Tanggal Mulai Mengajar" :value="$tutor->tanggal_mulai_mengahar?->format('Y-m-d')" />
                    <div class="md:col-span-2">
                        <x-input name="foto" label="Foto Profil (Biarkan kosong jika tidak ingin mengubah)" type="file" accept="image/*" />
                    </div>
                </div>

                <div>
                    <x-textarea name="catatan" label="Catatan Tambahan" rows="3" :value="$tutor->catatan" />
                </div>

                <div class="flex justify-end gap-3 mt-6 pt-6 border-t">
                    <a href="{{ route('tutors.index') }}" class="btn-secondary">Batal</a>
                    <button type="submit" class="btn-primary">Perbarui Data</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
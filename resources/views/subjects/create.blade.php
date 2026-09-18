<x-app-layout>
    <x-slot name="title">Tambah Mata Pelajaran</x-slot>

    <div class="max-w-2xl mx-auto">
        <x-breadcrumb :items="[
            ['label' => 'Mata Pelajaran', 'href' => route('mata-pelajaran.index')],
            ['label' => 'Tambah Mata Pelajaran']
        ]" />

        <div class="card p-6">
            <form method="POST" action="{{ route('mata-pelajaran.store') }}" class="space-y-6">
                @csrf

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="label">Kode Mata Pelajaran</label>
                        <input type="text" name="kode" value="{{ old('kode') }}" placeholder="Misalnya: MTK, BIND, IPA" class="input @error('kode') input-error @enderror">
                        @error('kode')
                        <p class="input-error">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="label">Nama Mata Pelajaran</label>
                        <input type="text" name="nama" value="{{ old('nama') }}" placeholder="Nama lengkap mata pelajaran" class="input @error('nama') input-error @enderror">
                        @error('nama')
                        <p class="input-error">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div>
                    <label class="label">Deskripsi</label>
                    <textarea name="deskripsi" rows="3" placeholder="Deskripsi singkat mata pelajaran" class="input @error('deskripsi') input-error @enderror">{{ old('deskripsi') }}</textarea>
                    @error('deskripsi')
                    <p class="input-error">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-start">
                    <div class="flex items-center h-5">
                        <input id="status_aktif" name="status_aktif" type="checkbox" {{ old('status_aktif') ? 'checked' : '' }} class="h-4 w-4 text-primary focus:ring-primary border-border rounded">
                    </div>
                    <div class="ml-3">
                        <label for="status_aktif" class="label-text">Aktif</label>
                    </div>
                </div>

                <div class="flex justify-end pt-4">
                    <a href="{{ route('mata-pelajaran.index') }}" class="btn-secondary mr-2">Batal</a>
                    <button type="submit" class="btn-primary">
                        <x-icon name="plus" class="w-4 h-4 mr-2" />
                        Simpan Mata Pelajaran
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>

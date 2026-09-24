<x-app-layout>
    <x-slot name="title">Buat Rombel Baru</x-slot>

    <div class="max-w-2xl mx-auto">
        <x-breadcrumb :items="[
            ['label' => 'Rombel', 'url' => route('rombel.index')],
            ['label' => 'Tambah Rombel']
        ]" />

        <div class="card p-6">
            <form method="POST" action="{{ route('rombel.store') }}" class="space-y-6">
                @csrf

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="label">Nama Rombel</label>
                        <input type="text" name="nama_rombel" placeholder="Misalnya: X IPA, VII B" class="input @error('nama_rombel') input-error @enderror">
                        @error('nama_rombel')
                        <p class="input-error">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="label">Kode Rombel</label>
                        <input type="text" name="kode_rombel" placeholder="Misalnya: X-IPA, VII-B" class="input @error('kode_rombel') input-error @enderror">
                        @error('kode_rombel')
                        <p class="input-error">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div>
                    <label class="label">Program</label>
                    <select name="program_id" class="input @error('program_id') input-error @enderror">
                        <option value="">-- Pilih Program --</option>
                        @foreach($programs as $program)
                            <option value="{{ $program->id }}" {{ old('program_id') == $program->id ? 'selected' : '' }}>
                                {{ $program->nama }}
                            </option>
                        @endforeach
                    </select>
                    @error('program_id')
                    <p class="input-error">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="label">Tahun Ajaran</label>
                    <select name="tahun_ajaran_id" class="input @error('tahun_ajaran_id') input-error @enderror">
                        <option value="">-- Pilih Tahun Ajaran --</option>
                        @foreach($academicYears as $year)
                                                <option value="{{ $year->id }}" {{ old('tahun_ajaran_id') == $year->id ? 'selected' : '' }}>
                                                    {{ $year->nama }}
                                                </option>
                                            @endforeach
                    </select>
                    @error('tahun_ajaran_id')
                    <p class="input-error">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="label">Wali Kelas</label>
                    <input type="text" name="wali_kelas_name" placeholder="Masukkan nama wali kelas..." class="input @error('wali_kelas_name') input-error @enderror" value="{{ old('wali_kelas_name') }}">
                    @error('wali_kelas_name')
                        <p class="input-error">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="label">Status Aktif</label>
                    <div class="flex items-center">
                        <input id="status_aktif" name="status_aktif" type="checkbox" {{ old('status_aktif') ? 'checked' : '' }} class="h-4 w-4 text-primary focus:ring-primary border-border rounded">
                        <label for="status_aktif" class="label-text ml-3">Aktif</label>
                    </div>
                </div>

                <div class="flex justify-end pt-4">
                    <a href="{{ route('rombel.index') }}" class="btn-secondary mr-2">Batal</a>
                    <button type="submit" class="btn-primary">
                        <x-icon name="plus" class="w-4 h-4 mr-2" />
                        Buat Rombel
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
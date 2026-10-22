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

                <div class="mt-6 relative" x-data="{
                    query: '{{ old('wali_kelas_name') }}',
                    results: [],
                    open: false,
                    loading: false,
                    async search() {
                        if (this.query.length < 1) { this.results = []; this.open = false; return; }
                        this.loading = true;
                        try {
                            const res = await fetch(`{{ route('rombel.search-wali-kelas') }}?q=${encodeURIComponent(this.query)}`);
                            this.results = await res.json();
                            this.open = true;
                        } finally {
                            this.loading = false;
                        }
                    },
                    select(user) {
                        this.query = user.name;
                        document.getElementById('wali_kelas_id').value = user.id;
                        this.open = false;
                    }
                }">
                    <label class="label" for="wali_kelas_name">Wali Kelas</label>
                    <input type="text" x-model="query" @input.debounce.300ms="search()" name="wali_kelas_name" id="wali_kelas_name" placeholder="Ketik nama wali kelas..." class="input @error('wali_kelas_name') input-error @enderror" autocomplete="off">
                    <input type="hidden" name="wali_kelas_id" id="wali_kelas_id" value="{{ old('wali_kelas_id') }}">

                    <ul x-show="open" @click.outside="open = false" class="absolute z-10 w-full bg-white border border-[#D9E2E5] rounded-lg shadow-lg mt-1 max-h-60 overflow-y-auto" style="display: none;">
                        <template x-for="user in results" :key="user.id">
                            <li @click="select(user)" class="px-4 py-2 hover:bg-gray-100 cursor-pointer text-sm">
                                <span x-text="user.name"></span>
                            </li>
                        </template>
                        <li x-show="results.length === 0 && !loading" class="px-4 py-2 text-sm text-muted">Tidak ada wali kelas yang cocok</li>
                    </ul>

                    @error('wali_kelas_name')
                        <p class="input-error">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="label">Status Aktif</label>
                    <div class="flex items-center">
                        <input id="status_aktif" name="status_aktif" type="checkbox" value="1" {{ old('status_aktif', true) ? 'checked' : '' }} class="h-4 w-4 text-primary focus:ring-primary border-border rounded">
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
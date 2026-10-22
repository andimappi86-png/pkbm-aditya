<x-app-layout title="Edit Rombel">
    <div class="max-w-4xl mx-auto space-y-6">
        <x-breadcrumb :items="[
            ['label' => 'Rombel', 'href' => route('rombel.index')],
            ['label' => $classroom->nama_rombel, 'href' => route('rombel.show', ['classroom' => $classroom->id])],
            ['label' => 'Edit']
        ]" />

        <div class="bg-white border border-[#D9E2E5] rounded-xl shadow-sm p-6">
            <h2 class="text-xl font-bold text-[#172B3A] mb-6 border-b border-[#D9E2E5] pb-4">Edit Rombel</h2>
            <form method="POST" action="{{ route('rombel.update', ['classroom' => $classroom]) }}" class="space-y-6">
                @csrf
                @method('PUT')

                <!-- Section 1: Informasi Rombel -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="nama_rombel" class="block text-sm font-semibold text-[#172B3A] mb-1">Nama Rombel *</label>
                        <input type="text" name="nama_rombel" id="nama_rombel" value="{{ old('nama_rombel', $classroom->nama_rombel) }}" placeholder="Misalnya: X IPA, VII B" class="w-full px-3 py-2 border {{ $errors->has('nama_rombel') ? 'border-[#C62828]' : 'border-[#D9E2E5]' }} rounded-lg focus:ring-[#167C80] focus:border-[#167C80]" required>
                    </div>
                    <div>
                        <label for="kode_rombel" class="block text-sm font-semibold text-[#172B3A] mb-1">Kode Rombel *</label>
                        <input type="text" name="kode_rombel" id="kode_rombel" value="{{ old('kode_rombel', $classroom->kode_rombel) }}" placeholder="Misalnya: X-IPA, VII-B" class="w-full px-3 py-2 border {{ $errors->has('kode_rombel') ? 'border-[#C62828]' : 'border-[#D9E2E5]' }} rounded-lg focus:ring-[#167C80] focus:border-[#167C80]" required>
                    </div>
                </div>

                <!-- Section 2: Akademik -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="program_id" class="block text-sm font-semibold text-[#172B3A] mb-1">Program *</label>
                        <select name="program_id" id="program_id" class="w-full px-3 py-2 border border-[#D9E2E5] rounded-lg focus:ring-[#167C80] focus:border-[#167C80]" required>
                            <option value="">-- Pilih Program --</option>
                            @foreach($programs as $program)
                                <option value="{{ $program->id }}" {{ old('program_id') == $program->id || ($classroom->program_id && $classroom->program_id == $program->id) ? 'selected' : '' }}>
                                    {{ $program->nama }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="tahun_ajaran_id" class="block text-sm font-semibold text-[#172B3A] mb-1">Tahun Ajaran *</label>
                        <select name="tahun_ajaran_id" id="tahun_ajaran_id" class="w-full px-3 py-2 border border-[#D9E2E5] rounded-lg focus:ring-[#167C80] focus:border-[#167C80]" required>
                            <option value="">-- Pilih Tahun Ajaran --</option>
                            @foreach($academicYears as $year)

                                <option value="{{ $year->id }}" {{ old('tahun_ajaran_id') == $year->id || ($classroom->tahun_ajaran_id && $classroom->tahun_ajaran_id == $year->id) ? 'selected' : '' }}>
                                    {{ $year->nama }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Section 3: Wali Kelas (Manual Input) -->
                <div class="mt-6 relative" x-data="{
                    query: '{{ old('wali_kelas_name', $classroom->waliKelas?->name ?? '') }}',
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
                    <label for="wali_kelas_name" class="block text-sm font-semibold text-[#172B3A] mb-1">Wali Kelas (opsional)</label>
                    <input type="text" x-model="query" @input.debounce.300ms="search()" id="wali_kelas_name" name="wali_kelas_name" placeholder="Ketik nama wali kelas..." class="w-full px-3 py-2 border {{ $errors->has('wali_kelas_name') ? 'border-[#C62828]' : 'border-[#D9E2E5]' }} rounded-lg focus:ring-[#167C80] focus:border-[#167C80]" autocomplete="off">
                    <input type="hidden" name="wali_kelas_id" id="wali_kelas_id" value="{{ old('wali_kelas_id', $classroom->wali_kelas_id) }}">

                    <ul x-show="open" @click.outside="open = false" class="absolute z-10 w-full bg-white border border-[#D9E2E5] rounded-lg shadow-lg mt-1 max-h-60 overflow-y-auto" style="display: none;">
                        <template x-for="user in results" :key="user.id">
                            <li @click="select(user)" class="px-4 py-2 hover:bg-gray-100 cursor-pointer text-sm">
                                <span x-text="user.name"></span>
                            </li>
                        </template>
                        <li x-show="results.length === 0 && !loading" class="px-4 py-2 text-sm text-muted">Tidak ada wali kelas yang cocok</li>
                    </ul>
                    @error('wali_kelas_name')
                        <p class="text-[#C62828] text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Section 4: Status -->
                <div class="mt-6">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="status_aktif" value="1" {{ old('status_aktif', $classroom->status_aktif ? true : false) ? 'checked' : '' }} class="w-4 h-4 text-[#167C80] rounded">
                        <span class="text-sm font-semibold text-[#172B3A]">Aktif</span>
                    </label>
                </div>

                <div class="flex justify-end pt-4 border-t border-[#D9E2E5]">
                    <a href="{{ route('rombel.index') }}" class="px-6 py-2.5 border border-[#D9E2E5] text-[#172B3A] rounded-lg font-medium hover:bg-[#F5F7F8] transition-colors mr-3">Batal</a>
                    <button type="submit" class="inline-flex items-center gap-2 px-6 py-2.5 bg-[#167C80] text-white rounded-lg font-medium hover:bg-[#0F5F63] transition-colors">
                        <x-icon name="check" class="w-4 h-4" />
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
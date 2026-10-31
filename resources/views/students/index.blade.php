<x-app-layout>
    <x-slot name="title">Daftar Peserta Didik</x-slot>

    <div x-data="{
        showAddModal: {{ $errors->any() ? 'true' : 'false' }},
        selectedClassroomId: '{{ $classrooms->count() === 1 ? $classrooms->first()->id : old('rombel_id', '') }}',
        tempNisn: '{{ old('nisn', '') }}',
        generateTempNisn() {
            this.tempNisn = '99' + Math.floor(10000000 + Math.random() * 90000000);
        }
    }" class="max-w-7xl mx-auto space-y-4">
        <!-- Header & Action -->
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <h1 class="text-xl font-bold text-navy">Daftar Peserta Didik</h1>
                <p class="text-sm text-muted">Kelola data peserta didik PKBM dan informasi akademiknya.</p>
            </div>
            @can('students.create')
            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('peserta-didik.template') }}" class="btn-secondary text-sm">Template</a>
                <a href="{{ route('peserta-didik.import') }}" class="btn-secondary text-sm">Import</a>
                <a href="{{ route('peserta-didik.bulk-assign') }}" class="btn-secondary text-sm">Penempatan Rombel</a>
                <a href="{{ route('peserta-didik.create') }}" class="btn-primary text-sm">+ Tambah Peserta Didik</a>
            </div>
            @endcan

            @can('students.manage')
            <a href="{{ route('peserta-didik.link-accounts') }}" class="btn-secondary text-sm">Penautan Akun Siswa</a>
            @endcan
        </div>

        <!-- Statistics -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="card p-4 border-l-4 border-primary">
                <div class="text-xs font-bold text-muted uppercase">Total Peserta</div>
                <div class="text-xl font-bold text-navy">{{ $stats['total'] }}</div>
            </div>
            <div class="card p-4 border-l-4 border-emerald-500">
                <div class="text-xs font-bold text-muted uppercase">Aktif</div>
                <div class="text-xl font-bold text-navy">{{ $stats['aktif'] }}</div>
            </div>
            <div class="card p-4 border-l-4 border-amber-500">
                <div class="text-xs font-bold text-muted uppercase">Tidak Aktif</div>
                <div class="text-xl font-bold text-navy">{{ $stats['nonaktif'] }}</div>
            </div>
        </div>

        <!-- Filter & Aksi -->
        <div class="card p-4">
            <div class="flex flex-col lg:flex-row lg:items-end justify-between gap-3">
                <form method="GET" action="{{ route('peserta-didik.index') }}" class="flex-1 grid grid-cols-1 sm:grid-cols-12 gap-3 items-end">
                    <div class="sm:col-span-6">
                        <label class="block text-xs font-bold text-muted uppercase mb-1">Pencarian</label>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari Nama/NIS/NISN/NIK..." class="input w-full">
                    </div>
                    <div class="sm:col-span-3">
                        <label class="block text-xs font-bold text-muted uppercase mb-1">Program</label>
                        <x-select name="program_id" :options="$programs->pluck('nama', 'id')->toArray()" :selected="request('program_id')" placeholder="Semua Program" />
                    </div>
                    <div class="sm:col-span-3 flex gap-2">
                        <button type="submit" class="btn-primary flex-1">Filter</button>
                        <a href="{{ route('peserta-didik.index') }}" class="btn-secondary">Reset</a>
                    </div>
                </form>

                @if($classrooms->isNotEmpty())
                <div class="shrink-0 flex items-end">
                    <button type="button"
                            @click="showAddModal = true"
                            class="w-full sm:w-auto bg-[#14532D] hover:bg-[#0D2618] text-white px-4 py-2.5 rounded-xl text-xs sm:text-sm font-semibold inline-flex items-center justify-center gap-2 whitespace-nowrap shadow-xs transition-colors">
                        <x-icon name="user-plus" class="w-4 h-4" />
                        <span>+ Tambah Siswa</span>
                    </button>
                </div>
                @endif
            </div>
        </div>

        @if($classrooms->isNotEmpty())
        <!-- MODAL FORM + TAMBAH SISWA -->
        <div x-show="showAddModal"
             x-cloak
             class="fixed inset-0 z-50 overflow-y-auto"
             aria-labelledby="modal-title"
             role="dialog"
             aria-modal="true">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <!-- Backdrop -->
                <div x-show="showAddModal"
                     x-transition:enter="ease-out duration-200"
                     x-transition:enter-start="opacity-0"
                     x-transition:enter-end="opacity-100"
                     x-transition:leave="ease-in duration-150"
                     x-transition:leave-start="opacity-100"
                     x-transition:leave-end="opacity-0"
                     class="fixed inset-0 transition-opacity bg-slate-900/50 backdrop-blur-xs"
                     @click="showAddModal = false"
                     aria-hidden="true"></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <!-- Dialog Content -->
                <div x-show="showAddModal"
                     x-transition:enter="ease-out duration-200"
                     x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave="ease-in duration-150"
                     x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     class="inline-block w-full max-w-xl text-left align-bottom transition-all transform bg-white rounded-xl shadow-lg sm:my-8 sm:align-middle border border-slate-200 max-h-[90vh] overflow-hidden flex flex-col">

                    <!-- Header -->
                    <div class="px-5 py-4 bg-slate-50 border-b border-slate-200 flex items-center justify-between shrink-0">
                        <div class="flex items-center gap-2.5">
                            <div class="w-8 h-8 rounded-lg bg-[#14532D]/10 text-[#14532D] flex items-center justify-center shrink-0">
                                <x-icon name="user-plus" class="w-4 h-4" />
                            </div>
                            <div>
                                <h3 class="text-sm font-bold text-slate-900">Tambah Peserta Didik Baru</h3>
                                <p class="text-xs text-slate-500">Daftarkan peserta didik langsung ke rombel kelolaan.</p>
                            </div>
                        </div>
                        <button type="button"
                                @click="showAddModal = false"
                                class="p-1 text-slate-400 hover:text-slate-600 rounded-lg hover:bg-slate-100 transition-colors">
                            <x-icon name="x" class="w-5 h-5" />
                        </button>
                    </div>

                    <!-- Form Body -->
                    <form method="POST" action="{{ route('peserta-didik.quick-store') }}" class="flex flex-col flex-1 overflow-hidden">
                        @csrf
                        <input type="hidden" name="redirect_to" value="{{ route('peserta-didik.index') }}">

                        <div class="p-5 overflow-y-auto flex-1 space-y-4 text-xs sm:text-sm">
                            @if($errors->any())
                            <div class="p-3 bg-red-50 border border-red-200 text-red-700 rounded-lg text-xs space-y-1">
                                <div class="font-semibold">Peringatan Validasi:</div>
                                <ul class="list-disc list-inside">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                            @endif

                            <!-- Kelas / Rombel -->
                            <div>
                                <label class="block text-xs font-semibold text-slate-800 mb-1">
                                    Kelas / Rombel <span class="text-red-500">*</span>
                                </label>
                                <select name="rombel_id"
                                        x-model="selectedClassroomId"
                                        required
                                        class="w-full px-3 py-2 text-xs rounded-lg border border-slate-300 focus:outline-hidden focus:border-[#14532D] focus:ring-1 focus:ring-[#14532D]">
                                    @if($classrooms->count() > 1)
                                        <option value="">-- Pilih Kelas / Rombel --</option>
                                    @endif
                                    @foreach($classrooms as $cls)
                                        <option value="{{ $cls->id }}">{{ $cls->nama_rombel }} ({{ $cls->program->nama ?? 'Program' }})</option>
                                    @endforeach
                                </select>
                                <p class="text-[11px] text-slate-400 mt-1">
                                    @if($classrooms->count() === 1)
                                        Terpilih otomatis: Rombel ampu Anda.
                                    @else
                                        Pilih rombel tujuan penempatan peserta didik.
                                    @endif
                                </p>
                            </div>

                            <!-- Nama Lengkap -->
                            <div>
                                <label class="block text-xs font-semibold text-slate-800 mb-1">
                                    Nama Lengkap <span class="text-red-500">*</span>
                                </label>
                                <input type="text"
                                       name="nama_lengkap"
                                       value="{{ old('nama_lengkap') }}"
                                       required
                                       maxlength="150"
                                       placeholder="Contoh: Muhammad Ilham"
                                       class="w-full px-3 py-2 text-xs rounded-lg border border-slate-300 focus:outline-hidden focus:border-[#14532D] focus:ring-1 focus:ring-[#14532D]" />
                            </div>

                            <!-- NISN / Nomor Induk -->
                            <div>
                                <div class="flex items-center justify-between mb-1">
                                    <label class="block text-xs font-semibold text-slate-800">
                                        NISN / Nomor Induk <span class="text-red-500">*</span>
                                    </label>
                                    <button type="button"
                                            @click="generateTempNisn()"
                                            class="text-[11px] font-semibold text-[#14532D] hover:text-[#0D2618] underline flex items-center gap-1">
                                        <x-icon name="sparkles" class="w-3 h-3" />
                                        <span>Buat Nomor Sementara</span>
                                    </button>
                                </div>
                                <input type="text"
                                       name="nisn"
                                       x-model="tempNisn"
                                       required
                                       maxlength="30"
                                       placeholder="10 digit NISN atau nomor induk siswa"
                                       class="w-full px-3 py-2 text-xs font-mono rounded-lg border border-slate-300 focus:outline-hidden focus:border-[#14532D] focus:ring-1 focus:ring-[#14532D] tabular-nums" />
                                <p class="text-[11px] text-slate-400 mt-1">Gunakan nomor NISN Dapodik atau klik "Buat Nomor Sementara" jika belum terbit.</p>
                            </div>

                            <!-- NIK (Opsional) -->
                            <div>
                                <label class="block text-xs font-semibold text-slate-800 mb-1">
                                    NIK (Jika Diperlukan / Opsional)
                                </label>
                                <input type="text"
                                       name="nik"
                                       value="{{ old('nik') }}"
                                       maxlength="16"
                                       placeholder="16 digit NIK KTP / Kartu Keluarga"
                                       class="w-full px-3 py-2 text-xs font-mono rounded-lg border border-slate-300 focus:outline-hidden focus:border-[#14532D] focus:ring-1 focus:ring-[#14532D] tabular-nums" />
                                <p class="text-[11px] text-slate-400 mt-1">Kosongkan jika belum tersedia. Validasi keunikan akan diterapkan jika diisi.</p>
                            </div>

                            <!-- Jenis Kelamin -->
                            <div>
                                <label class="block text-xs font-semibold text-slate-800 mb-1">
                                    Jenis Kelamin <span class="text-red-500">*</span>
                                </label>
                                <div class="grid grid-cols-2 gap-2">
                                    <label class="flex items-center gap-2 p-2.5 border border-slate-300 rounded-lg cursor-pointer hover:bg-slate-50 transition-colors">
                                        <input type="radio" name="jenis_kelamin" value="L" {{ old('jenis_kelamin', 'L') === 'L' ? 'checked' : '' }} class="text-[#14532D] focus:ring-[#14532D]" />
                                        <span class="text-xs font-medium text-slate-800">Laki-laki (L)</span>
                                    </label>
                                    <label class="flex items-center gap-2 p-2.5 border border-slate-300 rounded-lg cursor-pointer hover:bg-slate-50 transition-colors">
                                        <input type="radio" name="jenis_kelamin" value="P" {{ old('jenis_kelamin') === 'P' ? 'checked' : '' }} class="text-[#14532D] focus:ring-[#14532D]" />
                                        <span class="text-xs font-medium text-slate-800">Perempuan (P)</span>
                                    </label>
                                </div>
                            </div>

                            <!-- Status Masuk & WhatsApp -->
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-xs font-semibold text-slate-800 mb-1">
                                        Status Masuk
                                    </label>
                                    <select name="status_masuk" class="w-full px-3 py-2 text-xs rounded-lg border border-slate-300 focus:outline-hidden focus:border-[#14532D] focus:ring-1 focus:ring-[#14532D]">
                                        <option value="Siswa Baru" {{ old('status_masuk') === 'Siswa Baru' ? 'selected' : '' }}>Siswa Baru</option>
                                        <option value="Pindahan / Mutasi Masuk" {{ old('status_masuk') === 'Pindahan / Mutasi Masuk' ? 'selected' : '' }}>Pindahan / Mutasi Masuk</option>
                                        <option value="Warga Belajar Kembali" {{ old('status_masuk') === 'Warga Belajar Kembali' ? 'selected' : '' }}>Warga Belajar Kembali</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-slate-800 mb-1">
                                        No. WhatsApp (Opsional)
                                    </label>
                                    <input type="tel"
                                           name="no_hp"
                                           value="{{ old('no_hp') }}"
                                           maxlength="30"
                                           placeholder="Contoh: 08123456789"
                                           class="w-full px-3 py-2 text-xs rounded-lg border border-slate-300 focus:outline-hidden focus:border-[#14532D] focus:ring-1 focus:ring-[#14532D]" />
                                </div>
                            </div>
                        </div>

                        <!-- Footer Actions -->
                        <div class="px-5 py-3 bg-slate-50 border-t border-slate-200 flex items-center justify-end gap-2 shrink-0">
                            <button type="button"
                                    @click="showAddModal = false"
                                    class="px-3.5 py-2 text-xs font-medium text-slate-700 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 transition-colors whitespace-nowrap">
                                Batal
                            </button>
                            <button type="submit"
                                    class="px-4 py-2 text-xs font-semibold text-white bg-[#14532D] hover:bg-[#0D2618] rounded-lg transition-colors whitespace-nowrap shadow-xs inline-flex items-center gap-1.5">
                                <x-icon name="check" class="w-4 h-4" />
                                <span>Simpan & Tempatkan ke Kelas</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        @endif

        <!-- Table -->
        <div class="card p-0 overflow-hidden">
            <div class="table-shell overflow-x-auto">
                <table class="min-w-full divide-y divide-border">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-bold text-muted uppercase">NIS/NISN</th>
                            <th class="px-6 py-3 text-left text-xs font-bold text-muted uppercase">Nama Lengkap</th>
                            <th class="px-6 py-3 text-left text-xs font-bold text-muted uppercase">Program</th>
                            <th class="px-6 py-3 text-left text-xs font-bold text-muted uppercase">Status</th>
                            <th class="px-6 py-3 text-right text-xs font-bold text-muted uppercase">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border">
                        @forelse($students as $student)
                        <tr class="hover:bg-primary-soft/20 transition-colors">
                            <td class="px-6 py-4">
                                <div class="font-medium text-navy">{{ $student->nis }}</div>
                                <div class="text-xs text-muted">{{ $student->nisn ?? '-' }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="font-medium text-navy">{{ $student->nama_lengkap }}</div>
                                <div class="text-xs text-muted">{{ $student->nik }}</div>
                            </td>
                            <td class="px-6 py-4 text-sm text-navy">{{ $student->program->nama ?? '-' }}</td>
                            <td class="px-6 py-4">
                                <x-badge variant="{{ $student->status == 'aktif' ? 'success' : 'warning' }}">
                                    {{ ucfirst($student->status) }}
                                </x-badge>
                            </td>
                            <td class="px-6 py-4 text-right space-x-2">
                                <a href="{{ route('peserta-didik.show', $student->id) }}" class="text-primary hover:text-primary-dark">Lihat</a>
                                @can('students.update')
                                <a href="{{ route('peserta-didik.edit', $student->id) }}" class="text-primary hover:text-primary-dark">Edit</a>
                                @endcan
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-12 text-muted">Belum ada data peserta didik yang sesuai.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($students->hasPages())
            <div class="px-6 py-4 border-t border-border bg-slate-50">
                {{ $students->links() }}
            </div>
            @endif
        </div>
    </div>
</x-app-layout>

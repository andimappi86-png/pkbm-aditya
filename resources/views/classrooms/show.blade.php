<x-app-layout>
    <x-slot name="title">{{ $classroom->nama_rombel }}</x-slot>

    <div class="w-full space-y-6" x-data="{
        activeMainTab: 'anggota',
        showAddModal: false,
        addMethod: 'pilih',
        confirmRemoveModal: false,
        studentToRemove: null,
        removeActionUrl: '',

        // Pilih Siswa State
        searchUnassigned: '',
        selectedStudentIds: [],
        unassignedList: {{ Js::from($unassignedStudents->map(fn($s) => [
            'id' => $s->id,
            'nis' => $s->nis ?? '-',
            'nama_lengkap' => $s->nama_lengkap,
            'jenis_kelamin' => $s->jenis_kelamin,
            'program' => $s->program?->nama ?? '-',
            'tahun_ajaran' => $s->academicYear?->nama ?? '-',
        ])) }},

        get filteredUnassigned() {
            if (!this.searchUnassigned.trim()) {
                return this.unassignedList;
            }
            const q = this.searchUnassigned.toLowerCase();
            return this.unassignedList.filter(s =>
                s.nama_lengkap.toLowerCase().includes(q) ||
                (s.nis && s.nis.toLowerCase().includes(q))
            );
        },

        toggleSelectAllUnassigned() {
            if (this.selectedStudentIds.length === this.filteredUnassigned.length) {
                this.selectedStudentIds = [];
            } else {
                this.selectedStudentIds = this.filteredUnassigned.map(s => s.id);
            }
        },

        // Bulk Add State
        bulkRawText: '',
        bulkLoading: false,
        bulkPreviewData: null,
        bulkErrorMsg: '',

        validateBulkPreview() {
            if (!this.bulkRawText.trim()) {
                this.bulkErrorMsg = 'Silakan paste daftar siswa terlebih dahulu.';
                return;
            }
            this.bulkLoading = true;
            this.bulkErrorMsg = '';

            fetch('{{ route('rombel.students.bulk-preview', $classroom) }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ raw_data: this.bulkRawText })
            })
            .then(res => res.json())
            .then(data => {
                this.bulkLoading = false;
                if (data.message && !data.rows) {
                    this.bulkErrorMsg = data.message;
                } else {
                    this.bulkPreviewData = data;
                }
            })
            .catch(err => {
                this.bulkLoading = false;
                this.bulkErrorMsg = 'Terjadi kesalahan saat memeriksa data. Pastikan koneksi aman.';
            });
        },

        openRemoveModal(studentId, studentName, removeUrl) {
            this.studentToRemove = { id: studentId, name: studentName };
            this.removeActionUrl = removeUrl;
            this.confirmRemoveModal = true;
        }
    }">
        <!-- Header -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <x-breadcrumb :items="[
                    ['label' => 'Rombel', 'url' => route('rombel.index')],
                    ['label' => $classroom->nama_rombel]
                ]" />
                <h1 class="text-2xl lg:text-3xl font-bold text-navy mt-2">{{ $classroom->nama_rombel }}</h1>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('rombel.index') }}" class="btn btn-secondary flex items-center gap-2">
                    <x-icon name="arrow-left" class="w-4 h-4" />
                    <span>Kembali</span>
                </a>
                @can('classrooms.update')
                <a href="{{ route('rombel.edit', $classroom) }}" class="btn btn-primary flex items-center gap-2">
                    <x-icon name="edit" class="w-4 h-4" />
                    <span>Edit Rombel</span>
                </a>
                @endcan
            </div>
        </div>

        <!-- Ringkasan Rombel -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="card p-5 bg-white border border-slate-200 rounded-xl shadow-xs">
                <div class="text-xs font-semibold text-muted uppercase tracking-wider mb-1">Kode Rombel</div>
                <div class="text-xl font-bold text-navy">{{ $classroom->kode_rombel }}</div>
            </div>
            <div class="card p-5 bg-white border border-slate-200 rounded-xl shadow-xs">
                <div class="text-xs font-semibold text-muted uppercase tracking-wider mb-1">Program</div>
                <div class="text-lg font-bold text-navy">{{ $classroom->program->nama ?? '-' }}</div>
            </div>
            <div class="card p-5 bg-white border border-slate-200 rounded-xl shadow-xs">
                <div class="text-xs font-semibold text-muted uppercase tracking-wider mb-1">Tahun Ajaran</div>
                <div class="text-lg font-bold text-navy">{{ $classroom->academicYear?->nama ?? '-' }}</div>
            </div>
            <div class="card p-5 bg-white border border-slate-200 rounded-xl shadow-xs">
                <div class="text-xs font-semibold text-muted uppercase tracking-wider mb-1">Jumlah Siswa</div>
                <div class="text-2xl font-bold text-primary-dark">{{ $classroom->students->count() }} <span class="text-xs font-normal text-muted">Siswa</span></div>
            </div>
        </div>

        <!-- Navigasi Tab Utama: Anggota Kelas & Informasi Rombel -->
        <div class="border-b border-slate-200">
            <nav class="flex space-x-6">
                <button
                    type="button"
                    @click="activeMainTab = 'anggota'"
                    :class="activeMainTab === 'anggota' ? 'border-primary text-primary font-semibold' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300 font-normal'"
                    class="py-3 px-1 border-b-2 text-sm flex items-center gap-2 transition-colors">
                    <x-icon name="users" class="w-4 h-4" />
                    <span>Anggota Kelas</span>
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-700">
                        {{ $classroom->students->count() }}
                    </span>
                </button>
                <button
                    type="button"
                    @click="activeMainTab = 'informasi'"
                    :class="activeMainTab === 'informasi' ? 'border-primary text-primary font-semibold' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300 font-normal'"
                    class="py-3 px-1 border-b-2 text-sm flex items-center gap-2 transition-colors">
                    <x-icon name="info" class="w-4 h-4" />
                    <span>Informasi Rombel</span>
                </button>
            </nav>
        </div>

        <!-- TAB 1: ANGGOTA KELAS -->
        <div x-show="activeMainTab === 'anggota'" class="space-y-4">
            <div class="card p-6 bg-white border border-slate-200 rounded-xl shadow-xs">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-5">
                    <div>
                        <h2 class="text-lg font-semibold text-navy">Daftar Anggota Kelas</h2>
                        <p class="text-xs text-muted mt-0.5">Kelola siswa yang terdaftar aktif dalam rombel ini.</p>
                    </div>

                    <div class="flex flex-wrap items-center gap-2.5">
                        @if($canManage)
                        <button
                            type="button"
                            @click="showAddModal = true; addMethod = 'pilih'"
                            class="btn btn-primary text-sm flex items-center gap-1.5 shadow-xs">
                            <x-icon name="plus" class="w-4 h-4" />
                            <span>Tambah Siswa ke Kelas</span>
                        </button>
                        <a
                            href="{{ route('peserta-didik.create', ['rombel_id' => $classroom->id]) }}"
                            class="btn btn-secondary text-xs flex items-center gap-1.5"
                            title="Formulir Lengkap Siswa Baru">
                            <x-icon name="user-plus" class="w-3.5 h-3.5" />
                            <span>Form Lengkap</span>
                        </a>
                        <a
                            href="{{ route('peserta-didik.bulk-assign', ['rombel_id' => $classroom->id]) }}"
                            class="btn btn-secondary text-xs flex items-center gap-1.5"
                            title="Bulk Assign Siswa">
                            <x-icon name="users" class="w-3.5 h-3.5" />
                            <span>Bulk Assign</span>
                        </a>
                        @endif
                    </div>
                </div>

                <div class="overflow-x-auto rounded-lg border border-slate-200">
                    <table class="min-w-full divide-y divide-slate-200 text-sm">
                        <thead>
                            <tr class="bg-slate-50 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">
                                <th class="px-3 py-2.5 text-center w-12">No</th>
                                <th class="px-3 py-2.5">NIS / Identitas</th>
                                <th class="px-3 py-2.5">Nama Siswa</th>
                                <th class="px-3 py-2.5">Jenis Kelamin</th>
                                <th class="px-3 py-2.5">Kontak</th>
                                <th class="px-3 py-2.5">Status</th>
                                <th class="px-3 py-2.5 text-right w-36">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white">
                            @forelse($classroom->students as $index => $student)
                            <tr class="hover:bg-slate-50/80 transition-colors">
                                <td class="px-3 py-2.5 text-center text-slate-500 tabular-nums">{{ $index + 1 }}</td>
                                <td class="px-3 py-2.5 font-mono text-xs text-slate-600">{{ $student->nis ?: '-' }}</td>
                                <td class="px-3 py-2.5">
                                    <div class="font-medium text-slate-900">{{ $student->nama_lengkap }}</div>
                                    @if($student->catatan)
                                    <div class="text-[11px] text-slate-500">{{ Str::limit($student->catatan, 35) }}</div>
                                    @endif
                                </td>
                                <td class="px-3 py-2.5 text-slate-600">{{ $student->jenis_kelamin }}</td>
                                <td class="px-3 py-2.5 text-slate-600 tabular-nums">{{ $student->no_hp ?: '-' }}</td>
                                <td class="px-3 py-2.5">
                                    <x-badge variant="{{ $student->status == 'aktif' ? 'success' : 'warning' }}">
                                        {{ ucfirst($student->status) }}
                                    </x-badge>
                                </td>
                                <td class="px-3 py-2.5 text-right">
                                    <div class="inline-flex items-center justify-end gap-1.5">
                                        @can('students.update')
                                        <a
                                            href="{{ route('peserta-didik.edit', $student) }}"
                                            class="inline-flex items-center px-2 py-1 text-xs font-medium text-slate-700 bg-white border border-slate-300 rounded hover:bg-slate-50 transition-colors"
                                            title="Edit data siswa">
                                            <x-icon name="edit" class="w-3.5 h-3.5 mr-1 text-slate-500" />
                                            <span>Edit</span>
                                        </a>
                                        @endcan

                                        @if($canManage)
                                        <button
                                            type="button"
                                            @click="openRemoveModal({{ $student->id }}, '{{ addslashes($student->nama_lengkap) }}', '{{ route('rombel.students.remove', [$classroom, $student]) }}')"
                                            class="inline-flex items-center px-2 py-1 text-xs font-medium text-red-700 bg-red-50 border border-red-200 rounded hover:bg-red-100 transition-colors"
                                            title="Keluarkan dari kelas">
                                            <x-icon name="user-minus" class="w-3.5 h-3.5 mr-1 text-red-500" />
                                            <span>Keluarkan</span>
                                        </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center py-12">
                                    <div class="flex flex-col items-center justify-center text-center">
                                        <div class="w-12 h-12 rounded-full bg-slate-100 flex items-center justify-center text-slate-400 mb-3">
                                            <x-icon name="users" class="w-6 h-6" />
                                        </div>
                                        <h3 class="text-sm font-semibold text-slate-800">Belum Ada Anggota di Kelas Ini</h3>
                                        <p class="text-xs text-slate-500 max-w-sm mt-1">Tambahkan siswa yang sudah terdaftar, lakukan input cepat, atau impor massal daftar siswa.</p>
                                        @if($canManage)
                                        <button
                                            type="button"
                                            @click="showAddModal = true; addMethod = 'pilih'"
                                            class="btn btn-primary text-xs mt-4 flex items-center gap-1.5">
                                            <x-icon name="plus" class="w-3.5 h-3.5" />
                                            <span>+ Tambah Siswa ke Kelas</span>
                                        </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- TAB 2: INFORMASI ROMBEL -->
        <div x-show="activeMainTab === 'informasi'" class="space-y-4" style="display: none;">
            <div class="card p-6 bg-white border border-slate-200 rounded-xl shadow-xs">
                <h3 class="text-base font-semibold text-navy mb-4">Informasi Tambahan Rombel</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                    <div class="p-4 rounded-xl border border-slate-200 bg-slate-50/50">
                        <span class="text-xs text-muted block mb-1 uppercase tracking-wider font-semibold">Wali Kelas</span>
                        <span class="font-medium text-navy text-base">
                            {{ $classroom->wali_kelas_id ? ($classroom->waliKelas?->name ?? 'Tidak diketahui') : 'Belum ditetapkan' }}
                        </span>
                    </div>
                    <div class="p-4 rounded-xl border border-slate-200 bg-slate-50/50">
                        <span class="text-xs text-muted block mb-1 uppercase tracking-wider font-semibold">Terakhir Diperbarui</span>
                        <span class="font-medium text-navy text-base">
                            {{ $classroom->updated_at ? $classroom->updated_at->format('d M Y, H:i') : '-' }}
                        </span>
                    </div>
                    @if($classroom->deskripsi)
                    <div class="col-span-1 md:col-span-2 p-4 rounded-xl border border-slate-200 bg-slate-50/50">
                        <span class="text-xs text-muted block mb-1 uppercase tracking-wider font-semibold">Deskripsi</span>
                        <p class="text-navy text-sm">{{ $classroom->deskripsi }}</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- MODAL: + TAMBAH SISWA KE KELAS (3 METODE) -->
        <div
            x-show="showAddModal"
            class="fixed inset-0 z-50 overflow-y-auto"
            style="display: none;"
            x-cloak
            @keydown.escape.window="showAddModal = false">
            <!-- Backdrop -->
            <div
                x-show="showAddModal"
                x-transition:enter="ease-out duration-200"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="ease-in duration-150"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                class="fixed inset-0 bg-slate-900/50 backdrop-blur-xs transition-opacity"
                @click="showAddModal = false"></div>

            <div class="flex min-h-screen items-center justify-center p-4">
                <div
                    x-show="showAddModal"
                    x-transition:enter="ease-out duration-200"
                    x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave="ease-in duration-150"
                    x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    class="relative w-full max-w-3xl max-h-[90vh] overflow-y-auto rounded-xl bg-white shadow-xl border border-slate-200 p-6 flex flex-col"
                    @click.stop>

                    <!-- Modal Header -->
                    <div class="flex items-center justify-between pb-4 border-b border-slate-200">
                        <div>
                            <h3 class="text-lg font-semibold text-slate-900">Tambah Siswa ke Kelas</h3>
                            <p class="text-xs text-slate-500 mt-0.5">Rombel: <span class="font-medium text-slate-800">{{ $classroom->nama_rombel }}</span> ({{ $classroom->program->nama ?? '-' }})</p>
                        </div>
                        <button
                            type="button"
                            @click="showAddModal = false"
                            class="text-slate-400 hover:text-slate-600 p-1.5 rounded-lg hover:bg-slate-100 transition-colors">
                            <x-icon name="x" class="w-5 h-5" />
                        </button>
                    </div>

                    <!-- Method Segmented Controls (Shadcn style) -->
                    <div class="mt-4 p-1 bg-slate-100 rounded-lg inline-flex w-full grid grid-cols-3 gap-1">
                        <button
                            type="button"
                            @click="addMethod = 'pilih'"
                            :class="addMethod === 'pilih' ? 'bg-white text-slate-900 shadow-xs font-semibold' : 'text-slate-600 hover:text-slate-900 font-normal'"
                            class="py-2 text-xs rounded-md text-center transition-all flex items-center justify-center gap-1.5">
                            <x-icon name="user-check" class="w-3.5 h-3.5" />
                            <span>Pilih Siswa Terdaftar</span>
                        </button>
                        <button
                            type="button"
                            @click="addMethod = 'input_cepat'"
                            :class="addMethod === 'input_cepat' ? 'bg-white text-slate-900 shadow-xs font-semibold' : 'text-slate-600 hover:text-slate-900 font-normal'"
                            class="py-2 text-xs rounded-md text-center transition-all flex items-center justify-center gap-1.5">
                            <x-icon name="user-plus" class="w-3.5 h-3.5" />
                            <span>Input Cepat</span>
                        </button>
                        <button
                            type="button"
                            @click="addMethod = 'massal'"
                            :class="addMethod === 'massal' ? 'bg-white text-slate-900 shadow-xs font-semibold' : 'text-slate-600 hover:text-slate-900 font-normal'"
                            class="py-2 text-xs rounded-md text-center transition-all flex items-center justify-center gap-1.5">
                            <x-icon name="file-text" class="w-3.5 h-3.5" />
                            <span>Tambah Massal</span>
                        </button>
                    </div>

                    <!-- METODE 1: PILIH SISWA TERDAFTAR -->
                    <div x-show="addMethod === 'pilih'" class="mt-5 space-y-4">
                        <form method="POST" action="{{ route('rombel.students.assign', $classroom) }}">
                            @csrf
                            <div class="space-y-3">
                                <div class="flex items-center justify-between gap-3">
                                    <div class="relative flex-1">
                                        <input
                                            type="text"
                                            x-model="searchUnassigned"
                                            placeholder="Cari nama atau NIS siswa belum berkelas..."
                                            class="w-full text-xs rounded-lg border-slate-200 pl-8 pr-3 py-2 focus:border-primary focus:ring-1 focus:ring-primary" />
                                        <div class="absolute left-2.5 top-2.5 text-slate-400">
                                            <x-icon name="search" class="w-3.5 h-3.5" />
                                        </div>
                                    </div>
                                    <button
                                        type="button"
                                        @click="toggleSelectAllUnassigned()"
                                        class="text-xs text-primary font-medium hover:underline whitespace-nowrap">
                                        <span x-text="selectedStudentIds.length === filteredUnassigned.length && filteredUnassigned.length > 0 ? 'Batal Pilih Semua' : 'Pilih Semua'"></span>
                                    </button>
                                </div>

                                <div class="border border-slate-200 rounded-lg max-h-64 overflow-y-auto divide-y divide-slate-100">
                                    <template x-if="filteredUnassigned.length === 0">
                                        <div class="p-6 text-center text-xs text-slate-500">
                                            Tidak ada siswa aktif tanpa kelas yang cocok.
                                        </div>
                                    </template>

                                    <template x-for="student in filteredUnassigned" :key="student.id">
                                        <label class="flex items-center justify-between p-3 hover:bg-slate-50 cursor-pointer transition-colors">
                                            <div class="flex items-center gap-3">
                                                <input
                                                    type="checkbox"
                                                    name="student_ids[]"
                                                    :value="student.id"
                                                    x-model="selectedStudentIds"
                                                    class="rounded border-slate-300 text-primary focus:ring-primary h-4 w-4" />
                                                <div>
                                                    <div class="text-xs font-semibold text-slate-900" x-text="student.nama_lengkap"></div>
                                                    <div class="text-[11px] text-slate-500">
                                                        NIS: <span class="font-mono" x-text="student.nis"></span> |
                                                        JK: <span x-text="student.jenis_kelamin"></span> |
                                                        Program: <span x-text="student.program"></span>
                                                    </div>
                                                </div>
                                            </div>
                                            <span class="text-[11px] font-medium text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded border border-emerald-100">
                                                Tanpa Kelas
                                            </span>
                                        </label>
                                    </template>
                                </div>

                                <div class="flex items-center justify-between pt-3 border-t border-slate-200">
                                    <div class="text-xs text-slate-500">
                                        Terpilih: <span class="font-semibold text-slate-800" x-text="selectedStudentIds.length"></span> siswa
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <button
                                            type="button"
                                            @click="showAddModal = false"
                                            class="btn btn-secondary text-xs">
                                            Batal
                                        </button>
                                        <button
                                            type="submit"
                                            :disabled="selectedStudentIds.length === 0"
                                            class="btn btn-primary text-xs flex items-center gap-1.5 disabled:opacity-50 disabled:cursor-not-allowed">
                                            <x-icon name="check" class="w-3.5 h-3.5" />
                                            <span>Tambahkan Siswa Terpilih</span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>

                    <!-- METODE 2: INPUT CEPAT -->
                    <div x-show="addMethod === 'input_cepat'" class="mt-5 space-y-4" style="display: none;">
                        <form method="POST" action="{{ route('rombel.students.quick-store', $classroom) }}">
                            @csrf
                            <div class="space-y-4">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <!-- Nama Lengkap -->
                                    <div class="col-span-1 md:col-span-2">
                                        <label class="block text-xs font-semibold text-slate-700 mb-1">
                                            Nama Lengkap <span class="text-red-500">*</span>
                                        </label>
                                        <input
                                            type="text"
                                            name="nama_lengkap"
                                            required
                                            placeholder="Contoh: Ahmad Fauzan"
                                            class="w-full text-xs rounded-lg border-slate-200 px-3 py-2 focus:border-primary focus:ring-1 focus:ring-primary" />
                                    </div>

                                    <!-- Nomor Identitas (NIS/NIK) -->
                                    <div>
                                        <label class="block text-xs font-semibold text-slate-700 mb-1">
                                            Nomor Identitas (NIS / NIK) <span class="text-red-500">*</span>
                                        </label>
                                        <input
                                            type="text"
                                            name="nomor_identitas"
                                            required
                                            placeholder="Contoh: 20261001 atau NIK 16 digit"
                                            class="w-full text-xs rounded-lg border-slate-200 px-3 py-2 font-mono focus:border-primary focus:ring-1 focus:ring-primary" />
                                        <span class="text-[11px] text-slate-400 mt-0.5 block">Harus unik di database sistem.</span>
                                    </div>

                                    <!-- Jenis Kelamin -->
                                    <div>
                                        <label class="block text-xs font-semibold text-slate-700 mb-1">
                                            Jenis Kelamin <span class="text-red-500">*</span>
                                        </label>
                                        <select
                                            name="jenis_kelamin"
                                            required
                                            class="w-full text-xs rounded-lg border-slate-200 px-3 py-2 focus:border-primary focus:ring-1 focus:ring-primary">
                                            <option value="">Pilih Jenis Kelamin</option>
                                            <option value="Laki-laki">Laki-laki (L)</option>
                                            <option value="Perempuan">Perempuan (P)</option>
                                        </select>
                                    </div>

                                    <!-- Status Masuk -->
                                    <div>
                                        <label class="block text-xs font-semibold text-slate-700 mb-1">
                                            Status Masuk
                                        </label>
                                        <select
                                            name="status_masuk"
                                            class="w-full text-xs rounded-lg border-slate-200 px-3 py-2 focus:border-primary focus:ring-1 focus:ring-primary">
                                            <option value="Siswa Baru" selected>Siswa Baru</option>
                                            <option value="Pindahan">Pindahan</option>
                                            <option value="Aktif">Aktif</option>
                                        </select>
                                    </div>

                                    <!-- Kontak / No. HP -->
                                    <div>
                                        <label class="block text-xs font-semibold text-slate-700 mb-1">
                                            Kontak / No. HP (Opsional)
                                        </label>
                                        <input
                                            type="text"
                                            name="kontak"
                                            placeholder="Contoh: 08123456789"
                                            class="w-full text-xs rounded-lg border-slate-200 px-3 py-2 tabular-nums focus:border-primary focus:ring-1 focus:ring-primary" />
                                    </div>
                                </div>

                                <div class="bg-slate-50 p-3 rounded-lg border border-slate-100 text-[11px] text-slate-600">
                                    <span class="font-semibold text-slate-700">Otomatisasi:</span> Siswa langsung didaftarkan dengan status <span class="font-medium text-emerald-700">Aktif</span> dan dimasukkan ke rombel ini. Kehadiran absensi tidak diubah otomatis.
                                </div>

                                <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-200">
                                    <button
                                        type="button"
                                        @click="showAddModal = false"
                                        class="btn btn-secondary text-xs">
                                        Batal
                                    </button>
                                    <button
                                        type="submit"
                                        class="btn btn-primary text-xs flex items-center gap-1.5">
                                        <x-icon name="check" class="w-3.5 h-3.5" />
                                        <span>Simpan & Tambah ke Kelas</span>
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>

                    <!-- METODE 3: TAMBAH MASSAL -->
                    <div x-show="addMethod === 'massal'" class="mt-5 space-y-4" style="display: none;">
                        <div class="space-y-3">
                            <div class="bg-slate-50 p-3 rounded-lg border border-slate-200 text-xs text-slate-600 space-y-1">
                                <div class="font-semibold text-slate-800">Petunjuk Format Tambah Massal:</div>
                                <p class="text-[11px]">Paste data per baris dipisah koma atau tab (dari Excel):</p>
                                <code class="block bg-white p-2 rounded border border-slate-200 font-mono text-[11px] text-slate-700">
                                    Nama Lengkap, Nomor Identitas, Jenis Kelamin (L/P), Kontak (opsional), Status Masuk (opsional)
                                </code>
                                <p class="text-[10px] text-slate-400">Contoh: Budi Santoso, 7601011501900001, L, 08123456789, Baru</p>
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">
                                    Paste Daftar Siswa
                                </label>
                                <textarea
                                    x-model="bulkRawText"
                                    rows="5"
                                    placeholder="Paste daftar baris di sini..."
                                    class="w-full text-xs font-mono rounded-lg border-slate-200 px-3 py-2 focus:border-primary focus:ring-1 focus:ring-primary"></textarea>
                            </div>

                            <div class="flex items-center gap-2">
                                <button
                                    type="button"
                                    @click="validateBulkPreview()"
                                    :disabled="bulkLoading || !bulkRawText.trim()"
                                    class="btn btn-secondary text-xs flex items-center gap-1.5 disabled:opacity-50">
                                    <template x-if="bulkLoading">
                                        <span class="inline-block animate-spin mr-1">⟳</span>
                                    </template>
                                    <x-icon name="eye" class="w-3.5 h-3.5" />
                                    <span>Periksa & Preview Data</span>
                                </button>
                                <button
                                    type="button"
                                    x-show="bulkPreviewData"
                                    @click="bulkPreviewData = null; bulkRawText = ''"
                                    class="text-xs text-slate-500 hover:text-slate-700">
                                    Reset
                                </button>
                            </div>

                            <!-- Error Message -->
                            <div x-show="bulkErrorMsg" class="p-2.5 rounded-lg bg-red-50 border border-red-200 text-red-700 text-xs">
                                <span x-text="bulkErrorMsg"></span>
                            </div>

                            <!-- PREVIEW RESULT SECTION -->
                            <div x-show="bulkPreviewData" class="space-y-3 pt-3 border-t border-slate-200">
                                <!-- Summary Cards -->
                                <div class="grid grid-cols-3 gap-2 text-center">
                                    <div class="p-2 bg-slate-50 rounded-lg border border-slate-200">
                                        <div class="text-[10px] uppercase font-semibold text-slate-500">Total Baris</div>
                                        <div class="text-base font-bold text-slate-800" x-text="bulkPreviewData?.total_rows ?? 0"></div>
                                    </div>
                                    <div class="p-2 bg-emerald-50 rounded-lg border border-emerald-200">
                                        <div class="text-[10px] uppercase font-semibold text-emerald-700">Siap Disimpan</div>
                                        <div class="text-base font-bold text-emerald-700" x-text="bulkPreviewData?.valid_count ?? 0"></div>
                                    </div>
                                    <div class="p-2 bg-red-50 rounded-lg border border-red-200">
                                        <div class="text-[10px] uppercase font-semibold text-red-700">Tidak Valid</div>
                                        <div class="text-base font-bold text-red-700" x-text="bulkPreviewData?.invalid_count ?? 0"></div>
                                    </div>
                                </div>

                                <template x-if="bulkPreviewData?.invalid_count > 0">
                                    <div class="p-2.5 rounded-lg bg-amber-50 border border-amber-200 text-amber-800 text-[11px] flex items-center gap-2">
                                        <x-icon name="alert-triangle" class="w-4 h-4 text-amber-600 shrink-0" />
                                        <span>Perhatian: Baris tidak valid tidak akan disimpan. Hanya siswa dengan status valid yang akan dimasukkan ke rombel.</span>
                                    </div>
                                </template>

                                <!-- Table Preview -->
                                <div class="border border-slate-200 rounded-lg max-h-52 overflow-y-auto">
                                    <table class="min-w-full divide-y divide-slate-200 text-xs">
                                        <thead class="bg-slate-50 text-slate-600">
                                            <tr>
                                                <th class="px-2.5 py-2 text-left w-8">#</th>
                                                <th class="px-2.5 py-2 text-left">Nama</th>
                                                <th class="px-2.5 py-2 text-left">Identitas</th>
                                                <th class="px-2.5 py-2 text-left">JK</th>
                                                <th class="px-2.5 py-2 text-left">Status Validasi</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-slate-100 bg-white">
                                            <template x-for="row in bulkPreviewData?.rows ?? []" :key="row.index">
                                                <tr :class="row.is_valid ? 'bg-white' : 'bg-red-50/40'">
                                                    <td class="px-2.5 py-1.5 text-slate-400 font-mono" x-text="row.index"></td>
                                                    <td class="px-2.5 py-1.5 font-medium text-slate-800" x-text="row.nama_lengkap"></td>
                                                    <td class="px-2.5 py-1.5 font-mono text-slate-600" x-text="row.nomor_identitas"></td>
                                                    <td class="px-2.5 py-1.5 text-slate-600" x-text="row.jenis_kelamin"></td>
                                                    <td class="px-2.5 py-1.5">
                                                        <template x-if="row.is_valid">
                                                            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                                                Siap Disimpan
                                                            </span>
                                                        </template>
                                                        <template x-if="!row.is_valid">
                                                            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-semibold bg-red-50 text-red-700 border border-red-200" :title="row.error" x-text="row.error"></span>
                                                        </template>
                                                    </td>
                                                </tr>
                                            </template>
                                        </tbody>
                                    </table>
                                </div>

                                <!-- Action Form to Bulk Store -->
                                <form method="POST" action="{{ route('rombel.students.bulk-store', $classroom) }}">
                                    @csrf
                                    <input type="hidden" name="raw_data" :value="bulkRawText" />
                                    <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-200">
                                        <button
                                            type="button"
                                            @click="showAddModal = false"
                                            class="btn btn-secondary text-xs">
                                            Batal
                                        </button>
                                        <button
                                            type="submit"
                                            :disabled="!bulkPreviewData || bulkPreviewData.valid_count === 0"
                                            class="btn btn-primary text-xs flex items-center gap-1.5 disabled:opacity-50 disabled:cursor-not-allowed">
                                            <x-icon name="check" class="w-3.5 h-3.5" />
                                            <span>Konfirmasi & Simpan (<span x-text="bulkPreviewData?.valid_count ?? 0"></span> Siswa Valid)</span>
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        <!-- MODAL KONFIRMASI KELUARKAN SISWA -->
        <div
            x-show="confirmRemoveModal"
            class="fixed inset-0 z-50 overflow-y-auto"
            style="display: none;"
            x-cloak
            @keydown.escape.window="confirmRemoveModal = false">
            <div
                x-show="confirmRemoveModal"
                class="fixed inset-0 bg-slate-900/50 backdrop-blur-xs transition-opacity"
                @click="confirmRemoveModal = false"></div>

            <div class="flex min-h-screen items-center justify-center p-4">
                <div
                    x-show="confirmRemoveModal"
                    class="relative w-full max-w-md rounded-xl bg-white shadow-xl border border-slate-200 p-6"
                    @click.stop>
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-10 h-10 rounded-full bg-red-100 text-red-600 flex items-center justify-center shrink-0">
                            <x-icon name="alert-triangle" class="w-5 h-5" />
                        </div>
                        <div>
                            <h3 class="text-base font-semibold text-slate-900">Keluarkan Siswa dari Kelas?</h3>
                            <p class="text-xs text-slate-500">Konfirmasi pengeluaran anggota rombel.</p>
                        </div>
                    </div>

                    <p class="text-xs text-slate-600 leading-relaxed mb-5">
                        Apakah Anda yakin ingin mengeluarkan <span class="font-semibold text-slate-900" x-text="studentToRemove?.name"></span> dari rombel <span class="font-semibold text-slate-900">{{ $classroom->nama_rombel }}</span>? Data siswa tetap aman di sistem dengan status tanpa kelas.
                    </p>

                    <form method="POST" :action="removeActionUrl">
                        @csrf
                        <div class="flex items-center justify-end gap-2.5">
                            <button
                                type="button"
                                @click="confirmRemoveModal = false"
                                class="btn btn-secondary text-xs">
                                Batal
                            </button>
                            <button
                                type="submit"
                                class="btn text-xs bg-red-600 hover:bg-red-700 text-white font-medium px-4 py-2 rounded-lg transition-colors">
                                Ya, Keluarkan Siswa
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </div>
</x-app-layout>

<x-app-layout>
    <x-slot name="title">{{ $classroom->nama_rombel }}</x-slot>

    <div class="w-full space-y-6">
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
            <div class="card p-5 bg-white border border-slate-200 rounded-xl shadow-sm">
                <div class="text-xs font-semibold text-muted uppercase tracking-wider mb-1">Kode Rombel</div>
                <div class="text-xl font-bold text-navy">{{ $classroom->kode_rombel }}</div>
            </div>
            <div class="card p-5 bg-white border border-slate-200 rounded-xl shadow-sm">
                <div class="text-xs font-semibold text-muted uppercase tracking-wider mb-1">Program</div>
                <div class="text-lg font-bold text-navy">{{ $classroom->program->nama ?? '-' }}</div>
            </div>
            <div class="card p-5 bg-white border border-slate-200 rounded-xl shadow-sm">
                <div class="text-xs font-semibold text-muted uppercase tracking-wider mb-1">Tahun Ajaran</div>
                <div class="text-lg font-bold text-navy">{{ $classroom->academicYear?->nama ?? '-' }}</div>
            </div>
            <div class="card p-5 bg-white border border-slate-200 rounded-xl shadow-sm">
                <div class="text-xs font-semibold text-muted uppercase tracking-wider mb-1">Jumlah Siswa</div>
                <div class="text-2xl font-bold text-primary-dark">{{ $classroom->students->count() }} <span class="text-xs font-normal text-muted">Siswa</span></div>
            </div>
        </div>

        <!-- Detail Tambahan -->
        <div class="card p-6 bg-white border border-slate-200 rounded-xl shadow-sm">
            <h3 class="text-lg font-semibold text-navy mb-4">Informasi Tambahan</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                <div class="p-4 rounded-xl border border-slate-100 bg-slate-50">
                    <span class="text-muted block mb-1">Wali Kelas</span>
                    <span class="font-medium text-navy text-base">
                        {{ $classroom->wali_kelas_id ? ($classroom->waliKelas?->name ?? 'Tidak diketahui') : 'Belum ditetapkan' }}
                    </span>
                </div>
                <div class="p-4 rounded-xl border border-slate-100 bg-slate-50">
                    <span class="text-muted block mb-1">Terakhir Diperbarui</span>
                    <span class="font-medium text-navy text-base">
                        {{ $classroom->updated_at ? $classroom->updated_at->format('d M Y, H:i') : '-' }}
                    </span>
                </div>
                @if($classroom->deskripsi)
                <div class="col-span-1 md:col-span-2 p-4 rounded-xl border border-slate-100 bg-slate-50">
                    <span class="text-muted block mb-1">Deskripsi</span>
                    <p class="text-navy">{{ $classroom->deskripsi }}</p>
                </div>
                @endif
            </div>
        </div>

        <!-- Daftar Siswa -->
        <div class="card p-6 bg-white border border-slate-200 rounded-xl shadow-sm">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-4">
                <div>
                    <h2 class="text-lg font-semibold text-navy">Daftar Siswa di Rombel Ini</h2>
                    <p class="text-sm text-muted">Total terdaftar: {{ $classroom->students->count() }} siswa</p>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    @can('students.update')
                        <a href="{{ route('peserta-didik.bulk-assign', ['rombel_id' => $classroom->id]) }}" class="btn btn-secondary text-sm">
                            Tempatkan Siswa ke Rombel
                        </a>
                    @endcan
                    @can('students.create')
                        <a href="{{ route('peserta-didik.create', ['rombel_id' => $classroom->id]) }}" class="btn btn-primary text-sm">
                            Tambah Siswa
                        </a>
                    @endcan
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-border text-sm">
                    <thead>
                        <tr class="bg-slate-50 text-left text-xs font-semibold text-muted uppercase">
                            <th class="px-4 py-3">No</th>
                            <th class="px-4 py-3">NIS</th>
                            <th class="px-4 py-3">Nama Siswa</th>
                            <th class="px-4 py-3">Jenis Kelamin</th>
                            <th class="px-4 py-3">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border">
                        @forelse($classroom->students as $index => $student)
                        <tr class="hover:bg-slate-50/60 transition-colors">
                            <td class="px-4 py-3">{{ $index + 1 }}</td>
                            <td class="px-4 py-3 font-mono text-muted">{{ $student->nis ?? '-' }}</td>
                            <td class="px-4 py-3 font-medium text-navy">{{ $student->nama_lengkap }}</td>
                            <td class="px-4 py-3">{{ $student->jenis_kelamin }}</td>
                            <td class="px-4 py-3">
                                <x-badge variant="{{ $student->status == 'aktif' ? 'success' : 'warning' }}">
                                    {{ ucfirst($student->status) }}
                                </x-badge>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-8">
                                <x-empty-state title="Belum ada siswa di rombel ini" />
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
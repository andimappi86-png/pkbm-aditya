<x-app-layout>
    <x-slot name="title">Daftar Peserta Didik</x-slot>

    <div class="max-w-7xl mx-auto space-y-6">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <x-breadcrumb :items="[['label' => 'Peserta Didik']]" />
            
            @can('students.create')
            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('peserta-didik.template') }}" class="btn-secondary">
                    <x-icon name="download" class="w-4 h-4 mr-2" />
                    Template
                </a>
                <a href="{{ route('peserta-didik.import') }}" class="btn-secondary">
                    <x-icon name="upload" class="w-4 h-4 mr-2" />
                    Import
                </a>
                <a href="{{ route('peserta-didik.create') }}" class="btn-primary">
                    <x-icon name="plus" class="w-4 h-4 mr-2" />
                    Tambah
                </a>
            </div>
            @endcan
        </div>

        <div class="card p-6">
            <form method="GET" action="{{ route('peserta-didik.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                <div>
                    <label class="label">Pencarian</label>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari Nama/NIS/NISN/NIK..." class="input">
                </div>
                <div>
                    <label class="label">Program</label>
                    <x-select name="program_id" :options="$programs->pluck('nama', 'id')->toArray()" :selected="request('program_id')" placeholder="Semua Program" />
                </div>
                <div>
                    <label class="label">Status</label>
                    <x-select name="status" :options="['aktif' => 'Aktif', 'lulus' => 'Lulus', 'keluar' => 'Keluar', 'pindah' => 'Pindah', 'nonaktif' => 'Nonaktif']" :selected="request('status')" placeholder="Semua Status" />
                </div>
                <div class="flex items-end">
                    <button type="submit" class="btn-secondary w-full">Filter</button>
                </div>
            </form>

            <div class="table-shell overflow-x-auto">
                <table class="min-w-full divide-y divide-border">
                    <thead>
                        <tr>
                            <th>NIS/NISN</th>
                            <th>Nama Lengkap</th>
                            <th>L/P</th>
                            <th>Program</th>
                            <th>Rombel</th>
                            <th>Status</th>
                            <th class="text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border">
                        @forelse($students as $student)
                        <tr>
                            <td>
                                <div class="font-medium text-navy">{{ $student->nis }}</div>
                                <div class="text-xs text-muted">{{ $student->nisn ?? '-' }}</div>
                            </td>
                            <td>
                                <div class="font-medium text-navy">{{ $student->nama_lengkap }}</div>
                                <div class="text-xs text-muted">{{ $student->nik }}</div>
                            </td>
                            <td>{{ $student->jenis_kelamin == 'Laki-laki' ? 'L' : 'P' }}</td>
                            <td>{{ $student->program->nama ?? '-' }}</td>
                            <td>{{ $student->classroom->nama_rombel ?? '-' }}</td>
                            <td>
                                <x-badge variant="{{ $student->status == 'aktif' ? 'success' : 'warning' }}">
                                    {{ ucfirst($student->status) }}
                                </x-badge>
                            </td>
                            <td class="text-right space-x-2">
                                <a href="{{ route('peserta-didik.show', $student->id) }}" class="inline-flex p-2 text-muted hover:text-primary-dark transition-colors">
                                    <x-icon name="eye" class="w-5 h-5" />
                                </a>
                                @can('students.update')
                                <a href="{{ route('peserta-didik.edit', $student->id) }}" class="inline-flex p-2 text-muted hover:text-primary-dark transition-colors">
                                    <x-icon name="pencil" class="w-5 h-5" />
                                </a>
                                @endcan
                                @can('students.delete')
                                <form action="{{ route('peserta-didik.destroy', $student->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Yakin ingin menghapus data ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="inline-flex p-2 text-muted hover:text-accent transition-colors">
                                        <x-icon name="trash" class="w-5 h-5" />
                                    </button>
                                </form>
                                @endcan
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-8">
                                <x-empty-state title="Belum ada data peserta didik" />
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <div class="mt-4">
                {{ $students->links() }}
            </div>
        </div>
    </div>
</x-app-layout>

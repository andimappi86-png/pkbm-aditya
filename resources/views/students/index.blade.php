<x-app-layout>
    <x-slot name="title">Daftar Peserta Didik</x-slot>

    <div class="max-w-7xl mx-auto space-y-4">
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

        <!-- Filter -->
        <div class="card p-4">
            <form method="GET" action="{{ route('peserta-didik.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                <div class="md:col-span-2">
                    <label class="block text-xs font-bold text-muted uppercase mb-1">Pencarian</label>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari Nama/NIS/NISN/NIK..." class="input w-full">
                </div>
                <div>
                    <label class="block text-xs font-bold text-muted uppercase mb-1">Program</label>
                    <x-select name="program_id" :options="$programs->pluck('nama', 'id')->toArray()" :selected="request('program_id')" placeholder="Semua Program" />
                </div>
                <div class="flex gap-2">
                    <button type="submit" class="btn-primary flex-1">Filter</button>
                    <a href="{{ route('peserta-didik.index') }}" class="btn-secondary">Reset</a>
                </div>
            </form>
        </div>

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

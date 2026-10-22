<x-app-layout>
    <x-slot name="title">Data Tutor</x-slot>

    <div class="max-w-7xl mx-auto space-y-6">
        <!-- Header -->
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <h1 class="text-2xl font-bold text-navy">Data Tutor</h1>
                <p class="text-sm text-muted mt-1">Kelola data tutor dan informasi pengajar PKBM.</p>
            </div>
            
            @can('tutors.create')
            <a href="{{ route('tutors.create') }}" class="btn-primary flex items-center gap-2">
                <x-icon name="plus" class="w-4 h-4" />
                Tambah Tutor
            </a>
            @endcan
        </div>

        <!-- Statistics -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="card p-4 border-l-4 border-primary">
                <div class="text-sm text-muted">Total Tutor</div>
                <div class="text-2xl font-bold text-navy">{{ $totalTutors }}</div>
            </div>
            <div class="card p-4 border-l-4 border-emerald-500">
                <div class="text-sm text-muted">Tutor Aktif</div>
                <div class="text-2xl font-bold text-navy">{{ $activeTutors }}</div>
            </div>
            <div class="card p-4 border-l-4 border-amber-500">
                <div class="text-sm text-muted">Tutor Tidak Aktif</div>
                <div class="text-2xl font-bold text-navy">{{ $inactiveTutors }}</div>
            </div>
        </div>

        <!-- Filter -->
        <div class="card p-4">
            <form method="GET" action="{{ route('tutors.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                <div class="md:col-span-2">
                    <label class="block text-xs font-semibold text-muted uppercase mb-1">Pencarian</label>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama, NIK, email..." class="input w-full">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-muted uppercase mb-1">Status</label>
                    <select name="status_tutor" class="select w-full">
                        <option value="">Semua Status</option>
                        <option value="aktif" {{ request('status_tutor') == 'aktif' ? 'selected' : '' }}>Aktif</option>
                        <option value="tidak_aktif" {{ request('status_tutor') == 'tidak_aktif' ? 'selected' : '' }}>Tidak Aktif</option>
                    </select>
                </div>
                <div class="flex gap-2">
                    <button type="submit" class="btn-primary flex-1">Filter</button>
                    <a href="{{ route('tutors.index') }}" class="btn-secondary">Reset</a>
                </div>
            </form>
        </div>

        <!-- Table -->
        <div class="card p-0 overflow-hidden">
            <div class="table-shell overflow-x-auto">
                <table class="min-w-full divide-y divide-border">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-bold text-muted uppercase">Nomor Induk</th>
                            <th class="px-6 py-3 text-left text-xs font-bold text-muted uppercase">Nama Lengkap</th>
                            <th class="px-6 py-3 text-left text-xs font-bold text-muted uppercase">Status</th>
                            <th class="px-6 py-3 text-right text-xs font-bold text-muted uppercase">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border">
                        @forelse($tutors as $tutor)
                        <tr class="hover:bg-primary-soft/20 transition-colors">
                            <td class="px-6 py-4">
                                <div class="font-medium text-navy">{{ $tutor->nomor_induk }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="font-medium text-navy">{{ $tutor->nama_lengkap }}</div>
                                <div class="text-xs text-muted">{{ $tutor->email ?? '-' }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-1 rounded text-xs font-bold {{ $tutor->status_tutor == 'aktif' ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700' }}">
                                    {{ ucfirst($tutor->status_tutor) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right space-x-2">
                                @can('tutors.view')
                                <a href="{{ route('tutors.show', $tutor->id) }}" class="text-primary hover:text-primary-dark">Lihat</a>
                                @endcan
                                @can('tutors.edit')
                                <a href="{{ route('tutors.edit', $tutor->id) }}" class="text-primary hover:text-primary-dark">Edit</a>
                                @endcan
                                @can('tutors.delete')
                                <form action="{{ route('tutors.destroy', $tutor->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Yakin?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-accent hover:text-red-800">Hapus</button>
                                </form>
                                @endcan
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center py-12">
                                <p class="text-muted">Belum ada data tutor yang sesuai.</p>
                                <a href="{{ route('tutors.index') }}" class="text-primary hover:underline mt-2 inline-block">Reset Filter</a>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            @if($tutors->hasPages())
            <div class="px-6 py-4 border-t border-border bg-slate-50">
                {{ $tutors->links() }}
            </div>
            @endif
        </div>
    </div>
</x-app-layout>
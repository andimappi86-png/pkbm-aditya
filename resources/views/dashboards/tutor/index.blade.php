<x-app-layout>
    <x-slot name="title">Data Tutor</x-slot>

    <div class="max-w-7xl mx-auto space-y-6">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <x-breadcrumb :items="[['label' => 'Data Tutor']]" />
            
            @can('tutors.create')
            <a href="{{ route('tutors.create') }}" class="btn-primary">
                <x-icon name="plus" class="w-4 h-4" />
                Tambah Tutor
            </a>
            @endcan
        </div>

        <div class="card p-6">
            <form method="GET" action="{{ route('tutors.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                <div>
                    <label class="label">Pencarian</label>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama lengkap, nomor induk, atau email..." class="input">
                </div>
                <div>
                    <label class="label">Status</label>
                    <select name="status_tutor" class="select">
                        <option value="">Semua Status</option>
                        <option value="aktif" {{ request('status_tutor') == 'aktif' ? 'selected' : '' }}>Aktif</option>
                        <option value="tidak_aktif" {{ request('status_tutor') == 'tidak_aktif' ? 'selected' : '' }}>Tidak Aktif</option>
                    </select>
                </div>
                <div class="flex items-end">
                    <button type="submit" class="btn-secondary w-full">Filter</button>
                </div>
            </form>

            <div class="table-shell overflow-x-auto">
                <table class="min-w-full divide-y divide-border">
                    <thead>
                        <tr>
                            <th>Nomor Induk</th>
                            <th>Nama Lengkap</th>
                            <th>Nama Panggilan</th>
                            <th>Jenis Kelamin</th>
                            <th>Status</th>
                            <th class="text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border">
                        @forelse($tutors as $tutor)
                        <tr>
                            <td>
                                <div class="font-medium text-navy">{{ $tutor->nomor_induk }}</div>
                            </td>
                            <td>
                                <div class="font-medium text-navy">{{ $tutor->nama_lengkap }}</div>
                                <div class="text-xs text-muted">{{ $tutor->nama_panggilan ?? '-' }}</div>
                            </td>
                            <td>{{ $tutor->jenis_kelamin == 'Laki-laki' ? 'L' : 'P' }}</td>
                            <td>
                                <x-badge variant="{{ $tutor->status_tutor == 'aktif' ? 'success' : 'warning' }}">
                                    {{ ucfirst($tutor->status_tutor) }}
                                </x-badge>
                            </td>
                            <td class="text-right space-x-2">
                                @can('tutors.view')
                                <a href="{{ route('tutors.show', $tutor->id) }}" class="inline-flex p-2 text-primary hover:text-primary-dark transition-colors">
                                    <x-icon name="eye" class="w-5 h-5" />
                                </a>
                                @endcan
                                @can('tutors.edit')
                                <a href="{{ route('tutors.edit', $tutor->id) }}" class="inline-flex p-2 text-primary hover:text-primary-dark transition-colors">
                                    <x-icon name="pencil" class="w-5 h-5" />
                                </a>
                                @endcan
                                @can('tutors.delete')
                                <form action="{{ route('tutors.destroy', $tutor->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Yakin ingin menghapus data ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="inline-flex p-2 text-primary hover:text-accent transition-colors">
                                        <x-icon name="trash" class="w-5 h-5" />
                                    </button>
                                </form>
                                @endcan
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-8">
                                <x-empty-state title="Belum ada data tutor" />
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <div class="mt-4">
                {{ $tutors->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
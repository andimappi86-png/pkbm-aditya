<x-app-layout>
    <x-slot name="title">Daftar Mata Pelajaran</x-slot>

    <div class="max-w-7xl mx-auto space-y-6">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <x-breadcrumb :items="[['label' => 'Mata Pelajaran']]" />
            
            @can('subjects.create')
            <a href="{{ route('mata-pelajaran.create') }}" class="btn-primary">
                <x-icon name="plus" class="w-4 h-4" />
                Tambah Mata Pelajaran
            </a>
            @endcan
        </div>

        <div class="card p-6">
            <form method="GET" action="{{ route('mata-pelajaran.index') }}" class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                <div>
                    <label class="label">Pencarian</label>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari Kode/Nama..." class="input">
                </div>
                <div>
                    <label class="label">Status</label>
                    <x-select name="status_aktif" :options="[
                        '' => 'Semua Status',
                        'true' => 'Aktif',
                        'false' => 'Nonaktif'
                    ]" :selected="request('status_aktif')" placeholder="Semua Status" />
                </div>
                <div class="flex items-end">
                    <button type="submit" class="btn-secondary w-full">Filter</button>
                </div>
            </form>

            <div class="table-shell overflow-x-auto">
                <table class="min-w-full divide-y divide-border">
                    <thead>
                        <tr>
                            <th>Kode</th>
                            <th>Nama Mata Pelajaran</th>
                            <th>Deskripsi</th>
                            <th>Status</th>
                            <th class="text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border">
                        @forelse($subjects as $subject)
                        <tr>
                            <td>{{ $subject->kode }}</td>
                            <td>{{ $subject->nama }}</td>
                            <td class="max-w-xs">{{ $subject->deskripsi ?? '-' }}</td>
                            <td>
                                <x-badge variant="{{ $subject->status_aktif ? 'success' : 'warning' }}">
                                    {{ $subject->status_aktif ? 'Aktif' : 'Nonaktif' }}
                                </x-badge>
                            </td>
                            <td class="text-right space-x-2">
                                <a href="{{ route('mata-pelajaran.show', $subject->id) }}" class="inline-flex p-2 text-muted hover:text-primary-dark transition-colors">
                                    <x-icon name="eye" class="w-5 h-5" />
                                </a>
                                @can('subjects.update')
                                <a href="{{ route('mata-pelajaran.edit', $subject->id) }}" class="inline-flex p-2 text-muted hover:text-primary-dark transition-colors">
                                    <x-icon name="pencil" class="w-5 h-5" />
                                </a>
                                @endcan
                                @can('subjects.delete')
                                <form action="{{ route('mata-pelajaran.destroy', $subject->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Yakin ingin menghapus data ini?')">
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
                            <td colspan="5" class="text-center py-8">
                                <x-empty-state title="Belum ada data mata pelajaran" />
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <div class="mt-4">
                {{ $subjects->links() }}
            </div>
        </div>
    </div>
</x-app-layout>

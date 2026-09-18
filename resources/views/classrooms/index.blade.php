<x-app-layout>
    <x-slot name="title">Daftar Rombel</x-slot>

    <div class="max-w-7xl mx-auto space-y-6">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <x-breadcrumb :items="[['label' => 'Rombel']]" />
            
            @can('classrooms.create')
            <a href="{{ route('rombel.create') }}" class="btn-primary">
                <x-icon name="plus" class="w-4 h-4" />
                Buat Rombel Baru
            </a>
            @endcan
        </div>

        <div class="card p-6">
            <form method="GET" action="{{ route('rombel.index') }}" class="grid grid-cols-1 md:grid-cols-6 gap-4 mb-6">
                <div class="col-span-1 md:col-span-2">
                    <label class="label">Pencarian</label>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama/kode rombel..." class="input">
                </div>
                <div class="col-span-1 md:col-span-2">
                    <label class="label">Program</label>
                    <select name="program_id" class="input">
                        <option value="">Semua Program</option>
                        @foreach($programs as $program)
                            <option value="{{ $program->id }}" {{ request('program_id') == $program->id ? 'selected' : '' }}>
                                {{ $program->nama }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-span-1 md:col-span-2">
                    <label class="label">Tahun Ajaran</label>
                    <select name="tahun_ajaran_id" class="input">
                        <option value="">Semua Tahun Ajaran</option>
                        @foreach($academicYears as $year)
                            <option value="{{ $year->id }}" {{ request('tahun_ajaran_id') == $year->id ? 'selected' : '' }}>
                                {{ $year->nama_ajaran }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-span-1 md:col-span-2">
                    <label class="label">Status</label>
                    <select name="status_aktif" class="input">
                        <option value="">Semua Status</option>
                        <option value="true" {{ request('status_aktif') == 'true' ? 'selected' : '' }}>Aktif</option>
                        <option value="false" {{ request('status_aktif') == 'false' ? 'selected' : '' }}>Nonaktif</option>
                    </select>
                </div>
                <div class="col-span-1 md:col-span-2 flex items-end">
                    <button type="submit" class="btn-secondary w-full">Filter</button>
                </div>
            </form>

            <div class="table-shell overflow-x-auto">
                <table class="min-w-full divide-y divide-border">
                    <thead>
                        <tr>
                            <th>Kode Rombel</th>
                            <th>Nama Rombel</th>
                            <th>Program</th>
                            <th>Tahun Ajaran</th>
                            <th>Wali Kelas</th>
                            <th>Siswa</th>
                            <th class="text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border">
                        @forelse($classrooms as $classroom)
                        <tr>
                            <td>{{ $classroom->kode_rombel }}</td>
                            <td>{{ $classroom->nama_rombel }}</td>
                            <td>{{ $classroom->program->nama ?? '-' }}</td>
                            <td>{{ $classroom->academicYear->nama_ajaran ?? '-' }}</td>
                            <td>
                                @if($classroom->wali_kelas_id)
                                    {{ $classroom->homeroomTeacher->nama_lengkap ?? 'Tidak diketahui' }}
                                @else
                                    <span class="text-xs text-muted">Belum ada wali kelas</span>
                                @endif
                            </td>
                            <td class="text-center">
                                {{ $classroom->students->count() }} siswa
                            </td>
                            <td class="text-right space-x-2">
                                @can('classrooms.view')
                                <a href="{{ route('rombel.show', $classroom->id) }}" class="inline-flex p-2 text-muted hover:text-primary-dark transition-colors" title="Lihat">
                                    <x-icon name="eye" class="w-5 h-5" />
                                </a>
                                @endcan
                                @can('classrooms.update')
                                <a href="{{ route('rombel.edit', $classroom->id) }}" class="inline-flex p-2 text-muted hover:text-primary-dark transition-colors" title="Edit">
                                    <x-icon name="pencil" class="w-5 h-5" />
                                </a>
                                @endcan
                                @can('classrooms.delete')
                                <form action="{{ route('rombel.destroy', $classroom->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Yakin ingin menghapus rombel ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="inline-flex p-2 text-muted hover:text-accent transition-colors" title="Hapus">
                                        <x-icon name="trash" class="w-5 h-5" />
                                    </button>
                                </form>
                                @endcan
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-8">
                                <x-empty-state title="Belum ada data rombel" />
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <div class="mt-4">
                {{ $classrooms->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
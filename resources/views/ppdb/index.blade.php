<x-app-layout>
    <x-slot name="title">Penerimaan Peserta Didik Baru (PPDB)</x-slot>

    <div class="space-y-6">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h2 class="text-2xl font-bold text-navy">Data Pendaftaran PPDB</h2>
                <p class="text-muted mt-1">Kelola data pendaftaran calon peserta didik baru.</p>
            </div>
            @can('admissions.apply')
            <a href="{{ route('ppdb.create') }}" class="btn-primary">
                <x-icon name="plus" class="w-5 h-5 mr-2" />
                Tambah Pendaftaran
            </a>
            @endcan
        </div>

        <!-- Filter & Search -->
        <div class="card p-4">
            <form action="{{ route('ppdb.index') }}" method="GET" class="flex flex-col md:flex-row gap-4">
                <div class="flex-1">
                    <x-input-label for="search" value="Cari" />
                    <x-text-input id="search" name="search" type="text" class="w-full mt-1" value="{{ request('search') }}" placeholder="No. Pendaftaran / Nama / NIK" />
                </div>
                <div class="w-full md:w-48">
                    <x-input-label for="status" value="Status" />
                    <x-select id="status" name="status" class="w-full mt-1">
                        <option value="">Semua Status</option>
                        @foreach(\App\Models\Ppdb::statusOptions() as $val => $label)
                            <option value="{{ $val }}" {{ request('status') === $val ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </x-select>
                </div>
                <div class="w-full md:w-48">
                    <x-input-label for="program_id" value="Program" />
                    <x-select id="program_id" name="program_id" class="w-full mt-1">
                        <option value="">Semua Program</option>
                        @foreach($programs as $program)
                            <option value="{{ $program->id }}" {{ request('program_id') == $program->id ? 'selected' : '' }}>{{ $program->nama }}</option>
                        @endforeach
                    </x-select>
                </div>
                <div class="w-full md:w-auto flex items-end">
                    <x-primary-button type="submit" class="w-full md:w-auto justify-center">
                        <x-icon name="filter" class="w-4 h-4 mr-2" /> Filter
                    </x-primary-button>
                </div>
            </form>
        </div>

        <!-- Table -->
        <div class="card overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-primary-soft text-primary-dark border-b border-border">
                            <th class="p-4 font-semibold text-sm">No. Pendaftaran</th>
                            <th class="p-4 font-semibold text-sm">Nama Lengkap</th>
                            <th class="p-4 font-semibold text-sm">Program</th>
                            <th class="p-4 font-semibold text-sm">Tahun Ajaran</th>
                            <th class="p-4 font-semibold text-sm">Tanggal Daftar</th>
                            <th class="p-4 font-semibold text-sm">Status</th>
                            <th class="p-4 font-semibold text-sm text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border">
                        @forelse($ppdbs as $ppdb)
                            <tr class="hover:bg-background transition-colors">
                                <td class="p-4">
                                    <span class="font-medium text-navy">{{ $ppdb->nomor_pendaftaran }}</span>
                                </td>
                                <td class="p-4">
                                    <div class="text-navy font-medium">{{ $ppdb->nama_lengkap }}</div>
                                    <div class="text-xs text-muted">NIK: {{ $ppdb->nik }}</div>
                                </td>
                                <td class="p-4">
                                    <span class="text-sm">{{ $ppdb->program->nama }}</span>
                                </td>
                                <td class="p-4 text-sm">{{ $ppdb->academicYear->nama }}</td>
                                <td class="p-4 text-sm">{{ $ppdb->created_at->format('d/m/Y') }}</td>
                                <td class="p-4">
                                    <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-medium {{ $ppdb->status_badge_class }}">
                                        {{ $ppdb->status_label }}
                                    </span>
                                </td>
                                <td class="p-4 text-right">
                                    <a href="{{ route('ppdb.show', $ppdb) }}" class="p-2 text-primary hover:bg-primary-soft rounded-lg transition-colors inline-block" title="Detail">
                                        <x-icon name="eye" class="w-5 h-5" />
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="p-8 text-center text-muted">
                                    <div class="flex flex-col items-center justify-center">
                                        <x-icon name="inbox" class="w-12 h-12 text-muted mb-3 opacity-50" />
                                        <p>Belum ada data pendaftaran.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($ppdbs->hasPages())
                <div class="p-4 border-t border-border">
                    {{ $ppdbs->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
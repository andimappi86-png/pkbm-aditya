<x-app-layout>
    <x-slot name="title">Manajemen Surat</x-slot>

    <div class="space-y-6">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h2 class="text-2xl font-bold text-navy">Surat Keterangan</h2>
                <p class="text-muted mt-1">Kelola pembuatan dan arsip surat keterangan peserta didik.</p>
            </div>
            @can('letters.create')
            <a href="{{ route('letters.create') }}" class="btn-primary">
                <x-icon name="file-plus" class="w-5 h-5 mr-2" />
                Buat Surat Baru
            </a>
            @endcan
        </div>

        <!-- Filter & Search -->
        <div class="card p-4">
            <form action="{{ route('letters.index') }}" method="GET" class="flex flex-col md:flex-row gap-4">
                <div class="flex-1">
                    <x-input-label for="search" value="Cari" />
                    <x-text-input id="search" name="search" type="text" class="w-full mt-1" value="{{ request('search') }}" placeholder="Nomor surat / nama / tujuan" />
                </div>
                <div class="w-full md:w-48">
                    <x-input-label for="jenis_surat" value="Jenis Surat" />
                    <x-select id="jenis_surat" name="jenis_surat" class="w-full mt-1">
                        <option value="">Semua Jenis</option>
                        @foreach(\App\Models\Letter::jenisOptions() as $val => $label)
                            <option value="{{ $val }}" {{ request('jenis_surat') === $val ? 'selected' : '' }}>{{ $label }}</option>
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
                            <th class="p-4 font-semibold text-sm">Nomor Surat</th>
                            <th class="p-4 font-semibold text-sm">Jenis Surat</th>
                            <th class="p-4 font-semibold text-sm">Penerima / Peserta Didik</th>
                            <th class="p-4 font-semibold text-sm">Tanggal</th>
                            <th class="p-4 font-semibold text-sm">Tujuan</th>
                            <th class="p-4 font-semibold text-sm text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border">
                        @forelse($letters as $letter)
                            <tr class="hover:bg-background transition-colors">
                                <td class="p-4">
                                    <span class="font-mono text-sm font-medium text-navy">{{ $letter->nomor_surat }}</span>
                                </td>
                                <td class="p-4">
                                    <span class="text-sm">{{ $letter->jenis_label }}</span>
                                </td>
                                <td class="p-4">
                                    @if($letter->student)
                                        <div class="text-navy font-medium">{{ $letter->student->nama_lengkap }}</div>
                                        <div class="text-xs text-muted">NIS: {{ $letter->student->nis }}</div>
                                    @else
                                        <span class="text-muted italic">Umum</span>
                                    @endif
                                </td>
                                <td class="p-4 text-sm">{{ $letter->tanggal->format('d/m/Y') }}</td>
                                <td class="p-4 text-sm">{{ $letter->tujuan ?? '-' }}</td>
                                <td class="p-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('letters.show', $letter) }}" class="p-2 text-primary hover:bg-primary-soft rounded-lg transition-colors inline-block" title="Detail">
                                            <x-icon name="eye" class="w-5 h-5" />
                                        </a>
                                        @can('letters.print')
                                        <a href="{{ route('letters.print', $letter) }}" target="_blank" class="p-2 text-primary hover:bg-primary-soft rounded-lg transition-colors inline-block" title="Cetak">
                                            <x-icon name="printer" class="w-5 h-5" />
                                        </a>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="p-8 text-center text-muted">
                                    <div class="flex flex-col items-center justify-center">
                                        <x-icon name="file-text" class="w-12 h-12 text-muted mb-3 opacity-50" />
                                        <p>Belum ada surat.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($letters->hasPages())
                <div class="p-4 border-t border-border">
                    {{ $letters->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
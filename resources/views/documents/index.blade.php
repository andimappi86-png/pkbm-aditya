<x-app-layout>
    <x-slot name="title">Manajemen Dokumen</x-slot>

    <div class="space-y-6">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h2 class="text-2xl font-bold text-navy">Dokumen Peserta Didik</h2>
                <p class="text-muted mt-1">Kelola dan arsipkan dokumen persuratan dan identitas.</p>
            </div>
            @can('documents.upload')
            <a href="{{ route('documents.create') }}" class="btn-primary">
                <x-icon name="upload" class="w-5 h-5 mr-2" />
                Upload Dokumen
            </a>
            @endcan
        </div>

        <!-- Filter & Search -->
        <div class="card p-4">
            <form action="{{ route('documents.index') }}" method="GET" class="flex flex-col md:flex-row gap-4">
                <div class="flex-1">
                    <x-input-label for="search" value="Cari" />
                    <x-text-input id="search" name="search" type="text" class="w-full mt-1" value="{{ request('search') }}" placeholder="Nama file / nama pemilik" />
                </div>
                <div class="w-full md:w-48">
                    <x-input-label for="jenis_dokumen" value="Jenis" />
                    <x-select id="jenis_dokumen" name="jenis_dokumen" class="w-full mt-1">
                        <option value="">Semua Jenis</option>
                        @foreach(\App\Models\Document::jenisOptions() as $val => $label)
                            <option value="{{ $val }}" {{ request('jenis_dokumen') === $val ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </x-select>
                </div>
                <div class="w-full md:w-48">
                    <x-input-label for="status_verifikasi" value="Status" />
                    <x-select id="status_verifikasi" name="status_verifikasi" class="w-full mt-1">
                        <option value="">Semua Status</option>
                        <option value="pending" {{ request('status_verifikasi') === 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="diverifikasi" {{ request('status_verifikasi') === 'diverifikasi' ? 'selected' : '' }}>Diverifikasi</option>
                        <option value="ditolak" {{ request('status_verifikasi') === 'ditolak' ? 'selected' : '' }}>Ditolak</option>
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
                            <th class="p-4 font-semibold text-sm">Nama File</th>
                            <th class="p-4 font-semibold text-sm">Pemilik / Terkait</th>
                            <th class="p-4 font-semibold text-sm">Jenis Dokumen</th>
                            <th class="p-4 font-semibold text-sm">Tanggal Upload</th>
                            <th class="p-4 font-semibold text-sm">Status</th>
                            <th class="p-4 font-semibold text-sm text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border">
                        @forelse($documents as $doc)
                            <tr class="hover:bg-background transition-colors">
                                <td class="p-4">
                                    <div class="flex items-center gap-2">
                                        <x-icon name="file-text" class="w-5 h-5 text-muted" />
                                        <span class="font-medium text-navy">{{ $doc->nama_file }}</span>
                                    </div>
                                </td>
                                <td class="p-4">
                                    @if($doc->student)
                                        <div class="text-navy font-medium">{{ $doc->student->nama_lengkap }}</div>
                                        <div class="text-xs text-muted">Peserta Didik</div>
                                    @elseif($doc->ppdb)
                                        <div class="text-navy font-medium">{{ $doc->ppdb->nama_lengkap }}</div>
                                        <div class="text-xs text-muted">Calon Peserta</div>
                                    @else
                                        <div class="text-navy font-medium">{{ $doc->catatan ?: 'Dokumen Manual / Umum' }}</div>
                                        <div class="text-xs text-muted">Umum / Tanpa Tautan</div>
                                    @endif
                                </td>
                                <td class="p-4">
                                    <span class="text-sm">{{ $doc->jenis_label }}</span>
                                </td>
                                <td class="p-4 text-sm">{{ $doc->uploaded_at->format('d/m/Y H:i') }}</td>
                                <td class="p-4">
                                    <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-medium {{ $doc->status_badge_class }}">
                                        {{ $doc->status_label }}
                                    </span>
                                </td>
                                <td class="p-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('documents.show', $doc) }}" class="p-2 text-primary hover:bg-primary-soft rounded-lg transition-colors inline-block" title="Detail">
                                            <x-icon name="eye" class="w-5 h-5" />
                                        </a>
                                        @can('documents.download')
                                        <a href="{{ route('documents.download', $doc) }}" class="p-2 text-primary hover:bg-primary-soft rounded-lg transition-colors inline-block" title="Download">
                                            <x-icon name="download" class="w-5 h-5" />
                                        </a>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="p-8 text-center text-muted">
                                    <div class="flex flex-col items-center justify-center">
                                        <x-icon name="inbox" class="w-12 h-12 text-muted mb-3 opacity-50" />
                                        <p>Belum ada dokumen.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($documents->hasPages())
                <div class="p-4 border-t border-border">
                    {{ $documents->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
<x-app-layout>
    <x-slot name="title">Laporan PPDB</x-slot>
    <div class="max-w-7xl mx-auto space-y-6">
        <div class="flex justify-between items-center">
            <h2 class="text-2xl font-bold text-navy">Laporan PPDB</h2>
            <div class="flex gap-2">
                <a href="{{ route('reports.index') }}" class="btn-secondary">Kembali</a>
                <a href="{{ route('reports.print', ['type' => 'ppdb'] + request()->all()) }}" target="_blank" class="btn-primary">
                    <x-icon name="printer" class="w-4 h-4 mr-2" /> Cetak
                </a>
            </div>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="card p-4 text-center">
                <div class="text-2xl font-bold text-navy">{{ $stats['total'] }}</div>
                <div class="text-sm text-muted">Total Pendaftar</div>
            </div>
            <div class="card p-4 text-center">
                <div class="text-2xl font-bold text-yellow-600">{{ $stats['pending'] }}</div>
                <div class="text-sm text-muted">Menunggu Verifikasi</div>
            </div>
            <div class="card p-4 text-center">
                <div class="text-2xl font-bold text-green-600">{{ $stats['diterima'] }}</div>
                <div class="text-sm text-muted">Diterima</div>
            </div>
            <div class="card p-4 text-center">
                <div class="text-2xl font-bold text-red-600">{{ $stats['ditolak'] }}</div>
                <div class="text-sm text-muted">Ditolak</div>
            </div>
        </div>
        
        <div class="card overflow-hidden">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-surface border-b border-border">
                        <th class="p-4 font-semibold text-navy">No. Daftar</th>
                        <th class="p-4 font-semibold text-navy">Nama Lengkap</th>
                        <th class="p-4 font-semibold text-navy">NIK</th>
                        <th class="p-4 font-semibold text-navy">Program</th>
                        <th class="p-4 font-semibold text-navy">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border">
                    @forelse($ppdbs as $ppdb)
                    <tr class="hover:bg-surface/50">
                        <td class="p-4">{{ $ppdb->nomor_pendaftaran }}</td>
                        <td class="p-4">{{ $ppdb->nama_lengkap }}</td>
                        <td class="p-4">{{ $ppdb->nik }}</td>
                        <td class="p-4">{{ $ppdb->program->nama ?? '-' }}</td>
                        <td class="p-4">
                            <span class="px-2 py-1 text-xs rounded-full {{ $ppdb->status_badge_class }}">{{ ucfirst($ppdb->status) }}</span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="p-8 text-center text-muted">Tidak ada data pendaftaran.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
            @if($ppdbs->hasPages())
                <div class="p-4 border-t border-border">
                    {{ $ppdbs->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
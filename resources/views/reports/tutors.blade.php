<x-app-layout>
    <x-slot name="title">Laporan Tutor</x-slot>
    <div class="max-w-7xl mx-auto space-y-6">
        <div class="flex justify-between items-center">
            <h2 class="text-2xl font-bold text-navy">Laporan Tutor</h2>
            <div class="flex gap-2">
                <a href="{{ route('reports.index') }}" class="btn-secondary">Kembali</a>
                <a href="{{ route('reports.print', ['type' => 'tutors'] + request()->all()) }}" target="_blank" class="btn-primary">
                    <x-icon name="printer" class="w-4 h-4 mr-2" /> Cetak
                </a>
            </div>
        </div>
        
        <!-- Filters -->
        <div class="card p-6">
            <form action="{{ route('reports.tutors') }}" method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <x-input-label value="Cari Nama" />
                    <x-text-input name="search" value="{{ $filters['search'] }}" class="w-full" />
                </div>
                <div class="flex items-end">
                    <button type="submit" class="btn-primary w-full justify-center">Filter</button>
                </div>
            </form>
        </div>
        
        <div class="card overflow-hidden">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-surface border-b border-border">
                        <th class="p-4 font-semibold text-navy">NIP</th>
                        <th class="p-4 font-semibold text-navy">Nama Tutor</th>
                        <th class="p-4 font-semibold text-navy">Mata Pelajaran Diampu</th>
                        <th class="p-4 font-semibold text-navy">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border">
                    @forelse($tutors as $tutor)
                    <tr class="hover:bg-surface/50">
                        <td class="p-4">{{ $tutor->nomor_induk ?? '-' }}</td>
                        <td class="p-4">{{ $tutor->nama_lengkap }}</td>
                        <td class="p-4">
                            @foreach($tutor->subjects as $subject)
                                <span class="bg-gray-100 px-2 py-1 rounded text-xs">{{ $subject->nama }}</span>
                            @endforeach
                        </td>
                        <td class="p-4">
                            <span class="px-2 py-1 bg-green-100 text-green-800 text-xs rounded-full">Aktif</span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="p-8 text-center text-muted">Tidak ada data tutor.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
            @if($tutors->hasPages())
                <div class="p-4 border-t border-border">
                    {{ $tutors->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
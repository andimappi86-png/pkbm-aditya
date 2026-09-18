<x-app-layout>
    <x-slot name="title">Laporan Nilai</x-slot>
    <div class="max-w-7xl mx-auto space-y-6">
        <div class="flex justify-between items-center">
            <h2 class="text-2xl font-bold text-navy">Laporan Nilai Siswa</h2>
            <div class="flex gap-2">
                <a href="{{ route('reports.index') }}" class="btn-secondary">Kembali</a>
                <a href="{{ route('reports.print', ['type' => 'grades'] + request()->all()) }}" target="_blank" class="btn-primary">
                    <x-icon name="printer" class="w-4 h-4 mr-2" /> Cetak
                </a>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="card p-4 text-center">
                <div class="text-2xl font-bold text-navy">{{ $stats['count'] }}</div>
                <div class="text-sm text-muted">Total Penilaian</div>
            </div>
            <div class="card p-4 text-center">
                <div class="text-2xl font-bold text-primary">{{ number_format($stats['avg'], 1) }}</div>
                <div class="text-sm text-muted">Rata-rata Nilai</div>
            </div>
        </div>
        
        <div class="card overflow-hidden">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-surface border-b border-border">
                        <th class="p-4 font-semibold text-navy">Siswa</th>
                        <th class="p-4 font-semibold text-navy">Rombel</th>
                        <th class="p-4 font-semibold text-navy">Mata Pelajaran</th>
                        <th class="p-4 font-semibold text-navy">Tipe</th>
                        <th class="p-4 font-semibold text-navy">Nilai</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border">
                    @forelse($grades as $grade)
                    <tr class="hover:bg-surface/50">
                        <td class="p-4">{{ $grade->student->user->name ?? '-' }}</td>
                        <td class="p-4">{{ $grade->student->classroom->nama_rombel ?? '-' }}</td>
                        <td class="p-4">{{ $grade->assignment->penugasanMengajar->subject->nama ?? '-' }}</td>
                        <td class="p-4">{{ $grade->type ?? 'Tugas' }}</td>
                        <td class="p-4 font-semibold">{{ $grade->nilai }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="p-8 text-center text-muted">Tidak ada data nilai.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
            @if($grades->hasPages())
                <div class="p-4 border-t border-border">
                    {{ $grades->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
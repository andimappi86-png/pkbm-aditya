<x-app-layout>
    <x-slot name="title">Laporan Kehadiran</x-slot>
    <div class="max-w-7xl mx-auto space-y-6">
        <div class="flex justify-between items-center">
            <h2 class="text-2xl font-bold text-navy">Laporan Kehadiran</h2>
            <div class="flex gap-2">
                <a href="{{ route('reports.index') }}" class="btn-secondary">Kembali</a>
                <a href="{{ route('reports.print', ['type' => 'attendance'] + request()->all()) }}" target="_blank" class="btn-primary">
                    <x-icon name="printer" class="w-4 h-4 mr-2" /> Cetak
                </a>
            </div>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="card p-4 text-center">
                <div class="text-2xl font-bold text-navy">{{ $stats['total'] }}</div>
                <div class="text-sm text-muted">Total Data</div>
            </div>
            <div class="card p-4 text-center">
                <div class="text-2xl font-bold text-green-600">{{ $stats['hadir'] }}</div>
                <div class="text-sm text-muted">Hadir/Izin</div>
            </div>
            <div class="card p-4 text-center">
                <div class="text-2xl font-bold text-red-600">{{ $stats['alpha'] }}</div>
                <div class="text-sm text-muted">Alpha</div>
            </div>
            <div class="card p-4 text-center">
                <div class="text-2xl font-bold text-primary">{{ $stats['rate'] }}%</div>
                <div class="text-sm text-muted">Persentase Kehadiran</div>
            </div>
        </div>
        
        <div class="card overflow-hidden">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-surface border-b border-border">
                        <th class="p-4 font-semibold text-navy">Tanggal</th>
                        <th class="p-4 font-semibold text-navy">Siswa</th>
                        <th class="p-4 font-semibold text-navy">Rombel</th>
                        <th class="p-4 font-semibold text-navy">Mata Pelajaran</th>
                        <th class="p-4 font-semibold text-navy">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border">
                    @forelse($attendances as $attendance)
                    <tr class="hover:bg-surface/50">
                        <td class="p-4">{{ $attendance->tanggal->format('d/m/Y') }}</td>
                        <td class="p-4">{{ $attendance->student->user->name ?? '-' }}</td>
                        <td class="p-4">{{ $attendance->student->classroom->nama_rombel ?? '-' }}</td>
                        <td class="p-4">{{ $attendance->schedule->subject->nama ?? '-' }}</td>
                        <td class="p-4">
                            @if($attendance->status == 'hadir')
                                <span class="px-2 py-1 bg-green-100 text-green-800 text-xs rounded-full">Hadir</span>
                            @elseif($attendance->status == 'izin')
                                <span class="px-2 py-1 bg-yellow-100 text-yellow-800 text-xs rounded-full">Izin</span>
                            @elseif($attendance->status == 'sakit')
                                <span class="px-2 py-1 bg-blue-100 text-blue-800 text-xs rounded-full">Sakit</span>
                            @else
                                <span class="px-2 py-1 bg-red-100 text-red-800 text-xs rounded-full">Alpha</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="p-8 text-center text-muted">Tidak ada data kehadiran.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
            @if($attendances->hasPages())
                <div class="p-4 border-t border-border">
                    {{ $attendances->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
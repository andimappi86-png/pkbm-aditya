<x-app-layout>
    <x-slot name="title">Laporan Tugas & Kuis</x-slot>
    <div class="max-w-7xl mx-auto space-y-6">
        <div class="flex justify-between items-center">
            <h2 class="text-2xl font-bold text-navy">Laporan Tugas & Kuis</h2>
            <div class="flex gap-2">
                <a href="{{ route('reports.index') }}" class="btn-secondary">Kembali</a>
                <a href="{{ route('reports.print', ['type' => 'assignments'] + request()->all()) }}" target="_blank" class="btn-primary">
                    <x-icon name="printer" class="w-4 h-4 mr-2" /> Cetak
                </a>
            </div>
        </div>
        
        <div class="card overflow-hidden">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-surface border-b border-border">
                        <th class="p-4 font-semibold text-navy">Judul</th>
                        <th class="p-4 font-semibold text-navy">Tipe</th>
                        <th class="p-4 font-semibold text-navy">Mata Pelajaran</th>
                        <th class="p-4 font-semibold text-navy">Rombel</th>
                        <th class="p-4 font-semibold text-navy">Tutor</th>
                        <th class="p-4 font-semibold text-navy">Deadline</th>
                        <th class="p-4 font-semibold text-navy">Dikumpulkan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border">
                    @forelse($assignments as $assignment)
                    <tr class="hover:bg-surface/50">
                        <td class="p-4">{{ $assignment->judul }}</td>
                        <td class="p-4">
                            <span class="px-2 py-1 bg-blue-100 text-blue-800 text-xs rounded-full">{{ $assignment->tipe ?? 'Tugas' }}</span>
                        </td>
                        <td class="p-4">{{ $assignment->penugasanMengajar->subject->nama ?? '-' }}</td>
                        <td class="p-4">{{ $assignment->penugasanMengajar->classroom->nama_rombel ?? '-' }}</td>
                        <td class="p-4">{{ $assignment->penugasanMengajar->tutor->user->name ?? '-' }}</td>
                        <td class="p-4">{{ $assignment->deadline ? $assignment->deadline->format('d/m/Y H:i') : '-' }}</td>
                        <td class="p-4 text-center">{{ $assignment->submissions_count ?? 0 }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="p-8 text-center text-muted">Tidak ada data tugas/kuis.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
            @if($assignments->hasPages())
                <div class="p-4 border-t border-border">
                    {{ $assignments->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
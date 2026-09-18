<x-app-layout>
    <x-slot name="title">Laporan Aktivitas Pembelajaran</x-slot>
    <div class="max-w-7xl mx-auto space-y-6">
        <div class="flex justify-between items-center">
            <h2 class="text-2xl font-bold text-navy">Laporan Aktivitas Pembelajaran</h2>
            <div class="flex gap-2">
                <a href="{{ route('reports.index') }}" class="btn-secondary">Kembali</a>
                <a href="{{ route('reports.print', ['type' => 'learning'] + request()->all()) }}" target="_blank" class="btn-primary">
                    <x-icon name="printer" class="w-4 h-4 mr-2" /> Cetak
                </a>
            </div>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
            <div class="card p-4 text-center">
                <div class="text-2xl font-bold text-primary">{{ $stats['total_materi'] }}</div>
                <div class="text-sm text-muted">Total Materi</div>
            </div>
            <div class="card p-4 text-center">
                <div class="text-2xl font-bold text-primary">{{ $stats['total_tugas'] }}</div>
                <div class="text-sm text-muted">Total Tugas</div>
            </div>
            <div class="card p-4 text-center">
                <div class="text-2xl font-bold text-primary">{{ $stats['total_kuis'] }}</div>
                <div class="text-sm text-muted">Total Kuis</div>
            </div>
            <div class="card p-4 text-center">
                <div class="text-2xl font-bold text-primary">{{ $stats['total_pengumpulan'] }}</div>
                <div class="text-sm text-muted">Total Pengumpulan</div>
            </div>
            <div class="card p-4 text-center">
                <div class="text-2xl font-bold text-primary">{{ $stats['total_kuis_diikuti'] }}</div>
                <div class="text-sm text-muted">Kuis Diikuti</div>
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
                    </tr>
                </thead>
                <tbody class="divide-y divide-border">
                    @foreach($assignments as $assignment)
                    <tr class="hover:bg-surface/50">
                        <td class="p-4">{{ $assignment->judul }}</td>
                        <td class="p-4"><span class="px-2 py-1 bg-blue-100 text-blue-800 text-xs rounded-full">Tugas</span></td>
                        <td class="p-4">{{ $assignment->penugasanMengajar->subject->nama ?? '-' }}</td>
                        <td class="p-4">{{ $assignment->penugasanMengajar->classroom->nama_rombel ?? '-' }}</td>
                        <td class="p-4">{{ $assignment->penugasanMengajar->tutor->user->name ?? '-' }}</td>
                    </tr>
                    @endforeach
                    @foreach($quizzes as $quiz)
                    <tr class="hover:bg-surface/50">
                        <td class="p-4">{{ $quiz->judul }}</td>
                        <td class="p-4"><span class="px-2 py-1 bg-purple-100 text-purple-800 text-xs rounded-full">Kuis</span></td>
                        <td class="p-4">{{ $quiz->penugasanMengajar->subject->nama ?? '-' }}</td>
                        <td class="p-4">{{ $quiz->penugasanMengajar->classroom->nama_rombel ?? '-' }}</td>
                        <td class="p-4">{{ $quiz->penugasanMengajar->tutor->user->name ?? '-' }}</td>
                    </tr>
                    @endforeach
                    @foreach($materials as $material)
                    <tr class="hover:bg-surface/50">
                        <td class="p-4">{{ $material->judul }}</td>
                        <td class="p-4"><span class="px-2 py-1 bg-green-100 text-green-800 text-xs rounded-full">Materi</span></td>
                        <td class="p-4">{{ $material->penugasanMengajar->subject->nama ?? '-' }}</td>
                        <td class="p-4">{{ $material->penugasanMengajar->classroom->nama_rombel ?? '-' }}</td>
                        <td class="p-4">{{ $material->penugasanMengajar->tutor->user->name ?? '-' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
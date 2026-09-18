<x-app-layout>
    <x-slot name="title">Rekap Nilai Peserta Didik</x-slot>

    <div class="max-w-7xl mx-auto space-y-6">
        <div class="flex flex-col sm:flex-row gap-4 items-start sm:items-center justify-between">
            <x-breadcrumb :items="[['label' => 'Akademik'], ['label' => 'Nilai']]" />
            @can('grades.manage')
            <a href="{{ route('nilai.create') }}" class="inline-flex items-center justify-center rounded-xl bg-primary px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-primary-dark transition-colors focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2">
                <svg class="mr-2 -ml-1 w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Input Nilai Baru
            </a>
            @endcan
        </div>

        <!-- Filter -->
        <div class="bg-surface border border-border rounded-2xl p-4 shadow-xs">
            <form action="{{ route('nilai.index') }}" method="GET" class="flex flex-col sm:flex-row gap-4 items-end">
                <div class="flex-1 w-full">
                    <label for="keyword" class="block text-xs font-medium text-muted mb-1">Cari Siswa</label>
                    <input type="text" name="keyword" id="keyword" value="{{ request('keyword') }}" placeholder="Nama siswa..." class="block w-full rounded-xl border-border bg-background px-3 py-2 text-sm focus:border-primary focus:ring-primary">
                </div>
                <button type="submit" class="w-full sm:w-auto inline-flex items-center justify-center rounded-xl bg-white border border-border px-4 py-2 text-sm font-medium text-navy hover:bg-background transition-colors focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2 shadow-xs">
                    Terapkan Filter
                </button>
            </form>
        </div>

        <!-- Tabel Rekap Nilai -->
        <div class="bg-surface border border-border rounded-2xl overflow-hidden shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm whitespace-nowrap">
                    <thead class="bg-background/50 border-b border-border text-muted">
                        <tr>
                            <th class="px-4 py-3 font-medium">Siswa</th>
                            <th class="px-4 py-3 font-medium">Mata Pelajaran</th>
                            <th class="px-4 py-3 font-medium">Semester</th>
                            <th class="px-4 py-3 font-medium text-right">Tugas</th>
                            <th class="px-4 py-3 font-medium text-right">UTS</th>
                            <th class="px-4 py-3 font-medium text-right">UAS</th>
                            <th class="px-4 py-3 font-medium text-right">Akhir</th>
                            <th class="px-4 py-3 font-medium text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border">
                        @forelse($grades as $grade)
                        <tr class="hover:bg-background/50 transition-colors">
                            <td class="px-4 py-3">
                                <div class="font-medium text-navy">{{ $grade->student->nama_lengkap }}</div>
                                <div class="text-xs text-muted">{{ $grade->student->nomor_induk }}</div>
                            </td>
                            <td class="px-4 py-3">
                                <div>{{ $grade->penugasanMengajar->subject->nama }}</div>
                                <div class="text-xs text-muted">{{ $grade->penugasanMengajar->tutor->nama_lengkap }}</div>
                            </td>
                            <td class="px-4 py-3 text-muted">
                                {{ $grade->penugasanMengajar->semester->nama }}
                                <br><span class="text-xs">{{ $grade->penugasanMengajar->academicYear->nama }}</span>
                            </td>
                            <td class="px-4 py-3 text-right font-medium">{{ $grade->nilai_tugas ?? '-' }}</td>
                            <td class="px-4 py-3 text-right font-medium">{{ $grade->nilai_uts ?? '-' }}</td>
                            <td class="px-4 py-3 text-right font-medium">{{ $grade->nilai_uas ?? '-' }}</td>
                            <td class="px-4 py-3 text-right">
                                <span class="inline-flex items-center justify-center px-2 py-1 text-xs font-bold rounded-md {{ $grade->nilai_akhir >= 70 ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700' }}">
                                    {{ $grade->nilai_akhir ?? '-' }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right space-x-2">
                                <a href="{{ route('nilai.show', $grade) }}" class="text-primary hover:text-primary-dark font-medium transition-colors">Detail</a>
                                @can('grades.manage')
                                <a href="{{ route('nilai.edit', $grade) }}" class="text-primary hover:text-primary-dark font-medium transition-colors">Edit</a>
                                @endcan
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="px-4 py-8 text-center text-muted">Belum ada data nilai.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($grades->hasPages())
            <div class="p-4 border-t border-border">
                {{ $grades->links() }}
            </div>
            @endif
        </div>
    </div>
</x-app-layout>
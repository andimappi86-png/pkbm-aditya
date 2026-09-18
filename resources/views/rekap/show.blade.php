<x-app-layout>
    <x-slot name="title">Rekap Akademik: {{ $student->nama_lengkap }}</x-slot>

    <div class="max-w-6xl mx-auto space-y-6">
        <div class="flex flex-col sm:flex-row gap-4 items-start sm:items-center justify-between">
            <x-breadcrumb :items="[['label' => 'Laporan'], ['label' => 'Rekap Akademik', 'url' => route('rekap.index')], ['label' => $student->nama_lengkap]]" />
            
            <button onclick="window.print()" class="inline-flex items-center gap-2 rounded-xl bg-white border border-border px-4 py-2 text-sm font-medium text-navy hover:bg-background transition shadow-xs">
                <svg class="w-4 h-4 text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" /></svg>
                <span>Cetak Rekap</span>
            </button>
        </div>

        <!-- Profil Ringkas -->
        <div class="bg-surface border border-border rounded-2xl p-6 shadow-sm">
            <div class="flex flex-col md:flex-row justify-between gap-6">
                <div>
                    <h2 class="text-xl font-bold text-navy mb-1">{{ $student->nama_lengkap }}</h2>
                    <p class="text-sm text-muted">NISN / Nomor Induk: {{ $student->nomor_induk }}</p>
                    <div class="mt-4 flex flex-wrap gap-4 text-sm">
                        <div><span class="text-muted">Program:</span> <span class="font-medium text-navy">{{ $student->program?->nama ?? '-' }}</span></div>
                        <div><span class="text-muted">Rombel:</span> <span class="font-medium text-navy">{{ $student->classroom?->nama ?? '-' }}</span></div>
                        <div><span class="text-muted">Tahun Ajaran:</span> <span class="font-medium text-navy">{{ $student->academicYear?->nama ?? '-' }}</span></div>
                    </div>
                </div>

                <!-- KPI Cards -->
                <div class="flex gap-4">
                    <div class="p-4 bg-background rounded-2xl border border-border text-center min-w-[120px]">
                        <span class="text-xs font-semibold text-muted block mb-1">Kehadiran</span>
                        <span class="text-2xl font-black {{ $attendancePercentage >= 75 ? 'text-emerald-600' : 'text-amber-600' }}">{{ $attendancePercentage }}%</span>
                        <span class="text-[11px] text-muted block mt-1">{{ $attendanceStats['hadir'] ?? 0 }}/{{ $totalAttendance }} Pertemuan</span>
                    </div>
                    <div class="p-4 bg-background rounded-2xl border border-border text-center min-w-[120px]">
                        <span class="text-xs font-semibold text-muted block mb-1">Rata-rata Nilai</span>
                        <span class="text-2xl font-black text-primary">{{ round($averageGrade, 1) }}</span>
                        <span class="text-[11px] text-muted block mt-1">{{ $grades->count() }} Mata Pelajaran</span>
                    </div>
                    <div class="p-4 bg-background rounded-2xl border border-border text-center min-w-[120px]">
                        <span class="text-xs font-semibold text-muted block mb-1">Tugas Masuk</span>
                        <span class="text-2xl font-black text-navy">{{ $totalSubmissions }}</span>
                        <span class="text-[11px] text-muted block mt-1">{{ $gradedSubmissions }} Dinilai</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Rincian Nilai -->
            <div class="bg-surface border border-border rounded-2xl p-6 shadow-sm space-y-4">
                <h3 class="text-base font-bold text-navy">Rekap Nilai Per Mata Pelajaran</h3>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm whitespace-nowrap">
                        <thead class="bg-background/50 border-b border-border text-muted">
                            <tr>
                                <th class="px-3 py-2 font-medium">Mata Pelajaran</th>
                                <th class="px-3 py-2 text-center font-medium">Tugas</th>
                                <th class="px-3 py-2 text-center font-medium">UTS</th>
                                <th class="px-3 py-2 text-center font-medium">UAS</th>
                                <th class="px-3 py-2 text-right font-medium">Akhir</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border">
                            @forelse($grades as $gr)
                            <tr>
                                <td class="px-3 py-2.5">
                                    <div class="font-medium text-navy">{{ $gr->penugasanMengajar->subject->nama }}</div>
                                    <div class="text-xs text-muted">{{ $gr->penugasanMengajar->tutor->nama_lengkap }}</div>
                                </td>
                                <td class="px-3 py-2.5 text-center">{{ $gr->nilai_tugas ?? '-' }}</td>
                                <td class="px-3 py-2.5 text-center">{{ $gr->nilai_uts ?? '-' }}</td>
                                <td class="px-3 py-2.5 text-center">{{ $gr->nilai_uas ?? '-' }}</td>
                                <td class="px-3 py-2.5 text-right font-bold text-navy">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs {{ ($gr->nilai_akhir ?? 0) >= 70 ? 'bg-emerald-100 text-emerald-800' : 'bg-red-100 text-red-800' }}">
                                        {{ $gr->nilai_akhir ?? '-' }}
                                    </span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="px-3 py-6 text-center text-muted text-xs">Belum ada data nilai.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Rincian Tugas & Kehadiran -->
            <div class="space-y-6">
                <!-- Kehadiran Breakdown -->
                <div class="bg-surface border border-border rounded-2xl p-6 shadow-sm space-y-4">
                    <h3 class="text-base font-bold text-navy">Rincian Presensi</h3>
                    <div class="grid grid-cols-4 gap-3 text-center">
                        <div class="p-3 rounded-xl bg-emerald-50 border border-emerald-200">
                            <span class="text-xs text-emerald-800 font-semibold block">Hadir</span>
                            <span class="text-lg font-black text-emerald-700">{{ $attendanceStats['hadir'] ?? 0 }}</span>
                        </div>
                        <div class="p-3 rounded-xl bg-blue-50 border border-blue-200">
                            <span class="text-xs text-blue-800 font-semibold block">Izin</span>
                            <span class="text-lg font-black text-blue-700">{{ $attendanceStats['izin'] ?? 0 }}</span>
                        </div>
                        <div class="p-3 rounded-xl bg-amber-50 border border-amber-200">
                            <span class="text-xs text-amber-800 font-semibold block">Sakit</span>
                            <span class="text-lg font-black text-amber-700">{{ $attendanceStats['sakit'] ?? 0 }}</span>
                        </div>
                        <div class="p-3 rounded-xl bg-red-50 border border-red-200">
                            <span class="text-xs text-red-800 font-semibold block">Alpa</span>
                            <span class="text-lg font-black text-red-700">{{ $attendanceStats['alpa'] ?? 0 }}</span>
                        </div>
                    </div>
                </div>

                <!-- Submissions Breakdown -->
                <div class="bg-surface border border-border rounded-2xl p-6 shadow-sm space-y-4">
                    <h3 class="text-base font-bold text-navy">Riwayat Pengumpulan Tugas</h3>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm whitespace-nowrap">
                            <thead class="bg-background/50 border-b border-border text-muted">
                                <tr>
                                    <th class="px-3 py-2 font-medium">Tugas</th>
                                    <th class="px-3 py-2 font-medium">Status</th>
                                    <th class="px-3 py-2 text-right font-medium">Nilai</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-border">
                                @forelse($submissions as $sub)
                                <tr>
                                    <td class="px-3 py-2">
                                        <div class="font-medium text-navy">{{ $sub->assignment->judul }}</div>
                                        <div class="text-xs text-muted">{{ $sub->assignment->penugasanMengajar->subject->nama }}</div>
                                    </td>
                                    <td class="px-3 py-2">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold {{ $sub->status === 'graded' ? 'bg-emerald-100 text-emerald-800' : 'bg-blue-100 text-blue-800' }}">
                                            {{ ucfirst($sub->status) }}
                                        </span>
                                    </td>
                                    <td class="px-3 py-2 text-right font-bold text-navy">{{ $sub->nilai ?? '-' }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="3" class="px-3 py-4 text-center text-muted text-xs">Belum ada riwayat tugas.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

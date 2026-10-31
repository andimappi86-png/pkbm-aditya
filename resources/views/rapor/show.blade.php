<x-app-layout>
    <x-slot name="title">Rapor: {{ $student->nama_lengkap }}</x-slot>

    <div class="max-w-5xl mx-auto space-y-6">
        <div class="flex flex-col sm:flex-row gap-4 items-start sm:items-center justify-between">
            <x-breadcrumb :items="[['label' => 'Laporan'], ['label' => 'Rapor', 'url' => route('rapor.index')], ['label' => $student->nama_lengkap]]" />
            
            <a href="{{ route('rapor.print', $student) }}?academic_year_id={{ $selectedAcademicYear?->id }}&semester_id={{ $selectedSemester?->id }}" target="_blank" class="inline-flex items-center gap-2 rounded-xl bg-primary px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-primary-dark transition-colors focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" /></svg>
                <span>Cetak Rapor</span>
            </a>
        </div>

        <div class="bg-surface border border-border rounded-2xl p-6 shadow-sm mb-6">
            <form action="{{ route('rapor.show', $student) }}" method="GET" class="flex gap-4">
                <div>
                    <label class="block text-xs font-medium text-navy mb-1">Tahun Ajaran</label>
                    <select name="academic_year_id" onchange="this.form.submit()" class="block w-full rounded-xl border-border bg-background px-3 py-2 text-sm focus:border-primary focus:ring-primary">
                        @foreach($academicYears as $year)
                            <option value="{{ $year->id }}" {{ $selectedAcademicYear?->id == $year->id ? 'selected' : '' }}>{{ $year->nama }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-navy mb-1">Semester</label>
                    <select name="semester_id" onchange="this.form.submit()" class="block w-full rounded-xl border-border bg-background px-3 py-2 text-sm focus:border-primary focus:ring-primary">
                        @foreach($semesters as $sem)
                            <option value="{{ $sem->id }}" {{ $selectedSemester?->id == $sem->id ? 'selected' : '' }}>{{ $sem->nama }}</option>
                        @endforeach
                    </select>
                </div>
            </form>
        </div>

        <!-- Tampilan Preview Rapor (A4 like proportions but styled for screen) -->
        <div class="bg-white border border-border rounded-2xl p-8 md:p-12 shadow-sm text-navy max-w-4xl mx-auto">
            <div class="text-center border-b-2 border-navy pb-4 mb-6">
                <h1 class="text-xl font-bold uppercase tracking-wider">Laporan Hasil Belajar Peserta Didik</h1>
                <h2 class="text-lg font-bold uppercase">PKBM Aditya Kabupaten Mamuju</h2>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-2 mb-8 text-sm">
                <div class="flex"><span class="w-32 font-semibold">Nama Peserta Didik</span><span class="mr-2">:</span><span class="font-bold">{{ $student->nama_lengkap }}</span></div>
                <div class="flex"><span class="w-32 font-semibold">Program</span><span class="mr-2">:</span><span>{{ $student->program?->nama ?? '-' }}</span></div>
                <div class="flex"><span class="w-32 font-semibold">NIS / NISN</span><span class="mr-2">:</span><span>{{ $student->nis ?: ($student->nisn ?: '-') }}</span></div>
                <div class="flex">
                    <span class="w-32 font-semibold">Rombongan Belajar</span>
                    <span class="mr-2">:</span>
                    @if($student->classroom)
                        <a href="{{ route('rombel.show', $student->classroom) }}" class="font-semibold text-primary hover:underline inline-flex items-center gap-1" title="Lihat detail rombel {{ $student->classroom->nama_rombel }}">
                            <span>{{ $student->classroom->nama_rombel }}</span>
                            <svg class="w-3.5 h-3.5 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" /></svg>
                        </a>
                    @else
                        <span>-</span>
                    @endif
                </div>
                <div class="flex"><span class="w-32 font-semibold">Tahun Ajaran</span><span class="mr-2">:</span><span>{{ $selectedAcademicYear?->nama ?? '-' }}</span></div>
                <div class="flex"><span class="w-32 font-semibold">Semester</span><span class="mr-2">:</span><span>{{ $selectedSemester?->nama ?? '-' }}</span></div>
            </div>

            <h3 class="font-bold mb-3 text-sm">A. Capaian Kompetensi / Nilai Akademik</h3>
            <div class="overflow-x-auto mb-8 border border-border">
                <table class="w-full text-left text-sm">
                    <thead class="bg-gray-100 border-b border-border">
                        <tr>
                            <th class="px-4 py-2 font-semibold border-r border-border w-12 text-center">No</th>
                            <th class="px-4 py-2 font-semibold border-r border-border">Mata Pelajaran</th>
                            <th class="px-4 py-2 font-semibold border-r border-border text-center w-24">Tugas</th>
                            <th class="px-4 py-2 font-semibold border-r border-border text-center w-24">UTS</th>
                            <th class="px-4 py-2 font-semibold border-r border-border text-center w-24">UAS</th>
                            <th class="px-4 py-2 font-semibold border-r border-border text-center w-24">Nilai Akhir</th>
                            <th class="px-4 py-2 font-semibold">Tutor Pengampu</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border">
                        @forelse($grades as $idx => $gr)
                        <tr>
                            <td class="px-4 py-2 border-r border-border text-center">{{ $idx + 1 }}</td>
                            <td class="px-4 py-2 border-r border-border">{{ $gr->penugasanMengajar->subject->nama }}</td>
                            <td class="px-4 py-2 border-r border-border text-center">{{ $gr->nilai_tugas ?? '-' }}</td>
                            <td class="px-4 py-2 border-r border-border text-center">{{ $gr->nilai_uts ?? '-' }}</td>
                            <td class="px-4 py-2 border-r border-border text-center">{{ $gr->nilai_uas ?? '-' }}</td>
                            <td class="px-4 py-2 border-r border-border text-center font-bold">{{ $gr->nilai_akhir ?? '-' }}</td>
                            <td class="px-4 py-2 text-xs">{{ $gr->penugasanMengajar->tutor->nama_lengkap }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="px-4 py-6 text-center text-gray-500">Belum ada data nilai pada semester ini.</td>
                        </tr>
                        @endforelse
                    </tbody>
                    <tfoot class="bg-gray-50 border-t-2 border-border">
                        <tr>
                            <td colspan="5" class="px-4 py-2 text-right font-bold border-r border-border">Rata-rata Nilai</td>
                            <td class="px-4 py-2 text-center font-bold border-r border-border text-primary">{{ $averageGrade }}</td>
                            <td></td>
                        </tr>
                        <tr>
                            <td colspan="5" class="px-4 py-2 text-right font-bold border-r border-border">Status Pencapaian</td>
                            <td colspan="2" class="px-4 py-2 font-bold {{ $averageGrade >= 70 ? 'text-emerald-600' : 'text-amber-600' }}">
                                {{ $statusKelulusan }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <h3 class="font-bold mb-3 text-sm">B. Ketidakhadiran</h3>
            <div class="border border-border w-full max-w-sm text-sm">
                <div class="flex border-b border-border">
                    <div class="w-2/3 px-4 py-2 border-r border-border">Sakit</div>
                    <div class="w-1/3 px-4 py-2 text-center font-medium">{{ $totalSakit }} hari</div>
                </div>
                <div class="flex border-b border-border">
                    <div class="w-2/3 px-4 py-2 border-r border-border">Izin</div>
                    <div class="w-1/3 px-4 py-2 text-center font-medium">{{ $totalIzin }} hari</div>
                </div>
                <div class="flex border-b border-border">
                    <div class="w-2/3 px-4 py-2 border-r border-border">Tanpa Keterangan (Alpa)</div>
                    <div class="w-1/3 px-4 py-2 text-center font-medium">{{ $totalAlpa }} hari</div>
                </div>
                <div class="flex font-semibold bg-gray-50">
                    <div class="w-2/3 px-4 py-2 border-r border-border">Total Kehadiran</div>
                    <div class="w-1/3 px-4 py-2 text-center">{{ $totalHadir }} hari</div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
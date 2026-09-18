<x-app-layout>
    <x-slot name="title">Detail Nilai</x-slot>

    <div class="max-w-2xl mx-auto space-y-6">
        <x-breadcrumb :items="[['label' => 'Akademik'], ['label' => 'Nilai', 'url' => route('nilai.index')], ['label' => 'Detail Nilai']]" />

        <div class="bg-surface border border-border rounded-2xl p-6 shadow-sm space-y-6">
            <div class="border-b border-border pb-4">
                <h3 class="text-lg font-bold text-navy">{{ $grade->student->nama_lengkap }}</h3>
                <p class="text-sm text-muted">NISN / Nomor Induk: {{ $grade->student->nomor_induk }} | Rombel: {{ $grade->penugasanMengajar->classroom->nama }}</p>
            </div>

            <div class="grid grid-cols-2 gap-4 text-sm">
                <div>
                    <span class="text-muted block text-xs">Mata Pelajaran</span>
                    <span class="font-medium text-navy">{{ $grade->penugasanMengajar->subject->nama }}</span>
                </div>
                <div>
                    <span class="text-muted block text-xs">Tutor Pengajar</span>
                    <span class="font-medium text-navy">{{ $grade->penugasanMengajar->tutor->nama_lengkap }}</span>
                </div>
                <div>
                    <span class="text-muted block text-xs">Tahun Ajaran / Semester</span>
                    <span class="font-medium text-navy">{{ $grade->penugasanMengajar->academicYear->nama }} ({{ $grade->penugasanMengajar->semester->nama }})</span>
                </div>
            </div>

            <div class="border-t border-border pt-4">
                <h4 class="text-xs font-bold uppercase tracking-wider text-muted mb-3">Rincian Komponen Nilai</h4>
                <div class="grid grid-cols-4 gap-4 text-center">
                    <div class="p-3 bg-background rounded-xl border border-border">
                        <div class="text-xs text-muted mb-1">Tugas</div>
                        <div class="text-lg font-bold text-navy">{{ $grade->nilai_tugas ?? '-' }}</div>
                    </div>
                    <div class="p-3 bg-background rounded-xl border border-border">
                        <div class="text-xs text-muted mb-1">UTS</div>
                        <div class="text-lg font-bold text-navy">{{ $grade->nilai_uts ?? '-' }}</div>
                    </div>
                    <div class="p-3 bg-background rounded-xl border border-border">
                        <div class="text-xs text-muted mb-1">UAS</div>
                        <div class="text-lg font-bold text-navy">{{ $grade->nilai_uas ?? '-' }}</div>
                    </div>
                    <div class="p-3 bg-primary-soft/50 rounded-xl border border-primary/20">
                        <div class="text-xs text-primary-dark font-medium mb-1">Akhir</div>
                        <div class="text-lg font-extrabold text-primary-dark">{{ $grade->nilai_akhir ?? '-' }}</div>
                    </div>
                </div>
            </div>

            @if($grade->catatan)
            <div class="border-t border-border pt-4">
                <h4 class="text-xs font-bold uppercase tracking-wider text-muted mb-2">Catatan Kompetensi</h4>
                <p class="text-sm text-navy bg-background p-3 rounded-xl border border-border">{{ $grade->catatan }}</p>
            </div>
            @endif

            <div class="border-t border-border pt-4 flex justify-between">
                <a href="{{ route('nilai.index') }}" class="inline-flex items-center justify-center rounded-xl bg-white border border-border px-4 py-2 text-sm font-medium text-navy hover:bg-background transition-colors focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2 shadow-xs">
                    Kembali
                </a>
                @can('grades.manage')
                <div class="space-x-2">
                    <a href="{{ route('nilai.edit', $grade) }}" class="inline-flex items-center justify-center rounded-xl bg-primary px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-primary-dark transition-colors focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2">
                        Edit
                    </a>
                </div>
                @endcan
            </div>
        </div>
    </div>
</x-app-layout>
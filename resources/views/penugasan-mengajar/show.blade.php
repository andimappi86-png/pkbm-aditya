<x-app-layout>
    <x-slot name="title">Detail Penugasan Mengajar</x-slot>

    <div class="max-w-3xl mx-auto space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-navy">Detail Penugasan Mengajar</h2>
                <p class="text-sm text-muted mt-1">Informasi tutor, mata pelajaran, dan rombel yang diampu.</p>
            </div>
            <a href="{{ route('penugasan-mengajar.index') }}" class="btn-secondary">Kembali</a>
        </div>

        <div class="card p-6">
            <dl class="grid md:grid-cols-2 gap-x-6 gap-y-5">
                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wide text-muted">Tutor</dt>
                    <dd class="mt-1 text-sm font-medium text-navy">{{ $penugasanMengajar->tutor?->user?->name ?? '-' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wide text-muted">Mata Pelajaran</dt>
                    <dd class="mt-1 text-sm font-medium text-navy">{{ $penugasanMengajar->subject?->nama ?? '-' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wide text-muted">Rombel</dt>
                    <dd class="mt-1 text-sm font-medium text-navy">{{ $penugasanMengajar->classroom?->nama_rombel ?? '-' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wide text-muted">Tahun Ajaran</dt>
                    <dd class="mt-1 text-sm text-navy">{{ $penugasanMengajar->academicYear?->nama ?? '-' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wide text-muted">Semester</dt>
                    <dd class="mt-1 text-sm text-navy">{{ $penugasanMengajar->semester?->nama_semester ?? '-' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wide text-muted">Status</dt>
                    <dd class="mt-1">
                        <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold {{ $penugasanMengajar->status_aktif ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-600' }}">
                            {{ $penugasanMengajar->status_aktif ? 'Aktif' : 'Nonaktif' }}
                        </span>
                    </dd>
                </div>
            </dl>

            @can('classrooms.manage')
                <div class="flex justify-end gap-3 mt-8 pt-4 border-t border-border">
                    <a href="{{ route('penugasan-mengajar.index') }}" class="btn-secondary">Kembali</a>
                    <a href="{{ route('penugasan-mengajar.edit', $penugasanMengajar) }}" class="btn-primary">Edit Penugasan</a>
                </div>
            @endcan
        </div>
    </div>
</x-app-layout>

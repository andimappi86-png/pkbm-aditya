<x-app-layout>
    <x-slot name="title">Detail Jadwal Pelajaran</x-slot>

    <div class="max-w-4xl mx-auto space-y-6">
        <x-breadcrumb :items="[['label' => 'Akademik'], ['label' => 'Jadwal Pelajaran', 'url' => route('jadwal.index')], ['label' => 'Detail']]" />

        <div class="card p-6">
            <h3 class="text-lg font-semibold text-navy mb-6">Detail Jadwal Pelajaran</h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="text-xs text-muted font-medium uppercase">Hari</label>
                    <p class="text-base font-semibold text-navy">{{ $schedule->hari }}</p>
                </div>

                <div>
                    <label class="text-xs text-muted font-medium uppercase">Waktu</label>
                    <p class="text-base font-semibold text-navy">
                        {{ \Carbon\Carbon::parse($schedule->jam_mulai)->format('H:i') }} - {{ \Carbon\Carbon::parse($schedule->jam_selesai)->format('H:i') }}
                    </p>
                </div>

                <div>
                    <label class="text-xs text-muted font-medium uppercase">Rombel</label>
                    <p class="text-base font-semibold text-navy">{{ $schedule->penugasanMengajar?->classroom?->nama_rombel ?? '-' }}</p>
                </div>

                <div>
                    <label class="text-xs text-muted font-medium uppercase">Mata Pelajaran</label>
                    <p class="text-base font-semibold text-navy">{{ $schedule->penugasanMengajar?->subject?->nama ?? '-' }}</p>
                </div>

                <div>
                    <label class="text-xs text-muted font-medium uppercase">Tutor</label>
                    <p class="text-base font-semibold text-navy">{{ $schedule->penugasanMengajar?->tutor?->nama_lengkap ?? '-' }}</p>
                </div>

                <div>
                    <label class="text-xs text-muted font-medium uppercase">Ruangan</label>
                    <p class="text-base font-semibold text-navy">{{ $schedule->ruangan ?? '-' }}</p>
                </div>

                <div>
                    <label class="text-xs text-muted font-medium uppercase">Status</label>
                    <p>
                        @if($schedule->status_aktif)
                            <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-50 text-emerald-700 border border-emerald-200">
                                Aktif
                            </span>
                        @else
                            <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-rose-50 text-rose-700 border border-rose-200">
                                Nonaktif
                            </span>
                        @endif
                    </p>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-6 mt-6 border-t border-border">
                <a href="{{ route('jadwal.index') }}" class="btn-secondary">Kembali</a>
                @can('schedules.manage')
                <a href="{{ route('jadwal.edit', $schedule) }}" class="btn-primary">Edit Jadwal</a>
                @endcan
            </div>
        </div>
    </div>
</x-app-layout>

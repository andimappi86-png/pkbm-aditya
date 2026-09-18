<x-app-layout>
    <x-slot name="title">Detail Absensi</x-slot>

    <div class="max-w-2xl mx-auto space-y-6">
        <x-breadcrumb :items="[['label' => 'Akademik'], ['label' => 'Absensi', 'url' => route('absensi.index')], ['label' => 'Detail']]" />

        <div class="card p-6">
            <h3 class="text-lg font-semibold text-navy mb-6">Detail Data Absensi</h3>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="text-xs text-muted font-medium uppercase">Tanggal</label>
                    <p class="text-navy font-medium">{{ $attendance->tanggal->format('d M Y') }}</p>
                </div>
                <div>
                    <label class="text-xs text-muted font-medium uppercase">Status</label>
                    <p class="font-bold {{ $attendance->status == 'hadir' ? 'text-emerald-600' : 'text-rose-600' }}">{{ ucfirst($attendance->status) }}</p>
                </div>
                <div class="col-span-2">
                    <label class="text-xs text-muted font-medium uppercase">Peserta Didik</label>
                    <p class="text-navy font-semibold">{{ $attendance->student->nama_lengkap }}</p>
                </div>
                <div class="col-span-2">
                    <label class="text-xs text-muted font-medium uppercase">Keterangan</label>
                    <p class="text-navy">{{ $attendance->keterangan ?? '-' }}</p>
                </div>
            </div>

            <div class="mt-8 border-t border-border pt-4">
                <a href="{{ route('absensi.index') }}" class="btn-secondary">Kembali</a>
            </div>
        </div>
    </div>
</x-app-layout>
<x-app-layout>
    <x-slot name="title">Input Absensi Peserta Didik</x-slot>

    <div class="max-w-5xl mx-auto space-y-6">
        <x-breadcrumb :items="[['label' => 'Akademik'], ['label' => 'Absensi', 'url' => route('absensi.index')], ['label' => 'Input']]" />

        <div class="card p-6">
            <h3 class="text-lg font-semibold text-navy mb-6">Pilih Jadwal & Tanggal</h3>

            <form method="GET" action="{{ route('absensi.create') }}" class="grid grid-cols-1 md:grid-cols-2 gap-4 items-end mb-6">
                <div>
                    <label class="label">Jadwal Pelajaran <span class="text-accent">*</span></label>
                    <select name="schedule_id" class="input" required onchange="this.form.submit()">
                        <option value="">-- Pilih Jadwal --</option>
                        @foreach($schedules as $s)
                            <option value="{{ $s->id }}" {{ request('schedule_id') == $s->id ? 'selected' : '' }}>
                                {{ $s->hari }}: {{ $s->penugasanMengajar?->classroom?->nama_rombel }} - {{ $s->penugasanMengajar?->subject?->nama }} ({{ \Carbon\Carbon::parse($s->jam_mulai)->format('H:i') }} - {{ \Carbon\Carbon::parse($s->jam_selesai)->format('H:i') }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <button type="submit" class="btn-primary w-full md:w-auto">Tampilkan Daftar Siswa</button>
                </div>
            </form>

            @if($selectedSchedule)
                <div class="border-t border-border pt-6">
                    <div class="mb-4">
                        <h4 class="font-medium text-navy">Input Absensi Rombel: {{ $selectedSchedule->penugasanMengajar?->classroom?->nama_rombel }}</h4>
                        <p class="text-sm text-muted">Mata Pelajaran: {{ $selectedSchedule->penugasanMengajar?->subject?->nama }} | Tutor: {{ $selectedSchedule->penugasanMengajar?->tutor?->nama_lengkap }}</p>
                    </div>

                    <form action="{{ route('absensi.store') }}" method="POST">
                        @csrf
                        <input type="hidden" name="schedule_id" value="{{ $selectedSchedule->id }}">
                        
                        <div class="mb-6 max-w-xs">
                            <label class="label">Tanggal Absensi <span class="text-accent">*</span></label>
                            <input type="date" name="tanggal" value="{{ old('tanggal', date('Y-m-d')) }}" class="input @error('tanggal') border-accent @enderror" required />
                            @error('tanggal') <p class="text-xs text-accent mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div class="overflow-x-auto">
                            <table class="w-full text-left border-collapse text-sm">
                                <thead>
                                    <tr class="border-b border-border text-muted font-medium">
                                        <th class="py-3 px-4">Nama Siswa</th>
                                        <th class="py-3 px-4">Status</th>
                                        <th class="py-3 px-4">Keterangan</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-border">
                                    @forelse($students as $index => $student)
                                        <tr>
                                            <td class="py-3 px-4">
                                                <input type="hidden" name="attendances[{{ $index }}][student_id]" value="{{ $student->id }}">
                                                <div class="font-medium text-navy">{{ $student->nama_lengkap }}</div>
                                                <div class="text-[10px] text-muted">NIS: {{ $student->nis }}</div>
                                            </td>
                                            <td class="py-3 px-4">
                                                <div class="flex flex-wrap gap-4">
                                                    @foreach(['hadir', 'izin', 'sakit', 'alpa'] as $status)
                                                        <label class="flex items-center gap-1.5 cursor-pointer">
                                                            <input type="radio" name="attendances[{{ $index }}][status]" value="{{ $status }}" {{ old("attendances.{$index}.status", 'hadir') == $status ? 'checked' : '' }} class="text-primary focus:ring-primary/20">
                                                            <span class="text-xs text-navy uppercase">{{ $status }}</span>
                                                        </label>
                                                    @endforeach
                                                </div>
                                            </td>
                                            <td class="py-3 px-4">
                                                <input type="text" name="attendances[{{ $index }}][keterangan]" value="{{ old("attendances.{$index}.keterangan") }}" placeholder="Opsional" class="input py-1 text-xs w-full max-w-xs">
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="3" class="py-6 text-center text-muted">
                                                <div class="flex flex-col items-center justify-center space-y-1">
                                                    <p class="font-medium text-navy">Belum ada peserta didik dengan status aktif di rombel ini.</p>
                                                    <p class="text-xs text-muted">Pastikan siswa sudah ditempatkan ke rombel melalui menu Penugasan Rombel dan memiliki status aktif.</p>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        @if($students->isNotEmpty())
                            <div class="mt-8 flex justify-end gap-3 border-t border-border pt-4">
                                <a href="{{ route('absensi.index') }}" class="btn-secondary">Batal</a>
                                <button type="submit" class="btn-primary">Simpan Semua Absensi</button>
                            </div>
                        @endif
                    </form>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
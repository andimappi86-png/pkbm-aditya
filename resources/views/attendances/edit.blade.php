<x-app-layout>
    <x-slot name="title">Edit Absensi</x-slot>

    <div class="max-w-2xl mx-auto space-y-6">
        <x-breadcrumb :items="[['label' => 'Akademik'], ['label' => 'Absensi', 'url' => route('absensi.index')], ['label' => 'Edit']]" />

        <div class="card p-6">
            <h3 class="text-lg font-semibold text-navy mb-6">Edit Data Absensi</h3>
            
            <form action="{{ route('absensi.update', $attendance) }}" method="POST" class="space-y-4">
                @csrf
                @method('PUT')
                
                <input type="hidden" name="schedule_id" value="{{ $attendance->schedule_id }}">
                <input type="hidden" name="student_id" value="{{ $attendance->student_id }}">
                <input type="hidden" name="tanggal" value="{{ $attendance->tanggal->format('Y-m-d') }}">

                <div>
                    <label class="label">Peserta Didik</label>
                    <input type="text" value="{{ $attendance->student->nama_lengkap }}" class="input bg-gray-50" disabled />
                </div>

                <div>
                    <label class="label">Status <span class="text-accent">*</span></label>
                    <select name="status" class="input" required>
                        @foreach(['hadir', 'izin', 'sakit', 'alpa'] as $st)
                            <option value="{{ $st }}" {{ old('status', $attendance->status) == $st ? 'selected' : '' }}>{{ ucfirst($st) }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="label">Keterangan</label>
                    <textarea name="keterangan" class="input" rows="3">{{ old('keterangan', $attendance->keterangan) }}</textarea>
                </div>

                <div class="flex justify-end gap-3 pt-4 border-t border-border">
                    <a href="{{ route('absensi.index') }}" class="btn-secondary">Batal</a>
                    <button type="submit" class="btn-primary">Perbarui Absensi</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
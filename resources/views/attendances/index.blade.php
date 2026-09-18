<x-app-layout>
    <x-slot name="title">Rekap Absensi Peserta Didik</x-slot>

    <div class="max-w-7xl mx-auto space-y-6">
        <div class="flex flex-col sm:flex-row gap-4 items-start sm:items-center justify-between">
            <x-breadcrumb :items="[['label' => 'Akademik'], ['label' => 'Absensi Peserta Didik']]" />

            @can('attendance.manage')
                <a href="{{ route('absensi.create') }}" class="btn-primary flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    <span>Input Absensi</span>
                </a>
            @endcan
        </div>

        @if(session('success'))
            <div class="p-4 rounded-xl bg-emerald-50 text-emerald-700 border border-emerald-200">
                {{ session('success') }}
            </div>
        @endif

        <div class="card p-6">
            <h3 class="text-lg font-semibold text-navy mb-4">Daftar Rekap Absensi</h3>

            <form method="GET" action="{{ route('absensi.index') }}" class="mb-6 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
                <div>
                    <label class="label">Tanggal</label>
                    <input type="date" name="tanggal" value="{{ request('tanggal') }}" class="input" />
                </div>

                <div>
                    <label class="label">Rombel</label>
                    <select name="classroom_id" class="input">
                        <option value="">-- Semua --</option>
                        @foreach($classrooms as $cls)
                            <option value="{{ $cls->id }}" {{ request('classroom_id') == $cls->id ? 'selected' : '' }}>{{ $cls->nama_rombel }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="label">Tutor</label>
                    <select name="tutor_id" class="input">
                        <option value="">-- Semua --</option>
                        @foreach($tutors as $tutor)
                            <option value="{{ $tutor->id }}" {{ request('tutor_id') == $tutor->id ? 'selected' : '' }}>{{ $tutor->nama_lengkap }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="label">Mata Pelajaran</label>
                    <select name="subject_id" class="input">
                        <option value="">-- Semua --</option>
                        @foreach($subjects as $subject)
                            <option value="{{ $subject->id }}" {{ request('subject_id') == $subject->id ? 'selected' : '' }}>{{ $subject->nama }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="label">Status</label>
                    <select name="status" class="input">
                        <option value="">-- Semua --</option>
                        @foreach(['hadir', 'izin', 'sakit', 'alpa'] as $st)
                            <option value="{{ $st }}" {{ request('status') == $st ? 'selected' : '' }}>{{ ucfirst($st) }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="lg:col-span-5 flex justify-end gap-2">
                    <a href="{{ route('absensi.index') }}" class="btn-secondary">Reset</a>
                    <button type="submit" class="btn-primary">Filter</button>
                </div>
            </form>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-sm">
                    <thead>
                        <tr class="border-b border-border text-muted font-medium">
                            <th class="py-3 px-4">Tanggal</th>
                            <th class="py-3 px-4">Peserta Didik</th>
                            <th class="py-3 px-4">Rombel</th>
                            <th class="py-3 px-4">Mata Pelajaran</th>
                            <th class="py-3 px-4">Tutor</th>
                            <th class="py-3 px-4">Status</th>
                            <th class="py-3 px-4">Keterangan</th>
                            <th class="py-3 px-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border">
                        @forelse($attendances as $a)
                            <tr class="hover:bg-primary-soft/30 transition">
                                <td class="py-3 px-4">{{ $a->tanggal->format('d/m/Y') }}</td>
                                <td class="py-3 px-4 font-medium">{{ $a->student->nama_lengkap ?? '-' }}</td>
                                <td class="py-3 px-4">{{ $a->schedule?->penugasanMengajar?->classroom?->nama_rombel ?? '-' }}</td>
                                <td class="py-3 px-4">{{ $a->schedule?->penugasanMengajar?->subject?->nama ?? '-' }}</td>
                                <td class="py-3 px-4">{{ $a->schedule?->penugasanMengajar?->tutor?->nama_lengkap ?? '-' }}</td>
                                <td class="py-3 px-4">
                                    @php
                                        $status = $a->status;
                                        $cls = match($status) {
                                            'hadir' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                            'izin'  => 'bg-amber-50 text-amber-700 border-amber-200',
                                            'sakit' => 'bg-sky-50 text-sky-700 border-sky-200',
                                            'alpa'  => 'bg-rose-50 text-rose-700 border-rose-200',
                                            default => 'bg-gray-50 text-gray-700 border-gray-200',
                                        };
                                    @endphp
                                    <span class="px-2.5 py-0.5 rounded-full text-xs font-medium border {{ $cls }}">
                                        {{ ucfirst($status) }}
                                    </span>
                                </td>
                                <td class="py-3 px-4">{{ $a->keterangan ?? '-' }}</td>
                                <td class="py-3 px-4 text-right space-x-2">
                                    @can('attendance.manage')
                                        <a href="{{ route('absensi.edit', $a) }}" class="text-primary hover:underline font-medium">Edit</a>
                                        <form action="{{ route('absensi.destroy', $a) }}" method="POST" class="inline" onsubmit="return confirm('Hapus data absensi ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-accent hover:underline font-medium">Hapus</button>
                                        </form>
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="py-6 text-center text-muted">Belum ada data absensi.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $attendances->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
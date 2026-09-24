<x-app-layout>
    <x-slot name="title">Jadwal Pelajaran</x-slot>

    <div class="max-w-7xl mx-auto space-y-6">
        <div class="flex flex-col sm:flex-row gap-4 items-start sm:items-center justify-between">
            <x-breadcrumb :items="[['label' => 'Akademik'], ['label' => 'Jadwal Pelajaran']]" />
            
            @can('schedules.manage')
            <a href="{{ route('jadwal.create') }}" class="btn-primary flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                <span>Tambah Jadwal</span>
            </a>
            @endcan
        </div>

        @if(session('success'))
            <div class="p-4 rounded-xl bg-emerald-50 text-emerald-700 border border-emerald-200">
                {{ session('success') }}
            </div>
        @endif

        <div class="card p-6">
            <div class="flex flex-col sm:flex-row justify-between items-center mb-6 gap-4">
                <h3 class="text-lg font-semibold text-navy">Daftar Jadwal Pelajaran</h3>

                <!-- Filter Form -->
                <form method="GET" action="{{ route('jadwal.index') }}" class="flex flex-wrap items-center gap-3">
                    <select name="classroom_id" class="input py-1.5 text-sm" onchange="this.form.submit()">
                        <option value="">-- Semua Rombel --</option>
                        @foreach($classrooms as $cls)
                            <option value="{{ $cls->id }}" {{ request('classroom_id') == $cls->id ? 'selected' : '' }}>
                                {{ $cls->nama_rombel }}
                            </option>
                        @endforeach
                    </select>

                    <select name="hari" class="input py-1.5 text-sm" onchange="this.form.submit()">
                        <option value="">-- Semua Hari --</option>
                        @foreach(['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'] as $h)
                            <option value="{{ $h }}" {{ request('hari') == $h ? 'selected' : '' }}>
                                {{ $h }}
                            </option>
                        @endforeach
                    </select>
                </form>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-sm">
                    <thead>
                        <tr class="border-b border-border text-muted font-medium">
                            <th class="py-3 px-4">Hari / Waktu</th>
                            <th class="py-3 px-4">Rombel</th>
                            <th class="py-3 px-4">Mata Pelajaran</th>
                            <th class="py-3 px-4">Tutor</th>
                            <th class="py-3 px-4">Ruangan</th>
                            <th class="py-3 px-4">Status</th>
                            <th class="py-3 px-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border">
                        @forelse($schedules as $schedule)
                            <tr class="hover:bg-primary-soft/30 transition">
                                <td class="py-3 px-4">
                                    <div class="font-semibold text-navy">{{ $schedule->hari }}</div>
                                    <div class="text-xs text-muted">
                                        {{ \Carbon\Carbon::parse($schedule->jam_mulai)->format('H:i') }} - {{ \Carbon\Carbon::parse($schedule->jam_selesai)->format('H:i') }}
                                    </div>
                                </td>
                                <td class="py-3 px-4 font-medium">
                                    {{ $schedule->penugasanMengajar?->classroom?->nama_rombel ?? '-' }}
                                </td>
                                <td class="py-3 px-4">
                                    {{ $schedule->penugasanMengajar?->subject?->nama ?? '-' }}
                                </td>
                                <td class="py-3 px-4">
                                    {{ $schedule->penugasanMengajar?->tutor?->nama_lengkap ?? '-' }}
                                </td>
                                <td class="py-3 px-4">
                                    {{ $schedule->ruangan ?? '-' }}
                                </td>
                                <td class="py-3 px-4">
                                    @if($schedule->status_aktif)
                                        <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            Aktif
                                        </span>
                                    @else
                                        <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-rose-50 text-rose-700 border border-rose-200">
                                            Nonaktif
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-right space-x-2">
                                    <a href="{{ route('jadwal.show', $schedule) }}" class="text-primary hover:underline font-medium">
                                        Lihat
                                    </a>
                                    @can('schedules.manage')
                                    <a href="{{ route('jadwal.edit', $schedule) }}" class="text-primary hover:underline font-medium">
                                        Edit
                                    </a>
                                    <form action="{{ route('jadwal.destroy', $schedule) }}" method="POST" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus jadwal ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-accent hover:underline font-medium">
                                            Hapus
                                        </button>
                                    </form>
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-6 text-center text-muted">
                                    Belum ada data jadwal pelajaran.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $schedules->links() }}
            </div>
        </div>
    </div>
</x-app-layout>

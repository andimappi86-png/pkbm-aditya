<x-app-layout>
    <x-slot name="title">Daftar Rapor Peserta Didik</x-slot>

    <div class="max-w-7xl mx-auto space-y-6">
        <div class="flex flex-col sm:flex-row gap-4 items-start sm:items-center justify-between">
            <x-breadcrumb :items="[['label' => 'Laporan'], ['label' => 'Rapor']]" />
        </div>

        <!-- Filter Card -->
        <div class="bg-surface border border-border rounded-2xl p-5 shadow-xs">
            <form action="{{ route('rapor.index') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-5 gap-4">
                <div>
                    <label for="academic_year_id" class="block text-xs font-medium text-navy mb-1">Tahun Ajaran</label>
                    <select name="academic_year_id" id="academic_year_id" class="block w-full rounded-xl border-border bg-background px-3 py-2 text-sm focus:border-primary focus:ring-primary">
                        <option value="">Semua</option>
                        @foreach($academicYears as $year)
                            <option value="{{ $year->id }}" {{ request('academic_year_id') == $year->id ? 'selected' : '' }}>{{ $year->nama }}</option>
                        @endforeach
                    </select>
                </div>
                
                <div>
                    <label for="semester_id" class="block text-xs font-medium text-navy mb-1">Semester</label>
                    <select name="semester_id" id="semester_id" class="block w-full rounded-xl border-border bg-background px-3 py-2 text-sm focus:border-primary focus:ring-primary">
                        <option value="">Semua</option>
                        @foreach($semesters as $sem)
                            <option value="{{ $sem->id }}" {{ request('semester_id') == $sem->id ? 'selected' : '' }}>{{ $sem->nama }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="program_id" class="block text-xs font-medium text-navy mb-1">Program</label>
                    <select name="program_id" id="program_id" class="block w-full rounded-xl border-border bg-background px-3 py-2 text-sm focus:border-primary focus:ring-primary">
                        <option value="">Semua</option>
                        @foreach($programs as $prog)
                            <option value="{{ $prog->id }}" {{ request('program_id') == $prog->id ? 'selected' : '' }}>{{ $prog->nama }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="classroom_id" class="block text-xs font-medium text-navy mb-1">Rombel / Kelas</label>
                    <select name="classroom_id" id="classroom_id" class="block w-full rounded-xl border-border bg-background px-3 py-2 text-sm focus:border-primary focus:ring-primary">
                        <option value="">Semua</option>
                        @foreach($classrooms as $cls)
                            <option value="{{ $cls->id }}" {{ request('classroom_id') == $cls->id ? 'selected' : '' }}>{{ $cls->nama }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="keyword" class="block text-xs font-medium text-navy mb-1">Cari Nama/NISN</label>
                    <div class="flex gap-2">
                        <input type="text" name="keyword" id="keyword" value="{{ request('keyword') }}" placeholder="Cari..." class="block w-full rounded-xl border-border bg-background px-3 py-2 text-sm focus:border-primary focus:ring-primary">
                        <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-primary px-3 py-2 text-sm font-medium text-white shadow-xs hover:bg-primary-dark transition">
                            Cari
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Daftar Siswa -->
        <div class="bg-surface border border-border rounded-2xl overflow-hidden shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm whitespace-nowrap">
                    <thead class="bg-background/50 border-b border-border text-muted">
                        <tr>
                            <th class="px-4 py-3 font-medium">Siswa</th>
                            <th class="px-4 py-3 font-medium">Program</th>
                            <th class="px-4 py-3 font-medium">Rombel</th>
                            <th class="px-4 py-3 font-medium text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border">
                        @forelse($students as $st)
                        <tr class="hover:bg-background/50 transition-colors">
                            <td class="px-4 py-3">
                                <div class="font-medium text-navy">{{ $st->nama_lengkap }}</div>
                                <div class="text-xs text-muted">{{ $st->nomor_induk }}</div>
                            </td>
                            <td class="px-4 py-3 text-muted">{{ $st->program?->nama ?? '-' }}</td>
                            <td class="px-4 py-3 text-muted">{{ $st->classroom?->nama ?? '-' }}</td>
                            <td class="px-4 py-3 text-right">
                                <a href="{{ route('rapor.show', $st) }}?academic_year_id={{ request('academic_year_id') }}&semester_id={{ request('semester_id') }}" class="inline-flex items-center gap-1 text-sm font-medium text-primary hover:text-primary-dark transition">
                                    <span>Lihat Rapor</span>
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="px-4 py-8 text-center text-muted">Tidak ada data peserta didik yang cocok.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($students->hasPages())
            <div class="p-4 border-t border-border">
                {{ $students->links() }}
            </div>
            @endif
        </div>
    </div>
</x-app-layout>
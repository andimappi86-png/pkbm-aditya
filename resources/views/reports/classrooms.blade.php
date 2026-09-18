<x-app-layout>
    <x-slot name="title">Laporan Rombel</x-slot>
    <div class="max-w-7xl mx-auto space-y-6">
        <div class="flex justify-between items-center">
            <h2 class="text-2xl font-bold text-navy">Laporan Rombel</h2>
            <div class="flex gap-2">
                <a href="{{ route('reports.index') }}" class="btn-secondary">Kembali</a>
                <a href="{{ route('reports.print', ['type' => 'classrooms'] + request()->all()) }}" target="_blank" class="btn-primary">
                    <x-icon name="printer" class="w-4 h-4 mr-2" /> Cetak
                </a>
            </div>
        </div>
        
        <!-- Filters -->
        <div class="card p-6">
            <form action="{{ route('reports.classrooms') }}" method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <x-input-label value="Program" />
                    <select name="program_id" class="w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary">
                        <option value="">Semua Program</option>
                        @foreach(\App\Models\Program::all() as $p)
                            <option value="{{ $p->id }}" {{ $filters['program_id'] == $p->id ? 'selected' : '' }}>{{ $p->nama }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <x-input-label value="Tahun Ajaran" />
                    <select name="tahun_ajaran_id" class="w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary">
                        <option value="">Semua Tahun Ajaran</option>
                        @foreach(\App\Models\AcademicYear::all() as $ta)
                            <option value="{{ $ta->id }}" {{ $filters['tahun_ajaran_id'] == $ta->id ? 'selected' : '' }}>{{ $ta->nama }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex items-end">
                    <button type="submit" class="btn-primary w-full justify-center">Filter</button>
                </div>
            </form>
        </div>
        
        <div class="card overflow-hidden">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-surface border-b border-border">
                        <th class="p-4 font-semibold text-navy">Nama Rombel</th>
                        <th class="p-4 font-semibold text-navy">Program</th>
                        <th class="p-4 font-semibold text-navy">Tahun Ajaran</th>
                        <th class="p-4 font-semibold text-navy">Wali Kelas</th>
                        <th class="p-4 font-semibold text-navy">Jumlah Siswa</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border">
                    @forelse($classrooms as $classroom)
                    <tr class="hover:bg-surface/50">
                        <td class="p-4">{{ $classroom->nama_rombel }}</td>
                        <td class="p-4">{{ $classroom->program->nama ?? '-' }}</td>
                        <td class="p-4">{{ $classroom->academicYear->nama ?? '-' }}</td>
                        <td class="p-4">{{ $classroom->waliKelas->name ?? '-' }}</td>
                        <td class="p-4 text-center">{{ $classroom->students_count }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="p-8 text-center text-muted">Tidak ada data rombel.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
            @if($classrooms->hasPages())
                <div class="p-4 border-t border-border">
                    {{ $classrooms->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
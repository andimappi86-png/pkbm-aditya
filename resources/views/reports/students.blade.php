<x-app-layout>
    <x-slot name="title">Laporan Peserta Didik</x-slot>
    
    <div class="max-w-7xl mx-auto space-y-6">
        <div class="flex justify-between items-center">
            <h2 class="text-2xl font-bold text-navy">Laporan Peserta Didik</h2>
            <div class="flex gap-2">
                <a href="{{ route('reports.index') }}" class="btn-secondary">Kembali</a>
                <a href="{{ route('reports.print', ['type' => 'students'] + request()->all()) }}" target="_blank" class="btn-primary">
                    <x-icon name="printer" class="w-4 h-4 mr-2" /> Cetak
                </a>
            </div>
        </div>
        
        <!-- Filters -->
        <div class="card p-6">
            <form action="{{ route('reports.students') }}" method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <x-input-label value="Cari Nama/NIS" />
                    <x-text-input name="search" value="{{ $filters['search'] }}" class="w-full" />
                </div>
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
                    <x-input-label value="Rombel" />
                    <select name="rombel_id" class="w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary">
                        <option value="">Semua Rombel</option>
                        @foreach(\App\Models\Classroom::all() as $c)
                            <option value="{{ $c->id }}" {{ $filters['rombel_id'] == $c->id ? 'selected' : '' }}>{{ $c->nama_rombel }}</option>
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
                        <th class="p-4 font-semibold text-navy">NIS / NISN</th>
                        <th class="p-4 font-semibold text-navy">Nama Lengkap</th>
                        <th class="p-4 font-semibold text-navy">L/P</th>
                        <th class="p-4 font-semibold text-navy">Program</th>
                        <th class="p-4 font-semibold text-navy">Rombel</th>
                        <th class="p-4 font-semibold text-navy">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border">
                    @forelse($students as $student)
                    <tr class="hover:bg-surface/50">
                        <td class="p-4">
                            <div class="font-medium text-navy">{{ $student->nis }}</div>
                            <div class="text-sm text-muted">{{ $student->nisn }}</div>
                        </td>
                        <td class="p-4">{{ $student->nama_lengkap }}</td>
                        <td class="p-4">{{ $student->jenis_kelamin }}</td>
                        <td class="p-4">{{ $student->program->nama ?? '-' }}</td>
                        <td class="p-4">{{ $student->classroom->nama_rombel ?? '-' }}</td>
                        <td class="p-4">
                            <span class="px-2 py-1 bg-green-100 text-green-800 text-xs rounded-full">{{ $student->status }}</span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="p-8 text-center text-muted">Tidak ada data peserta didik.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
            @if($students->hasPages())
                <div class="p-4 border-t border-border">
                    {{ $students->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
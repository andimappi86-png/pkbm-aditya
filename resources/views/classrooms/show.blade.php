<x-app-layout>
    <x-slot name="title">{{ $classroom->nama_rombel }}</x-slot>

    <div class="max-w-4xl mx-auto">
        <x-breadcrumb :items="[
            ['label' => 'Rombel', 'url' => route('rombel.index')],
            ['label' => $classroom->nama_rombel]
        ]" />

        <div class="space-y-6">
            <div class="card p-6">
                <div class="flex items-start">
                    <div class="w-1/4">
                        <div class="space-y-2 text-center">
                            <div class="text-2xl font-bold text-navy">{{ $classroom->kode_rombel }}</div>
                            <div class="text-lg font-medium text-navy">{{ $classroom->nama_rombel }}</div>
                            @if($classroom->wali_kelas_id)
                                <div class="text-sm text-muted">Wali Kelas: {{ $classroom->homeroomTeacher->nama_lengkap }}</div>
                            @else
                                <div class="text-sm text-muted">Belum ada wali kelas</div>
                            @endif
                            <div class="text-xs text-muted">Siswa: {{ $classroom->students->count() }}</div>
                        </div>
                    </div>
                    <div class="w-3/4 pl-6">
                        <div class="space-y-4">
                            @if($classroom->deskripsi)
                            <div>
                                <label class="label-block">Deskripsi</label>
                                <p class="text-muted">{{ $classroom->deskripsi }}</p>
                            </div>
                            @endif
                            <div>
                                <label class="label-block">Program</label>
                                <p class="text-muted">{{ $classroom->program->nama ?? '-' }}</p>
                            </div>
                            <div>
                                <label class="label-block">Tahun Ajaran</label>
                                <p class="text-muted">{{ $classroom->academicYear->nama_ajaran ?? '-' }}</p>
                            </div>
                            <div>
                                <label class="label-block">Wali Kelas</label>
                                <p class="text-muted">{{ $classroom->wali_kelas_id ? $classroom->homeroomTeacher->nama_lengkap : 'Belum ditetapkan' }}</p>
                            </div>
                            <div>
                                <label class="label-block">Jumlah Siswa</label>
                                <p class="text-muted">{{ $classroom->students->count() }}</p>
                            </div>
                            <div>
                                <label class="label-block">Terakhir Diperbarui</label>
                                <p class="text-muted">{{ $classroom->updated_at ? $classroom->updated_at->format('d M Y') : '-' }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            @if($classroom->students->isNotEmpty())
            <div class="card p-6">
                <div class="flex justify-between items-start mb-4">
                    <h2 class="text-lg font-semibold">Daftar Siswa di Rombel Ini</h2>
                    <span class="text-sm text-muted">{{ $classroom->students->count() }} siswa</span>
                </div>

                <div class="table-shell overflow-x-auto">
                    <table class="min-w-full divide-y divide-border">
                        <thead>
                            <tr>
                                <th>NIS</th>
                                <th>Nama Siswa</th>
                                <th>Jenis Kelamin</th>
                                <th>Status</th>
                            </thead>
                            <tbody class="divide-y divide-border">
                                @forelse($classroom->students as $student)
                                <tr>
                                    <td>{{ $student->nis ?? '-' }}</td>
                                    <td class="font-medium text-navy">{{ $student->nama_lengkap }}</td>
                                    <td>{{ $student->jenis_kelamin }}</td>
                                    <td>
                                        <x-badge variant="{{ $student->status == 'aktif' ? 'success' : 'warning' }}">
                                            {{ ucfirst($student->status) }}
                                        </x-badge>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center py-8">
                                        <x-empty-state title="Belum ada siswa di rombel ini" />
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</x-app-layout>
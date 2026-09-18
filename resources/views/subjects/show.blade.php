<x-app-layout>
    <x-slot name="title">{{ $subject->nama }}</x-slot>

    <div class="max-w-4xl mx-auto">
        <x-breadcrumb :items="[
            ['label' => 'Mata Pelajaran', 'href' => route('mata-pelajaran.index')],
            ['label' => $subject->nama]
        ]" />

        <div class="space-y-6">
            <div class="card p-6">
                <div class="flex items-start">
                    <div class="w-1/4">
                        <div class="space-y-2 text-center">
                            <div class="text-2xl font-bold text-navy">{{ $subject->kode }}</div>
                            <div class="text-lg font-medium">{{ $subject->nama }}</div>
                            <div class="text-sm text-muted">{{ $subject->status_aktif ? 'Aktif' : 'Nonaktif' }}</div>
                        </div>
                    </div>
                    <div class="w-3/4 pl-6">
                        <div class="space-y-4">
                            @if($subject->deskripsi)
                            <div>
                                <label class="label-block">Deskripsi</label>
                                <p class="text-muted">{{ $subject->deskripsi }}</p>
                            </div>
                            @endif
                            <div>
                                <label class="label-block">Tanggal Dibuat</label>
                                <p class="text-muted">{{ $subject->created_at->format('d M Y') }}</p>
                            </div>
                            <div>
                                <label class="label-block">Terakhir Diperbarui</label>
                                <p class="text-muted">{{ $subject->updated_at->format('d M Y') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            @if($subject->tutors->isNotEmpty())
            <div class="card p-6">
                <div class="flex justify-between items-start mb-4">
                    <h2 class="text-lg font-semibold">Daftar Tutor yang Mengajar Mata Pelajaran Ini</h2>
                    @can('tutors.create')
                    <a href="{{ route('tutors.create') }}" class="btn-sm btn-secondary">
                        <x-icon name="plus" class="w-4 h-4" />
                        Tambah Tutor
                    </a>
                    @endcan
                </div>

                <div class="table-shell overflow-x-auto">
                    <table class="min-w-full divide-y divide-border">
                        <thead>
                            <tr>
                                <th>Nomor Induk</th>
                                <th>Nama Tutor</th>
                                <th>Bidang Keahlian</th>
                                <th>Status</th>
                                <th class="text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border">
                            @forelse($subject->tutors as $tutor)
                            <tr>
                                <td>{{ $tutor->nomor_induk ?? '-' }}</td>
                                <td>
                                    <div class="font-medium text-navy">{{ $tutor->nama_lengkap }}</div>
                                    <div class="text-xs text-muted">{{ $tutor->nama_panggilan }}</div>
                                </td>
                                <td>{{ $tutor->bidang_keahlian ?? '-' }}</td>
                                <td>
                                    <x-badge variant="{{ $tutor->status_tutor == 'aktif' ? 'success' : 'warning' }}">
                                        {{ ucfirst($tutor->status_tutor) }}
                                    </x-badge>
                                </td>
                                <td class="text-right space-x-2">
                                    <a href="{{ route('tutors.show', $tutor->id) }}" class="inline-flex p-2 text-muted hover:text-primary-dark transition-colors">
                                        <x-icon name="eye" class="w-5 h-5" />
                                    </a>
                                    @can('tutors.update')
                                    <a href="{{ route('tutors.edit', $tutor->id) }}" class="inline-flex p-2 text-muted hover:text-primary-dark transition-colors">
                                        <x-icon name="pencil" class="w-5 h-5" />
                                    </a>
                                    @endcan
                                    @can('tutors.delete')
                                    <form action="{{ route('tutors.destroy', $tutor->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Yakin ingin menghapus data ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="inline-flex p-2 text-muted hover:text-accent transition-colors">
                                            <x-icon name="trash" class="w-5 h-5" />
                                        </button>
                                    </form>
                                    @endcan
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center py-8">
                                    <x-empty-state title="Belum ada tutor yang mengajar mata pelajaran ini" />
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @endif
        </div>
    </div>
</x-app-layout>

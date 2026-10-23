<x-app-layout>
    <x-slot name="title">Pengelolaan Penugasan</x-slot>

    <div class="max-w-7xl mx-auto space-y-6">
        <div class="flex flex-col sm:flex-row gap-4">
            <x-breadcrumb :items="[['label' => 'Pengelolaan'], ['label' => 'Penugasan Tutor']]" />
            
            @can('classrooms.manage')
            <a href="{{ route('classroom-assignments.create-tutor-subject') }}" class="btn-primary">
                <x-icon name="plus" class="w-4 h-4" />
                Alokasikan Tutor ke Mata Pelajaran
            </a>
            @endcan
            
            @can('classrooms.manage')
            <a href="{{ route('classroom-assignments.create-tutor-classroom') }}" class="btn-primary">
                <x-icon name="plus" class="w-4 h-4" />
                Alokasikan Tutor ke Rombel
            </a>
            @endcan
            
            @can('classrooms.manage')
            <a href="{{ route('classroom-assignments.create-subject-classroom') }}" class="btn-primary">
                <x-icon name="plus" class="w-4 h-4" />
                Alokasikan Mata Pelajaran ke Rombel
            </a>
            @endcan
        </div>

        <div class="card p-6">
            <h3 class="text-lg font-semibold text-navy mb-4">Daftar Penugasan</h3>
            
            <div class="table-shell overflow-x-auto">
                <table class="min-w-full divide-y divide-border">
                    <thead>
                        <tr>
                            <th scope="col">Tutor</th>
                            <th scope="col">Mata Pelajaran</th>
                            <th scope="col">Rombel</th>
                            <th scope="col" class="text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border">
                        @forelse($assignments as $pm)
                        <tr class="border-b">
                            <td class="font-medium">{{ $pm->tutor_nama }}</td>
                            <td class="text-sm text-muted">{{ $pm->subject_nama }}</td>
                            <td class="text-sm text-muted">{{ $pm->classroom_nama }}</td>
                            <td class="text-right">
                                <a href="{{ route('classroom-assignments.tutor-assignments', $pm->tutor_id) }}" class="text-sm font-medium text-primary hover:underline">
                                    Lihat Penugasan
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="px-4 py-10 text-center text-sm text-muted">
                                Belum ada penugasan tutor. Gunakan tombol alokasi di atas untuk menambahkan.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            {{ $assignments->links() }}
        </div>
    </div>
</x-app-layout>
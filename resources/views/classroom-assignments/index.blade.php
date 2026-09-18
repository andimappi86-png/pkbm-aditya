<x-app-layout>
    <x-slot name="title">Pengelolaan Penugasan</x-slot>

    <div class="max-w-7xl mx-auto space-y-6">
        <div class="flex flex-col sm:flex-row gap-4">
            <x-breadcrumb :items="[['label' => 'Pengelolaan'], ['label' => 'Penugasan Tutor']]" />
            
            @can('classrooms.manage')
            <a href="{{ route('classroom-assignments.create-tutor-subject') }}" class="btn-primary">
                <x-icon name="plus" class="w-4 h-4" />
                Aliokasikan Tutor ke Mata Pelajaran
            </a>
            @endcan
            
            @can('classrooms.manage')
            <a href="{{ route('classroom-assignments.create-tutor-classroom') }}" class="btn-primary">
                <x-icon name="plus" class="w-4 h-4" />
                Aliokasikan Tutor ke Rombel
            </a>
            @endcan
            
            @can('classrooms.manage')
            <a href="{{ route('classroom-assignments.create-subject-classroom') }}" class="btn-primary">
                <x-icon name="plus" class="w-4 h-4" />
                Aliokasikan Mata Pelajaran ke Rombel
            </a>
            @endcan
        </div>

        <div class="card p-6">
            <h3 class="text-lg font-semibold text-navy mb-4">Daftar Penugasan</h3>
            
            <div class="table-shell overflow-x-auto">
                <table class="min-w-full divide-y divide-border">
                    <thead>
                        <tr>
                            <th>Tutor</th>
                            <th>Mata Pelajaran</th>
                            <th>Rombel</th>
                            <th class="text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border">
                        @foreach($tutors as $tutor)
                        <tr class="border-b">
                            <td class="font-medium">{{ $tutor->nama_lengkap }}</td>
                            @foreach($tutor->subjects as $subject)
                            <td class="text-sm text-muted">{{ $subject->nama }}</td>
                            @endforeach
                            @foreach($tutor->classrooms as $classroom)
                            <td class="text-sm text-muted">{{ $classroom->nama_rombel }}</td>
                            @endforeach
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            {{ $tutors->links() }}
        </div>
    </div>
</x-app-layout>
<x-app-layout>
    <x-slot name="title">Penugasan Tutor</x-slot>

    <div class="max-w-5xl mx-auto space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-navy">Penugasan Tutor</h2>
                <p class="text-sm text-muted mt-1">{{ $tutor->user?->name ?? '-' }}</p>
            </div>
            <a href="{{ route('classroom-assignments.index') }}" class="btn-secondary">Kembali</a>
        </div>

        <div class="grid gap-6 lg:grid-cols-2">
            <div class="card overflow-hidden">
                <div class="px-5 py-4 border-b border-border">
                    <h3 class="font-semibold text-navy">Mata Pelajaran</h3>
                    <p class="text-xs text-muted mt-0.5">Mata pelajaran yang diampu beserta rombelnya.</p>
                </div>
                @if($tutor->subjects->isEmpty())
                    <div class="p-8 text-center text-sm text-muted">Tutor belum memiliki penugasan mata pelajaran.</div>
                @else
                    <div class="divide-y divide-border">
                        @foreach($tutor->subjects as $subject)
                            <div class="px-5 py-4">
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <p class="text-sm font-semibold text-navy">{{ $subject->nama }}</p>
                                        <p class="text-xs text-muted mt-0.5">{{ $subject->kode }}</p>
                                    </div>
                                    <span class="text-xs text-muted">{{ $subject->classrooms->count() }} rombel</span>
                                </div>
                                @if($subject->classrooms->isNotEmpty())
                                    <div class="flex flex-wrap gap-2 mt-3">
                                        @foreach($subject->classrooms as $classroom)
                                            <span class="inline-flex items-center rounded-md border border-border bg-background px-2 py-1 text-xs text-navy">
                                                {{ $classroom->nama_rombel }}
                                            </span>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <div class="card overflow-hidden">
                <div class="px-5 py-4 border-b border-border">
                    <h3 class="font-semibold text-navy">Rombel</h3>
                    <p class="text-xs text-muted mt-0.5">Rombel yang terkait dengan penugasan tutor.</p>
                </div>
                @if($tutor->classrooms->isEmpty())
                    <div class="p-8 text-center text-sm text-muted">Tutor belum memiliki penugasan rombel.</div>
                @else
                    <div class="divide-y divide-border">
                        @foreach($tutor->classrooms as $classroom)
                            <div class="px-5 py-4">
                                <p class="text-sm font-semibold text-navy">{{ $classroom->nama_rombel }}</p>
                                <p class="text-xs text-muted mt-0.5">{{ $classroom->kode_rombel }}</p>
                                @if($classroom->subjects->isNotEmpty())
                                    <div class="flex flex-wrap gap-2 mt-3">
                                        @foreach($classroom->subjects as $subject)
                                            <span class="inline-flex items-center rounded-md border border-border bg-background px-2 py-1 text-xs text-navy">
                                                {{ $subject->nama }}
                                            </span>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>

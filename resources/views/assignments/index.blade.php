<x-app-layout>
    <x-slot name="title">Daftar Tugas & Quiz</x-slot>

    <div class="max-w-7xl mx-auto space-y-6">
        <div class="flex flex-col sm:flex-row gap-4 items-start sm:items-center justify-between">
            <x-breadcrumb :items="[['label' => 'Pembelajaran'], ['label' => 'Tugas & Quiz']]" />
            @can('assignments.manage')
            <a href="{{ route('tugas.create') }}" class="inline-flex items-center justify-center rounded-xl bg-primary px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-primary-dark transition-colors focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2">
                <svg class="mr-2 -ml-1 w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Buat Tugas Baru
            </a>
            @endcan
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse($assignments as $assignment)
            <div class="bg-surface border border-border rounded-2xl overflow-hidden shadow-sm flex flex-col hover:shadow-md transition-shadow">
                <div class="p-5 flex-1">
                    <div class="flex justify-between items-start mb-3">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $assignment->type === 'quiz' ? 'bg-amber-100 text-amber-800' : 'bg-blue-100 text-blue-800' }}">
                            {{ ucfirst($assignment->type) }}
                        </span>
                        @if($assignment->deadline)
                        <span class="text-xs text-muted flex items-center">
                            <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            {{ $assignment->deadline->format('d M Y, H:i') }}
                        </span>
                        @endif
                    </div>
                    
                    <a href="{{ route('tugas.show', $assignment) }}" class="block group">
                        <h3 class="text-lg font-bold text-navy group-hover:text-primary transition-colors line-clamp-2 mb-2">{{ $assignment->judul }}</h3>
                    </a>
                    
                    <p class="text-sm text-muted line-clamp-3 mb-4">{{ $assignment->deskripsi ?? 'Tidak ada deskripsi.' }}</p>
                    
                    <div class="pt-4 border-t border-border mt-auto">
                        <div class="flex items-center gap-3 text-sm">
                            <div class="w-8 h-8 rounded-full bg-primary/10 flex items-center justify-center text-primary font-bold">
                                {{ substr($assignment->penugasanMengajar->tutor->nama_lengkap, 0, 1) }}
                            </div>
                            <div>
                                <p class="font-medium text-navy">{{ $assignment->penugasanMengajar->subject->nama }}</p>
                                <p class="text-xs text-muted">Kelas {{ $assignment->penugasanMengajar->classroom->nama }}</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-background/50 px-5 py-3 border-t border-border flex justify-between items-center">
                    <a href="{{ route('tugas.show', $assignment) }}" class="text-sm font-medium text-primary hover:text-primary-dark transition-colors">Lihat Detail →</a>
                    
                    @can('assignments.manage')
                    <div class="flex gap-2">
                        <a href="{{ route('tugas.edit', $assignment) }}" class="text-muted hover:text-primary transition-colors">
                            <span class="sr-only">Edit</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                        </a>
                        <form action="{{ route('tugas.destroy', $assignment) }}" method="POST" class="inline" onsubmit="return confirm('Hapus tugas ini?');">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-muted hover:text-red-600 transition-colors">
                                <span class="sr-only">Hapus</span>
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                            </button>
                        </form>
                    </div>
                    @endcan
                </div>
            </div>
            @empty
            <div class="col-span-full py-12 text-center text-muted bg-surface border border-border rounded-2xl">
                <svg class="mx-auto h-12 w-12 text-muted mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <h3 class="mt-2 text-sm font-medium text-navy">Belum ada Tugas/Quiz</h3>
                <p class="mt-1 text-sm">Tidak ada data tugas yang tersedia saat ini.</p>
            </div>
            @endforelse
        </div>

        @if($assignments->hasPages())
        <div class="mt-6">
            {{ $assignments->links() }}
        </div>
        @endif
    </div>
</x-app-layout>
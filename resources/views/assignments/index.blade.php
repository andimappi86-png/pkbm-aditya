@php
    $penugasan_id = request('penugasan_mengajar_id');
    $penugasanMengajar = $penugasan_id ? \App\Models\PenugasanMengajar::find($penugasan_id) : null;
@endphp

<x-app-layout>
    <x-slot name="title">Tugas & Evaluasi</x-slot>

    <div class="max-w-7xl mx-auto space-y-6">
        @if($penugasanMengajar)
            <x-detail-kelas-header :penugasan="$penugasanMengajar" activeTab="tugas" />
        @else
            <!-- Header -->
            <div class="flex flex-col sm:flex-row gap-4 items-start sm:items-center justify-between">
                <div>
                    <x-breadcrumb :items="[['label' => 'Pembelajaran'], ['label' => 'Tugas & Evaluasi']]" />
                    <h1 class="text-2xl lg:text-3xl font-bold text-navy mt-2">Tugas & Evaluasi</h1>
                    <p class="text-muted mt-1">Daftar penugasan untuk evaluasi pembelajaran.</p>
                </div>
                @can('assignments.manage')
                <a href="{{ route('tugas.create') }}" class="inline-flex items-center justify-center rounded-xl bg-primary px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-primary-dark transition-colors focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2">
                    <svg class="mr-2 -ml-1 w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Buat Tugas Baru
                </a>
                @endcan
            </div>
        @endif

        @if($studentNotice)
            <x-alert variant="info" title="Tugas belum dapat ditampilkan">
                {{ $studentNotice }}
            </x-alert>
        @endif

        <div class="space-y-4">
            @forelse($assignments as $assignment)
            <div class="bg-surface border border-border rounded-xl p-5 hover:shadow-soft hover:border-primary/50 transition-all flex flex-col sm:flex-row sm:items-center gap-4 justify-between">
                <div class="flex items-start gap-4 flex-1">
                    <div class="w-12 h-12 rounded-xl {{ $assignment->type === 'quiz' ? 'bg-amber-50 text-amber-600' : 'bg-primary-soft text-primary' }} flex items-center justify-center shrink-0">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            @if($assignment->type === 'quiz')
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            @else
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                            @endif
                        </svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 mb-1">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider {{ $assignment->type === 'quiz' ? 'bg-amber-100 text-amber-800' : 'bg-blue-100 text-blue-800' }}">
                                {{ ucfirst($assignment->type) }}
                            </span>
                            @if($assignment->deadline)
                            <span class="text-xs text-muted flex items-center bg-gray-50 px-2 py-0.5 rounded border border-border">
                                <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                Batas: {{ $assignment->deadline->format('d M Y, H:i') }}
                            </span>
                            @endif
                        </div>
                        <h3 class="text-base font-bold text-navy group-hover:text-primary transition-colors line-clamp-1"><a href="{{ route('tugas.show', $assignment) }}">{{ $assignment->judul }}</a></h3>
                        <p class="text-sm text-muted line-clamp-1 mt-1">{{ $assignment->penugasanMengajar->subject->nama }} - {{ $assignment->penugasanMengajar->classroom->nama_rombel }}</p>
                    </div>
                </div>
                
                <div class="flex items-center gap-3 shrink-0">
                    <a href="{{ route('tugas.show', $assignment) }}" class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl bg-surface border border-border text-navy font-medium text-sm hover:bg-primary-soft hover:text-primary-dark transition-colors shadow-sm shrink-0">
                        Detail Tugas
                    </a>
                    
                    @can('assignments.manage')
                    <div class="flex gap-1">
                        <a href="{{ route('tugas.edit', $assignment) }}" class="p-2.5 rounded-xl text-muted hover:text-primary hover:bg-primary-soft transition-colors" title="Edit">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                        </a>
                        <form action="{{ route('tugas.destroy', $assignment) }}" method="POST" class="inline" onsubmit="return confirm('Hapus tugas ini?');">
                            @csrf @method('DELETE')
                            <button type="submit" class="p-2.5 rounded-xl text-muted hover:text-red-600 hover:bg-red-50 transition-colors" title="Hapus">
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
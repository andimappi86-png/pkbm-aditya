@php
    $penugasan_id = request('penugasan_mengajar_id');
    $penugasanMengajar = $penugasan_id ? \App\Models\PenugasanMengajar::find($penugasan_id) : null;
@endphp

<x-app-layout>
    <x-slot name="title">Kuis & Ujian</x-slot>

    <div class="max-w-7xl mx-auto space-y-6">
        @if($penugasanMengajar)
            <x-detail-kelas-header :penugasan="$penugasanMengajar" activeTab="quiz" />
        @else
            <div class="flex flex-col sm:flex-row gap-4 items-start sm:items-center justify-between">
                <div>
                    <x-breadcrumb :items="[['label' => 'Pembelajaran'], ['label' => 'Kuis & Ujian']]" />
                    <h1 class="text-2xl lg:text-3xl font-bold text-navy mt-2">Kuis & Ujian</h1>
                    <p class="text-muted mt-1">Daftar kuis dan ujian untuk evaluasi pembelajaran.</p>
                </div>
                @can('materials.manage')
                <a href="{{ route('quiz.create') }}" class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl bg-primary text-white font-medium text-sm hover:bg-primary-dark transition-colors shadow-sm hover:shadow-md focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                    Buat Quiz Baru
                </a>
                @endcan
            </div>
        @endif

        <div class="space-y-4">
            @forelse($quizzes as $quiz)
            <article class="card border border-border hover:border-primary/50 hover:shadow-soft transition-all duration-300">
                <div class="p-5 flex flex-col sm:flex-row sm:items-center gap-4 justify-between">
                    <div class="flex items-start gap-4 flex-1 min-w-0">
                        <div class="w-12 h-12 rounded-xl bg-primary-soft text-primary flex items-center justify-center shrink-0">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 mb-2">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider {{ $quiz->status_aktif ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                                    {{ $quiz->status_aktif ? 'Aktif' : 'Draft' }}
                                </span>
                                <span class="text-xs text-muted flex items-center gap-1 bg-gray-50 px-2 py-0.5 rounded border border-border">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                    {{ $quiz->durasi_menit }} Menit
                                </span>
                            </div>
                            <h3 class="text-base font-bold text-navy group-hover:text-primary transition-colors line-clamp-1"><a href="{{ route('quiz.show', $quiz) }}">{{ $quiz->judul }}</a></h3>
                            <p class="text-sm text-muted line-clamp-1 mt-1">{{ $quiz->penugasanMengajar->subject->nama }} - {{ $quiz->penugasanMengajar->classroom->nama_rombel }}</p>
                        </div>
                    </div>
                    
                    <div class="flex items-center gap-3 shrink-0 sm:ml-4">
                        <a href="{{ route('quiz.show', $quiz) }}" class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl bg-primary text-white font-medium text-sm hover:bg-primary-dark transition-colors shadow-sm hover:shadow-md shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" /></svg>
                            Mulai Quiz
                        </a>
                        
                        @can('materials.manage')
                        <div class="flex gap-1">
                            <a href="{{ route('quiz.edit', $quiz) }}" class="p-2.5 rounded-xl text-muted hover:text-primary hover:bg-primary-soft transition-colors" title="Edit">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                            </a>
                            <form action="{{ route('quiz.destroy', $quiz) }}" method="POST" class="inline" onsubmit="return confirm('Hapus quiz ini?');">
                                @csrf @method('DELETE')
                                <button type="submit" class="p-2.5 rounded-xl text-muted hover:text-red-600 hover:bg-red-50 transition-colors" title="Hapus">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                </button>
                            </form>
                        </div>
                        @endcan
                    </div>
                </div>
            </article>
            @empty
            <div class="card py-16 text-center border border-border">
                <svg class="mx-auto h-16 w-16 text-muted mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <h3 class="mt-2 text-lg font-medium text-navy">Belum Ada Kuis & Ujian</h3>
                <p class="mt-1 text-sm text-muted">Tidak ada data kuis yang tersedia saat ini.</p>
                @can('materials.manage')
                <a href="{{ route('quiz.create') }}" class="mt-4 inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-primary text-white font-medium text-sm hover:bg-primary-dark transition-colors">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                    Buat Quiz Pertama
                </a>
                @endcan
            </div>
            @endforelse
        </div>

        @if($quizzes->hasPages())
        <div class="mt-6">
            {{ $quizzes->links() }}
        </div>
        @endif
    </div>
</x-app-layout>
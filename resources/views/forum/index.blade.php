@php
    $penugasan_id = request('penugasan_mengajar_id');
    $penugasanMengajar = $penugasan_id ? \App\Models\PenugasanMengajar::find($penugasan_id) : null;
@endphp

<x-app-layout>
    <x-slot name="title">Forum Diskusi</x-slot>

    <div class="max-w-7xl mx-auto space-y-6">
        @if($penugasanMengajar)
            <x-detail-kelas-header :penugasan="$penugasanMengajar" activeTab="diskusi" />
        @else
            <div class="flex flex-col sm:flex-row gap-4 items-start sm:items-center justify-between">
                <div>
                    <x-breadcrumb :items="[['label' => 'Pembelajaran'], ['label' => 'Forum Diskusi']]" />
                    <h1 class="text-2xl lg:text-3xl font-bold text-navy mt-2">Forum Diskusi</h1>
                    <p class="text-muted mt-1">Ruang diskusi interaktif untuk peserta didik dan tutor.</p>
                </div>
                @can('materials.manage')
                <a href="{{ route('forum.create') }}" class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl bg-primary text-white font-medium text-sm hover:bg-primary-dark transition-colors shadow-sm hover:shadow-md focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                    Buat Diskusi Baru
                </a>
                @endcan
            </div>
        @endif

        <div class="space-y-4">
            @forelse($forums as $forum)
            <article class="card p-5 border border-border hover:border-primary/50 hover:shadow-soft transition-all duration-300">
                <div class="flex items-start justify-between gap-4">
                    <div class="flex items-start gap-4">
                        <div class="w-12 h-12 rounded-full bg-primary-soft text-primary font-bold flex items-center justify-center shrink-0">
                            {{ substr($forum->user->name ?? 'U', 0, 1) }}
                        </div>
                        <div>
                            <div class="flex items-center gap-2 mb-1">
                                <span class="text-sm font-medium text-navy">{{ $forum->user->name ?? 'Pengguna' }}</span>
                                <span class="text-xs text-muted">&bull; {{ $forum->created_at->diffForHumans() }}</span>
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-gray-100 text-gray-700">
                                    {{ $forum->penugasanMengajar->subject->nama }}
                                </span>
                            </div>
                            <h3 class="text-lg font-bold text-navy group-hover:text-primary transition-colors mb-2">
                                <a href="{{ route('forum.show', $forum) }}">{{ $forum->judul }}</a>
                            </h3>
                            <p class="text-sm text-muted line-clamp-2 mb-3">{{ $forum->isi }}</p>
                            <div class="flex items-center gap-4">
                                <span class="inline-flex items-center gap-1.5 text-xs font-medium text-primary bg-primary-soft px-2.5 py-1 rounded-md">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" /></svg>
                                    {{ $forum->comments_count ?? 0 }} Balasan
                                </span>
                                <a href="{{ route('forum.show', $forum) }}" class="text-sm font-semibold text-primary hover:text-primary-dark transition-colors">Ikut Diskusi &rarr;</a>
                            </div>
                        </div>
                    </div>
                    
                    @can('materials.manage')
                    <div class="flex gap-1 shrink-0">
                        <a href="{{ route('forum.edit', $forum) }}" class="p-2.5 rounded-xl text-muted hover:text-primary hover:bg-primary-soft transition-colors" title="Edit">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                        </a>
                        <form action="{{ route('forum.destroy', $forum) }}" method="POST" class="inline" onsubmit="return confirm('Hapus diskusi ini?');">
                            @csrf @method('DELETE')
                            <button type="submit" class="p-2.5 rounded-xl text-muted hover:text-red-600 hover:bg-red-50 transition-colors" title="Hapus">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                            </button>
                        </form>
                    </div>
                    @endcan
                </div>
            </article>
            @empty
            <div class="card py-16 text-center border border-border">
                <svg class="mx-auto h-16 w-16 text-muted mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a1.994 1.994 0 01-1.414-.586m0 0L11 14h4a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2v4l.586-.586z" />
                </svg>
                <h3 class="mt-2 text-lg font-medium text-navy">Belum Ada Diskusi</h3>
                <p class="mt-1 text-sm text-muted">Belum ada percakapan diskusi yang dibuat.</p>
                @can('materials.manage')
                <a href="{{ route('forum.create') }}" class="mt-4 inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-primary text-white font-medium text-sm hover:bg-primary-dark transition-colors">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                    Mulai Diskusi Baru
                </a>
                @endcan
            </div>
            @endforelse
        </div>
        
        @if($forums->hasPages())
        <div class="mt-4">
            {{ $forums->links() }}
        </div>
        @endif
    </div>
</x-app-layout>
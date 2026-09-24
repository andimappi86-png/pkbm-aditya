@php
    $penugasan_id = request('penugasan_mengajar_id');
    $penugasanMengajar = $penugasan_id ? \App\Models\PenugasanMengajar::find($penugasan_id) : null;
@endphp

<x-app-layout>
    <x-slot name="title">Materi Pembelajaran</x-slot>

    <div class="max-w-7xl mx-auto space-y-6">
        @if($penugasanMengajar)
            <x-detail-kelas-header :penugasan="$penugasanMengajar" activeTab="materi" />
        @else
            <!-- Header for standalone Materi list -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <x-breadcrumb :items="[['label' => 'Pembelajaran'], ['label' => 'Materi']]" />
                    <h1 class="text-2xl lg:text-3xl font-bold text-navy mt-2">Materi Pembelajaran</h1>
                    <p class="text-muted mt-1">Akses dan pelajari seluruh bahan ajar modul pembelajaran.</p>
                </div>
                @can('materials.manage')
                <a href="{{ route('materi.create') }}" class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl bg-primary text-white font-medium text-sm hover:bg-primary-dark transition-colors shadow-sm hover:shadow-md focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                    Tambah Materi
                </a>
                @endcan
            </div>
        @endif

        <!-- Filter Bar -->
        <div class="card p-4 border border-border">
            <div class="flex flex-col sm:flex-row gap-4">
                <div class="flex-1 max-w-xs">
                    <label for="materi-search" class="sr-only">Cari Materi</label>
                    <div class="relative">
                        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                        <input type="text" id="materi-search" wire:model.debounce.300ms="search" placeholder="Cari judul materi..." class="w-full pl-10 pr-4 py-2.5 border border-border rounded-xl bg-surface focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition-all">
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <select wire:model="tipeFilter" class="px-4 py-2.5 border border-border rounded-xl bg-surface focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent text-sm min-w-[160px]">
                        <option value="all">Semua Tipe</option>
                        <option value="pdf">PDF</option>
                        <option value="video">Video</option>
                        <option value="link">Link</option>
                        <option value="file">File Lainnya</option>
                    </select>
                    @if(request()->filled('penugasan_mengajar_id'))
                        <input type="hidden" name="penugasan_mengajar_id" value="{{ request('penugasan_mengajar_id') }}">
                    @endif
                </div>
            </div>
        </div>

        <!-- Materi List -->
        <div class="space-y-4" id="materi-list">
            @forelse($materials as $material)
                <article class="card border border-border hover:border-primary/50 hover:shadow-soft transition-all duration-300">
                    <div class="p-5 flex-1 flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
                        <div class="flex items-start gap-4 flex-1 min-w-0">
                            <div class="w-12 h-12 rounded-xl {{ $material->tipe === 'pdf' ? 'bg-red-50' : ($material->tipe === 'video' ? 'bg-purple-50' : ($material->tipe === 'link' ? 'bg-emerald-50' : 'bg-primary-soft')) }} flex items-center justify-center shrink-0">
                                @if($material->tipe === 'pdf')
                                    <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" /></svg>
                                @elseif($material->tipe === 'video')
                                    <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" /></svg>
                                @elseif($material->tipe === 'link')
                                    <svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" /></svg>
                                @else
                                    <svg class="w-6 h-6 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" /></svg>
                                @endif
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2 mb-2">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium uppercase {{ $material->tipe === 'pdf' ? 'bg-red-50 text-red-700' : ($material->tipe === 'video' ? 'bg-purple-50 text-purple-700' : ($material->tipe === 'link' ? 'bg-emerald-50 text-emerald-700' : 'bg-primary-soft text-primary-dark')) }}">
                                        {{ strtoupper($material->tipe) }}
                                    </span>
                                    <span class="text-xs text-muted">Urutan: #{{ $material->urutan ?? 1 }}</span>
                                </div>
                                <h3 class="text-base font-bold text-navy group-hover:text-primary transition-colors line-clamp-1 mb-1">{{ $material->judul }}</h3>
                                <p class="text-sm text-muted line-clamp-2 mb-3">{{ $material->deskripsi ?? 'Tidak ada deskripsi.' }}</p>
                                <div class="flex items-center gap-3 text-sm">
                                    <div class="w-8 h-8 rounded-full bg-primary/10 flex items-center justify-center text-primary font-bold">
                                        {{ substr($material->penugasanMengajar->tutor->user->name ?? $material->penugasanMengajar->tutor->nama_lengkap, 0, 1) }}
                                    </div>
                                    <div>
                                        <p class="font-medium text-navy">{{ $material->penugasanMengajar->subject->nama }}</p>
                                        <p class="text-xs text-muted">{{ $material->penugasanMengajar->classroom->nama }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="flex items-center gap-3 shrink-0 sm:ml-4">
                            <a href="{{ route('materi.show', $material) }}" class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl bg-primary text-white font-medium text-sm hover:bg-primary-dark transition-colors shadow-sm hover:shadow-md shrink-0">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" /></svg>
                                Buka
                            </a>
                            
                            @can('materials.manage')
                            <div class="flex gap-1">
                                <a href="{{ route('materi.edit', $material) }}" class="p-2.5 rounded-xl text-muted hover:text-primary hover:bg-primary-soft transition-colors" title="Edit">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                                </a>
                                <form action="{{ route('materi.destroy', $material) }}" method="POST" class="inline" onsubmit="return confirm('Hapus materi ini?');">
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
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                </svg>
                <h3 class="mt-2 text-lg font-medium text-navy">Belum Ada Materi Pembelajaran</h3>
                <p class="mt-1 text-sm text-muted">Tidak ada data materi yang tersedia saat ini.</p>
                @can('materials.manage')
                <a href="{{ route('materi.create') }}" class="mt-4 inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-primary text-white font-medium text-sm hover:bg-primary-dark transition-colors">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                    Buat Materi Pertama
                </a>
                @endcan
            </div>
            @endforelse
        </div>

        @if($materials->hasPages())
        <div class="mt-6">
            {{ $materials->links() }}
        </div>
        @endif
    </div>
</x-app-layout>
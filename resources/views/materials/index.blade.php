<x-app-layout>
    <x-slot name="title">Daftar Materi Pembelajaran</x-slot>

    <div class="max-w-7xl mx-auto space-y-6">
        <div class="flex flex-col sm:flex-row gap-4 items-start sm:items-center justify-between">
            <x-breadcrumb :items="[['label' => 'Pembelajaran'], ['label' => 'Materi']]" />
            @can('materials.manage')
            <a href="{{ route('materi.create') }}" class="inline-flex items-center justify-center rounded-xl bg-primary px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-primary-dark transition-colors focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2">
                <svg class="mr-2 -ml-1 w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Tambah Materi Baru
            </a>
            @endcan
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse($materials as $material)
            <div class="bg-surface border border-border rounded-2xl overflow-hidden shadow-sm flex flex-col hover:shadow-md transition-shadow">
                <div class="p-5 flex-1">
                    <div class="flex justify-between items-start mb-3">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium uppercase {{ $material->tipe === 'pdf' ? 'bg-red-100 text-red-800' : ($material->tipe === 'video' ? 'bg-purple-100 text-purple-800' : ($material->tipe === 'link' ? 'bg-emerald-100 text-emerald-800' : 'bg-gray-100 text-gray-800')) }}">
                            {{ $material->tipe }}
                        </span>
                        <span class="text-xs text-muted">Urutan: #{{ $material->urutan ?? 1 }}</span>
                    </div>
                    
                    <a href="{{ route('materi.show', $material) }}" class="block group">
                        <h3 class="text-lg font-bold text-navy group-hover:text-primary transition-colors line-clamp-2 mb-2">{{ $material->judul }}</h3>
                    </a>
                    
                    <p class="text-sm text-muted line-clamp-3 mb-4">{{ $material->deskripsi ?? 'Tidak ada deskripsi.' }}</p>
                    
                    <div class="pt-4 border-t border-border mt-auto">
                        <div class="flex items-center gap-3 text-sm">
                            <div class="w-8 h-8 rounded-full bg-primary/10 flex items-center justify-center text-primary font-bold">
                                {{ substr($material->penugasanMengajar->tutor->nama_lengkap, 0, 1) }}
                            </div>
                            <div>
                                <p class="font-medium text-navy">{{ $material->penugasanMengajar->subject->nama }}</p>
                                <p class="text-xs text-muted">Kelas {{ $material->penugasanMengajar->classroom->nama }}</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-background/50 px-5 py-3 border-t border-border flex justify-between items-center">
                    <a href="{{ route('materi.show', $material) }}" class="text-sm font-medium text-primary hover:text-primary-dark transition-colors">Pelajari Materi →</a>
                    
                    @can('materials.manage')
                    <div class="flex gap-2">
                        <a href="{{ route('materi.edit', $material) }}" class="text-muted hover:text-primary transition-colors">
                            <span class="sr-only">Edit</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                        </a>
                        <form action="{{ route('materi.destroy', $material) }}" method="POST" class="inline" onsubmit="return confirm('Hapus materi ini?');">
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
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                </svg>
                <h3 class="mt-2 text-sm font-medium text-navy">Belum ada Materi Pembelajaran</h3>
                <p class="mt-1 text-sm">Tidak ada data materi yang tersedia saat ini.</p>
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
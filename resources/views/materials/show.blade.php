<x-app-layout>
    <x-slot name="title">{{ $material->judul }}</x-slot>

    <div class="max-w-4xl mx-auto space-y-6">
        <x-breadcrumb :items="[['label' => 'Pembelajaran'], ['label' => 'Materi', 'url' => route('materi.index')], ['label' => $material->judul]]" />

        <div class="bg-surface border border-border rounded-2xl p-6 shadow-sm space-y-6">
            <div class="flex items-center justify-between gap-4">
                <h2 class="text-2xl font-bold text-navy">{{ $material->judul }}</h2>
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium uppercase {{ $material->tipe === 'pdf' ? 'bg-red-100 text-red-800' : ($material->tipe === 'video' ? 'bg-purple-100 text-purple-800' : ($material->tipe === 'link' ? 'bg-emerald-100 text-emerald-800' : 'bg-gray-100 text-gray-800')) }}">
                    {{ $material->tipe }}
                </span>
            </div>

            <div class="prose prose-navy max-w-none">
                @if($material->isi_materi)
                    <div class="whitespace-pre-line text-navy leading-relaxed">{{ $material->isi_materi }}</div>
                @endif

                @if($material->tipe === 'video' && $material->video_url)
                    <div class="aspect-video bg-background rounded-2xl border border-border overflow-hidden">
                        <iframe class="w-full h-full" src="{{ $material->video_url }}" frameborder="0" allowfullscreen></iframe>
                    </div>
                @endif

                @if($material->tipe === 'link' && $material->video_url)
                    <a href="{{ $material->video_url }}" target="_blank" class="inline-flex items-center gap-2 text-primary font-medium hover:underline">
                        Buka Tautan Materi
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" /></svg>
                    </a>
                @endif

                @if($material->file_path)
                    <div class="p-4 bg-background rounded-2xl border border-border flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <svg class="w-8 h-8 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                            <div>
                                <p class="font-semibold text-navy">Unduh File Materi</p>
                                <p class="text-xs text-muted">{{ pathinfo($material->file_path, PATHINFO_EXTENSION) }}</p>
                            </div>
                        </div>
                        <a href="{{ Storage::url($material->file_path) }}" target="_blank" class="inline-flex items-center gap-2 bg-primary text-white px-4 py-2 rounded-xl text-sm font-medium hover:bg-primary-dark transition">
                            Unduh Sekarang
                        </a>
                    </div>
                @endif
            </div>

            <div class="border-t border-border pt-4">
                <p class="text-sm font-medium text-muted">Deskripsi:</p>
                <p class="text-navy text-sm">{{ $material->deskripsi }}</p>
            </div>
        </div>
    </div>
</x-app-layout>
<x-app-layout>
    <x-slot name="title">Forum Diskusi</x-slot>

    <div class="max-w-7xl mx-auto space-y-6">
        <div class="flex flex-col sm:flex-row gap-4 items-start sm:items-center justify-between">
            <x-breadcrumb :items="[['label' => 'Pembelajaran'], ['label' => 'Forum Diskusi']]" />
            @can('materials.manage')
            <a href="{{ route('forum.create') }}" class="inline-flex items-center justify-center rounded-xl bg-primary px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-primary-dark transition focus:ring-2 focus:ring-primary focus:ring-offset-2">
                Buat Diskusi Baru
            </a>
            @endcan
        </div>

        <div class="bg-surface border border-border rounded-2xl shadow-sm p-4">
            <div class="space-y-4">
                @forelse($forums as $forum)
                <div class="p-4 bg-background border border-border rounded-xl hover:border-primary-soft transition">
                    <div class="flex justify-between items-start">
                        <div>
                            <h3 class="text-lg font-bold text-navy mb-1">
                                <a href="{{ route('forum.show', $forum) }}" class="hover:text-primary transition">{{ $forum->judul }}</a>
                            </h3>
                            <div class="text-sm text-muted space-x-2">
                                <span>Oleh: {{ $forum->user->name }}</span>
                                <span>&bull;</span>
                                <span>{{ $forum->created_at->diffForHumans() }}</span>
                                <span>&bull;</span>
                                <span>{{ $forum->penugasanMengajar->subject->nama }} - {{ $forum->penugasanMengajar->classroom->nama }}</span>
                            </div>
                        </div>
                        @can('materials.manage')
                        <div class="flex gap-2">
                            <a href="{{ route('forum.edit', $forum) }}" class="text-muted hover:text-primary transition">Edit</a>
                            <form action="{{ route('forum.destroy', $forum) }}" method="POST" onsubmit="return confirm('Hapus forum ini?');">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-muted hover:text-red-600 transition">Hapus</button>
                            </form>
                        </div>
                        @endcan
                    </div>
                </div>
                @empty
                <p class="text-center text-muted py-8">Belum ada diskusi yang dibuat.</p>
                @endforelse
            </div>
            @if($forums->hasPages())
            <div class="mt-4">
                {{ $forums->links() }}
            </div>
            @endif
        </div>
    </div>
</x-app-layout>
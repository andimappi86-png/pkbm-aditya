<x-app-layout>
    <x-slot name="title">{{ $forum->judul }}</x-slot>

    <div class="max-w-4xl mx-auto space-y-6">
        <x-breadcrumb :items="[['label' => 'Forum Diskusi', 'url' => route('forum.index')], ['label' => $forum->judul]]" />

        <div class="bg-surface border border-border rounded-2xl p-6 shadow-sm space-y-6">
            <div>
                <h1 class="text-2xl font-bold text-navy">{{ $forum->judul }}</h1>
                <div class="text-sm text-muted mt-1 space-x-2">
                    <span>Oleh: <strong>{{ $forum->user->name }}</strong></span>
                    <span>&bull;</span>
                    <span>{{ $forum->created_at->translatedFormat('d F Y, H:i') }}</span>
                    <span>&bull;</span>
                    <span>{{ $forum->penugasanMengajar->subject->nama }} ({{ $forum->penugasanMengajar->classroom->nama }})</span>
                </div>
            </div>

            <div class="prose max-w-none text-navy whitespace-pre-line border-t border-b border-border py-4">
                {{ $forum->isi }}
            </div>

            <!-- Komentar / Balasan -->
            <div class="space-y-4">
                <h3 class="text-lg font-bold text-navy">Komentar & Diskusi ({{ $forum->comments->count() }})</h3>

                <div class="space-y-3">
                    @forelse($forum->comments as $comment)
                    <div class="p-4 bg-background rounded-xl border border-border">
                        <div class="flex justify-between items-start mb-2">
                            <div>
                                <span class="font-semibold text-navy text-sm">{{ $comment->user->name }}</span>
                                <span class="text-xs text-muted ml-2">{{ $comment->created_at->diffForHumans() }}</span>
                            </div>
                            @if(auth()->id() === $comment->user_id || auth()->user()->can('materials.manage'))
                            <form action="{{ route('forum.comments.destroy', $comment) }}" method="POST" onsubmit="return confirm('Hapus komentar?');">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-xs text-red-500 hover:underline">Hapus</button>
                            </form>
                            @endif
                        </div>
                        <p class="text-sm text-navy whitespace-pre-line">{{ $comment->isi }}</p>
                    </div>
                    @empty
                    <p class="text-sm text-muted">Belum ada tanggapan. Jadilah yang pertama berkomentar!</p>
                    @endforelse
                </div>

                <form action="{{ route('forum.comments.store', $forum) }}" method="POST" class="mt-6 space-y-3">
                    @csrf
                    <textarea name="isi" rows="3" required placeholder="Tuliskan komentar atau pertanyaan Anda..." class="block w-full rounded-xl border-border bg-background px-3 py-2 text-sm focus:border-primary focus:ring-primary"></textarea>
                    <button type="submit" class="inline-flex items-center rounded-xl bg-primary px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-primary-dark transition">Kirim Komentar</button>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
<x-app-layout>
    <x-slot name="title">Edit Forum Diskusi</x-slot>

    <div class="max-w-3xl mx-auto space-y-6">
        <x-breadcrumb :items="[['label' => 'Forum Diskusi', 'url' => route('forum.index')], ['label' => 'Edit']]" />

        <div class="bg-surface border border-border rounded-2xl p-6 shadow-sm">
            <form action="{{ route('forum.update', $forum) }}" method="POST" class="space-y-6">
                @csrf
                @method('PUT')
                <div>
                    <label for="judul" class="block text-sm font-medium text-navy mb-1.5">Judul Diskusi</label>
                    <input type="text" name="judul" id="judul" value="{{ old('judul', $forum->judul) }}" required class="block w-full rounded-xl border-border bg-background px-3 py-2 text-sm focus:border-primary focus:ring-primary">
                    @error('judul') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="isi" class="block text-sm font-medium text-navy mb-1.5">Isi / Topik Diskusi</label>
                    <textarea name="isi" id="isi" rows="6" required class="block w-full rounded-xl border-border bg-background px-3 py-2 text-sm focus:border-primary focus:ring-primary">{{ old('isi', $forum->isi) }}</textarea>
                    @error('isi') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
                <div class="flex justify-end gap-3 pt-4 border-t border-border">
                    <a href="{{ route('forum.show', $forum) }}" class="inline-flex items-center rounded-xl bg-white border border-border px-4 py-2 text-sm font-medium text-navy hover:bg-background transition">Batal</a>
                    <button type="submit" class="inline-flex items-center rounded-xl bg-primary px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-primary-dark transition">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
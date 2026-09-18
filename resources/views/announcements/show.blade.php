<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Detail Pengumuman') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="mb-4">
                <a href="{{ route('announcements.index') }}" class="text-blue-600 hover:underline">&larr; Kembali</a>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-8">
                    <div class="border-b pb-4 mb-6">
                        <div class="flex gap-2 items-center mb-4">
                            <span class="bg-blue-100 text-blue-800 text-sm px-2 py-1 rounded">{{ $announcement->kategori }}</span>
                            @if($announcement->published_at)
                                <span class="text-gray-500 text-sm">{{ $announcement->published_at->format('d F Y H:i') }}</span>
                            @else
                                <span class="bg-yellow-100 text-yellow-800 text-sm px-2 py-1 rounded">Draft</span>
                            @endif
                        </div>
                        
                        <h1 class="text-3xl font-bold mb-2">{{ $announcement->judul }}</h1>
                        <div class="text-sm text-gray-600">
                            Oleh: {{ $announcement->user->name }}
                        </div>
                    </div>
                    
                    <div class="prose max-w-none">
                        {!! nl2br(e($announcement->isi)) !!}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
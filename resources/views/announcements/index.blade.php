<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Pengumuman') }}
            </h2>
            @can('announcements.manage')
            <a href="{{ route('announcements.create') }}" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                Buat Pengumuman
            </a>
            @endcan
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Filter Section -->
            <div class="bg-white p-4 rounded shadow mb-6">
                <form action="{{ route('announcements.index') }}" method="GET" class="flex flex-col md:flex-row gap-4">
                    <div class="flex-1">
                        <x-input-label for="search" value="Cari Judul" />
                        <x-text-input id="search" name="search" type="text" class="mt-1 block w-full" value="{{ request('search') }}" />
                    </div>
                    
                    @if(auth()->user()->can('announcements.manage'))
                    <div class="w-full md:w-48">
                        <x-input-label for="status" value="Status" />
                        <select id="status" name="status" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                            <option value="">Semua</option>
                            <option value="published" {{ request('status') === 'published' ? 'selected' : '' }}>Dipublikasi</option>
                            <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draft</option>
                        </select>
                    </div>
                    @endif
                    
                    <div class="w-full md:w-48">
                        <x-input-label for="kategori" value="Kategori" />
                        <select id="kategori" name="kategori" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                            <option value="">Semua</option>
                            <option value="Akademik" {{ request('kategori') === 'Akademik' ? 'selected' : '' }}>Akademik</option>
                            <option value="Umum" {{ request('kategori') === 'Umum' ? 'selected' : '' }}>Umum</option>
                            <option value="Keuangan" {{ request('kategori') === 'Keuangan' ? 'selected' : '' }}>Keuangan</option>
                        </select>
                    </div>
                    
                    <div class="flex items-end">
                        <x-primary-button type="submit">Filter</x-primary-button>
                    </div>
                </form>
            </div>

            <!-- List Section -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    @if($announcements->isEmpty())
                        <div class="text-center py-8 text-gray-500">
                            Tidak ada pengumuman.
                        </div>
                    @else
                        <div class="space-y-4">
                            @foreach($announcements as $announcement)
                                <div class="border p-4 rounded hover:bg-gray-50 flex flex-col md:flex-row justify-between items-start gap-4">
                                    <div class="flex-1">
                                        <div class="flex items-center gap-2 mb-2">
                                            <span class="bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded">{{ $announcement->kategori }}</span>
                                            @if($announcement->published_at)
                                                <span class="text-sm text-gray-500">{{ $announcement->published_at->format('d M Y H:i') }}</span>
                                            @else
                                                <span class="bg-yellow-100 text-yellow-800 text-xs px-2 py-1 rounded">Draft</span>
                                            @endif
                                            
                                            @if(!$announcement->status_aktif)
                                                <span class="bg-red-100 text-red-800 text-xs px-2 py-1 rounded">Tidak Aktif</span>
                                            @endif
                                        </div>
                                        
                                        <h3 class="text-lg font-semibold">
                                            <a href="{{ route('announcements.show', $announcement) }}" class="text-blue-600 hover:underline">
                                                {{ $announcement->judul }}
                                            </a>
                                        </h3>
                                        
                                        @if(auth()->user()->can('announcements.manage'))
                                            <div class="mt-2 text-sm text-gray-600 flex gap-4">
                                                <span>Target Role: {{ $announcement->target_role ?? 'Semua' }}</span>
                                                @if($announcement->target_program)
                                                    <span>Program: {{ $announcement->program->nama ?? '-' }}</span>
                                                @endif
                                                @if($announcement->target_rombel)
                                                    <span>Rombel: {{ $announcement->classroom->nama ?? '-' }}</span>
                                                @endif
                                            </div>
                                        @endif
                                    </div>
                                    
                                    @can('announcements.manage')
                                        <div class="flex gap-2">
                                            <a href="{{ route('announcements.edit', $announcement) }}" class="text-indigo-600 hover:text-indigo-900">Edit</a>
                                            <form action="{{ route('announcements.destroy', $announcement) }}" method="POST" class="inline" onsubmit="return confirm('Yakin ingin menghapus?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:text-red-900">Hapus</button>
                                            </form>
                                        </div>
                                    @endcan
                                </div>
                            @endforeach
                        </div>
                        
                        <div class="mt-4">
                            {{ $announcements->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
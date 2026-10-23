<x-app-layout>
    <div class="p-6">
        <div class="flex justify-between mb-6">
            <h1 class="text-2xl font-bold">Daftar Tahun Ajaran</h1>
            @can('academic-years.manage')
            <a href="{{ route('academic-years.create') }}" class="px-4 py-2 bg-primary text-white rounded">Tambah Tahun Ajaran</a>
            @endcan
        </div>

        @if(session('success'))
            <div class="mb-4 p-4 bg-green-100 text-green-700 rounded">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="mb-4 p-4 bg-red-100 text-red-700 rounded">{{ session('error') }}</div>
        @endif

        <div class="bg-white p-6 rounded shadow overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b">
                        <th scope="col" class="p-2 text-left">Nama</th>
                        <th scope="col" class="p-2 text-left">Mulai</th>
                        <th scope="col" class="p-2 text-left">Selesai</th>
                        <th scope="col" class="p-2 text-left">Status</th>
                        <th scope="col" class="p-2 text-left">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($academicYears as $year)
                    <tr class="border-b">
                        <td class="p-2">{{ $year->nama }}</td>
                        <td class="p-2">{{ $year->tanggal_mulai->format('d/m/Y') }}</td>
                        <td class="p-2">{{ $year->tanggal_selesai->format('d/m/Y') }}</td>
                        <td class="p-2">
                            <span class="px-2 py-1 {{ $year->status_aktif ? 'bg-green-100' : 'bg-gray-100' }} rounded text-sm">
                                {{ $year->status_aktif ? 'Aktif' : 'Nonaktif' }}
                            </span>
                        </td>
                        <td class="p-2">
                            @can('academic-years.manage')
                            <a href="{{ route('academic-years.edit', $year) }}" class="text-blue-600">Edit</a>
                            <form action="{{ route('academic-years.destroy', $year) }}" method="POST" class="inline" onsubmit="return confirm('Hapus data?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-red-600 ml-2">Hapus</button>
                            </form>
                            @endcan
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="p-8 text-center text-sm text-muted">Belum ada tahun ajaran.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>

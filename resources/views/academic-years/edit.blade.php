<x-app-layout>
    <div class="p-6">
        <h1 class="text-2xl font-bold mb-6">Edit Tahun Ajaran</h1>

        <div class="bg-white p-6 rounded shadow max-w-2xl">
            <form action="{{ route('academic-years.update', $academicYear) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="mb-4">
                    <label class="block text-sm font-semibold mb-1">Nama Tahun Ajaran</label>
                    <input type="text" name="nama" value="{{ old('nama', $academicYear->nama) }}" class="w-full border p-2 rounded" placeholder="Contoh: 2026/2027">
                    @error('nama') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-semibold mb-1">Tanggal Mulai</label>
                        <input type="date" name="tanggal_mulai" value="{{ old('tanggal_mulai', $academicYear->tanggal_mulai?->format('Y-m-d')) }}" class="w-full border p-2 rounded">
                        @error('tanggal_mulai') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-semibold mb-1">Tanggal Selesai</label>
                        <input type="date" name="tanggal_selesai" value="{{ old('tanggal_selesai', $academicYear->tanggal_selesai?->format('Y-m-d')) }}" class="w-full border p-2 rounded">
                        @error('tanggal_selesai') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="mb-6">
                    <label class="flex items-center">
                        <input type="checkbox" name="status_aktif" value="1" {{ old('status_aktif', $academicYear->status_aktif) ? 'checked' : '' }} class="mr-2">
                        <span>Aktifkan Tahun Ajaran Ini</span>
                    </label>
                    <p class="text-sm text-gray-500">Mencentang ini akan menonaktifkan tahun ajaran aktif lainnya.</p>
                </div>

                <div class="flex gap-2">
                    <button type="submit" class="px-4 py-2 bg-primary text-white rounded">Perbarui</button>
                    <a href="{{ route('academic-years.index') }}" class="px-4 py-2 border rounded">Batal</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>

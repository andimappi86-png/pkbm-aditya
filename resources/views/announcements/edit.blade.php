<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Edit Pengumuman') }}
            </h2>
            <a href="{{ route('announcements.index') }}" class="text-blue-600 hover:underline">&larr; Kembali</a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-8">
                    <form action="{{ route('announcements.update', $announcement) }}" method="POST" class="space-y-6">
                        @csrf
                        @method('PUT')
                        
                        <div>
                            <x-input-label for="judul" value="Judul" />
                            <x-text-input id="judul" name="judul" type="text" class="mt-1 block w-full" value="{{ old('judul', $announcement->judul) }}" required />
                            @error('judul')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <x-input-label for="isi" value="Isi" />
                            <textarea id="isi" name="isi" rows="10" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>{{ old('isi', $announcement->isi) }}</textarea>
                            @error('isi')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <x-input-label for="kategori" value="Kategori" />
                            <select id="kategori" name="kategori" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
                                <option value="Akademik" {{ old('kategori', $announcement->kategori) === 'Akademik' ? 'selected' : '' }}>Akademik</option>
                                <option value="Umum" {{ old('kategori', $announcement->kategori) === 'Umum' ? 'selected' : '' }}>Umum</option>
                                <option value="Keuangan" {{ old('kategori', $announcement->kategori) === 'Keuangan' ? 'selected' : '' }}>Keuangan</option>
                            </select>
                            @error('kategori')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <x-input-label for="target_role" value="Target Role (Opsional)" />
                            <select id="target_role" name="target_role" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                <option value="">Semua Role</option>
                                @foreach($roles as $role)
                                    <option value="{{ $role->name }}" {{ old('target_role', $announcement->target_role) === $role->name ? 'selected' : '' }}>{{ $role->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <x-input-label for="target_program" value="Target Program (Opsional)" />
                            <select id="target_program" name="target_program" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                <option value="">Semua Program</option>
                                @foreach($programs as $program)
                                    <option value="{{ $program->id }}" {{ old('target_program', $announcement->target_program) == $program->id ? 'selected' : '' }}>{{ $program->nama }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <x-input-label for="target_rombel" value="Target Rombel (Opsional)" />
                            <select id="target_rombel" name="target_rombel" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                <option value="">Semua Rombel</option>
                                @foreach($classrooms as $classroom)
                                    <option value="{{ $classroom->id }}" {{ old('target_rombel', $announcement->target_rombel) == $classroom->id ? 'selected' : '' }}>{{ $classroom->nama }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <x-input-label for="published_at" value="Tanggal Publikasi" />
                            <x-text-input id="published_at" name="published_at" type="datetime-local" class="mt-1 block w-full" value="{{ old('published_at', $announcement->published_at ? $announcement->published_at->format('Y-m-d\TH:i') : '') }}" />
                            <p class="mt-1 text-sm text-gray-500">Kosongkan untuk draft.</p>
                        </div>

                        <div class="flex items-center gap-4">
                            <label class="flex items-center gap-2">
                                <input type="checkbox" name="status_aktif" value="1" {{ old('status_aktif', $announcement->status_aktif) ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                <span class="text-sm">Aktif</span>
                            </label>
                        </div>

                        <div class="flex gap-4">
                            <x-primary-button type="submit" name="publish" value="1" @if(!$announcement->published_at) class="bg-green-600 hover:bg-green-700" @endif>Publikasikan</x-primary-button>
                            <x-primary-button type="submit" class="bg-gray-600 hover:bg-gray-700">Simpan Perubahan</x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
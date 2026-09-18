<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Buat Pengumuman') }}
            </h2>
            <a href="{{ route('announcements.index') }}" class="text-blue-600 hover:underline">&larr; Kembali</a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-8">
                    <form action="{{ route('announcements.store') }}" method="POST" class="space-y-6">
                        @csrf
                        
                        <div>
                            <x-input-label for="judul" value="Judul" />
                            <x-text-input id="judul" name="judul" type="text" class="mt-1 block w-full" value="{{ old('judul') }}" required />
                            @error('judul')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <x-input-label for="isi" value="Isi" />
                            <textarea id="isi" name="isi" rows="10" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>{{ old('isi') }}</textarea>
                            @error('isi')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <x-input-label for="kategori" value="Kategori" />
                            <select id="kategori" name="kategori" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
                                <option value="Akademik" {{ old('kategori') === 'Akademik' ? 'selected' : '' }}>Akademik</option>
                                <option value="Umum" {{ old('kategori') === 'Umum' ? 'selected' : '' }}>Umum</option>
                                <option value="Keuangan" {{ old('kategori') === 'Keuangan' ? 'selected' : '' }}>Keuangan</option>
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
                                    <option value="{{ $role->name }}" {{ old('target_role') === $role->name ? 'selected' : '' }}>{{ $role->name }}</option>
                                @endforeach
                            </select>
                            <p class="mt-1 text-sm text-gray-500">Kosongkan untuk semua role</p>
                        </div>

                        <div>
                            <x-input-label for="target_program" value="Target Program (Opsional)" />
                            <select id="target_program" name="target_program" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                <option value="">Semua Program</option>
                                @foreach($programs as $program)
                                    <option value="{{ $program->id }}" {{ old('target_program') == $program->id ? 'selected' : '' }}>{{ $program->nama }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <x-input-label for="target_rombel" value="Target Rombel (Opsional)" />
                            <select id="target_rombel" name="target_rombel" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                <option value="">Semua Rombel</option>
                                @foreach($classrooms as $classroom)
                                    <option value="{{ $classroom->id }}" {{ old('target_rombel') == $classroom->id ? 'selected' : '' }}>{{ $classroom->nama }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <x-input-label for="published_at" value="Tanggal Publikasi (Opsional)" />
                            <x-text-input id="published_at" name="published_at" type="datetime-local" class="mt-1 block w-full" value="{{ old('published_at') ? \Carbon\Carbon::parse(old('published_at'))->format('Y-m-d\TH:i') : '' }}" />
                            <p class="mt-1 text-sm text-gray-500">Kosongkan untuk draft. Diisi otomatis jika diklik "Publikasikan".</p>
                        </div>

                        <div class="flex gap-4">
                            <x-primary-button type="submit" name="publish" value="1">Publikasikan</x-primary-button>
                            <x-primary-button type="submit" class="bg-gray-600 hover:bg-gray-700">Simpan Draft</x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
<x-app-layout title="Edit Mata Pelajaran">
    <div class="max-w-[800px] mx-auto space-y-6">
        <!-- Breadcrumb -->
        <x-breadcrumb :items="[
            ['label' => 'Mata Pelajaran', 'href' => route('mata-pelajaran.index')],
            ['label' => $subject->nama, 'href' => route('mata-pelajaran.show', $subject->id)],
            ['label' => 'Edit']
        ]" />

        <!-- Header -->
        <div>
            <div class="flex items-center gap-3">
                <h2 class="text-2xl font-bold text-[#172B3A]">Edit Mata Pelajaran</h2>
                <span class="px-2.5 py-1 text-xs font-mono font-semibold bg-gray-100 text-[#172B3A] rounded border border-[#D9E2E5]">Kode: {{ $subject->kode }}</span>
            </div>
            <p class="text-[#64748B] mt-1">Perbarui data informasi mata pelajaran.</p>
        </div>

        <!-- Form -->
        <div class="bg-white border border-[#D9E2E5] rounded-xl shadow-sm">
            <form method="POST" action="{{ route('mata-pelajaran.update', $subject->id) }}" class="p-6 space-y-6" x-data="{ statusAktif: {{ old('status_aktif', $subject->status_aktif) ? 'true' : 'false' }} }">
                @csrf
                @method('PUT')

                <!-- Section 1: Informasi Mata Pelajaran -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="kode" class="block text-sm font-semibold text-[#172B3A] mb-1">Kode Mata Pelajaran *</label>
                        <input type="text" name="kode" id="kode" value="{{ old('kode', $subject->kode) }}" placeholder="CTH: MTK-01" class="w-full px-3 py-2 border {{ $errors->has('kode') ? 'border-[#C62828] focus:ring-[#C62828]' : 'border-[#D9E2E5] focus:ring-[#167C80]' }} rounded-lg focus:outline-none focus:ring-2 focus:border-transparent text-[#172B3A]" required>
                        <p class="text-xs text-[#64748B] mt-1">Gunakan kode singkat dan konsisten, misalnya MAT-01.</p>
                        @error('kode')
                            <p class="text-sm text-[#C62828] mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <div>
                        <label for="nama" class="block text-sm font-semibold text-[#172B3A] mb-1">Nama Mata Pelajaran *</label>
                        <input type="text" name="nama" id="nama" value="{{ old('nama', $subject->nama) }}" placeholder="Contoh: Matematika" class="w-full px-3 py-2 border {{ $errors->has('nama') ? 'border-[#C62828] focus:ring-[#C62828]' : 'border-[#D9E2E5] focus:ring-[#167C80]' }} rounded-lg focus:outline-none focus:ring-2 focus:border-transparent text-[#172B3A]" required>
                        @error('nama')
                            <p class="text-sm text-[#C62828] mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Section 2: Deskripsi -->
                <div>
                    <label for="deskripsi" class="block text-sm font-semibold text-[#172B3A] mb-1">Deskripsi</label>
                    <textarea name="deskripsi" id="deskripsi" rows="3" placeholder="Deskripsi singkat mengenai materi yang dipelajari" class="w-full px-3 py-2 border {{ $errors->has('deskripsi') ? 'border-[#C62828] focus:ring-[#C62828]' : 'border-[#D9E2E5] focus:ring-[#167C80]' }} rounded-lg focus:outline-none focus:ring-2 focus:border-transparent text-[#172B3A]">{{ old('deskripsi', $subject->deskripsi) }}</textarea>
                    <p class="text-xs text-[#64748B] mt-1">Tambahkan gambaran singkat mata pelajaran bila diperlukan.</p>
                    @error('deskripsi')
                        <p class="text-sm text-[#C62828] mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Section 3: Status -->
                <div class="border-t border-[#D9E2E5] pt-6">
                    <div class="flex items-start gap-4">
                        <div class="flex-1">
                            <h3 class="text-sm font-semibold text-[#172B3A]">Status Mata Pelajaran</h3>
                            <p class="text-xs text-[#64748B] mt-1">Mata pelajaran aktif dapat digunakan pada proses akademik sesuai permission dan relationship yang tersedia.</p>
                        </div>
                        <div class="flex items-center">
                            <input type="hidden" name="status_aktif" :value="statusAktif ? '1' : '0'">
                            <button type="button" @click="statusAktif = !statusAktif" :class="statusAktif ? 'bg-[#167C80]' : 'bg-gray-200'" class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-[#167C80] focus:ring-offset-2">
                                <span class="sr-only">Gunakan status aktif</span>
                                <span :class="statusAktif ? 'translate-x-5' : 'translate-x-0'" class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out"></span>
                            </button>
                            <span class="ml-3 text-sm font-semibold" :class="statusAktif ? 'text-[#167C80]' : 'text-gray-500'" x-text="statusAktif ? 'Aktif' : 'Nonaktif'"></span>
                        </div>
                    </div>
                </div>

                <!-- Bottom Action -->
                <div class="border-t border-[#D9E2E5] pt-6 flex justify-end gap-3">
                    <a href="{{ route('mata-pelajaran.index') }}" class="px-6 py-2.5 border border-[#D9E2E5] text-[#172B3A] rounded-lg font-medium hover:bg-[#F5F7F8] transition-colors">
                        Batal
                    </a>
                    <button type="submit" class="inline-flex items-center gap-2 px-6 py-2.5 bg-[#167C80] text-white rounded-lg font-medium hover:bg-[#0F5F63] transition-colors shadow-sm">
                        <x-icon name="check-circle" class="w-4 h-4" />
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <a href="{{ route('programs.index') }}" class="text-gray-400 hover:text-navy transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                </a>
                <div>
                    <h2 class="font-semibold text-xl text-navy leading-tight">
                        Detail Program: {{ $program->nama }}
                    </h2>
                    <p class="text-sm text-gray-500 mt-1">Kode: {{ $program->kode }}</p>
                </div>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- Ringkasan Info -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <h3 class="text-md font-semibold text-navy mb-2">Deskripsi Program</h3>
                <p class="text-gray-600 text-sm leading-relaxed mb-6">
                    {{ $program->deskripsi ?? 'Tidak ada deskripsi detail untuk program ini.' }}
                </p>

                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 border-t border-gray-100 pt-4">
                    <div>
                        <span class="block text-xs text-gray-400 uppercase tracking-wider font-semibold">Status Program</span>
                        <span class="inline-flex mt-1 items-center px-2 py-0.5 rounded text-xs font-medium bg-emerald-100 text-emerald-800">
                            Aktif
                        </span>
                    </div>
                    <div>
                        <span class="block text-xs text-gray-400 uppercase tracking-wider font-semibold">Total Rombel</span>
                        <span class="text-lg font-bold text-navy">{{ $program->classrooms->count() }}</span>
                    </div>
                </div>
            </div>

            <!-- Daftar Rombel -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center">
                    <h3 class="text-md font-semibold text-navy">Rombongan Belajar (Rombel) Terdaftar</h3>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50/50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Rombel</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tingkat / Fase</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kapasitas</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-100">
                            @forelse($program->classrooms as $classroom)
                                <tr class="hover:bg-gray-50/50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-navy">
                                        {{ $classroom->nama }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        Tingkat {{ $classroom->tingkat ?? '-' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $classroom->kapasitas ? $classroom->kapasitas . ' Siswa' : '-' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-emerald-50 text-emerald-700">
                                            Aktif
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-8 text-center text-sm text-gray-400">
                                        Belum ada rombel terdaftar pada program ini.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>

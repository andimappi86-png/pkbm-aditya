<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-navy leading-tight">
                    {{ __('Program PKBM') }}
                </h2>
                <p class="text-sm text-gray-500 mt-1">Daftar jenjang pendidikan kesetaraan yang diselenggarakan.</p>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                @forelse($programs as $program)
                    <div class="card bg-white border border-gray-100 rounded-xl shadow-sm hover:shadow transition-shadow overflow-hidden flex flex-col justify-between">
                        <div class="p-6">
                            <div class="flex items-start justify-between">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700">
                                    {{ $program->kode }}
                                </span>
                                <span class="text-xs text-gray-400 font-medium">Aktif</span>
                            </div>

                            <h3 class="text-lg font-bold text-navy mt-4 mb-2">
                                {{ $program->nama }}
                            </h3>

                            <p class="text-gray-500 text-sm mb-6 line-clamp-3 leading-relaxed">
                                {{ $program->deskripsi ?? 'Tidak ada deskripsi rinci untuk program ini.' }}
                            </p>

                            <div class="grid grid-cols-2 gap-4 border-t border-gray-50 pt-4">
                                <div>
                                    <span class="block text-xs text-gray-400 uppercase tracking-wider font-semibold">Peserta Didik</span>
                                    <span class="text-xl font-bold text-navy">{{ $program->students_count ?? 0 }}</span>
                                </div>
                                <div>
                                    <span class="block text-xs text-gray-400 uppercase tracking-wider font-semibold">Rombel</span>
                                    <span class="text-xl font-bold text-navy">{{ $program->classrooms_count ?? 0 }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="bg-gray-50/50 px-6 py-3 border-t border-gray-100 flex items-center justify-end">
                            <a href="{{ route('programs.show', $program) }}" class="text-sm font-semibold text-emerald-600 hover:text-emerald-700 flex items-center gap-1 transition-colors">
                                Detail Program
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                </svg>
                            </a>
                        </div>
                    </div>
                @empty
                    <div class="col-span-full bg-white p-8 rounded-xl text-center border border-gray-100">
                        <p class="text-gray-500">Belum ada data Program PKBM yang terdaftar.</p>
                    </div>
                @endforelse
            </div>

        </div>
    </div>
</x-app-layout>

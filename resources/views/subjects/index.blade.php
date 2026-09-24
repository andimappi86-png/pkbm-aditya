<x-app-layout title="Daftar Mata Pelajaran">
    <div class="max-w-[1280px] mx-auto space-y-6">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
            <div>
                <h1 class="text-2xl font-bold text-[#172B3A]">Mata Pelajaran</h1>
                <p class="text-[#64748B] mt-1">Kelola daftar mata pelajaran yang digunakan dalam kegiatan pembelajaran PKBM.</p>
            </div>
            @can('subjects.create')
            <a href="{{ route('mata-pelajaran.create') }}" class="inline-flex items-center gap-2 px-6 py-2.5 bg-[#167C80] text-white rounded-lg font-semibold hover:bg-[#0F5F63] transition-all shadow-sm">
                <x-icon name="plus" class="w-5 h-5" />
                Tambah Mata Pelajaran
            </a>
            @endcan
        </div>

        <!-- Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <div class="bg-white border border-[#D9E2E5] rounded-xl p-6 shadow-sm flex items-center gap-4">
                <div class="w-12 h-12 rounded-lg bg-[#E8F5F4] flex items-center justify-center">
                    <x-icon name="book" class="w-6 h-6 text-[#167C80]" />
                </div>
                <div>
                    <p class="text-sm text-[#64748B] font-medium uppercase tracking-wider">Total Mata Pelajaran</p>
                    <p class="text-2xl font-bold text-[#172B3A]">{{ $total ?? 0 }}</p>
                </div>
            </div>
            <div class="bg-white border border-[#D9E2E5] rounded-xl p-6 shadow-sm flex items-center gap-4">
                <div class="w-12 h-12 rounded-lg bg-green-50 flex items-center justify-center">
                    <x-icon name="check-circle" class="w-6 h-6 text-green-600" />
                </div>
                <div>
                    <p class="text-sm text-[#64748B] font-medium uppercase tracking-wider">Aktif</p>
                    <p class="text-2xl font-bold text-[#172B3A]">{{ $totalAktif ?? 0 }}</p>
                </div>
            </div>
            <div class="bg-white border border-[#D9E2E5] rounded-xl p-6 shadow-sm flex items-center gap-4">
                <div class="w-12 h-12 rounded-lg bg-gray-50 flex items-center justify-center">
                    <x-icon name="x-circle" class="w-6 h-6 text-[#64748B]" />
                </div>
                <div>
                    <p class="text-sm text-[#64748B] font-medium uppercase tracking-wider">Nonaktif</p>
                    <p class="text-2xl font-bold text-[#172B3A]">{{ $totalNonAktif ?? 0 }}</p>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="bg-white border border-[#D9E2E5] rounded-xl shadow-sm">
            <!-- Toolbar / Filter -->
            <div class="p-4 border-b border-[#D9E2E5] bg-[#F5F7F8] rounded-t-xl">
                <form method="GET" action="{{ route('mata-pelajaran.index') }}" class="flex flex-col sm:flex-row gap-4 items-end">
                    <div class="flex-1 w-full">
                        <label class="block text-sm font-semibold text-[#172B3A] mb-1">Pencarian</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <x-icon name="search" class="h-5 w-5 text-[#64748B]" />
                            </div>
                            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari kode atau nama mata pelajaran..." class="w-full pl-10 pr-3 py-2 border border-[#D9E2E5] rounded-lg focus:ring-[#167C80] focus:border-[#167C80] text-sm">
                        </div>
                    </div>
                    <div class="w-full sm:w-64 shrink-0">
                        <label class="block text-sm font-semibold text-[#172B3A] mb-1">Filter Status</label>
                        <select name="status_aktif" class="w-full px-3 py-2 border border-[#D9E2E5] rounded-lg focus:ring-[#167C80] focus:border-[#167C80] text-sm text-[#172B3A]">
                            <option value="">Semua Status</option>
                            <option value="true" {{ request('status_aktif') === 'true' ? 'selected' : '' }}>Aktif</option>
                            <option value="false" {{ request('status_aktif') === 'false' ? 'selected' : '' }}>Nonaktif</option>
                        </select>
                    </div>
                    <div class="flex gap-2 w-full sm:w-auto mt-2 sm:mt-0 shrink-0">
                        <button type="submit" class="w-full sm:w-auto px-4 py-2 bg-[#167C80] text-white rounded-lg font-medium hover:bg-[#0F5F63] transition-colors">
                            Terapkan
                        </button>
                        @if(request()->hasAny(['search', 'status_aktif']))
                            <a href="{{ route('mata-pelajaran.index') }}" class="w-full sm:w-auto text-center px-4 py-2 border border-[#D9E2E5] text-[#172B3A] rounded-lg font-medium hover:bg-[#F5F7F8] transition-colors">
                                Reset
                            </a>
                        @endif
                    </div>
                </form>
            </div>

            <!-- Table -->
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="bg-[#F5F7F8] border-b border-[#D9E2E5] text-[#64748B] uppercase tracking-wider text-xs">
                        <tr>
                            <th class="px-6 py-4 font-semibold">Kode</th>
                            <th class="px-6 py-4 font-semibold">Nama Mata Pelajaran</th>
                            <th class="px-6 py-4 font-semibold">Deskripsi</th>
                            <th class="px-6 py-4 font-semibold">Status</th>
                            <th class="px-6 py-4 font-semibold text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#D9E2E5]">
                        @forelse($subjects as $subject)
                        <tr class="hover:bg-gray-50/50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="font-mono text-[#172B3A] font-medium bg-gray-100 px-2 py-1 rounded">{{ $subject->kode }}</span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="font-semibold text-[#172B3A]">{{ $subject->nama }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="max-w-xs truncate text-[#64748B]" title="{{ $subject->deskripsi }}">{{ $subject->deskripsi ?: '-' }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($subject->status_aktif)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-[#E8F5F4] text-[#167C80]">
                                        Aktif
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-gray-100 text-gray-800">
                                        Nonaktif
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('mata-pelajaran.show', $subject->id) }}" class="p-2 text-[#64748B] hover:text-[#167C80] hover:bg-[#E8F5F4] rounded-lg transition-colors" title="Lihat Detail">
                                        <x-icon name="eye" class="w-5 h-5" />
                                    </a>
                                    @can('subjects.update')
                                    <a href="{{ route('mata-pelajaran.edit', $subject->id) }}" class="p-2 text-[#64748B] hover:text-[#167C80] hover:bg-[#E8F5F4] rounded-lg transition-colors" title="Edit">
                                        <x-icon name="pencil" class="w-5 h-5" />
                                    </a>
                                    @endcan
                                    @can('subjects.delete')
                                    <form action="{{ route('mata-pelajaran.destroy', $subject->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Apakah Anda yakin ingin menghapus mata pelajaran {{ $subject->nama }}? Tindakan ini tidak dapat dibatalkan.')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-2 text-[#64748B] hover:text-[#C62828] hover:bg-red-50 rounded-lg transition-colors" title="Hapus">
                                            <x-icon name="trash" class="w-5 h-5" />
                                        </button>
                                    </form>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center justify-center">
                                    <div class="w-16 h-16 bg-[#F5F7F8] rounded-full flex items-center justify-center mb-4">
                                        <x-icon name="book" class="w-8 h-8 text-[#64748B]" />
                                    </div>
                                    <h3 class="text-lg font-bold text-[#172B3A] mb-1">Belum ada mata pelajaran</h3>
                                    <p class="text-[#64748B] mb-4">Tambahkan mata pelajaran untuk mulai mengatur kegiatan pembelajaran.</p>
                                    @can('subjects.create')
                                    <a href="{{ route('mata-pelajaran.create') }}" class="px-6 py-2 bg-[#167C80] text-white rounded-lg font-medium hover:bg-[#0F5F63] transition-colors">
                                        Tambah Mata Pelajaran
                                    </a>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            @if($subjects->hasPages())
            <div class="p-4 border-t border-[#D9E2E5]">
                {{ $subjects->links() }}
            </div>
            @endif
        </div>
    </div>
</x-app-layout>

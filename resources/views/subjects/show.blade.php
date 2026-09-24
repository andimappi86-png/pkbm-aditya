<x-app-layout title="{{ $subject->nama }}">
    <div class="max-w-[1000px] mx-auto space-y-6">
        <!-- Breadcrumb -->
        <x-breadcrumb :items="[
            ['label' => 'Mata Pelajaran', 'href' => route('mata-pelajaran.index')],
            ['label' => $subject->nama]
        ]" />

        <!-- Header Card -->
        <div class="bg-white border border-[#D9E2E5] rounded-xl p-6 shadow-sm flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <div class="flex items-center gap-3">
                    <h1 class="text-2xl font-bold text-[#172B3A]">{{ $subject->nama }}</h1>
                    @if($subject->status_aktif)
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-[#E8F5F4] text-[#167C80]">
                            Aktif
                        </span>
                    @else
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-gray-100 text-gray-800">
                            Nonaktif
                        </span>
                    @endif
                </div>
                <p class="text-[#64748B] font-mono mt-1">Kode: {{ $subject->kode }}</p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('mata-pelajaran.index') }}" class="px-4 py-2 border border-[#D9E2E5] text-[#172B3A] rounded-lg font-medium hover:bg-[#F5F7F8] transition-colors text-sm">
                    Kembali
                </a>
                @can('subjects.update')
                <a href="{{ route('mata-pelajaran.edit', $subject->id) }}" class="inline-flex items-center gap-2 px-4 py-2 bg-[#167C80] text-white rounded-lg font-medium hover:bg-[#0F5F63] transition-colors text-sm shadow-sm">
                    <x-icon name="pencil" class="w-4 h-4" />
                    Edit
                </a>
                @endcan
            </div>
        </div>

        <!-- Detail Information -->
        <div class="bg-white border border-[#D9E2E5] rounded-xl shadow-sm p-6 space-y-6">
            <h2 class="text-lg font-bold text-[#172B3A] border-b border-[#D9E2E5] pb-3">Informasi Mata Pelajaran</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-xs font-semibold text-[#64748B] uppercase tracking-wider mb-1">Kode Mata Pelajaran</label>
                    <p class="text-base font-semibold text-[#172B3A] font-mono">{{ $subject->kode }}</p>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-[#64748B] uppercase tracking-wider mb-1">Nama Lengkap</label>
                    <p class="text-base font-semibold text-[#172B3A]">{{ $subject->nama }}</p>
                </div>
                <div class="md:col-span-2">
                    <label class="block text-xs font-semibold text-[#64748B] uppercase tracking-wider mb-1">Deskripsi</label>
                    <p class="text-sm text-[#172B3A] bg-[#F5F7F8] p-4 rounded-lg border border-[#D9E2E5] leading-relaxed">{{ $subject->deskripsi ?: 'Tidak ada deskripsi yang ditambahkan.' }}</p>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-[#64748B] uppercase tracking-wider mb-1">Tanggal Dibuat</label>
                    <p class="text-sm text-[#64748B]">{{ $subject->created_at ? $subject->created_at->isoFormat('D MMMM Y, HH:mm') : '-' }}</p>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-[#64748B] uppercase tracking-wider mb-1">Terakhir Diperbarui</label>
                    <p class="text-sm text-[#64748B]">{{ $subject->updated_at ? $subject->updated_at->isoFormat('D MMMM Y, HH:mm') : '-' }}</p>
                </div>
            </div>
        </div>

        <!-- Tutors Relationship -->
        @if($subject->tutors->isNotEmpty())
        <div class="bg-white border border-[#D9E2E5] rounded-xl shadow-sm p-6 space-y-4">
            <div class="flex justify-between items-center">
                <div>
                    <h2 class="text-lg font-bold text-[#172B3A]">Tutor Pengampu</h2>
                    <p class="text-xs text-[#64748B]">Daftar tutor yang mengajar mata pelajaran ini.</p>
                </div>
            </div>

            <div class="overflow-x-auto border border-[#D9E2E5] rounded-lg">
                <table class="w-full text-sm text-left">
                    <thead class="bg-[#F5F7F8] border-b border-[#D9E2E5] text-[#64748B] uppercase tracking-wider text-xs">
                        <tr>
                            <th class="px-4 py-3 font-semibold">Nomor Induk</th>
                            <th class="px-4 py-3 font-semibold">Nama Tutor</th>
                            <th class="px-4 py-3 font-semibold">Bidang Keahlian</th>
                            <th class="px-4 py-3 font-semibold">Status</th>
                            <th class="px-4 py-3 font-semibold text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#D9E2E5]">
                        @foreach($subject->tutors as $tutor)
                        <tr class="hover:bg-gray-50/50">
                            <td class="px-4 py-3 font-mono text-xs text-[#64748B]">{{ $tutor->nomor_induk ?? '-' }}</td>
                            <td class="px-4 py-3 font-semibold text-[#172B3A]">{{ $tutor->nama_lengkap }}</td>
                            <td class="px-4 py-3 text-[#64748B]">{{ $tutor->bidang_keahlian ?? '-' }}</td>
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $tutor->status_tutor == 'aktif' ? 'bg-[#E8F5F4] text-[#167C80]' : 'bg-gray-100 text-gray-800' }}">
                                    {{ ucfirst($tutor->status_tutor ?? 'aktif') }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <a href="{{ route('tutors.show', $tutor->id) }}" class="p-1.5 text-[#64748B] hover:text-[#167C80] inline-block rounded" title="Lihat Tutor">
                                    <x-icon name="eye" class="w-4 h-4" />
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif
    </div>
</x-app-layout>
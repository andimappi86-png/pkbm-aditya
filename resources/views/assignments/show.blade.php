<x-app-layout>
    <x-slot name="title">{{ $assignment->judul }}</x-slot>

    <div class="max-w-5xl mx-auto space-y-6">
        <x-breadcrumb :items="[['label' => 'Pembelajaran'], ['label' => 'Tugas & Quiz', 'url' => route('tugas.index')], ['label' => $assignment->judul]]" />

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Info Utama Tugas -->
            <div class="md:col-span-2 space-y-6">
                <div class="bg-surface border border-border rounded-2xl p-6 shadow-sm">
                    <div class="flex items-center gap-2 mb-3">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $assignment->type === 'quiz' ? 'bg-amber-100 text-amber-800' : 'bg-blue-100 text-blue-800' }}">
                            {{ ucfirst($assignment->type) }}
                        </span>
                        @if($assignment->deadline)
                        <span class="text-xs text-muted flex items-center">
                            <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            Deadline: {{ $assignment->deadline->format('d M Y, H:i') }}
                        </span>
                        @endif
                    </div>

                    <h2 class="text-xl font-bold text-navy mb-4">{{ $assignment->judul }}</h2>

                    @if($assignment->deskripsi)
                    <div class="text-sm text-navy mb-4">
                        <p class="font-medium text-muted mb-1 text-xs uppercase tracking-wider">Deskripsi</p>
                        <p class="whitespace-pre-line">{{ $assignment->deskripsi }}</p>
                    </div>
                    @endif

                    @if($assignment->instruksi)
                    <div class="text-sm text-navy mb-4">
                        <p class="font-medium text-muted mb-1 text-xs uppercase tracking-wider">Instruksi Pengerjaan</p>
                        <div class="p-4 bg-background rounded-xl border border-border whitespace-pre-line text-sm text-navy">
                            {{ $assignment->instruksi }}
                        </div>
                    </div>
                    @endif

                    @if($assignment->file_materi)
                    <div class="pt-4 border-t border-border">
                        <a href="{{ Storage::url($assignment->file_materi) }}" target="_blank" class="inline-flex items-center gap-2 text-sm font-medium text-primary hover:text-primary-dark">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" /></svg>
                            Unduh Lampiran Materi
                        </a>
                    </div>
                    @endif
                </div>

                <!-- Bagian Pengumpulan untuk Siswa -->
                @role('peserta-didik')
                @php
                    $mySubmission = $submissions->first();
                @endphp
                <div class="bg-surface border border-border rounded-2xl p-6 shadow-sm">
                    <h3 class="text-base font-bold text-navy mb-4">Pengumpulan Jawaban</h3>

                    @if(!$mySubmission)
                    <form action="{{ route('pengumpulan.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                        @csrf
                        <input type="hidden" name="assignment_id" value="{{ $assignment->id }}">

                        <div>
                            <label for="jawaban" class="block text-sm font-medium text-navy mb-1.5">Teks Jawaban</label>
                            <textarea name="jawaban" id="jawaban" rows="4" placeholder="Tuliskan jawaban Anda di sini..." class="block w-full rounded-xl border-border bg-background px-3 py-2 text-sm focus:border-primary focus:ring-primary">{{ old('jawaban') }}</textarea>
                            @error('jawaban') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="file_jawaban" class="block text-sm font-medium text-navy mb-1.5">Lampiran File (Opsional, max 10MB)</label>
                            <input type="file" name="file_jawaban" id="file_jawaban" class="block w-full rounded-xl border-border bg-background px-3 py-2 text-sm focus:border-primary focus:ring-primary">
                            @error('file_jawaban') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <div class="flex justify-end pt-2">
                            <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-primary px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-primary-dark transition-colors focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2">
                                Kumpulkan Tugas
                            </button>
                        </div>
                    </form>
                    @else
                    <div class="space-y-4">
                        <div class="p-4 bg-background rounded-xl border border-border">
                            <div class="flex justify-between items-center mb-2">
                                <span class="text-xs font-semibold uppercase tracking-wider text-muted">Status Pengumpulan</span>
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold {{ $mySubmission->status === 'graded' ? 'bg-emerald-100 text-emerald-800' : 'bg-blue-100 text-blue-800' }}">
                                    {{ ucfirst($mySubmission->status) }}
                                </span>
                            </div>
                            <p class="text-xs text-muted">Dikirim pada: {{ $mySubmission->submitted_at?->format('d M Y, H:i') ?? '-' }}</p>

                            @if($mySubmission->jawaban)
                            <div class="mt-3 pt-3 border-t border-border">
                                <p class="text-xs font-semibold text-muted mb-1">Jawaban Anda:</p>
                                <p class="text-sm text-navy whitespace-pre-line">{{ $mySubmission->jawaban }}</p>
                            </div>
                            @endif

                            @if($mySubmission->file_jawaban)
                            <div class="mt-3">
                                <a href="{{ Storage::url($mySubmission->file_jawaban) }}" target="_blank" class="inline-flex items-center gap-1.5 text-xs text-primary hover:underline font-medium">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                                    Unduh File Jawaban
                                </a>
                            </div>
                            @endif
                        </div>

                        @if($mySubmission->status === 'graded')
                        <div class="p-4 bg-emerald-50 border border-emerald-200 rounded-xl">
                            <div class="flex justify-between items-center mb-1">
                                <span class="text-sm font-bold text-emerald-900">Nilai</span>
                                <span class="text-2xl font-black text-emerald-700">{{ $mySubmission->nilai }}</span>
                            </div>
                            @if($mySubmission->feedback)
                            <div class="mt-2 pt-2 border-t border-emerald-200/50">
                                <p class="text-xs font-semibold text-emerald-800">Catatan/Feedback Tutor:</p>
                                <p class="text-sm text-emerald-900">{{ $mySubmission->feedback }}</p>
                            </div>
                            @endif
                        </div>
                        @endif
                    </div>
                    @endif
                </div>
                @endrole

                <!-- Tabel Pengumpulan untuk Tutor/Kepala -->
                @hasanyrole('tutor|kepala-pkbm|ketua-yayasan|wali-kelas')
                <div class="bg-surface border border-border rounded-2xl p-6 shadow-sm space-y-4">
                    <h3 class="text-base font-bold text-navy">Daftar Pengumpulan Siswa</h3>
                    
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm whitespace-nowrap">
                            <thead class="bg-background/50 border-b border-border text-muted">
                                <tr>
                                    <th class="px-4 py-3 font-medium">Siswa</th>
                                    <th class="px-4 py-3 font-medium">Waktu Kirim</th>
                                    <th class="px-4 py-3 font-medium">Status</th>
                                    <th class="px-4 py-3 font-medium text-right">Nilai</th>
                                    @can('assignments.manage')
                                    <th class="px-4 py-3 font-medium text-right">Aksi</th>
                                    @endcan
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-border">
                                @forelse($submissions as $sub)
                                <tr>
                                    <td class="px-4 py-3">
                                        <div class="font-medium text-navy">{{ $sub->student->nama_lengkap }}</div>
                                        <div class="text-xs text-muted">{{ $sub->student->nomor_induk }}</div>
                                    </td>
                                    <td class="px-4 py-3 text-muted text-xs">
                                        {{ $sub->submitted_at ? $sub->submitted_at->format('d M Y, H:i') : '-' }}
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold {{ $sub->status === 'graded' ? 'bg-emerald-100 text-emerald-800' : 'bg-blue-100 text-blue-800' }}">
                                            {{ ucfirst($sub->status) }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-right font-bold text-navy">
                                        {{ $sub->nilai ?? '-' }}
                                    </td>
                                    @can('assignments.manage')
                                    <td class="px-4 py-3 text-right">
                                        <!-- Form Penilaian Sederhana -->
                                        <details class="inline-block text-left">
                                            <summary class="cursor-pointer text-primary hover:text-primary-dark font-medium text-xs">Beri Nilai</summary>
                                            <div class="absolute right-4 mt-2 w-72 p-4 bg-surface border border-border shadow-lg rounded-2xl z-10 space-y-3">
                                                <form action="{{ route('pengumpulan.update', $sub) }}" method="POST">
                                                    @csrf @method('PUT')
                                                    <div>
                                                        <label class="block text-xs font-medium text-navy mb-1">Nilai (0-100)</label>
                                                        <input type="number" step="0.01" min="0" max="100" name="nilai" value="{{ $sub->nilai }}" required class="block w-full rounded-xl border-border bg-background px-3 py-1.5 text-xs">
                                                    </div>
                                                    <div class="mt-2">
                                                        <label class="block text-xs font-medium text-navy mb-1">Feedback</label>
                                                        <textarea name="feedback" rows="2" class="block w-full rounded-xl border-border bg-background px-3 py-1.5 text-xs">{{ $sub->feedback }}</textarea>
                                                    </div>
                                                    <button type="submit" class="mt-3 w-full inline-flex items-center justify-center rounded-xl bg-primary px-3 py-1.5 text-xs font-medium text-white shadow-sm hover:bg-primary-dark">
                                                        Simpan Nilai
                                                    </button>
                                                </form>
                                            </div>
                                        </details>
                                    </td>
                                    @endcan
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="px-4 py-6 text-center text-muted text-xs">Belum ada peserta yang mengumpulkan.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                @endhasanyrole
            </div>

            <!-- Sidebar Info Kelas/Tutor -->
            <div class="space-y-6">
                <div class="bg-surface border border-border rounded-2xl p-6 shadow-sm space-y-4">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-muted">Informasi Kelas</h3>
                    <div>
                        <p class="text-xs text-muted">Mata Pelajaran</p>
                        <p class="font-semibold text-navy">{{ $assignment->penugasanMengajar->subject->nama }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-muted">Rombel / Kelas</p>
                        <p class="font-semibold text-navy">{{ $assignment->penugasanMengajar->classroom->nama }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-muted">Tutor</p>
                        <p class="font-semibold text-navy">{{ $assignment->penugasanMengajar->tutor->nama_lengkap }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
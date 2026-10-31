<x-app-layout>
    <x-slot name="title">Penilaian Esai - {{ $quiz->judul }}</x-slot>

    <div class="max-w-6xl mx-auto space-y-6">
        <x-breadcrumb :items="[['label' => 'Pembelajaran'], ['label' => 'Kuis & Ujian', 'href' => route('quiz.index')], ['label' => $quiz->judul, 'href' => route('quiz.show', $quiz)], ['label' => 'Penilaian Esai']]" />

        <div class="card p-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
                <div>
                    <h1 class="text-2xl font-bold text-text">{{ $quiz->judul }}</h1>
                    <p class="text-muted mt-1">Antrean penilaian soal esai</p>
                </div>
            </div>

            @if($quiz->questions()->where('tipe', 'esai')->count() === 0)
            <div class="text-center py-12 border-2 border-dashed border-border rounded-xl">
                <svg class="mx-auto w-16 h-16 text-muted mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <h3 class="text-lg font-medium text-navy mb-2">Tidak Ada Soal Esai</h3>
                <p class="text-muted mb-4">Quiz ini tidak memiliki soal bertipe esai.</p>
                <a href="{{ route('quiz.questions.create', $quiz) }}" class="btn-primary">Tambah Soal Esai</a>
            </div>
            @else
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-border text-left text-sm text-muted">
                            <th class="pb-3 font-medium">Peserta Didik</th>
                            <th class="pb-3 font-medium">Percobaan</th>
                            <th class="pb-3 font-medium">Waktu Mulai</th>
                            <th class="pb-3 font-medium">Waktu Selesai</th>
                            <th class="pb-3 font-medium">Status</th>
                            <th class="pb-3 font-medium">Soal Esai</th>
                            <th class="pb-3 font-medium">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border">
                        @forelse($attempts as $attempt)
                        <tr class="hover:bg-gray-50">
                            <td class="py-4">
                                <div class="font-medium text-text">{{ $attempt->user->name }}</div>
                                <div class="text-xs text-muted">{{ $attempt->user->email }}</div>
                            </td>
                            <td class="py-4 text-center">
                                <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-primary-soft text-primary">
                                    Percobaan #{{ $attempt->attempt_number }}
                                </span>
                            </td>
                            <td class="py-4 text-sm text-muted">{{ $attempt->mulai_pada ? $attempt->mulai_pada->format('d M Y H:i') : '-' }}</td>
                            <td class="py-4 text-sm text-muted">{{ $attempt->selesai_pada ? $attempt->selesai_pada->format('d M Y H:i') : '-' }}</td>
                            <td class="py-4">
                                @if($attempt->status === 'dikerjakan')
                                <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-yellow-100 text-yellow-800">Dikerjakan</span>
                                @elseif($attempt->status === 'diserahkan')
                                <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-blue-100 text-blue-800">Diserahkan</span>
                                @elseif($attempt->status === 'dinilai')
                                <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-green-100 text-green-800">Dinilai</span>
                                @elseif($attempt->status === 'otomatis')
                                <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-purple-100 text-purple-800">Otomatis</span>
                                @else
                                <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-gray-100 text-gray-800">{{ ucfirst($attempt->status) }}</span>
                                @endif
                            </td>
                            <td class="py-4">
                                @php
                                    $essayAnswers = $attempt->answers()->whereHas('question', fn($q) => $q->where('tipe', 'esai'))->get();
                                    $totalEssay = $essayAnswers->count();
                                    $gradedEssay = $essayAnswers->where('is_graded', true)->count();
                                    $pendingEssay = $totalEssay - $gradedEssay;
                                @endphp
                                @if($totalEssay > 0)
                                <div class="flex items-center gap-2">
                                    <span class="text-sm font-medium">{{ $gradedEssay }}/{{ $totalEssay }}</span>
                                    @if($pendingEssay > 0)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-yellow-100 text-yellow-800">{{ $pendingEssay }} pending</span>
                                    @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">Selesai</span>
                                    @endif
                                </div>
                                @else
                                <span class="text-sm text-muted">-</span>
                                @endif
                            </td>
                            <td class="py-4">
                                <a href="{{ route('quiz.essay-grade.show', [$quiz, $attempt]) }}" class="btn-primary text-sm">Nilai</a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="py-12 text-center text-muted">Belum ada percobaan untuk quiz ini.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($attempts->hasPages())
            <div class="mt-6">
                {{ $attempts->links() }}
            </div>
            @endif
            @endif
        </div>
    </div>
</x-app-layout>
<x-app-layout>
    <x-slot name="title">Rekap Nilai - {{ $quiz->judul }}</x-slot>

    <div class="max-w-6xl mx-auto space-y-6">
        <x-breadcrumb :items="[['label' => 'Pembelajaran'], ['label' => 'Kuis & Ujian', 'href' => route('quiz.index')], ['label' => $quiz->judul, 'href' => route('quiz.show', $quiz)], ['label' => 'Rekap Nilai']]" />

        <div class="card p-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
                <div>
                    <h1 class="text-2xl font-bold text-text">{{ $quiz->judul }}</h1>
                    <p class="text-muted mt-1">Rekap nilai peserta didik</p>
                </div>
                <a href="{{ route('quiz.export-recap', $quiz) }}" class="btn-secondary">Ekspor CSV</a>
            </div>

            @if($recapData->isEmpty())
            <div class="text-center py-12 border-2 border-dashed border-border rounded-xl">
                <svg class="mx-auto w-16 h-16 text-muted mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <h3 class="text-lg font-medium text-navy mb-2">Belum Ada Data Nilai</h3>
                <p class="text-muted">Belum ada peserta didik yang menyelesaikan quiz ini.</p>
            </div>
            @else
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-border text-left text-sm text-muted">
                            <th class="pb-3 font-medium">No</th>
                            <th class="pb-3 font-medium">Peserta Didik</th>
                            <th class="pb-3 font-medium text-center">Percobaan</th>
                            <th class="pb-3 font-medium text-center">Status</th>
                            <th class="pb-3 font-medium text-center">Skor Otomatis</th>
                            <th class="pb-3 font-medium text-center">Skor Esai</th>
                            <th class="pb-3 font-medium text-center">Total Nilai</th>
                            <th class="pb-3 font-medium text-center">KKM</th>
                            <th class="pb-3 font-medium text-center">Waktu Selesai</th>
                            <th class="pb-3 font-medium text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border">
                        @foreach($recapData as $index => $data)
                        <tr class="hover:bg-gray-50">
                            <td class="py-4 text-center text-sm text-muted">{{ $index + 1 }}</td>
                            <td class="py-4">
                                <div class="font-medium text-text">{{ $data['user']->name }}</div>
                                <div class="text-xs text-muted">{{ $data['user']->email }}</div>
                            </td>
                            <td class="py-4 text-center">
                                <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-primary-soft text-primary">
                                    #{{ $data['attempt']->attempt_number }}
                                </span>
                            </td>
                            <td class="py-4 text-center">
                                @if($data['attempt']->status === 'dikerjakan')
                                <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-yellow-100 text-yellow-800">Dikerjakan</span>
                                @elseif($data['attempt']->status === 'diserahkan')
                                <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-blue-100 text-blue-800">Diserahkan</span>
                                @elseif($data['attempt']->status === 'dinilai')
                                <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-green-100 text-green-800">Dinilai</span>
                                @elseif($data['attempt']->status === 'otomatis')
                                <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-purple-100 text-purple-800">Otomatis</span>
                                @else
                                <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-gray-100 text-gray-800">{{ ucfirst($data['attempt']->status) }}</span>
                                @endif
                            </td>
                            <td class="py-4 text-center font-medium">
                                @if($data['auto_score'] !== null)
                                {{ number_format($data['auto_score'], 1) }}
                                @else
                                <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td class="py-4 text-center font-medium">
                                @if($data['essay_score'] !== null)
                                {{ number_format($data['essay_score'], 1) }}
                                @else
                                <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td class="py-4 text-center font-bold text-lg {{ $data['total_score'] >= ($quiz->kkm ?? 0) ? 'text-green-700' : 'text-red-700' }}">
                                @if($data['total_score'] !== null)
                                {{ number_format($data['total_score'], 1) }}
                                @else
                                <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td class="py-4 text-center">
                                @if($quiz->kkm)
                                <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium {{ $data['total_score'] !== null && $data['total_score'] >= $quiz->kkm ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                    {{ $data['total_score'] !== null && $data['total_score'] >= $quiz->kkm ? 'Lulus' : 'Tidak Lulus' }}
                                </span>
                                <div class="text-xs text-muted mt-1">KKM: {{ $quiz->kkm }}</div>
                                @else
                                <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td class="py-4 text-center text-sm text-muted">
                                {{ $data['attempt']->selesai_pada ? $data['attempt']->selesai_pada->format('d M Y H:i') : '-' }}
                            </td>
                            <td class="py-4 text-center">
                                <a href="{{ route('quiz.attempts.result', $data['attempt']) }}" class="btn-primary text-sm">Detail</a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($recapData->hasPages())
            <div class="mt-6">
                {{ $recapData->links() }}
            </div>
            @endif
            @endif
        </div>
    </div>
</x-app-layout>
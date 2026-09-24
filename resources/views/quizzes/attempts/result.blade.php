<x-app-layout>
    <x-slot name="title">Hasil Kuis: {{ $quiz->judul }}</x-slot>

    <div class="max-w-3xl mx-auto space-y-6">
        <x-breadcrumb :items="[['label' => 'Pembelajaran'], ['label' => 'Kuis & Ujian', 'href' => route('quiz.index')], ['label' => 'Hasil Kuis']]" />

        <div class="card p-8 text-center">
            <div class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-success/10 text-success mb-4">
                <x-icon name="check" class="w-10 h-10" />
            </div>
            
            <h1 class="text-2xl font-bold text-text mb-2">Kuis Selesai!</h1>
            <p class="text-muted mb-6">Anda telah menyelesaikan kuis "{{ $quiz->judul }}".</p>
            
            <div class="inline-block p-6 rounded-2xl bg-gray-50 border border-border">
                <div class="text-sm font-semibold text-muted uppercase tracking-wider mb-2">Nilai Akhir</div>
                <div class="text-5xl font-black {{ $attempt->nilai >= 70 ? 'text-success' : 'text-danger' }}">
                    {{ $attempt->nilai }}
                </div>
                <div class="text-sm text-muted mt-2">dari skala 100</div>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div class="card p-4 flex flex-col items-center justify-center text-center">
                <span class="text-muted text-sm mb-1">Mulai</span>
                <span class="font-medium text-text">{{ $attempt->mulai_waktu->format('d M Y, H:i') }}</span>
            </div>
            <div class="card p-4 flex flex-col items-center justify-center text-center">
                <span class="text-muted text-sm mb-1">Selesai</span>
                <span class="font-medium text-text">{{ $attempt->selesai_waktu->format('d M Y, H:i') }}</span>
            </div>
        </div>

        <div class="flex justify-center pt-4">
            <a href="{{ route('quiz.index') }}" class="btn-primary">
                Kembali ke Daftar Kuis
            </a>
        </div>
    </div>
</x-app-layout>
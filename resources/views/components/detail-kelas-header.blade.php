@props([
    'penugasan' => null,
    'activeTab' => 'overview'
])

@if($penugasan)
<div class="space-y-6 mb-6">
    <!-- Header Section -->
    <div class="card p-6 border border-border">
        <x-breadcrumb :items="[
            ['label' => 'Kelas Saya', 'url' => route('kelas-saya.index')],
            ['label' => 'Detail Kelas ' . $penugasan->subject->nama]
        ]" class="mb-4" />

        <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-6">
            <div>
                <div class="flex items-center gap-2 mb-2">
                    <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-semibold bg-primary-dark text-white uppercase tracking-wider">
                        {{ $penugasan->classroom->program->nama ?? 'Program' }}
                    </span>
                    <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-medium bg-primary-soft text-primary-dark">
                        {{ $penugasan->classroom->academicYear->nama_ajaran ?? 'Tahun Ajaran' }}
                    </span>
                </div>
                <h1 class="text-2xl lg:text-3xl font-bold text-navy">
                    {{ $penugasan->subject->nama }} ({{ $penugasan->classroom->nama_rombel }})
                </h1>
                <p class="text-muted mt-1 max-w-3xl">
                    Studi Terapan: Modul, Evaluasi, & Analisis Akademik
                </p>

                <div class="flex flex-wrap items-center gap-x-6 gap-y-3 mt-5">
                    <div class="flex items-center gap-3">
                        <x-avatar :src="$penugasan->tutor->user->avatarUrl ?? ''" :alt="$penugasan->tutor->nama_lengkap" size="sm" />
                        <div class="text-sm">
                            <p class="font-medium text-navy">{{ $penugasan->tutor->user->name ?? $penugasan->tutor->nama_lengkap }}</p>
                            <p class="text-xs text-muted">Tutor</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2 text-sm text-muted">
                        <svg class="w-4 h-4 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg>
                        Ruang Kelas Daring
                    </div>
                </div>
            </div>

            <div class="w-full lg:w-64 bg-background/50 rounded-xl p-4 border border-border">
                @php
                    $totalTasks = $penugasan->assignments()->count();
                    $studentId = auth()->user()->student->id ?? null;
                    $completedTasks = 0;
                    if ($studentId && $totalTasks > 0) {
                        $assignmentIds = $penugasan->assignments()->pluck('id');
                        $completedTasks = \App\Models\Submission::whereIn('assignment_id', $assignmentIds)
                            ->where('student_id', $studentId)
                            ->count();
                    }
                    $progressPercentage = $totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100) : 0;
                @endphp
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-bold text-muted uppercase tracking-wider">Progress Kelas</span>
                    <span class="text-sm font-bold text-primary">{{ $progressPercentage }}%</span>
                </div>
                <div class="h-2 bg-border rounded-full overflow-hidden">
                    <div class="h-full bg-primary rounded-full transition-all duration-500" style="width: {{ $progressPercentage }}%"></div>
                </div>
                <p class="text-xs text-muted mt-2 text-center">
                    {{ $penugasan->materials()->count() }} Materi &bull; {{ $completedTasks }} / {{ $totalTasks }} Tugas Selesai
                </p>
            </div>
        </div>
    </div>

    <!-- Navigation Tabs -->
    <div class="border-b border-border">
        <nav class="flex overflow-x-auto hide-scrollbar" aria-label="Tabs">
            <a href="{{ route('kelas-saya.show', $penugasan) }}" class="border-b-2 py-4 px-6 text-sm font-medium whitespace-nowrap transition-colors flex items-center gap-2 {{ $activeTab === 'overview' ? 'border-primary text-primary bg-primary-soft/30' : 'border-transparent text-muted hover:text-navy hover:border-border' }}">
                Overview Kelas
            </a>
            <a href="{{ route('materi.index', ['penugasan_mengajar_id' => $penugasan->id]) }}" class="border-b-2 py-4 px-6 text-sm font-medium whitespace-nowrap transition-colors flex items-center gap-2 {{ $activeTab === 'materi' ? 'border-primary text-primary bg-primary-soft/30' : 'border-transparent text-muted hover:text-navy hover:border-border' }}">
                Materi Pembelajaran <span class="bg-gray-100 text-muted px-2 py-0.5 rounded-full text-xs font-semibold">{{ $penugasan->materials()->count() }}</span>
            </a>
            <a href="{{ route('tugas.index', ['penugasan_mengajar_id' => $penugasan->id]) }}" class="border-b-2 py-4 px-6 text-sm font-medium whitespace-nowrap transition-colors flex items-center gap-2 {{ $activeTab === 'tugas' ? 'border-primary text-primary bg-primary-soft/30' : 'border-transparent text-muted hover:text-navy hover:border-border' }}">
                Tugas & Evaluasi <span class="bg-gray-100 text-muted px-2 py-0.5 rounded-full text-xs font-semibold">{{ $penugasan->assignments()->count() }}</span>
            </a>
            <a href="{{ route('quiz.index', ['penugasan_mengajar_id' => $penugasan->id]) }}" class="border-b-2 py-4 px-6 text-sm font-medium whitespace-nowrap transition-colors flex items-center gap-2 {{ $activeTab === 'quiz' ? 'border-primary text-primary bg-primary-soft/30' : 'border-transparent text-muted hover:text-navy hover:border-border' }}">
                Kuis & Ujian <span class="bg-gray-100 text-muted px-2 py-0.5 rounded-full text-xs font-semibold">{{ $penugasan->quizzes()->count() }}</span>
            </a>
            <a href="{{ route('forum.index', ['penugasan_mengajar_id' => $penugasan->id]) }}" class="border-b-2 py-4 px-6 text-sm font-medium whitespace-nowrap transition-colors flex items-center gap-2 {{ $activeTab === 'diskusi' ? 'border-primary text-primary bg-primary-soft/30' : 'border-transparent text-muted hover:text-navy hover:border-border' }}">
                Forum Diskusi <span class="bg-gray-100 text-muted px-2 py-0.5 rounded-full text-xs font-semibold">{{ $penugasan->forums()->count() }}</span>
            </a>
        </nav>
    </div>
</div>
@endif
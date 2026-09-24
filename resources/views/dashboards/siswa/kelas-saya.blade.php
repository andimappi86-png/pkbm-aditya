<x-app-layout>
    <x-slot name="title">Kelas Saya</x-slot>

    <div class="max-w-7xl mx-auto space-y-6">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <x-breadcrumb :items="[['label' => 'Pembelajaran'], ['label' => 'Kelas Saya']]" />
                <h1 class="text-2xl lg:text-3xl font-bold text-navy mt-2">Kelas Saya</h1>
                <p class="text-muted mt-1">Kelola dan lanjutkan pembelajaran yang sedang Anda ikuti.</p>
                @if($myClassroom)
                    <div class="flex items-center gap-4 mt-3 text-sm text-muted">
                        <span class="flex items-center gap-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg>
                            Rombel: {{ $myClassroom->nama_rombel }}
                        </span>
                        @if($myClassroom->program)
                            <span class="flex items-center gap-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" /></svg>
                                Program: {{ $myClassroom->program->nama }}
                            </span>
                        @endif
                        @if($myClassroom->academicYear)
                            <span class="flex items-center gap-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                {{ $myClassroom->academicYear->nama_ajaran }}
                            </span>
                        @endif
                    </div>
                @endif
            </div>
        </div>

        <!-- Filter -->
        <div class="card p-4">
            <div class="flex flex-col sm:flex-row gap-4">
                <div class="flex-1 max-w-xs">
                    <label for="search" class="sr-only">Cari Kelas</label>
                    <div class="relative">
                        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                        <input type="text" id="search" wire:model.debounce.300ms="search" placeholder="Cari nama mata pelajaran..." class="w-full pl-10 pr-4 py-2.5 border border-border rounded-xl bg-surface focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition-all">
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <select wire:model="statusFilter" class="px-4 py-2.5 border border-border rounded-xl bg-surface focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent text-sm">
                        <option value="all">Semua Kelas</option>
                        <option value="active">Sedang Berlangsung</option>
                        <option value="completed">Selesai</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Kelas Cards -->
        @if($penugasan->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6" id="kelas-grid">
                @foreach($penugasan as $p)
                    <article class="card flex flex-col border border-border hover:border-primary/50 hover:shadow-soft transition-all duration-300 group">
                        <div class="p-5 flex-1 flex flex-col">
                            <div class="flex items-start justify-between gap-3 mb-3">
                                <div class="flex-1 min-w-0">
                                    <h3 class="font-bold text-navy group-hover:text-primary transition-colors line-clamp-1">{{ $p->subject->nama }}</h3>
                                    <p class="text-sm text-muted mt-0.5">{{ $p->subject->kode }}</p>
                                </div>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-primary-soft text-primary-dark whitespace-nowrap shrink-0">
                                    {{ $p->subject->jenjang ?? 'LMS' }}
                                </span>
                            </div>

                            <div class="flex items-center gap-3 text-sm mb-4">
                                <div class="w-9 h-9 rounded-xl bg-primary-soft text-primary-dark flex items-center justify-center flex-shrink-0">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="font-medium text-navy truncate">{{ $p->classroom->nama_rombel }}</p>
                                    <p class="text-xs text-muted truncate">{{ $p->tutor->user->name ?? $p->tutor->nama_lengkap }}</p>
                                </div>
                            </div>

                            <div class="flex items-center gap-3 text-sm mb-4">
                                <div class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-700 flex items-center justify-center flex-shrink-0">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="font-medium text-navy">{{ $p->assignments->count() }} Tugas</p>
                                    <p class="text-xs text-muted">{{ $p->materials->count() }} Materi &bull; {{ $p->quizzes->count() }} Kuis</p>
                                </div>
                            </div>

                            <div class="mt-auto pt-4 border-t border-border">
                                <div class="flex items-center justify-between mb-3">
                                    <span class="text-sm font-medium text-navy">Progress Kelas</span>
                                    <span class="text-sm font-semibold text-primary">{{ $p->materials->count() > 0 ? round(($p->materials->count() > 0 ? 100 : 0)) : 0 }}%</span>
                                </div>
                                <div class="h-1.5 bg-border rounded-full overflow-hidden">
                                    <div class="h-full bg-primary rounded-full transition-all duration-500" style="width: {{ $p->materials->count() > 0 ? 65 : 0 }}%"></div>
                                </div>
                            </div>
                        </div>
                        <div class="p-5 bg-background/50 border-t border-border flex justify-end">
                            <a href="{{ route('kelas-saya.show', $p) }}" class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl bg-primary text-white font-medium text-sm hover:bg-primary-dark transition-colors shadow-sm hover:shadow-md focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" /></svg>
                                Masuk Kelas
                            </a>
                        </div>
                    </article>
                @endforeach
            </div>
        @else
            <div class="card py-16 text-center">
                <svg class="mx-auto h-16 w-16 text-muted mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                </svg>
                <h3 class="mt-2 text-lg font-medium text-navy">Belum Ada Kelas</h3>
                <p class="mt-1 text-sm text-muted">Anda belum terdaftar di rombel manapun. Silakan hubungi wali kelas Anda.</p>
            </div>
        @endif
    </div>
</x-app-layout>
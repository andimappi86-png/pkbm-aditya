<x-app-layout>
    
    <x-slot name="title">Dashboard Tutor</x-slot>

    <div class="max-w-7xl mx-auto space-y-6">
        <!-- Welcome Card -->
        <div class="card p-6 lg:p-8 bg-gradient-to-br from-surface via-surface to-primary-soft/40">
            <div class="flex items-start gap-6">
                <div class="flex-shrink-0">
                    <x-avatar :src="auth()->user()->avatarUrl" :alt="auth()->user()->name" size="xl" />
                </div>
                <div class="flex-1">
                    <h1 class="text-2xl lg:text-3xl font-bold text-navy mb-2">
                        Selamat datang, {{ auth()->user()->name }}
                    </h1>
                    <p class="text-muted mb-4">
                        Anda masuk sebagai <span class="font-semibold text-primary-dark">Tutor</span>
                    </p>
                    <div class="flex items-center gap-4 text-sm text-muted">
                        <div class="flex items-center gap-2">
                            <x-icon name="calendar" class="w-4 h-4" />
                            <span>{{ now()->locale('id')->isoFormat('dddd, D MMMM Y') }}</span>
                        </div>
                    </div>
                </div>
                <div class="hidden lg:block flex-shrink-0">
                    <img src="{{ asset('images/logo-pkbm-aditya.png') }}" alt="Logo" class="w-20 h-20 opacity-20">
                </div>
            </div>
        </div>

        <!-- Role Info -->
        <div class="card p-6">
            <div class="flex items-start gap-4">
                <div class="flex-shrink-0">
                    <div class="w-12 h-12 rounded-xl bg-primary-soft text-primary-dark flex items-center justify-center">
                        <x-icon name="info-circle" class="w-6 h-6" />
                    </div>
                </div>
                <div class="flex-1">
                    <h3 class="text-lg font-semibold text-navy mb-2">Dashboard Pembelajaran</h3>
                    <p class="text-muted text-sm leading-relaxed mb-3">
                        Sebagai Tutor, Anda dapat mengelola kegiatan pembelajaran untuk mata pelajaran dan kelas yang diampu. 
                        Anda dapat mengelola materi, tugas, kuis, ujian, absensi, dan penilaian.
                    </p>
                    <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg bg-primary-soft text-primary-dark text-sm font-medium">
                        <x-icon name="check-circle" class="w-4 h-4" />
                        <span>Subject & Class Scoped Access</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- My Subjects & Classes -->
        <div class="grid md:grid-cols-2 gap-6">
            <!-- My Subjects -->
            <div class="card p-6">
                <h3 class="text-lg font-semibold text-navy mb-4 flex items-center gap-2">
                    <x-icon name="book" class="w-5 h-5 text-primary" />
                    Mata Pelajaran Saya
                </h3>
                
                @if(count($mySubjects) > 0)
                    <div class="space-y-3">
                        @foreach($mySubjects as $subject)
                        <div class="flex items-center gap-3 p-3 rounded-xl border border-border">
                            <div class="w-10 h-10 rounded-lg bg-primary-soft text-primary-dark flex items-center justify-center">
                                <x-icon name="book-open" class="w-5 h-5" />
                            </div>
                            <div class="flex-1">
                                <div class="font-semibold text-navy text-sm">{{ $subject->name }}</div>
                                <div class="text-xs text-muted">{{ $subject->program->name }}</div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                @else
                    <x-empty-state 
                        icon="book"
                        title="Belum Ada Mata Pelajaran"
                        description="Penugasan mata pelajaran akan dilakukan di Phase 3."
                    />
                @endif
            </div>

            <!-- My Classes -->
            <div class="card p-6">
                <h3 class="text-lg font-semibold text-navy mb-4 flex items-center gap-2">
                    <x-icon name="users" class="w-5 h-5 text-primary" />
                    Kelas Mengajar Saya
                </h3>
                
                @if(count($myClasses) > 0)
                    <div class="space-y-3">
                        @foreach($myClasses as $class)
                        <div class="flex items-center gap-3 p-3 rounded-xl border border-border">
                            <div class="w-10 h-10 rounded-lg bg-primary-soft text-primary-dark flex items-center justify-center">
                                <x-icon name="layers" class="w-5 h-5" />
                            </div>
                            <div class="flex-1">
                                <div class="font-semibold text-navy text-sm">{{ $class->name }}</div>
                                <div class="text-xs text-muted">{{ $class->students_count }} Siswa</div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                @else
                    <x-empty-state 
                        icon="layers"
                        title="Belum Ada Kelas"
                        description="Penugasan kelas mengajar akan dilakukan di Phase 3."
                    />
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
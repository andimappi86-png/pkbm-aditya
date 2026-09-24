<x-app-layout title="Operator Dashboard">
    <div class="space-y-6">
        <!-- Welcome Card -->
        <div class="rounded-2xl p-6 md:p-8 bg-white border border-[#D9E2E5] shadow-sm">
            <div class="flex flex-col md:flex-row items-start md:items-center gap-6">
                <div class="flex-shrink-0">
                    <div class="w-16 h-16 rounded-2xl bg-[#E8F5F4] flex items-center justify-center">
                        <x-icon name="home" class="w-8 h-8 text-[#167C80]" />
                    </div>
                </div>
                <div class="flex-1 min-w-0">
                    <h2 class="text-2xl md:text-3xl font-bold text-[#172B3A] mb-1 truncate">
                        Selamat datang, {{ auth()->user()->name }}
                    </h2>
                    <p class="text-[#64748B]">
                        Anda masuk sebagai <span class="font-semibold text-[#167C80]">Operator Sistem</span>
                    </p>
                </div>
            </div>
        </div>

        <!-- KPI Stats Grid -->
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4">
            <x-stat-card title="Peserta Didik" :value="$stats['students']" variant="primary" icon="users" />
            <x-stat-card title="Tutor" :value="$stats['tutors']" variant="primary" icon="user-group" />
            <x-stat-card title="Rombel" :value="$stats['classrooms']" variant="primary" icon="layers" />
            <x-stat-card title="Mata Pelajaran" :value="$stats['subjects']" variant="primary" icon="book" />
            <x-stat-card title="Jadwal" :value="$stats['schedules']" variant="primary" icon="calendar" />
            
            <x-stat-card title="Absensi" :value="$stats['attendance']" variant="primary" icon="clipboard-check" />
            <x-stat-card title="Nilai" :value="$stats['grades']" variant="primary" icon="chart-bar" />
            <x-stat-card title="Tugas" :value="$stats['assignments']" variant="primary" icon="clipboard" />
            <x-stat-card title="Kuis" :value="$stats['quizzes']" variant="primary" icon="lightning-bolt" />
            <x-stat-card title="Materi" :value="$stats['materials']" variant="primary" icon="book-open" />
            
            <x-stat-card title="PPDB" :value="$stats['ppdb']" variant="accent" icon="user-check" />
            <x-stat-card title="Dokumen" :value="$stats['documents']" variant="accent" icon="file-text" />
            <x-stat-card title="Surat" :value="$stats['letters']" variant="accent" icon="envelope" />
            <x-stat-card title="Pengumuman" :value="$stats['announcements']" variant="accent" icon="megaphone" />
        </div>
    </div>
</x-app-layout>

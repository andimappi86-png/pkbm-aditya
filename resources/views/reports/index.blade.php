<x-app-layout>
    <x-slot name="title">Laporan Institusi</x-slot>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Laporan Institusi') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            <div class="card p-6">
                <h3 class="text-lg font-semibold text-navy mb-4">Pilih Jenis Laporan</h3>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <a href="{{ route('reports.students') }}" class="p-4 rounded-xl border border-border hover:border-primary/50 hover:bg-primary-soft transition-colors cursor-pointer text-center">
                        <x-icon name="users" class="w-8 h-8 mx-auto text-primary mb-2" />
                        <span class="font-medium text-navy">Peserta Didik</span>
                    </a>
                    
                    <a href="{{ route('reports.tutors') }}" class="p-4 rounded-xl border border-border hover:border-primary/50 hover:bg-primary-soft transition-colors cursor-pointer text-center">
                        <x-icon name="user-check" class="w-8 h-8 mx-auto text-primary mb-2" />
                        <span class="font-medium text-navy">Tutor</span>
                    </a>
                    
                    <a href="{{ route('reports.classrooms') }}" class="p-4 rounded-xl border border-border hover:border-primary/50 hover:bg-primary-soft transition-colors cursor-pointer text-center">
                        <x-icon name="layers" class="w-8 h-8 mx-auto text-primary mb-2" />
                        <span class="font-medium text-navy">Rombel</span>
                    </a>
                    
                    <a href="{{ route('reports.ppdb') }}" class="p-4 rounded-xl border border-border hover:border-primary/50 hover:bg-primary-soft transition-colors cursor-pointer text-center">
                        <x-icon name="document-text" class="w-8 h-8 mx-auto text-primary mb-2" />
                        <span class="font-medium text-navy">PPDB</span>
                    </a>
                    
                    <a href="{{ route('reports.attendance') }}" class="p-4 rounded-xl border border-border hover:border-primary/50 hover:bg-primary-soft transition-colors cursor-pointer text-center">
                        <x-icon name="calendar" class="w-8 h-8 mx-auto text-primary mb-2" />
                        <span class="font-medium text-navy">Kehadiran</span>
                    </a>
                    
                    <a href="{{ route('reports.grades') }}" class="p-4 rounded-xl border border-border hover:border-primary/50 hover:bg-primary-soft transition-colors cursor-pointer text-center">
                        <x-icon name="chart-bar" class="w-8 h-8 mx-auto text-primary mb-2" />
                        <span class="font-medium text-navy">Nilai Siswa</span>
                    </a>
                    
                    <a href="{{ route('reports.assignments') }}" class="p-4 rounded-xl border border-border hover:border-primary/50 hover:bg-primary-soft transition-colors cursor-pointer text-center">
                        <x-icon name="clipboard-check" class="w-8 h-8 mx-auto text-primary mb-2" />
                        <span class="font-medium text-navy">Tugas & Kuis</span>
                    </a>
                    
                    <a href="{{ route('reports.learning') }}" class="p-4 rounded-xl border border-border hover:border-primary/50 hover:bg-primary-soft transition-colors cursor-pointer text-center">
                        <x-icon name="lightning-bolt" class="w-8 h-8 mx-auto text-primary mb-2" />
                        <span class="font-medium text-navy">Aktivitas Belajar</span>
                    </a>
                </div>
            </div>
            
        </div>
    </div>
</x-app-layout>
<x-app-layout>
    <x-slot name="title">Detail Tutor</x-slot>

    <div class="max-w-7xl mx-auto space-y-6">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <x-breadcrumb :items="[
                ['label' => 'Data Tutor', 'url' => route('tutors.index')],
                ['label' => 'Detail Data']
            ]" />
            
            <div class="flex items-center gap-3">
                <a href="{{ route('tutors.index') }}" class="btn-secondary">
                    <x-icon name="chevron-left" class="w-4 h-4" />
                    Kembali
                </a>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Sidebar Info -->
            <div class="md:col-span-1 space-y-6">
                <div class="card p-6 text-center">
                    <div class="w-40 h-40 mx-auto rounded-2xl overflow-hidden bg-primary-soft border border-primary/20 mb-4 flex items-center justify-center">
                        @if($tutor->foto)
                            <img src="{{ asset('storage/' . $tutor->foto) }}" alt="{{ $tutor->nama_lengkap }}" class="w-full h-full object-cover">
                        @else
                            <x-icon name="users" class="w-16 h-16 text-primary-dark opacity-50" />
                        @endif
                    </div>
                    <h2 class="text-xl font-bold text-navy mb-1">{{ $tutor->nama_lengkap }}</h2>
                    <p class="text-muted text-sm">Nomor Induk: {{ $tutor->nomor_induk }}</p>
                    <x-badge variant="{{ $tutor->status_tutor == 'aktif' ? 'success' : 'warning' }}">
                        {{ ucfirst($tutor->status_tutor) }}
                    </x-badge>
                </div>

                <div class="card p-6 space-y-4">
                    <h3 class="text-sm font-semibold text-navy uppercase tracking-wider mb-2 border-b pb-2">Identitas</h3>
                    
                    <div>
                        <div class="text-xs text-muted">Jenis Kelamin</div>
                        <div class="text-sm font-medium text-navy">{{ $tutor->jenis_kelamin }}</div>
                    </div>
                    <div>
                        <div class="text-xs text-muted">Tempat, Tanggal Lahir</div>
                        <div class="text-sm font-medium text-navy">
                            {{ $tutor->tempat_lahir ?? '-' }}, 
                            {{ $tutor->tanggal_lahir ? $tutor->tanggal_lahir->format('d M Y') : '-' }}
                        </div>
                    </div>
                    <div>
                        <div class="text-xs text-muted">Agama</div>
                        <div class="text-sm font-medium text-navy">{{ $tutor->agama ?? '-' }}</div>
                    </div>
                </div>

                <div class="card p-6 space-y-4">
                    <h3 class="text-sm font-semibold text-navy uppercase tracking-wider mb-2 border-b pb-2">Kontak</h3>
                    
                    <div>
                        <div class="text-xs text-muted">No. Handphone</div>
                        <div class="text-sm font-medium text-navy">{{ $tutor->no_hp ?? '-' }}</div>
                    </div>
                    <div>
                        <div class="text-xs text-muted">Email</div>
                        <div class="text-sm font-medium text-navy">{{ $tutor->email ?? '-' }}</div>
                    </div>
                    <div>
                        <div class="text-xs text-muted">Alamat</div>
                        <div class="text-sm font-medium text-navy">{{ $tutor->alamat ?? '-' }}</div>
                    </div>
                </div>

                <div class="card p-6 space-y-4">
                    <h3 class="text-sm font-semibold text-navy uppercase tracking-wider mb-2 border-b pb-2">Profesional</h3>
                    
                    <div>
                        <div class="text-xs text-muted">Bidang Keahlian</div>
                        <div class="text-sm font-medium text-navy">{{ $tutor->bidang_keahlian ?? '-' }}</div>
                    </div>
                    <div>
                        <div class="text-xs text-muted">Pendidikan Terakhir</div>
                        <div class="text-sm font-medium text-navy">{{ $tutor->pendidikan_terakhir ?? '-' }}</div>
                    </div>
                    <div>
                        <div class="text-xs text-muted">Tanggal Mulai Mengajar</div>
                        <div class="text-sm font-medium text-navy">{{ $tutor->tanggal_mulai_mengahar ? $tutor->tanggal_mulai_mengahar->format('d M Y') : '-' }}</div>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="md:col-span-2 space-y-6">
                <div class="card p-6">
                    <h3 class="text-lg font-semibold text-navy border-b pb-2 mb-4">Catatan</h3>
                    <p class="text-sm text-navy">{{ $tutor->catatan ?? '-' }}</p>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
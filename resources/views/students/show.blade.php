<x-app-layout>
    <x-slot name="title">Detail Peserta Didik</x-slot>

    <div class="max-w-5xl mx-auto space-y-6">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <x-breadcrumb :items="[
                ['label' => 'Peserta Didik', 'url' => route('peserta-didik.index')],
                ['label' => 'Detail Data']
            ]" />
            
            <div class="flex items-center gap-3">
                <a href="{{ route('peserta-didik.index') }}" class="btn-secondary">
                    <x-icon name="chevron-left" class="w-4 h-4" />
                    Kembali
                </a>
                @can('students.update')
                <a href="{{ route('peserta-didik.edit', $peserta_didik->id) }}" class="btn-primary">
                    <x-icon name="pencil" class="w-4 h-4" />
                    Edit Data
                </a>
                @endcan
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Sidebar Info -->
            <div class="md:col-span-1 space-y-6">
                <div class="card p-6 text-center">
                    <div class="w-32 h-32 mx-auto rounded-2xl overflow-hidden bg-primary-soft border border-primary/20 mb-4 flex items-center justify-center">
                        @if($peserta_didik->foto)
                            <img src="{{ asset('storage/' . $peserta_didik->foto) }}" alt="{{ $peserta_didik->nama_lengkap }}" class="w-full h-full object-cover">
                        @else
                            <x-icon name="users" class="w-12 h-12 text-primary-dark opacity-50" />
                        @endif
                    </div>
                    <h2 class="text-xl font-bold text-navy mb-1">{{ $peserta_didik->nama_lengkap }}</h2>
                    <p class="text-muted text-sm mb-3">NIS: {{ $peserta_didik->nis }}</p>
                    <x-badge variant="{{ $peserta_didik->status == 'aktif' ? 'success' : 'warning' }}">
                        {{ ucfirst($peserta_didik->status) }}
                    </x-badge>
                </div>

                <div class="card p-6 space-y-4">
                    <h3 class="text-sm font-semibold text-navy uppercase tracking-wider mb-2 border-b pb-2">Status Akademik</h3>
                    
                    <div>
                        <div class="text-xs text-muted">Program</div>
                        <div class="font-medium text-navy">{{ $peserta_didik->program->nama ?? '-' }}</div>
                    </div>
                    <div>
                        <div class="text-xs text-muted">Tahun Masuk</div>
                        <div class="font-medium text-navy">{{ $peserta_didik->academicYear->nama ?? '-' }}</div>
                    </div>
                    <div>
                        <div class="text-xs text-muted">Rombel / Kelas Aktif</div>
                        <div class="font-medium text-navy">{{ $peserta_didik->classroom->nama_rombel ?? 'Belum ada rombel' }}</div>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="md:col-span-2 space-y-6">
                <div class="card p-6">
                    <h3 class="text-lg font-semibold text-navy border-b pb-2 mb-4">Informasi Pribadi</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-y-4 gap-x-6">
                        <div>
                            <div class="text-xs text-muted">NIK</div>
                            <div class="text-sm font-medium text-navy">{{ $peserta_didik->nik }}</div>
                        </div>
                        <div>
                            <div class="text-xs text-muted">NISN</div>
                            <div class="text-sm font-medium text-navy">{{ $peserta_didik->nisn ?? '-' }}</div>
                        </div>
                        <div>
                            <div class="text-xs text-muted">Nama Panggilan</div>
                            <div class="text-sm font-medium text-navy">{{ $peserta_didik->nama_panggilan ?? '-' }}</div>
                        </div>
                        <div>
                            <div class="text-xs text-muted">Jenis Kelamin</div>
                            <div class="text-sm font-medium text-navy">{{ $peserta_didik->jenis_kelamin }}</div>
                        </div>
                        <div>
                            <div class="text-xs text-muted">Tempat, Tanggal Lahir</div>
                            <div class="text-sm font-medium text-navy">
                                {{ $peserta_didik->tempat_lahir ?? '-' }}, 
                                {{ $peserta_didik->tanggal_lahir ? $peserta_didik->tanggal_lahir->format('d M Y') : '-' }}
                            </div>
                        </div>
                        <div>
                            <div class="text-xs text-muted">Agama</div>
                            <div class="text-sm font-medium text-navy">{{ $peserta_didik->agama ?? '-' }}</div>
                        </div>
                    </div>
                </div>

                <div class="card p-6">
                    <h3 class="text-lg font-semibold text-navy border-b pb-2 mb-4">Kontak & Alamat</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-y-4 gap-x-6">
                        <div class="sm:col-span-2">
                            <div class="text-xs text-muted">Alamat Lengkap</div>
                            <div class="text-sm font-medium text-navy">{{ $peserta_didik->alamat }}</div>
                        </div>
                        <div>
                            <div class="text-xs text-muted">RT / RW</div>
                            <div class="text-sm font-medium text-navy">{{ $peserta_didik->rt ?? '-' }} / {{ $peserta_didik->rw ?? '-' }}</div>
                        </div>
                        <div>
                            <div class="text-xs text-muted">Kelurahan / Desa</div>
                            <div class="text-sm font-medium text-navy">{{ $peserta_didik->kelurahan ?? '-' }}</div>
                        </div>
                        <div>
                            <div class="text-xs text-muted">Kecamatan</div>
                            <div class="text-sm font-medium text-navy">{{ $peserta_didik->kecamatan ?? '-' }}</div>
                        </div>
                        <div>
                            <div class="text-xs text-muted">Kabupaten / Kota</div>
                            <div class="text-sm font-medium text-navy">{{ $peserta_didik->kabupaten ?? '-' }}</div>
                        </div>
                        <div>
                            <div class="text-xs text-muted">No. HP</div>
                            <div class="text-sm font-medium text-navy">{{ $peserta_didik->no_hp ?? '-' }}</div>
                        </div>
                        <div>
                            <div class="text-xs text-muted">Email</div>
                            <div class="text-sm font-medium text-navy">{{ $peserta_didik->email ?? '-' }}</div>
                        </div>
                    </div>
                </div>

                <div class="card p-6">
                    <h3 class="text-lg font-semibold text-navy border-b pb-2 mb-4">Data Keluarga</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-y-4 gap-x-6">
                        <div>
                            <div class="text-xs text-muted">Nama Ayah</div>
                            <div class="text-sm font-medium text-navy">{{ $peserta_didik->nama_ayah ?? '-' }}</div>
                        </div>
                        <div>
                            <div class="text-xs text-muted">Nama Ibu</div>
                            <div class="text-sm font-medium text-navy">{{ $peserta_didik->nama_ibu ?? '-' }}</div>
                        </div>
                        <div>
                            <div class="text-xs text-muted">Nama Wali</div>
                            <div class="text-sm font-medium text-navy">{{ $peserta_didik->nama_wali ?? '-' }}</div>
                        </div>
                        <div>
                            <div class="text-xs text-muted">No. HP Wali</div>
                            <div class="text-sm font-medium text-navy">{{ $peserta_didik->no_hp_wali ?? '-' }}</div>
                        </div>
                        <div class="sm:col-span-2">
                            <div class="text-xs text-muted">Alamat Wali</div>
                            <div class="text-sm font-medium text-navy">{{ $peserta_didik->alamat_wali ?? '-' }}</div>
                        </div>
                    </div>
                </div>

                @if($peserta_didik->catatan)
                <div class="card p-6">
                    <h3 class="text-lg font-semibold text-navy border-b pb-2 mb-4">Catatan Tambahan</h3>
                    <p class="text-sm text-navy">{{ $peserta_didik->catatan }}</p>
                </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>

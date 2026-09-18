<x-app-layout>
    <x-slot name="title">Detail Pendaftaran PPDB</x-slot>

    <div class="max-w-4xl mx-auto space-y-6">
        <div class="flex items-center justify-between">
            <h2 class="text-2xl font-bold text-navy">Data Pendaftaran: {{ $ppdb->nomor_pendaftaran }}</h2>
            <div class="flex gap-2">
                <a href="{{ route('ppdb.bukti', $ppdb) }}" target="_blank" class="btn-secondary">
                    <x-icon name="printer" class="w-4 h-4 mr-2" />
                    Cetak Bukti
                </a>
                @can('admissions.manage')
                    <a href="{{ route('ppdb.index') }}" class="btn-secondary">Kembali</a>
                @else
                    <a href="{{ route('dashboard') }}" class="btn-secondary">Ke Dashboard</a>
                @endcan
            </div>
        </div>

        <div class="card p-6 border-t-4 border-t-primary">
            <div class="flex justify-between items-start mb-6 pb-6 border-b border-border">
                <div>
                    <h3 class="text-xl font-bold text-navy">{{ $ppdb->nama_lengkap }}</h3>
                    <p class="text-muted mt-1">NIK: {{ $ppdb->nik }}</p>
                </div>
                <div class="text-right">
                    <div class="mb-2">
                        <span class="inline-flex px-3 py-1 rounded-full text-sm font-semibold {{ $ppdb->status_badge_class }}">
                            {{ $ppdb->status_label }}
                        </span>
                    </div>
                    <p class="text-sm text-muted">Mendaftar pada: {{ $ppdb->created_at->format('d M Y, H:i') }}</p>
                </div>
            </div>

            <div class="grid md:grid-cols-2 gap-x-12 gap-y-8">
                <!-- Program -->
                <div>
                    <h4 class="text-sm font-bold text-primary uppercase tracking-wider mb-3">Program Pilihan</h4>
                    <dl class="space-y-3 text-sm">
                        <div class="flex justify-between">
                            <dt class="text-muted">Program:</dt>
                            <dd class="font-medium text-navy">{{ $ppdb->program->nama }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-muted">Tahun Ajaran:</dt>
                            <dd class="font-medium text-navy">{{ $ppdb->academicYear->nama }}</dd>
                        </div>
                    </dl>
                </div>

                <!-- Info Pribadi -->
                <div>
                    <h4 class="text-sm font-bold text-primary uppercase tracking-wider mb-3">Informasi Pribadi</h4>
                    <dl class="space-y-3 text-sm">
                        <div class="flex justify-between">
                            <dt class="text-muted">Jenis Kelamin:</dt>
                            <dd class="font-medium text-navy">{{ $ppdb->jenis_kelamin == 'L' ? 'Laki-laki' : 'Perempuan' }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-muted">TTL:</dt>
                            <dd class="font-medium text-navy">{{ $ppdb->tempat_lahir }}, {{ $ppdb->tanggal_lahir->format('d M Y') }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-muted">Agama:</dt>
                            <dd class="font-medium text-navy">{{ $ppdb->agama }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-muted">No. HP:</dt>
                            <dd class="font-medium text-navy">{{ $ppdb->no_hp }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-muted">Email:</dt>
                            <dd class="font-medium text-navy">{{ $ppdb->email }}</dd>
                        </div>
                    </dl>
                </div>

                <!-- Orang Tua -->
                <div>
                    <h4 class="text-sm font-bold text-primary uppercase tracking-wider mb-3">Data Orang Tua</h4>
                    <dl class="space-y-3 text-sm">
                        <div class="flex justify-between">
                            <dt class="text-muted">Nama Ayah:</dt>
                            <dd class="font-medium text-navy">{{ $ppdb->nama_ayah }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-muted">Nama Ibu:</dt>
                            <dd class="font-medium text-navy">{{ $ppdb->nama_ibu }}</dd>
                        </div>
                        @if($ppdb->nama_wali)
                        <div class="flex justify-between">
                            <dt class="text-muted">Nama Wali:</dt>
                            <dd class="font-medium text-navy">{{ $ppdb->nama_wali }}</dd>
                        </div>
                        @endif
                    </dl>
                </div>

                <!-- Alamat -->
                <div>
                    <h4 class="text-sm font-bold text-primary uppercase tracking-wider mb-3">Alamat Domisili</h4>
                    <p class="text-sm text-navy leading-relaxed">
                        {{ $ppdb->alamat }}<br>
                        @if($ppdb->rt || $ppdb->rw) RT {{ $ppdb->rt }}/RW {{ $ppdb->rw }}, @endif
                        {{ $ppdb->kelurahan }}, Kec. {{ $ppdb->kecamatan }}<br>
                        {{ $ppdb->kabupaten }}, {{ $ppdb->provinsi }}
                    </p>
                </div>
            </div>

            <!-- Dokumen -->
            <div class="mt-8 border-t border-border pt-6">
                <h4 class="text-sm font-bold text-primary uppercase tracking-wider mb-4">Dokumen Persyaratan</h4>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    @foreach([
                        'dok_kk' => 'Kartu Keluarga',
                        'dok_akta_kelahiran' => 'Akta Kelahiran',
                        'dok_ijasah' => 'Ijazah',
                        'dok_foto' => 'Pas Foto'
                    ] as $field => $label)
                        <div class="p-3 border border-border rounded-lg text-center">
                            <div class="text-sm font-medium text-navy mb-2">{{ $label }}</div>
                            @if($ppdb->$field)
                                <a href="{{ Storage::url($ppdb->$field) }}" target="_blank" class="inline-flex items-center text-xs text-primary hover:underline">
                                    <x-icon name="paperclip" class="w-3 h-3 mr-1" /> Lihat Dokumen
                                </a>
                            @else
                                <span class="text-xs text-muted">Belum Diupload</span>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Verification Action (Admin Only) -->
            @can('admissions.manage')
            <div class="mt-8 border-t border-border pt-6">
                <h4 class="text-sm font-bold text-primary uppercase tracking-wider mb-4">Verifikasi Pendaftaran</h4>
                <form action="{{ route('ppdb.verify', $ppdb) }}" method="POST" class="bg-surface rounded-xl p-4 border border-border">
                    @csrf
                    <div class="space-y-4">
                        <div>
                            <x-input-label for="status" value="Ubah Status" />
                            <x-select id="status" name="status" class="w-full md:w-1/2 mt-1" required>
                                @foreach(\App\Models\Ppdb::statusOptions() as $val => $label)
                                    @if($val !== \App\Models\Ppdb::STATUS_PENDING || $ppdb->status === \App\Models\Ppdb::STATUS_PENDING)
                                        <option value="{{ $val }}" {{ $ppdb->status === $val ? 'selected' : '' }}>{{ $label }}</option>
                                    @endif
                                @endforeach
                            </x-select>
                        </div>
                        <div>
                            <x-input-label for="catatan_verifikasi" value="Catatan Verifikasi (Opsional)" />
                            <textarea id="catatan_verifikasi" name="catatan_verifikasi" rows="2" class="w-full mt-1 rounded-xl border-border focus:border-primary focus:ring focus:ring-primary/20">{{ old('catatan_verifikasi', $ppdb->catatan_verifikasi) }}</textarea>
                        </div>
                        <div class="flex justify-end">
                            <x-primary-button type="submit">Simpan Hasil Verifikasi</x-primary-button>
                        </div>
                    </div>
                </form>
            </div>
            @else
                @if($ppdb->catatan_verifikasi)
                <div class="mt-8 bg-yellow-50 border border-yellow-200 rounded-xl p-4">
                    <h4 class="text-sm font-bold text-yellow-800 mb-2">Catatan Panitia:</h4>
                    <p class="text-sm text-yellow-900">{{ $ppdb->catatan_verifikasi }}</p>
                </div>
                @endif
            @endcan
        </div>
    </div>
</x-app-layout>
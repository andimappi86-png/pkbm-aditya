<x-app-layout>
    <x-slot name="title">Detail Dokumen</x-slot>

    <div class="max-w-4xl mx-auto space-y-6">
        <div class="flex items-center justify-between">
            <h2 class="text-2xl font-bold text-navy">Detail Dokumen</h2>
            <div class="flex gap-2">
                @can('documents.download')
                <a href="{{ route('documents.download', $document) }}" class="btn-primary">
                    <x-icon name="download" class="w-4 h-4 mr-2" />
                    Download
                </a>
                @endcan
                <a href="{{ route('documents.index') }}" class="btn-secondary">Kembali</a>
            </div>
        </div>

        <div class="card p-6 border-t-4 border-t-primary">
            <div class="flex justify-between items-start mb-6 pb-6 border-b border-border">
                <div>
                    <h3 class="text-xl font-bold text-navy">{{ $document->nama_file }}</h3>
                    <p class="text-muted mt-1">Jenis: {{ $document->jenis_label }}</p>
                </div>
                <div class="text-right">
                    <div class="mb-2">
                        <span class="inline-flex px-3 py-1 rounded-full text-sm font-semibold {{ $document->status_badge_class }}">
                            {{ $document->status_label }}
                        </span>
                    </div>
                    <p class="text-sm text-muted">Diunggah: {{ $document->uploaded_at->format('d M Y, H:i') }}</p>
                </div>
            </div>

            <div class="grid md:grid-cols-2 gap-x-12 gap-y-8 mb-8">
                <div>
                    <h4 class="text-sm font-bold text-primary uppercase tracking-wider mb-3">Informasi Pemilik</h4>
                    <dl class="space-y-3 text-sm">
                        @if($document->student)
                            <div class="flex justify-between">
                                <dt class="text-muted">Pemilik:</dt>
                                <dd class="font-medium text-navy">{{ $document->student->nama_lengkap }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-muted">NIS:</dt>
                                <dd class="font-medium text-navy">{{ $document->student->nis }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-muted">NISN:</dt>
                                <dd class="font-medium text-navy">{{ $document->student->nisn ?? '-' }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-muted">Program:</dt>
                                <dd class="font-medium text-navy">{{ $document->student->program->nama ?? '-' }}</dd>
                            </div>
                        @elseif($document->ppdb)
                            <div class="flex justify-between">
                                <dt class="text-muted">Pemilik:</dt>
                                <dd class="font-medium text-navy">{{ $document->ppdb->nama_lengkap }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-muted">No. Pendaftaran:</dt>
                                <dd class="font-medium text-navy">{{ $document->ppdb->nomor_pendaftaran }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-muted">Program:</dt>
                                <dd class="font-medium text-navy">{{ $document->ppdb->program->nama }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-muted">Tahun Ajaran:</dt>
                                <dd class="font-medium text-navy">{{ $document->ppdb->academicYear->nama }}</dd>
                            </div>
                        @else
                            <div class="flex justify-between">
                                <dt class="text-muted">Tipe:</dt>
                                <dd class="font-medium text-navy">Dokumen Manual / Umum</dd>
                            </div>
                            @if($document->catatan)
                            <div class="flex justify-between">
                                <dt class="text-muted">Keterangan:</dt>
                                <dd class="font-medium text-navy">{{ $document->catatan }}</dd>
                            </div>
                            @endif
                            <div class="flex justify-between">
                                <dt class="text-muted">Penautan:</dt>
                                <dd class="text-muted">Tidak dikaitkan ke siswa</dd>
                            </div>
                        @endif
                    </dl>
                </div>

                <div>
                    <h4 class="text-sm font-bold text-primary uppercase tracking-wider mb-3">Detail File</h4>
                    <dl class="space-y-3 text-sm">
                        <div class="flex justify-between">
                            <dt class="text-muted">Jenis Dokumen:</dt>
                            <dd class="font-medium text-navy">{{ $document->jenis_label }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-muted">Status Verifikasi:</dt>
                            <dd class="font-medium text-navy">
                                <span class="inline-flex px-2 py-1 rounded-full text-xs font-medium {{ $document->status_badge_class }}">
                                    {{ $document->status_label }}
                                </span>
                            </dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-muted">Tanggal Upload:</dt>
                            <dd class="font-medium text-navy">{{ $document->uploaded_at->format('d M Y, H:i') }}</dd>
                        </div>
                    </dl>
                </div>
            </div>

            @if($document->catatan)
            <div class="mb-6 p-4 bg-yellow-50 border border-yellow-200 rounded-xl">
                <h4 class="text-sm font-bold text-yellow-800 mb-1">Catatan Verifikasi:</h4>
                <p class="text-sm text-yellow-900">{{ $document->catatan }}</p>
            </div>
            @endif

            <!-- Verification Action (Admin Only) -->
            @can('documents.verify')
            <div class="border-t border-border pt-6">
                <h4 class="text-sm font-bold text-primary uppercase tracking-wider mb-4">Verifikasi Dokumen</h4>
                <form action="{{ route('documents.verify', $document) }}" method="POST" class="bg-surface rounded-xl p-4 border border-border">
                    @csrf
                    <div class="space-y-4">
                        <div>
                            <x-input-label for="status_verifikasi" value="Status Verifikasi" />
                            <x-select id="status_verifikasi" name="status_verifikasi" class="w-full md:w-1/2 mt-1" required>
                                <option value="diverifikasi" {{ $document->status_verifikasi === 'diverifikasi' ? 'selected' : '' }}>Diverifikasi</option>
                                <option value="ditolak" {{ $document->status_verifikasi === 'ditolak' ? 'selected' : '' }}>Ditolak</option>
                            </x-select>
                        </div>
                        <div>
                            <x-input-label for="catatan" value="Catatan (Opsional)" />
                            <textarea id="catatan" name="catatan" rows="2" class="w-full mt-1 rounded-xl border-border focus:border-primary focus:ring focus:ring-primary/20">{{ old('catatan', $document->catatan) }}</textarea>
                        </div>
                        <div class="flex justify-end">
                            <x-primary-button type="submit">Simpan Verifikasi</x-primary-button>
                        </div>
                    </div>
                </form>
            </div>
            @endcan
        </div>
    </div>
</x-app-layout>
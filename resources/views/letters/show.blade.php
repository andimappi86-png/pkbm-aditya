<x-app-layout>
    <x-slot name="title">Detail Surat</x-slot>

    <div class="max-w-4xl mx-auto space-y-6">
        <div class="flex items-center justify-between">
            <h2 class="text-2xl font-bold text-navy">Detail Surat</h2>
            <div class="flex gap-2">
                @can('letters.print')
                <a href="{{ route('letters.print', $letter) }}" target="_blank" class="btn-primary">
                    <x-icon name="printer" class="w-4 h-4 mr-2" />
                    Cetak Surat
                </a>
                @endcan
                <a href="{{ route('letters.index') }}" class="btn-secondary">Kembali</a>
            </div>
        </div>

        <div class="card p-8 border-t-4 border-t-primary">
            <div class="text-center mb-8 pb-6 border-b border-border">
                <img src="{{ asset('images/logo-pkbm-aditya.png') }}" alt="Logo PKBM Aditya" class="h-16 mx-auto mb-2">
                <h3 class="text-xl font-bold text-navy uppercase">{{ $letter->jenis_label }}</h3>
                <p class="text-sm font-mono text-muted">Nomor: {{ $letter->nomor_surat }}</p>
            </div>

            <div class="space-y-6 text-sm text-navy leading-relaxed">
                @if($letter->student)
                    <p>Yang bertanda tangan di bawah ini menerangkan bahwa:</p>
                    <table class="w-full mb-4">
                        <tr>
                            <td class="w-1/3 py-1 text-muted">Nama Lengkap</td>
                            <td class="font-medium">: {{ $letter->student->nama_lengkap }}</td>
                        </tr>
                        <tr>
                            <td class="py-1 text-muted">NIS / NISN</td>
                            <td class="font-medium">: {{ $letter->student->nis }} / {{ $letter->student->nisn ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="py-1 text-muted">Program</td>
                            <td class="font-medium">: {{ $letter->student->program->nama ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="py-1 text-muted">Tempat, Tanggal Lahir</td>
                            <td class="font-medium">: {{ $letter->student->tempat_lahir }}, {{ $letter->student->tanggal_lahir->format('d M Y') }}</td>
                        </tr>
                    </table>
                @endif

                <div class="bg-surface p-4 rounded-xl border border-border">
                    <p class="whitespace-pre-line">{{ $letter->isi }}</p>
                </div>

                @if($letter->tujuan)
                    <p>Demikian surat ini dibuat untuk dipergunakan sebagai: <strong>{{ $letter->tujuan }}</strong>.</p>
                @endif

                <div class="flex justify-end mt-12">
                    <div class="text-center">
                        <p class="mb-16">Mamuju, {{ $letter->tanggal->format('d F Y') }}</p>
                        <p class="font-bold border-b border-navy pb-1">{{ $letter->penandatangan_nama }}</p>
                        @if($letter->penandatangan_id && $letter->penandatangan->primaryRole() == 'kepala-pkbm')
                            <p class="text-xs text-muted mt-1">Kepala PKBM Aditya</p>
                        @else
                            <p class="text-xs text-muted mt-1">{{ $letter->penandatangan_id ? ucfirst($letter->penandatangan->primaryRole()) : 'Admin' }}</p>
                        @endif
                    </div>
                </div>
            </div>
            
            @can('letters.manage')
            <div class="mt-8 pt-6 border-t border-border flex justify-end gap-2">
                <a href="{{ route('letters.edit', $letter) }}" class="btn-secondary">
                    <x-icon name="pencil" class="w-4 h-4 mr-2" /> Edit Surat
                </a>
                <form action="{{ route('letters.destroy', $letter) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus surat ini?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="p-2.5 text-accent hover:bg-accent-soft rounded-lg transition-colors inline-flex items-center text-sm font-medium">
                        <x-icon name="trash" class="w-4 h-4 mr-2" /> Hapus
                    </button>
                </form>
            </div>
            @endcan
        </div>
    </div>
</x-app-layout>
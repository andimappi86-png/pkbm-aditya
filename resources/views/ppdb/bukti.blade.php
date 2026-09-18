<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Bukti Pendaftaran PPDB - {{ $ppdb->nomor_pendaftaran }}</title>
    @vite(['resources/css/app.css'])
    <style>
        @media print {
            .no-print { display: none !important; }
            body { background: white; }
            .card { box-shadow: none; border: 1px solid #e5e7eb; }
        }
    </style>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center p-8">
    <div class="w-full max-w-2xl bg-white shadow-lg rounded-xl p-8">
        <!-- Header -->
        <div class="text-center mb-8">
            <img src="{{ asset('images/logo-pkbm-aditya.png') }}" alt="Logo PKBM Aditya" class="h-20 mx-auto mb-4">
            <h1 class="text-2xl font-bold text-[#172B3A]">BUKTI PENDAFTARAN PPDB</h1>
            <p class="text-[#64748B] mt-1">Pusat Kegiatan Belajar Masyarakat Aditya</p>
            <p class="text-sm text-[#64748B]">Kabupaten Mamuju, Sulawesi Barat</p>
        </div>

        <!-- Nomor Pendaftaran -->
        <div class="bg-[#E8F5F4] border border-[#D9E2E5] rounded-xl p-4 mb-6 text-center">
            <p class="text-xs text-[#64748B] uppercase tracking-wider">Nomor Pendaftaran</p>
            <p class="text-3xl font-bold text-[#0F5F63] font-mono">{{ $ppdb->nomor_pendaftaran }}</p>
            <p class="text-sm text-[#64748B] mt-1">Tanggal Daftar: {{ $ppdb->created_at->format('d F Y, H:i') }}</p>
        </div>

        <!-- Status -->
        <div class="mb-6 p-4 rounded-lg {{ $ppdb->status === 'diterima' ? 'bg-green-50 border-green-200' : ($ppdb->status === 'ditolak' ? 'bg-red-50 border-red-200' : 'bg-yellow-50 border-yellow-200') }} border">
            <p class="text-sm font-bold {{ $ppdb->status === 'diterima' ? 'text-green-800' : ($ppdb->status === 'ditolak' ? 'text-red-800' : 'text-yellow-800') }}">
                Status: {{ $ppdb->status_label }}
            </p>
            @if($ppdb->catatan_verifikasi)
                <p class="text-sm text-gray-700 mt-1">{{ $ppdb->catatan_verifikasi }}</p>
            @endif
        </div>

        <!-- Data Pribadi -->
        <div class="mb-6">
            <h3 class="text-lg font-bold text-[#172B3A] border-b border-[#D9E2E5] pb-2 mb-4">Data Calon Peserta Didik</h3>
            <table class="w-full text-sm">
                <tr>
                    <td class="py-2 text-[#64748B] w-1/3">Nama Lengkap</td>
                    <td class="py-2 font-semibold text-[#172B3A]">: {{ $ppdb->nama_lengkap }}</td>
                </tr>
                <tr>
                    <td class="py-2 text-[#64748B]">Nama Panggilan</td>
                    <td class="py-2 font-semibold text-[#172B3A]">: {{ $ppdb->nama_panggilan ?? '-' }}</td>
                </tr>
                <tr>
                    <td class="py-2 text-[#64748B]">NIK</td>
                    <td class="py-2 font-semibold text-[#172B3A]">: {{ $ppdb->nik }}</td>
                </tr>
                <tr>
                    <td class="py-2 text-[#64748B]">NISN</td>
                    <td class="py-2 font-semibold text-[#172B3A]">: {{ $ppdb->nisn ?? '-' }}</td>
                </tr>
                <tr>
                    <td class="py-2 text-[#64748B]">Jenis Kelamin</td>
                    <td class="py-2 font-semibold text-[#172B3A]">: {{ $ppdb->jenis_kelamin == 'L' ? 'Laki-laki' : 'Perempuan' }}</td>
                </tr>
                <tr>
                    <td class="py-2 text-[#64748B]">Tempat / Tanggal Lahir</td>
                    <td class="py-2 font-semibold text-[#172B3A]">: {{ $ppdb->tempat_lahir }}, {{ $ppdb->tanggal_lahir->format('d F Y') }}</td>
                </tr>
                <tr>
                    <td class="py-2 text-[#64748B]">Agama</td>
                    <td class="py-2 font-semibold text-[#172B3A]">: {{ $ppdb->agama }}</td>
                </tr>
            </table>
        </div>

        <!-- Program -->
        <div class="mb-6">
            <h3 class="text-lg font-bold text-[#172B3A] border-b border-[#D9E2E5] pb-2 mb-4">Program Pilihan</h3>
            <table class="w-full text-sm">
                <tr>
                    <td class="py-2 text-[#64748B] w-1/3">Program</td>
                    <td class="py-2 font-semibold text-[#172B3A]">: {{ $ppdb->program->nama }}</td>
                </tr>
                <tr>
                    <td class="py-2 text-[#64748B]">Tahun Ajaran</td>
                    <td class="py-2 font-semibold text-[#172B3A]">: {{ $ppdb->academicYear->nama }}</td>
                </tr>
            </table>
        </div>

        <!-- Alamat -->
        <div class="mb-6">
            <h3 class="text-lg font-bold text-[#172B3A] border-b border-[#D9E2E5] pb-2 mb-4">Alamat Domisili</h3>
            <p class="text-sm text-[#172B3A] leading-relaxed">
                {{ $ppdb->alamat }}<br>
                @if($ppdb->rt || $ppdb->rw) RT {{ $ppdb->rt }}/RW {{ $ppdb->rw }}, @endif
                {{ $ppdb->kelurahan }}, Kec. {{ $ppdb->kecamatan }}<br>
                {{ $ppdb->kabupaten }}, {{ $ppdb->provinsi }}
            </p>
        </div>

        <!-- Orang Tua -->
        <div class="mb-6">
            <h3 class="text-lg font-bold text-[#172B3A] border-b border-[#D9E2E5] pb-2 mb-4">Data Orang Tua</h3>
            <table class="w-full text-sm">
                <tr>
                    <td class="py-2 text-[#64748B] w-1/3">Nama Ayah</td>
                    <td class="py-2 font-semibold text-[#172B3A]">: {{ $ppdb->nama_ayah }}</td>
                </tr>
                <tr>
                    <td class="py-2 text-[#64748B]">Pekerjaan Ayah</td>
                    <td class="py-2 font-semibold text-[#172B3A]">: {{ $ppdb->pekerjaan_ayah ?? '-' }}</td>
                </tr>
                <tr>
                    <td class="py-2 text-[#64748B]">Nama Ibu</td>
                    <td class="py-2 font-semibold text-[#172B3A]">: {{ $ppdb->nama_ibu }}</td>
                </tr>
                <tr>
                    <td class="py-2 text-[#64748B]">Pekerjaan Ibu</td>
                    <td class="py-2 font-semibold text-[#172B3A]">: {{ $ppdb->pekerjaan_ibu ?? '-' }}</td>
                </tr>
                @if($ppdb->nama_wali)
                <tr>
                    <td class="py-2 text-[#64748B]">Nama Wali</td>
                    <td class="py-2 font-semibold text-[#172B3A]">: {{ $ppdb->nama_wali }}</td>
                </tr>
                @endif
            </table>
        </div>

        <!-- Footer -->
        <div class="border-t border-[#D9E2E5] pt-6">
            <div class="grid grid-cols-2 gap-8">
                <div class="text-center">
                    <p class="text-sm text-[#64748B]">Calon Peserta Didik</p>
                    <div class="h-16"></div>
                    <p class="text-sm font-semibold text-[#172B3A]">{{ $ppdb->nama_lengkap }}</p>
                </div>
                <div class="text-center">
                    <p class="text-sm text-[#64748B]">Kepala PKBM Aditya</p>
                    <div class="h-16"></div>
                    <p class="text-sm font-semibold text-[#172B3A]">_____________________</p>
                </div>
            </div>
        </div>

        <div class="text-center mt-8 text-xs text-[#64748B]">
            <p>Dokumen ini dicetak secara otomatis oleh Sistem Informasi PKBM Aditya.</p>
            <p>Tanggal cetak: {{ now()->format('d F Y, H:i') }}</p>
        </div>

        <!-- Print Button -->
        <div class="text-center mt-6 no-print">
            <button onclick="window.print()" class="btn-primary px-8 py-3">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                </svg>
                Cetak / Simpan PDF
            </button>
        </div>
    </div>
</body>
</html>
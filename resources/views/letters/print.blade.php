<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $letter->jenis_label }} - {{ $letter->nomor_surat }}</title>
    @vite(['resources/css/app.css'])
    <style>
        @media print {
            .no-print { display: none !important; }
            body { background: white; margin: 0; padding: 0; }
            .print-container { box-shadow: none; border: none; padding: 0; max-width: 100%; }
        }
        @page {
            size: A4;
            margin: 2cm;
        }
    </style>
</head>
<body class="bg-gray-100 min-h-screen p-8 text-black">
    <div class="print-container w-full max-w-[21cm] min-h-[29.7cm] bg-white shadow-lg mx-auto p-12">
        <!-- Kop Surat -->
        <div class="flex items-center border-b-[3px] border-black pb-4 mb-8">
            <img src="{{ asset('images/logo-pkbm-aditya.png') }}" alt="Logo PKBM Aditya" class="h-24 w-24 object-contain">
            <div class="text-center flex-1 pr-12">
                <h1 class="text-xl font-bold uppercase tracking-wider">Pemerintah Kabupaten Mamuju</h1>
                <h2 class="text-2xl font-extrabold uppercase mt-1">Pusat Kegiatan Belajar Masyarakat (PKBM) Aditya</h2>
                <p class="text-sm mt-1">Jl. Poros Mamuju, Kabupaten Mamuju, Provinsi Sulawesi Barat</p>
                <p class="text-xs">Email: info@pkbmaditya.com | Telp: (0426) XXXXXX</p>
            </div>
        </div>

        <!-- Judul Surat -->
        <div class="text-center mb-8">
            <h3 class="text-lg font-bold uppercase underline">{{ $letter->jenis_label }}</h3>
            <p class="text-sm">Nomor: {{ $letter->nomor_surat }}</p>
        </div>

        <!-- Isi Surat -->
        <div class="space-y-4 text-justify leading-relaxed">
            @if($letter->student)
                <p>Yang bertanda tangan di bawah ini Kepala Pusat Kegiatan Belajar Masyarakat (PKBM) Aditya Kabupaten Mamuju, menerangkan bahwa:</p>
                
                <table class="w-full ml-8 mb-4">
                    <tr>
                        <td class="w-1/3 py-1">Nama Lengkap</td>
                        <td class="w-[2%] py-1">:</td>
                        <td class="py-1 font-bold">{{ $letter->student->nama_lengkap }}</td>
                    </tr>
                    <tr>
                        <td class="py-1">NIS / NISN</td>
                        <td class="py-1">:</td>
                        <td class="py-1">{{ $letter->student->nis }} / {{ $letter->student->nisn ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="py-1">Tempat, Tanggal Lahir</td>
                        <td class="py-1">:</td>
                        <td class="py-1">{{ $letter->student->tempat_lahir }}, {{ $letter->student->tanggal_lahir->format('d F Y') }}</td>
                    </tr>
                    <tr>
                        <td class="py-1">Program Pembelajaran</td>
                        <td class="py-1">:</td>
                        <td class="py-1">{{ $letter->student->program->nama ?? '-' }}</td>
                    </tr>
                </table>
            @endif

            <p class="whitespace-pre-line">{{ $letter->isi }}</p>

            @if($letter->tujuan)
                <p>Demikian surat keterangan ini dibuat dengan sesungguhnya untuk dapat dipergunakan sebagaimana mestinya, khususnya untuk <strong>{{ $letter->tujuan }}</strong>.</p>
            @else
                <p>Demikian surat keterangan ini dibuat dengan sesungguhnya untuk dapat dipergunakan sebagaimana mestinya.</p>
            @endif
        </div>

        <!-- Tanda Tangan -->
        <div class="flex justify-end mt-16">
            <div class="text-center">
                <p class="mb-20">Mamuju, {{ $letter->tanggal->format('d F Y') }}</p>
                <p class="font-bold underline">{{ $letter->penandatangan_nama }}</p>
                @if($letter->penandatangan_id && $letter->penandatangan->primaryRole() == 'kepala-pkbm')
                    <p>Kepala PKBM Aditya</p>
                @else
                    <p>{{ $letter->penandatangan_id ? ucfirst($letter->penandatangan->primaryRole()) : 'Admin' }}</p>
                @endif
            </div>
        </div>
    </div>

    <!-- Print Button -->
    <div class="text-center mt-8 pb-8 no-print">
        <button onclick="window.print()" class="bg-[#167C80] text-white px-8 py-3 rounded-lg font-semibold hover:bg-[#0F5F63] transition-colors shadow-lg inline-flex items-center">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
            </svg>
            Cetak Dokumen
        </button>
    </div>
</body>
</html>
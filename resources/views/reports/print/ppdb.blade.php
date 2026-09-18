<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan PPDB</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #000; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .text-center { text-align: center; }
        .header { margin-bottom: 30px; text-align: center; }
        @media print {
            @page { margin: 1cm; }
        }
    </style>
</head>
<body onload="window.print()">
    <div class="header">
        <h2>PKBM ADITYA KABUPATEN MAMUJU</h2>
        <h3>LAPORAN PENERIMAAN PESERTA DIDIK BARU (PPDB)</h3>
        <p>Tanggal Cetak: {{ now()->format('d/m/Y H:i') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>No. Pendaftaran</th>
                <th>Nama Lengkap</th>
                <th>NIK</th>
                <th>Program Tujuan</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($ppdbs as $index => $ppdb)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td>{{ $ppdb->nomor_pendaftaran }}</td>
                <td>{{ $ppdb->nama_lengkap }}</td>
                <td>{{ $ppdb->nik }}</td>
                <td>{{ $ppdb->program->nama ?? '-' }}</td>
                <td>{{ ucfirst($ppdb->status) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
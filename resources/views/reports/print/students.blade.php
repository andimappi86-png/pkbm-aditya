<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Peserta Didik</title>
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
        <h3>LAPORAN DATA PESERTA DIDIK</h3>
        <p>Tanggal Cetak: {{ now()->format('d/m/Y H:i') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>NIS</th>
                <th>NISN</th>
                <th>Nama Lengkap</th>
                <th>L/P</th>
                <th>Program</th>
                <th>Rombel</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($students as $index => $student)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td>{{ $student->nis }}</td>
                <td>{{ $student->nisn }}</td>
                <td>{{ $student->nama_lengkap }}</td>
                <td class="text-center">{{ $student->jenis_kelamin }}</td>
                <td>{{ $student->program->nama ?? '-' }}</td>
                <td>{{ $student->classroom->nama_rombel ?? '-' }}</td>
                <td>{{ $student->status }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
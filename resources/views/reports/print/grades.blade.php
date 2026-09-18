<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Nilai Siswa</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #000; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .text-center { text-align: center; }
        .header { margin-bottom: 30px; text-align: center; }
        .summary { margin-bottom: 20px; }
        @media print {
            @page { margin: 1cm; }
        }
    </style>
</head>
<body onload="window.print()">
    <div class="header">
        <h2>PKBM ADITYA KABUPATEN MAMUJU</h2>
        <h3>LAPORAN NILAI SISWA</h3>
        <p>Tanggal Cetak: {{ now()->format('d/m/Y H:i') }}</p>
    </div>

    <div class="summary">
        <p><strong>Total Penilaian:</strong> {{ $grades->count() }}</p>
        <p><strong>Rata-rata Nilai:</strong> {{ number_format($avg, 1) }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Nama Siswa</th>
                <th>Rombel</th>
                <th>Mata Pelajaran</th>
                <th>Tipe</th>
                <th>Nilai</th>
            </tr>
        </thead>
        <tbody>
            @foreach($grades as $index => $grade)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td>{{ $grade->student->user->name ?? '-' }}</td>
                <td>{{ $grade->student->classroom->nama_rombel ?? '-' }}</td>
                <td>{{ $grade->assignment->penugasanMengajar->subject->nama ?? '-' }}</td>
                <td>{{ $grade->type ?? 'Tugas' }}</td>
                <td class="text-center">{{ $grade->nilai }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
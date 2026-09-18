<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Rombel</title>
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
        <h3>LAPORAN DATA ROMBEL</h3>
        <p>Tanggal Cetak: {{ now()->format('d/m/Y H:i') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Nama Rombel</th>
                <th>Program</th>
                <th>Tahun Ajaran</th>
                <th>Wali Kelas</th>
                <th>Jumlah Siswa</th>
            </tr>
        </thead>
        <tbody>
            @foreach($classrooms as $index => $classroom)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td>{{ $classroom->nama_rombel }}</td>
                <td>{{ $classroom->program->nama ?? '-' }}</td>
                <td>{{ $classroom->academicYear->nama ?? '-' }}</td>
                <td>{{ $classroom->waliKelas->name ?? '-' }}</td>
                <td class="text-center">{{ $classroom->students_count }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
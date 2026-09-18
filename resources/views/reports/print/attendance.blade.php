<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Kehadiran</title>
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
        <h3>LAPORAN KEHADIRAN SISWA</h3>
        <p>Tanggal Cetak: {{ now()->format('d/m/Y H:i') }}</p>
    </div>

    <div class="summary">
        <p><strong>Total Data:</strong> {{ $total }}</p>
        <p><strong>Hadir/Izin:</strong> {{ $hadir }}</p>
        <p><strong>Alpha:</strong> {{ $alpha }}</p>
        <p><strong>Persentase Kehadiran:</strong> {{ $rate }}%</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Tanggal</th>
                <th>Siswa</th>
                <th>Rombel</th>
                <th>Mata Pelajaran</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($attendances as $index => $attendance)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td>{{ $attendance->tanggal->format('d/m/Y') }}</td>
                <td>{{ $attendance->student->user->name ?? '-' }}</td>
                <td>{{ $attendance->student->classroom->nama_rombel ?? '-' }}</td>
                <td>{{ $attendance->schedule->subject->nama ?? '-' }}</td>
                <td>{{ ucfirst($attendance->status) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
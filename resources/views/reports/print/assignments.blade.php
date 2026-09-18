<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Tugas & Kuis</title>
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
        <h3>LAPORAN TUGAS & KUIS</h3>
        <p>Tanggal Cetak: {{ now()->format('d/m/Y H:i') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Judul</th>
                <th>Tipe</th>
                <th>Mata Pelajaran</th>
                <th>Rombel</th>
                <th>Tutor</th>
                <th>Deadline</th>
                <th>Dikumpulkan</th>
            </tr>
        </thead>
        <tbody>
            @foreach($assignments as $index => $assignment)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td>{{ $assignment->judul }}</td>
                <td class="text-center">{{ $assignment->tipe ?? 'Tugas' }}</td>
                <td>{{ $assignment->penugasanMengajar->subject->nama ?? '-' }}</td>
                <td>{{ $assignment->penugasanMengajar->classroom->nama_rombel ?? '-' }}</td>
                <td>{{ $assignment->penugasanMengajar->tutor->user->name ?? '-' }}</td>
                <td>{{ $assignment->deadline ? $assignment->deadline->format('d/m/Y H:i') : '-' }}</td>
                <td class="text-center">{{ $assignment->submissions_count ?? 0 }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
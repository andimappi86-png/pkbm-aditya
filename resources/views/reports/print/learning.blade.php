<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Aktivitas Pembelajaran</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #000; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .text-center { text-align: center; }
        .header { margin-bottom: 30px; text-align: center; }
        .summary { display: flex; gap: 40px; margin-bottom: 20px; }
        @media print {
            @page { margin: 1cm; }
        }
    </style>
</head>
<body onload="window.print()">
    <div class="header">
        <h2>PKBM ADITYA KABUPATEN MAMUJU</h2>
        <h3>LAPORAN AKTIVITAS PEMBELAJARAN</h3>
        <p>Tanggal Cetak: {{ now()->format('d/m/Y H:i') }}</p>
    </div>

    <div class="summary">
        <div><strong>Materi:</strong> {{ $stats['total_materi'] }}</div>
        <div><strong>Tugas:</strong> {{ $stats['total_tugas'] }}</div>
        <div><strong>Kuis:</strong> {{ $stats['total_kuis'] }}</div>
        <div><strong>Pengumpulan:</strong> {{ $stats['total_pengumpulan'] }}</div>
        <div><strong>Kuis Diikuti:</strong> {{ $stats['total_kuis_diikuti'] }}</div>
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
            </tr>
        </thead>
        <tbody>
            @php $i = 0; @endphp
            @foreach($assignments as $assignment)
            <tr>
                <td class="text-center">{{ ++$i }}</td>
                <td>{{ $assignment->judul }}</td>
                <td>Tugas</td>
                <td>{{ $assignment->penugasanMengajar->subject->nama ?? '-' }}</td>
                <td>{{ $assignment->penugasanMengajar->classroom->nama_rombel ?? '-' }}</td>
                <td>{{ $assignment->penugasanMengajar->tutor->user->name ?? '-' }}</td>
            </tr>
            @endforeach
            @foreach($quizzes as $quiz)
            <tr>
                <td class="text-center">{{ ++$i }}</td>
                <td>{{ $quiz->judul }}</td>
                <td>Kuis</td>
                <td>{{ $quiz->penugasanMengajar->subject->nama ?? '-' }}</td>
                <td>{{ $quiz->penugasanMengajar->classroom->nama_rombel ?? '-' }}</td>
                <td>{{ $quiz->penugasanMengajar->tutor->user->name ?? '-' }}</td>
            </tr>
            @endforeach
            @foreach($materials as $material)
            <tr>
                <td class="text-center">{{ ++$i }}</td>
                <td>{{ $material->judul }}</td>
                <td>Materi</td>
                <td>{{ $material->penugasanMengajar->subject->nama ?? '-' }}</td>
                <td>{{ $material->penugasanMengajar->classroom->nama_rombel ?? '-' }}</td>
                <td>{{ $material->penugasanMengajar->tutor->user->name ?? '-' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
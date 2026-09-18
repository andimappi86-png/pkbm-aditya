<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rapor {{ $student->nama_lengkap }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Times New Roman', Times, serif;
            color: #1f2937;
            line-height: 1.5;
            padding: 20px;
        }
        @page {
            size: A4;
            margin: 1.5cm;
        }
        @media print {
            body { padding: 0; }
        }
        .rapor-document {
            max-width: 800px;
            margin: 0 auto;
        }
        .header {
            text-align: center;
            border-bottom: 3px double #1f2937;
            padding-bottom: 15px;
            margin-bottom: 25px;
        }
        .header h1 {
            font-size: 16pt;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 4px;
        }
        .header h2 {
            font-size: 14pt;
            text-transform: uppercase;
        }
        .identity {
            margin-bottom: 30px;
            font-size: 11pt;
        }
        .identity-row {
            display: flex;
            margin-bottom: 4px;
        }
        .identity-label {
            width: 200px;
            font-weight: bold;
        }
        .identity-value {
            flex: 1;
        }
        h3 {
            font-size: 12pt;
            margin-bottom: 8px;
            text-decoration: underline;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 25px;
            font-size: 11pt;
        }
        th, td {
            border: 1px solid #333;
            padding: 6px 8px;
            text-align: left;
        }
        th {
            background: #f3f4f6;
            font-weight: bold;
        }
        .text-center { text-align: center; }
        .font-bold { font-weight: bold; }
        .right { text-align: right; }
        .summary-row {
            font-weight: bold;
            background: #f9fafb;
        }
        .status-lulus { color: #047857; font-weight: bold; }
        .status-belum { color: #d97706; font-weight: bold; }
        .attendance-table {
            width: 60%;
            margin-bottom: 30px;
        }
        .signatures {
            display: flex;
            justify-content: space-between;
            margin-top: 50px;
            padding-top: 20px;
        }
        .sign-block { text-align: center; width: 45%; font-size: 11pt; }
        .sign-space { height: 70px; }
        .sign-name { font-weight: bold; text-decoration: underline; }
        @media print {
            .no-print { display: none; }
        }
    </style>
</head>
<body>
    <div class="rapor-document">
        <div class="header">
            <h1>Laporan Hasil Belajar Peserta Didik</h1>
            <h2>PKBM Aditya Kabupaten Mamuju</h2>
        </div>

        <div class="identity">
            <div class="identity-row"><span class="identity-label">Nama Peserta Didik</span>: <span class="identity-value font-bold">{{ $student->nama_lengkap }}</span></div>
            <div class="identity-row"><span class="identity-label">Nomor Induk / NISN</span>: <span class="identity-value">{{ $student->nomor_induk }}</span></div>
            <div class="identity-row"><span class="identity-label">Tempat, Tanggal Lahir</span>: <span class="identity-value">{{ $student->tempat_lahir ?? '-' }}, {{ $student->tanggal_lahir ? \Carbon\Carbon::parse($student->tanggal_lahir)->format('d F Y') : '-' }}</span></div>
            <div class="identity-row"><span class="identity-label">Program</span>: <span class="identity-value">{{ $student->program?->nama ?? '-' }}</span></div>
            <div class="identity-row"><span class="identity-label">Rombongan Belajar</span>: <span class="identity-value">{{ $student->classroom?->nama ?? '-' }}</span></div>
            <div class="identity-row"><span class="identity-label">Tahun Ajaran</span>: <span class="identity-value">{{ $selectedAcademicYear?->nama ?? '-' }}</span></div>
            <div class="identity-row"><span class="identity-label">Semester</span>: <span class="identity-value">{{ $selectedSemester?->nama ?? '-' }}</span></div>
        </div>

        <h3>A. Capaian Kompetensi / Nilai Akademik</h3>
        <table>
            <thead>
                <tr>
                    <th class="text-center" style="width:40px">No</th>
                    <th>Mata Pelajaran</th>
                    <th class="text-center">Tugas</th>
                    <th class="text-center">UTS</th>
                    <th class="text-center">UAS</th>
                    <th class="text-center">Akhir</th>
                    <th>Tutor Pengampu</th>
                </tr>
            </thead>
            <tbody>
                @forelse($grades as $idx => $gr)
                <tr>
                    <td class="text-center">{{ $idx + 1 }}</td>
                    <td>{{ $gr->penugasanMengajar->subject->nama }}</td>
                    <td class="text-center">{{ $gr->nilai_tugas ?? '-' }}</td>
                    <td class="text-center">{{ $gr->nilai_uts ?? '-' }}</td>
                    <td class="text-center">{{ $gr->nilai_uas ?? '-' }}</td>
                    <td class="text-center font-bold">{{ $gr->nilai_akhir ?? '-' }}</td>
                    <td>{{ $gr->penugasanMengajar->tutor->nama_lengkap }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center">Belum ada data nilai.</td>
                </tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr class="summary-row">
                    <td colspan="5" class="right">Rata-rata</td>
                    <td class="text-center">{{ $averageGrade }}</td>
                    <td></td>
                </tr>
                <tr class="summary-row">
                    <td colspan="5" class="right">Status</td>
                    <td colspan="2" class="{{ $averageGrade >= 70 ? 'status-lulus' : 'status-belum' }}">{{ $statusKelulusan }}</td>
                </tr>
            </tfoot>
        </table>

        <h3>B. Ketidakhadiran</h3>
        <table class="attendance-table">
            <tr>
                <td>Sakit</td>
                <td class="text-center">{{ $totalSakit }} hari</td>
            </tr>
            <tr>
                <td>Izin</td>
                <td class="text-center">{{ $totalIzin }} hari</td>
            </tr>
            <tr>
                <td>Tanpa Keterangan (Alpa)</td>
                <td class="text-center">{{ $totalAlpa }} hari</td>
            </tr>
            <tr class="font-bold">
                <td>Total Kehadiran</td>
                <td class="text-center">{{ $totalHadir }} hari</td>
            </tr>
        </table>

        <div class="signatures">
            <div class="sign-block">
                <p>Orang Tua / Wali</p>
                <div class="sign-space"></div>
                <p class="sign-name">.................................</p>
            </div>
            <div class="sign-block">
                <p>Mamuju, {{ now()->translatedFormat('d F Y') }}</p>
                <p>Kepala PKBM Aditya</p>
                <div class="sign-space"></div>
                <p class="sign-name">{{ $student->classroom?->waliKelas?->name ?? '.................................' }}</p>
            </div>
        </div>
    </div>

    <script>
        window.onload = function() { window.print(); };
    </script>
</body>
</html>
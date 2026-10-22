<?php

namespace App\Exports;

use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\Program;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class StudentTemplateExport implements FromArray, WithColumnWidths, WithEvents, WithHeadings, WithStyles
{
    protected $programs;

    protected $academicYears;

    protected $classrooms;

    public function __construct()
    {
        $this->programs = Program::where('status_aktif', true)->pluck('nama', 'id')->toArray();
        $this->academicYears = AcademicYear::where('status_aktif', true)->latest('tanggal_mulai')->pluck('nama_ajaran', 'id')->toArray();
        $this->classrooms = Classroom::where('status_aktif', true)->pluck('nama_rombel', 'id')->toArray();
    }

    public function array(): array
    {
        return [];
    }

    public function headings(): array
    {
        return [
            'nis',
            'nisn',
            'nik',
            'nama_lengkap',
            'nama_panggilan',
            'jenis_kelamin',
            'tempat_lahir',
            'tanggal_lahir',
            'agama',
            'status_perkawinan',
            'alamat',
            'rt',
            'rw',
            'kelurahan',
            'kecamatan',
            'kabupaten',
            'provinsi',
            'no_hp',
            'email',
            'program_id',
            'tahun_ajaran_id',
            'rombel_id',
            'status',
            'nama_ayah',
            'nama_ibu',
            'nama_wali',
            'no_hp_wali',
            'alamat_wali',
            'catatan',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 12], 'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '4472C4'], 'font' => ['color' => ['rgb' => 'FFFFFF']]]],
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 15,
            'B' => 15,
            'C' => 20,
            'D' => 30,
            'E' => 20,
            'F' => 25,
            'G' => 20,
            'H' => 20,
            'I' => 15,
            'J' => 20,
            'K' => 30,
            'L' => 8,
            'M' => 8,
            'N' => 18,
            'O' => 18,
            'P' => 18,
            'Q' => 18,
            'R' => 18,
            'S' => 25,
            'T' => 15,
            'U' => 15,
            'V' => 25,
            'W' => 20,
            'X' => 20,
            'Y' => 20,
            'Z' => 18,
            'AA' => 30,
            'AB' => 30,
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // Add example row
                $exampleRow = [
                    'NIS001',
                    'NISN001',
                    '3273040101000001',
                    'Ahmad Fauzi',
                    'Ahmad',
                    'Laki-laki',
                    'Jakarta',
                    '2010-01-15',
                    'Islam',
                    'Belum Menikah',
                    'Jl. Contoh No. 123',
                    '001',
                    '002',
                    'Kelurahan Contoh',
                    'Kecamatan Contoh',
                    'Kabupaten Contoh',
                    'Provinsi Contoh',
                    '081234567890',
                    'ahmad@example.com',
                    array_key_first($this->programs),
                    array_key_first($this->academicYears),
                    array_key_first($this->classrooms),
                    'aktif',
                    'Budi',
                    'Siti',
                    'Budi',
                    '081234567891',
                    'Jl. Contoh No. 123',
                    'Catatan tambahan',
                ];

                $sheet->insertNewRowBefore(2, 1);
                $sheet->setCellValue(Coordinate::stringFromColumnIndex(1).'2', $exampleRow[0]);
                foreach ($exampleRow as $col => $value) {
                    $sheet->setCellValue(Coordinate::stringFromColumnIndex($col + 1).'2', $value);
                }

                // Style example row
                for ($col = 1; $col <= count($exampleRow); $col++) {
                    $style = $sheet->getStyle(Coordinate::stringFromColumnIndex($col).'2');
                    $style->getFont()->setItalic(true);
                    $style->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('E2EFDA');
                }

                // Add instructions sheet
                $spreadsheet = $event->sheet->getParent();
                $instructionSheet = $spreadsheet->createSheet();
                $instructionSheet->setTitle('Panduan');

                $instructions = [
                    ['PETUNJUK PENGGUNAAN TEMPLATE IMPORT PESERTA DIDIK'],
                    [''],
                    ['KOLOM WAJIB (*)'],
                    ['NIS', 'Nomor Induk Siswa (unik)'],
                    ['NIK', 'Nomor Induk Kependudukan (16 digit)'],
                    ['Nama Lengkap', 'Nama lengkap peserta didik'],
                    ['Jenis Kelamin', 'Laki-laki atau Perempuan'],
                    ['Alamat', 'Alamat lengkap'],
                    ['Program ID', 'ID Program (lihat daftar di bawah)'],
                    ['Tahun Ajaran ID', 'ID Tahun Ajaran (lihat daftar di bawah)'],
                    ['Status', 'aktif, lulus, keluar, pindah, atau nonaktif'],
                    [''],
                    ['KOLOM OPSIONAL'],
                    ['NISN', 'Nomor Induk Siswa Nasional'],
                    ['Nama Panggilan', 'Nama panggilan sehari-hari'],
                    ['Tempat Lahir', 'Kota/kabupaten kelahiran'],
                    ['Tanggal Lahir', 'Format YYYY-MM-DD (contoh: 2010-01-15)'],
                    ['Agama', 'Islam, Kristen, Katolik, Hindu, Buddha, Konghucu'],
                    ['Status Perkawinan', 'Belum Menikah, Menikah, Cerai, dll'],
                    ['RT/RW', 'Nomor RT/RW'],
                    ['Kelurahan/Kecamatan/Kabupaten/Provinsi', 'Alamat detail'],
                    ['No HP', 'Nomor telepon/HP'],
                    ['Email', 'Alamat email'],
                    ['Rombel ID', 'ID Rombel/Kelas (lihat daftar di bawah)'],
                    ['Nama Ayah/Ibu/Wali', 'Nama orang tua/wali'],
                    ['No HP Wali', 'Nomor HP orang tua/wali'],
                    ['Alamat Wali', 'Alamat orang tua/wali'],
                    ['Catatan', 'Catatan tambahan'],
                    [''],
                    ['DAFTAR PROGRAM TERSEDIA'],
                ];

                $row = 1;
                foreach ($instructions as $instruction) {
                    $instructionSheet->setCellValue('A'.$row, $instruction[0]);
                    if (isset($instruction[1])) {
                        $instructionSheet->setCellValue('B'.$row, $instruction[1]);
                    }
                    $row++;
                }

                $row = count($instructions) + 1;
                $instructionSheet->setCellValue('A'.$row, 'ID');
                $instructionSheet->setCellValue('B'.$row, 'Nama Program');
                $instructionSheet->getStyle('A'.$row.':B'.$row)->getFont()->setBold(true);
                $row++;

                foreach ($this->programs as $id => $nama) {
                    $instructionSheet->setCellValue('A'.$row, $id);
                    $instructionSheet->setCellValue('B'.$row, $nama);
                    $row++;
                }

                $row += 2;
                $instructionSheet->setCellValue('A'.$row, 'DAFTAR TAHUN AJARAN TERSEDIA');
                $row++;
                $instructionSheet->setCellValue('A'.$row, 'ID');
                $instructionSheet->setCellValue('B'.$row, 'Nama Tahun Ajaran');
                $instructionSheet->getStyle('A'.$row.':B'.$row)->getFont()->setBold(true);
                $row++;

                foreach ($this->academicYears as $id => $nama) {
                    $instructionSheet->setCellValue('A'.$row, $id);
                    $instructionSheet->setCellValue('B'.$row, $nama);
                    $row++;
                }

                $row += 2;
                $instructionSheet->setCellValue('A'.$row, 'DAFTAR ROMBEL/KELAS TERSEDIA');
                $row++;
                $instructionSheet->setCellValue('A'.$row, 'ID');
                $instructionSheet->setCellValue('B'.$row, 'Nama Rombel');
                $instructionSheet->getStyle('A'.$row.':B'.$row)->getFont()->setBold(true);
                $row++;

                foreach ($this->classrooms as $id => $nama) {
                    $instructionSheet->setCellValue('A'.$row, $id);
                    $instructionSheet->setCellValue('B'.$row, $nama);
                    $row++;
                }

                $instructionSheet->getColumnDimension('A')->setWidth(15);
                $instructionSheet->getColumnDimension('B')->setWidth(50);
            },
        ];
    }
}

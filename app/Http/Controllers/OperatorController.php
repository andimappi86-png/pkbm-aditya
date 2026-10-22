<?php

namespace App\Http\Controllers;

use App\Casts\EncryptedAttribute;
use App\Imports\BulkImport;
use App\Imports\TutorImport;
use App\Models\AcademicYear;
use App\Models\Announcement;
use App\Models\Assignment;
use App\Models\Attendance;
use App\Models\Classroom;
use App\Models\Document;
use App\Models\Grade;
use App\Models\Letter;
use App\Models\Material;
use App\Models\Program;
use App\Models\Quiz;
use App\Models\Schedule;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Tutor;
use App\Models\User;
use App\Services\AesGcmService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class OperatorController extends Controller
{
    private AesGcmService $aesService;

    public function __construct(AesGcmService $aesService)
    {
        $this->aesService = $aesService;
    }

    public function showLoginForm()
    {
        if (Auth::check() && Auth::user()->hasRole('operator')) {
            return redirect()->route('operator.dashboard');
        }

        return view('operator.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        $remember = $request->boolean('remember');

        if (Auth::attempt($credentials, $remember)) {
            if (! Auth::user()->hasRole('operator')) {
                Auth::logout();

                return back()->withErrors([
                    'email' => 'Akses ditolak. Anda bukan Operator.',
                ])->onlyInput('email');
            }

            $request->session()->regenerate();

            return redirect()->intended(route('operator.dashboard'));
        }

        return back()->withErrors([
            'email' => 'Email atau password salah.',
        ])->onlyInput('email');
    }

    public function dashboard()
    {
        $stats = [
            'students' => Student::count(),
            'tutors' => Tutor::count(),
            'classrooms' => Classroom::count(),
            'subjects' => Subject::count(),
            'schedules' => Schedule::count(),
            'attendance' => Attendance::count(),
            'grades' => Grade::count(),
            'assignments' => Assignment::count(),
            'quizzes' => Quiz::count(),
            'materials' => Material::count(),
            'documents' => Document::count(),
            'letters' => Letter::count(),
            'announcements' => Announcement::count(),
        ];

        // Indikator operasional (bukan angka statis)
        $ops = [
            'total_users' => User::count(),
            'inactive_accounts' => User::where('status', '!=', 'active')->orWhereNull('status')->count(),
            'unlinked_student_accounts' => User::role('peserta-didik')->whereDoesntHave('student')->count(),
            'students_without_account' => Student::whereNull('user_id')->count(),
            'total_permissions' => Permission::count(),
        ];

        $rolesCount = Role::count();

        $recentAnnouncements = Announcement::query()
            ->where('status_aktif', true)
            ->latest('published_at')
            ->latest('id')
            ->take(4)
            ->get(['id', 'judul', 'created_at', 'published_at']);

        return view('operator.dashboard', compact(
            'stats',
            'ops',
            'rolesCount',
            'recentAnnouncements',
        ));
    }

    public function security()
    {
        $keyLength = strlen((string) config('services.aes_gcm.key', '')) * 8; // in bits

        $studentFields = 10; // NIK, alamat, tanggal_lahir, dll.
        $tutorFields = 6;

        $studentCount = Student::count();
        $tutorCount = Tutor::count();

        // Sample masked data for verification (3 students, 3 tutors)
        $sampleStudents = Student::take(3)->get()->map(function ($student) {
            $rawNik = $student->getRawOriginal('nik');
            $nikIsEncrypted = EncryptedAttribute::isEncryptedPayload($rawNik);

            return [
                'type' => 'Student',
                'name' => $student->nama_lengkap ?? 'Unknown',
                'masked_nik' => '************'.substr((string) $student->nik, -4),
                'status' => $nikIsEncrypted ? 'Encrypted ✅' : 'Plaintext ❌',
                'raw_excerpt' => substr($rawNik ?? '', 0, 20).'...',
            ];
        });

        $sampleTutors = Tutor::take(3)->get()->map(function ($tutor) {
            $rawNik = $tutor->getRawOriginal('nik');
            $nikIsEncrypted = EncryptedAttribute::isEncryptedPayload($rawNik);

            return [
                'type' => 'Tutor',
                'name' => $tutor->nama_lengkap ?? 'Unknown',
                'masked_nik' => '************'.substr((string) $tutor->nik, -4),
                'status' => $nikIsEncrypted ? 'Encrypted ✅' : 'Plaintext ❌',
                'raw_excerpt' => substr($rawNik ?? '', 0, 20).'...',
            ];
        });

        $maskedRecords = $sampleStudents->merge($sampleTutors);

        return view('operator.security', compact(
            'keyLength', 'studentFields', 'tutorFields', 'studentCount', 'tutorCount', 'maskedRecords'
        ));
    }

    public function encryptData(Request $request)
    {
        $request->validate([
            'plaintext' => 'required|string',
        ]);

        $start = microtime(true);
        $encrypted = $this->aesService->encrypt($request->plaintext);
        $time = microtime(true) - $start;

        return response()->json([
            'encrypted' => $encrypted,
            'time_ms' => round($time * 1000, 4),
        ]);
    }

    public function decryptData(Request $request)
    {
        $request->validate([
            'payload' => 'required|string',
        ]);

        try {
            $start = microtime(true);
            $plaintext = $this->aesService->decrypt($request->payload);
            $time = microtime(true) - $start;

            return response()->json([
                'success' => true,
                'plaintext' => $plaintext,
                'time_ms' => round($time * 1000, 4),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    public function avalanche(Request $request)
    {
        $request->validate([
            'plaintextA' => 'required|string',
            'plaintextB' => 'required|string',
        ]);

        $result = $this->aesService->calculateAvalanche($request->plaintextA, $request->plaintextB);

        return response()->json($result);
    }

    public function benchmark(Request $request)
    {
        $count = $request->input('count', 100);

        // Setup dummy data
        $dummies = [];
        for ($i = 0; $i < $count; $i++) {
            $dummies[] = [
                'nik' => '327'.str_pad((string) $i, 13, '0', STR_PAD_LEFT),
                'nama' => 'Dummy Name '.$i,
                'email' => 'dummy'.$i.'@example.com',
                'alamat' => 'Jalan Dummy No '.$i,
                'telepon' => '0812'.str_pad((string) $i, 8, '0', STR_PAD_LEFT),
            ];
        }

        // Test Plaintext Array Creation
        $start = microtime(true);
        $plaintextData = [];
        foreach ($dummies as $d) {
            $plaintextData[] = json_encode($d);
        }
        $plaintextTime = microtime(true) - $start;

        // Test Encryption
        $start = microtime(true);
        $encryptedData = [];
        foreach ($dummies as $d) {
            $encryptedData[] = $this->aesService->encrypt(json_encode($d));
        }
        $encryptionTime = microtime(true) - $start;

        // Test Decryption
        $start = microtime(true);
        $decryptedData = [];
        foreach ($encryptedData as $e) {
            $decryptedData[] = $this->aesService->decrypt($e);
        }
        $decryptionTime = microtime(true) - $start;

        return response()->json([
            'count' => $count,
            'plaintext_time_ms' => round($plaintextTime * 1000, 2),
            'encryption_time_ms' => round($encryptionTime * 1000, 2),
            'encryption_throughput' => round($count / max($encryptionTime, 0.0001), 0),
            'decryption_time_ms' => round($decryptionTime * 1000, 2),
            'decryption_throughput' => round($count / max($decryptionTime, 0.0001), 0),
        ]);
    }

    public function importForm()
    {
        if (! auth()->user()->hasRole('operator')) {
            abort(403, 'Akses khusus Operator.');
        }

        $defaultProgram = Program::where('status_aktif', true)->first();
        $defaultAcademicYear = AcademicYear::where('status_aktif', true)->first();

        return view('operator.import-data.index', compact('defaultProgram', 'defaultAcademicYear'));
    }

    public function importPreview(Request $request)
    {
        if (! auth()->user()->hasRole('operator')) {
            abort(403, 'Akses khusus Operator.');
        }

        $request->validate([
            'file' => ['required', 'file', 'mimes:csv,txt', 'max:10240'],
        ], [
            'file.required' => 'File CSV wajib diunggah.',
            'file.mimes' => 'File harus berformat CSV (.csv).',
            'file.max' => 'Ukuran file tidak boleh melebihi 10 MB.',
        ]);

        $file = $request->file('file');

        if ($file->getSize() === 0) {
            return back()->withErrors(['file' => 'File CSV tidak boleh kosong.'])->withInput();
        }

        $path = $file->storeAs('temp', 'import_bulk_'.auth()->id().'_'.time().'.csv');

        try {
            $import = new BulkImport(true);
            Excel::import($import, $path);
            $results = $import->getResults();
        } catch (\Throwable $e) {
            Storage::delete($path);

            return back()->withErrors(['file' => 'Gagal membaca CSV: '.$e->getMessage()])->withInput();
        }

        $defaultProgram = Program::where('status_aktif', true)->first();
        $defaultAcademicYear = AcademicYear::where('status_aktif', true)->first();

        return view('operator.import-data.index', [
            'step' => 'preview',
            'results' => $results,
            'path' => $path,
            'defaultProgram' => $defaultProgram,
            'defaultAcademicYear' => $defaultAcademicYear,
        ]);
    }

    public function importTutorForm()
    {
        if (! auth()->user()->hasRole('operator')) {
            abort(403, 'Akses khusus Operator.');
        }

        return view('operator.import-tutor.index');
    }

    public function importTutorPreview(Request $request)
    {
        if (! auth()->user()->hasRole('operator')) {
            abort(403, 'Akses khusus Operator.');
        }

        $request->validate([
            'file' => ['required', 'file', 'mimes:csv,txt', 'max:10240'],
        ]);

        $file = $request->file('file');
        $path = $file->storeAs('temp', 'import_tutor_'.auth()->id().'_'.time().'.csv');

        try {
            $import = new TutorImport(true);
            Excel::import($import, $path);
            $results = $import->getResults();
        } catch (\Throwable $e) {
            Storage::delete($path);

            return back()->withErrors(['file' => 'Gagal membaca CSV: '.$e->getMessage()])->withInput();
        }

        return view('operator.import-tutor.index', [
            'step' => 'preview',
            'results' => $results,
            'path' => $path,
        ]);
    }

    public function importTutorProcess(Request $request)
    {
        if (! auth()->user()->hasRole('operator')) {
            abort(403, 'Akses khusus Operator.');
        }

        $request->validate([
            'path' => ['required', 'string'],
        ]);

        $path = $request->input('path');
        if (! Storage::exists($path)) {
            return redirect()->route('operator.import-tutor.index')
                ->with('error', 'File import tidak ditemukan atau sudah kadaluarsa.');
        }

        $import = new TutorImport(false);

        try {
            Excel::import($import, $path);
            $results = $import->getResults();
        } catch (\Throwable $e) {
            Storage::delete($path);

            return redirect()->route('operator.import-tutor.index')
                ->with('error', 'Terjadi kesalahan sistem: '.$e->getMessage());
        }

        Storage::delete($path);

        return view('operator.import-tutor.index', [
            'step' => 'result',
            'results' => $results,
        ]);
    }

    public function importTutorTemplate()
    {
        $headers = [
            'nama',
            'jenis_kelamin',
            'tempat_lahir_dummy',
            'tanggal_lahir',
            'nik_dummy',
            'alamat_dummy',
            'no_hp_dummy',
            'email_dummy',
            'pendidikan_terakhir',
            'pekerjaan_jabatan',
            'status',
        ];

        $callback = function () use ($headers) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $headers);
            // Add a dummy row
            fputcsv($file, [
                'Budi Santoso',
                'Laki-laki',
                'Mamuju',
                '1985-05-20',
                '1234567890123456',
                'Jl. Raya No. 123',
                '08123456789',
                'budi@example.com',
                'S1 Pendidikan',
                'Matematika',
                'aktif',
            ]);
            fclose($file);
        };

        return response()->stream($callback, 200, [
            'Content-type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename=template_import_tutor.csv',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ]);
    }
}

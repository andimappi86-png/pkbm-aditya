<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\Assignment;
use App\Models\Attendance;
use App\Models\Classroom;
use App\Models\Document;
use App\Models\EncryptedDummy;
use App\Models\Grade;
use App\Models\Letter;
use App\Models\Material;
use App\Models\PlaintextDummy;
use App\Models\Ppdb;
use App\Models\Quiz;
use App\Models\Schedule;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Tutor;
use App\Services\AesGcmService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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

    public function profile()
    {
        return view('operator.profile');
    }

    public function updateProfile(Request $request)
    {
        $user = Auth::user();
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,'.$user->id,
            'phone' => 'nullable|string|max:20',
            'current_password' => 'nullable|required_with:password|current_password',
            'password' => 'nullable|min:8|confirmed',
        ]);

        $user->name = $request->name;
        $user->email = $request->email;
        $user->phone = $request->phone;

        if ($request->filled('password')) {
            $user->password = IlluminateSupportFacadesHash::make($request->password);
        }

        $user->save();

        return back()->with('status', 'Profil berhasil diperbarui.');
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
            'ppdb' => Ppdb::count(),
            'documents' => Document::count(),
            'letters' => Letter::count(),
            'announcements' => Announcement::count(),
        ];

        return view('operator.dashboard', compact('stats'));
    }

    public function security()
    {
        $keyLength = strlen(env('AES_256_GCM_KEY', '')) * 8; // in bits

        $dummyStats = [
            'encrypted' => EncryptedDummy::count(),
            'plaintext' => PlaintextDummy::count(),
        ];

        return view('operator.security', compact('keyLength', 'dummyStats'));
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
                'nik' => '327'.str_pad($i, 13, '0', STR_PAD_LEFT),
                'nama' => 'Dummy Name '.$i,
                'email' => 'dummy'.$i.'@example.com',
                'alamat' => 'Jalan Dummy No '.$i,
                'telepon' => '0812'.str_pad($i, 8, '0', STR_PAD_LEFT),
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
}

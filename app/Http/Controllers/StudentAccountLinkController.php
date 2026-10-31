<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class StudentAccountLinkController extends Controller
{
    public function index(Request $request): View
    {
        Gate::authorize('students.manage');

        $search = $request->string('search')->trim()->toString();

        $studentsQuery = Student::with(['program', 'academicYear', 'classroom'])
            ->whereNull('user_id');

        if ($search !== '') {
            $studentsQuery->where(function ($q) use ($search) {
                $q->where('nama_lengkap', 'like', "%{$search}%")
                    ->orWhere('nis', 'like', "%{$search}%")
                    ->orWhere('nisn', 'like', "%{$search}%");
            });
        }

        $students = $studentsQuery
            ->orderBy('nama_lengkap')
            ->paginate(25, ['*'], 'students_page')
            ->withQueryString();

        $accountsQuery = User::role('peserta-didik')
            ->where('status', 'active')
            ->whereDoesntHave('student');

        if ($search !== '') {
            $accountsQuery->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $accounts = $accountsQuery
            ->orderBy('name')
            ->paginate(25, ['*'], 'accounts_page')
            ->withQueryString();

        $linkableAccounts = User::role('peserta-didik')
            ->where('status', 'active')
            ->whereDoesntHave('student')
            ->orderBy('name')
            ->get(['id', 'name', 'email']);

        $linkedStudentsQuery = Student::with(['user:id,name,email', 'classroom'])
            ->whereNotNull('user_id');

        if ($search !== '') {
            $linkedStudentsQuery->where(function ($q) use ($search) {
                $q->where('nama_lengkap', 'like', "%{$search}%")
                    ->orWhere('nis', 'like', "%{$search}%")
                    ->orWhere('nisn', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($uq) use ($search) {
                        $uq->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }

        $linkedStudents = $linkedStudentsQuery
            ->orderBy('nama_lengkap')
            ->paginate(15, ['*'], 'linked_page')
            ->withQueryString();

        $stats = [
            'students_without_account' => Student::whereNull('user_id')->count(),
            'accounts_without_student' => User::role('peserta-didik')->where('status', 'active')->whereDoesntHave('student')->count(),
            'students_linked' => Student::whereNotNull('user_id')->count(),
        ];

        return view('students.account_links', compact(
            'students',
            'accounts',
            'linkableAccounts',
            'linkedStudents',
            'stats',
        ));
    }

    public function store(Request $request): RedirectResponse
    {
        Gate::authorize('students.manage');

        $request->validate([
            'student_id' => ['required', 'exists:students,id'],
            'user_id' => [
                'required',
                Rule::exists('users', 'id')->where(function ($query) {
                    $query->where('status', 'active');
                }),
            ],
        ], [
            'student_id.required' => 'Pilih peserta didik yang akan ditautkan.',
            'user_id.required' => 'Pilih akun peserta didik yang akan ditautkan.',
            'user_id.exists' => 'Akun peserta didik tidak valid atau tidak aktif.',
        ]);

        $errors = [];
        $studentLabel = null;
        $userLabel = null;

        DB::transaction(function () use ($request, &$errors, &$studentLabel, &$userLabel): void {
            $student = Student::whereKey($request->integer('student_id'))
                ->lockForUpdate()
                ->first();
            $user = User::whereKey($request->integer('user_id'))
                ->lockForUpdate()
                ->first();

            // Re-validate against locked rows so concurrent links cannot slip in.
            $errors = $this->validatePair($student, $user);

            if ($errors !== []) {
                return;
            }

            $student->forceFill(['user_id' => $user->id])->save();
            $studentLabel = $student->nama_lengkap;
            $userLabel = $user->name;
        });

        if ($errors !== []) {
            return back()
                ->withInput()
                ->withErrors($errors)
                ->with('error', 'Penautan akun gagal: '.reset($errors));
        }

        return redirect()
            ->route('peserta-didik.link-accounts')
            ->with('success', "Akun {$userLabel} berhasil ditautkan ke peserta didik {$studentLabel}.");
    }

    public function unlink(Student $peserta_didik): RedirectResponse
    {
        Gate::authorize('students.manage');

        if (! $peserta_didik->user_id) {
            return back()->with('error', 'Peserta didik '.$peserta_didik->nama_lengkap.' belum memiliki akun yang terhubung.');
        }

        $userName = $peserta_didik->user->name;

        DB::transaction(function () use ($peserta_didik): void {
            $peserta_didik->forceFill(['user_id' => null])->save();
        });

        return redirect()
            ->route('peserta-didik.link-accounts')
            ->with('success', "Tautan akun {$userName} berhasil dilepas dari peserta didik {$peserta_didik->nama_lengkap}.");
    }

    private function validatePair(?Student $student, ?User $user): array
    {
        $errors = [];

        if (! $student) {
            $errors['student_id'] = 'Peserta didik tidak ditemukan atau telah terhubung dengan akun lain.';
        } elseif ($student->user_id !== null) {
            $errors['student_id'] = 'Peserta didik '.$student->nama_lengkap.' sudah terhubung dengan akun lain.';
        }

        if (! $user) {
            $errors['user_id'] = 'Akun tidak ditemukan atau telah terhubung dengan peserta didik lain.';
        } elseif ($user->status !== 'active') {
            $errors['user_id'] = 'Akun '.$user->name.' tidak aktif dan tidak dapat ditautkan.';
        } elseif (! $user->hasRole('peserta-didik')) {
            $errors['user_id'] = 'Akun '.$user->name.' tidak memiliki role Peserta Didik.';
        } elseif (Student::where('user_id', $user->id)->exists()) {
            $errors['user_id'] = 'Akun '.$user->name.' sudah terhubung dengan peserta didik lain.';
        }

        return $errors;
    }
}

<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreStudentAccountRequest;
use App\Http\Requests\StoreStudentRequest;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\Program;
use App\Models\Student;
use App\Models\Tutor;
use App\Models\User;
use App\Services\StudentRegistrationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function __construct(private readonly StudentRegistrationService $studentRegistration) {}

    public function index(Request $request): View
    {
        Gate::authorize('users.view');

        $query = User::with(['roles', 'student:id,user_id,nama_lengkap,nis', 'tutor:id,user_id,nama_lengkap,nomor_induk']);

        if ($request->filled('search')) {
            $search = $request->string('search')->trim()->toString();
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if ($request->filled('role')) {
            $query->role($request->string('role')->toString());
        }

        if ($request->filled('status')) {
            $query->where('status', $request->string('status')->toString());
        }

        $users = $query->orderBy('name')->paginate(15)->withQueryString();

        $roles = Role::withCount('users')->orderBy('name')->get();

        $stats = [
            'total' => User::count(),
            'active' => User::where('status', 'active')->count(),
            'inactive' => User::where('status', '!=', 'active')->orWhereNull('status')->count(),
            'unlinked_students' => User::role('peserta-didik')->whereDoesntHave('student')->count(),
        ];

        return view('users.index', compact('users', 'roles', 'stats'));
    }

    /**
     * Data pendukung form pembuatan akun (Jalur A dan Jalur B).
     */
    public function create(Request $request): View
    {
        Gate::authorize('users.manage');

        $roles = Role::orderBy('name')->get(['id', 'name']);

        $unlinkedStudents = Student::whereNull('user_id')
            ->with('classroom:id,nama_rombel')
            ->orderBy('nama_lengkap')
            ->get(['id', 'nama_lengkap', 'nis', 'rombel_id']);

        $unlinkedTutors = Tutor::whereNull('user_id')
            ->orderBy('nama_lengkap')
            ->get(['id', 'nama_lengkap', 'nomor_induk']);

        $canRegisterStudent = $request->user()->can('students.create');

        // Profil siswa baru bisa dikirim balik dari form Buku Induk (?student=ID)
        // supaya operator kembali ke konteks penautan tanpa mencari ulang.
        $preselectedStudentId = $request->integer('student') ?: null;

        if ($preselectedStudentId !== null && ! $unlinkedStudents->contains('id', $preselectedStudentId)) {
            $preselectedStudentId = null;
        }

        $programs = collect();
        $academicYears = collect();
        $classrooms = collect();

        if ($canRegisterStudent) {
            $programs = Program::where('status_aktif', true)->orderBy('nama')->get(['id', 'nama']);
            $academicYears = AcademicYear::where('status_aktif', true)->latest('tanggal_mulai')->get(['id', 'nama']);
            $classrooms = Classroom::where('status_aktif', true)->orderBy('nama_rombel')->get(['id', 'nama_rombel']);
        }

        $roleLabels = [
            'peserta-didik' => 'Peserta Didik',
            'tutor' => 'Tutor',
            'wali-kelas' => 'Wali Kelas',
            'operator' => 'Operator',
            'kepala-pkbm' => 'Kepala PKBM',
            'ketua-yayasan' => 'Ketua Yayasan',
        ];

        $studentStatuses = [
            'aktif' => 'Aktif',
            'lulus' => 'Lulus',
            'keluar' => 'Keluar',
            'pindah' => 'Pindah',
            'nonaktif' => 'Nonaktif',
        ];

        return view('users.create', compact(
            'roles',
            'roleLabels',
            'unlinkedStudents',
            'unlinkedTutors',
            'canRegisterStudent',
            'preselectedStudentId',
            'programs',
            'academicYears',
            'classrooms',
            'studentStatuses',
        ));
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        Gate::authorize('users.manage');

        $data = $request->validated();

        $user = DB::transaction(function () use ($data): User {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'] ?? null,
                'status' => $data['status'] ?? 'active',
                'password' => $data['password'],
                'email_verified_at' => now(),
            ]);

            $user->assignRole($data['role']);

            if (! empty($data['student_id'])) {
                $student = Student::whereKey($data['student_id'])
                    ->lockForUpdate()
                    ->first();

                if (! $student || $student->user_id !== null) {
                    throw ValidationException::withMessages([
                        'student_id' => 'Profil siswa sudah terhubung dengan akun lain.',
                    ]);
                }

                $student->forceFill(['user_id' => $user->id])->save();
            }

            if (! empty($data['tutor_id'])) {
                $tutor = Tutor::whereKey($data['tutor_id'])
                    ->lockForUpdate()
                    ->first();

                if (! $tutor || $tutor->user_id !== null) {
                    throw ValidationException::withMessages([
                        'tutor_id' => 'Profil tutor sudah terhubung dengan akun lain.',
                    ]);
                }

                $tutor->forceFill(['user_id' => $user->id])->save();
            }

            return $user;
        });

        $profileNote = '';

        if (! empty($data['student_id']) && $user->student) {
            $profileNote = " Terhubung dengan profil siswa {$user->student->nama_lengkap} (NIS {$user->student->nis}).";
        } elseif (! empty($data['tutor_id']) && $user->tutor) {
            $profileNote = " Terhubung dengan profil tutor {$user->tutor->nama_lengkap}.";
        }

        return redirect()
            ->route('users.show', $user)
            ->with('success', "Akun {$user->name} berhasil dibuat dengan role {$data['role']}.{$profileNote}");
    }

    /**
     * Jalur B — tambah siswa baru sekaligus membuat akun login.
     *
     * Profil siswa dan akun user disimpan dalam satu transaksi; bila salah satu
     * gagal, keduanya dibatalkan. Role akun selalu peserta-didik.
     */
    public function storeWithStudent(StoreStudentAccountRequest $request): RedirectResponse
    {
        Gate::authorize('users.manage');
        Gate::authorize('students.create');

        $data = $request->validated();

        /** @var array{0: User, 1: Student} $result */
        $result = DB::transaction(function () use ($data): array {
            $student = $this->studentRegistration->create($this->studentPayload($data));

            $user = User::create([
                'name' => $student->nama_lengkap,
                'email' => $data['email'],
                'phone' => $data['phone'] ?? null,
                'status' => $data['status'],
                'password' => $data['password'],
                'email_verified_at' => now(),
            ]);

            $user->assignRole('peserta-didik');

            $student->forceFill(['user_id' => $user->id])->save();

            return [$user, $student];
        });

        [$user, $student] = $result;

        return redirect()
            ->route('users.show', $user)
            ->with('success', "Data siswa {$student->nama_lengkap} (NIS {$student->nis}) dan akun login berhasil dibuat dalam satu langkah.");
    }

    /**
     * Susun payload profil siswa dari input yang tervalidasi.
     *
     * Hanya kolom identitas/penempatan yang diizinkan; user_id dan role tidak
     * pernah diambil dari input sehingga tidak bisa dimanipulasi.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function studentPayload(array $data): array
    {
        $allowed = array_keys(StoreStudentRequest::identityRules() + StoreStudentRequest::placementRules());

        $payload = array_intersect_key($data, array_flip($allowed));

        unset($payload['email']);

        $payload['nama_lengkap'] = $data['name'];
        $payload['status'] = $data['student_status'] ?? 'aktif';

        // Nomor HP akun dipakai ulang sebagai kontak siswa agar operator tidak
        // mengisi data yang sama dua kali.
        if (empty($payload['no_hp'])) {
            $payload['no_hp'] = $data['phone'] ?? '';
        }

        return $payload;
    }

    public function show(User $user): View
    {
        Gate::authorize('users.view');

        $user->load(['roles', 'student.classroom', 'tutor', 'profile']);

        return view('users.show', compact('user'));
    }

    public function edit(User $user): View
    {
        Gate::authorize('users.manage');

        $user->load('roles');
        $roles = Role::orderBy('name')->get(['id', 'name']);

        $unlinkedStudents = Student::whereNull('user_id')
            ->with('classroom:id,nama_rombel')
            ->orderBy('nama_lengkap')
            ->get(['id', 'nama_lengkap', 'nis', 'rombel_id']);

        $unlinkedTutors = Tutor::whereNull('user_id')
            ->orderBy('nama_lengkap')
            ->get(['id', 'nama_lengkap', 'nomor_induk']);

        // Include currently linked profiles so they appear in dropdown if already linked
        if ($user->student) {
            $unlinkedStudents = $unlinkedStudents->concat(
                Student::whereKey($user->student->id)->get(['id', 'nama_lengkap', 'nis', 'rombel_id'])
            );
        }

        if ($user->tutor) {
            $unlinkedTutors = $unlinkedTutors->concat(
                Tutor::whereKey($user->tutor->id)->get(['id', 'nama_lengkap', 'nomor_induk'])
            );
        }

        $roleLabels = [
            'peserta-didik' => 'Peserta Didik',
            'tutor' => 'Tutor',
            'wali-kelas' => 'Wali Kelas',
            'operator' => 'Operator',
            'kepala-pkbm' => 'Kepala PKBM',
            'ketua-yayasan' => 'Ketua Yayasan',
        ];

        return view('users.edit', compact(
            'user',
            'roles',
            'unlinkedStudents',
            'unlinkedTutors',
            'roleLabels',
        ));
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        Gate::authorize('users.manage');

        $data = $request->validated();

        if ($user->is($request->user()) && ($data['role'] ?? null) !== 'operator') {
            throw ValidationException::withMessages([
                'role' => 'Anda tidak dapat mengubah role akun yang sedang digunakan.',
            ]);
        }

        if ($user->is($request->user()) && ($data['status'] ?? 'active') !== 'active') {
            throw ValidationException::withMessages([
                'status' => 'Anda tidak dapat menonaktifkan akun yang sedang digunakan.',
            ]);
        }

        $firstRole = $user->roles->first();
        $currentRole = $firstRole instanceof Role ? $firstRole->name : null;
        $newRole = $data['role'] ?? $currentRole;
        $linkedProfileRole = match (true) {
            $user->student !== null => 'peserta-didik',
            $user->tutor !== null => 'tutor',
            default => null,
        };

        // Prevent role change if profile is linked and new role doesn't match required profile
        // Allow tutor <-> wali-kelas transition since both use Tutor profile
        $requiresStudentProfile = in_array($newRole, ['peserta-didik']);
        $requiresTutorProfile = in_array($newRole, ['tutor', 'wali-kelas']);

        if ($linkedProfileRole !== null) {
            $hasStudentProfile = $user->student !== null;
            $hasTutorProfile = $user->tutor !== null;

            // Student profile linked but new role doesn't require it
            if ($hasStudentProfile && ! $requiresStudentProfile) {
                throw ValidationException::withMessages([
                    'role' => 'Akun ini tertaut ke profil peserta didik sehingga role tidak dapat diubah. Lepas tautan profil melalui menu Penautan Akun terlebih dahulu.',
                ]);
            }

            // Tutor profile linked but new role doesn't require it (i.e., not tutor or wali-kelas)
            if ($hasTutorProfile && ! $requiresTutorProfile) {
                throw ValidationException::withMessages([
                    'role' => 'Akun ini tertaut ke profil tutor sehingga role tidak dapat diubah. Lepas tautan profil melalui menu Penautan Akun terlebih dahulu.',
                ]);
            }
        }

        // If changing to a role that requires a profile, and no profile is linked yet,
        // the request validation already ensures student_id/tutor_id is provided.
        // We just need to link it here.
        $newStudentId = $data['student_id'] ?? null;
        $newTutorId = $data['tutor_id'] ?? null;

        DB::transaction(function () use ($user, $data, $newStudentId, $newTutorId): void {
            $user->update([
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'] ?? null,
                'status' => $data['status'] ?? $user->status,
            ]);

            if (! empty($data['role'])) {
                $user->syncRoles([$data['role']]);
            }

            // Link new student profile if provided
            if ($newStudentId !== null) {
                $student = Student::whereKey($newStudentId)
                    ->lockForUpdate()
                    ->first();

                if (! $student || $student->user_id !== null) {
                    throw ValidationException::withMessages([
                        'student_id' => 'Profil siswa sudah terhubung dengan akun lain.',
                    ]);
                }

                $student->forceFill(['user_id' => $user->id])->save();
            }

            // Link new tutor profile if provided
            if ($newTutorId !== null) {
                $tutor = Tutor::whereKey($newTutorId)
                    ->lockForUpdate()
                    ->first();

                if (! $tutor || $tutor->user_id !== null) {
                    throw ValidationException::withMessages([
                        'tutor_id' => 'Profil tutor sudah terhubung dengan akun lain.',
                    ]);
                }

                $tutor->forceFill(['user_id' => $user->id])->save();
            }
        });

        return redirect()
            ->route('users.show', $user)
            ->with('success', 'Data akun berhasil diperbarui.');
    }

    public function resetPassword(Request $request, User $user): RedirectResponse
    {
        Gate::authorize('users.manage');

        $request->validate([
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'password.confirmed' => 'Konfirmasi password tidak sama.',
            'password.min' => 'Password minimal 8 karakter.',
        ]);

        $user->forceFill(['password' => $request->string('password')->toString()])->save();

        return redirect()
            ->route('users.show', $user)
            ->with('success', "Password akun {$user->name} berhasil direset. Sampaikan password baru kepada pemilik akun secara aman.");
    }

    public function toggleStatus(Request $request, User $user): RedirectResponse
    {
        Gate::authorize('users.manage');

        if ($user->is($request->user())) {
            return back()->with('error', 'Anda tidak dapat mengubah status akun yang sedang digunakan.');
        }

        $targetStatus = $user->status === 'active' ? 'inactive' : 'active';

        if ($targetStatus !== 'active' && $this->isLastAdministrator($user)) {
            return back()->with('error', 'Akun ini adalah administrator aktif terakhir dan tidak dapat dinonaktifkan.');
        }

        $user->update(['status' => $targetStatus]);

        $message = $targetStatus === 'active'
            ? "Akun {$user->name} diaktifkan kembali."
            : "Akun {$user->name} dinonaktifkan.";

        return back()->with('success', $message);
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        Gate::authorize('users.manage');

        if ($user->is($request->user())) {
            return back()->with('error', 'Anda tidak dapat menghapus akun yang sedang digunakan.');
        }

        if ($this->isLastAdministrator($user)) {
            return back()->with('error', 'Akun ini adalah administrator aktif terakhir dan tidak dapat dihapus.');
        }

        $user->loadMissing(['student', 'tutor']);

        if ($user->student || $user->tutor) {
            return back()->with('error', 'Akun masih terhubung dengan profil siswa/tutor. Lepas tautan profil terlebih dahulu atau nonaktifkan akun.');
        }

        $name = $user->name;

        DB::transaction(function () use ($user): void {
            $user->syncRoles([]);
            $user->delete();
        });

        return redirect()
            ->route('users.index')
            ->with('success', "Akun {$name} berhasil dihapus.");
    }

    private function isLastAdministrator(User $user): bool
    {
        if ($user->status !== 'active' || ! $user->hasAnyRole(['operator', 'kepala-pkbm'])) {
            return false;
        }

        return User::role(['operator', 'kepala-pkbm'])
            ->where('status', 'active')
            ->whereKeyNot($user->id)
            ->count() === 0;
    }
}

<?php

use App\Http\Controllers\AcademicYearController;
use App\Http\Controllers\AnnouncementController;
use App\Http\Controllers\AssignmentController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\ClassroomAssignmentController;
use App\Http\Controllers\ClassroomController;
use App\Http\Controllers\ClassroomMemberController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\ForumCommentController;
use App\Http\Controllers\ForumController;
use App\Http\Controllers\GradeController;
use App\Http\Controllers\LetterController;
use App\Http\Controllers\MaterialController;
use App\Http\Controllers\OperatorController;
use App\Http\Controllers\PenugasanMengajarController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProgramController;
use App\Http\Controllers\QuestionController;
use App\Http\Controllers\QuizAiController;
use App\Http\Controllers\QuizAttemptController;
use App\Http\Controllers\QuizController;
use App\Http\Controllers\QuizEssayGradeController;
use App\Http\Controllers\RaporController;
use App\Http\Controllers\RekapAkademikController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\StudentAccountLinkController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\SubjectController;
use App\Http\Controllers\SubmissionController;
use App\Http\Controllers\TutorController;
use App\Http\Controllers\UserController;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;

Route::get('/debug-dashboard', function () {
    return response()->json(['user' => auth()->user()?->email, 'id' => auth()->id()]);
})->middleware(['auth']);

Route::get('/debug-find-user', function (Request $request) {
    $db = config('database.connections.mysql.database');
    $user = User::where('email', $request->query('email'))->first();
    if ($user) {
        $check = Hash::check($request->query('password'), $user->password);

        return response()->json(['found' => true, 'id' => $user->id, 'hash_check' => $check, 'db' => $db]);
    }

    return response()->json(['found' => false, 'db' => $db]);
});

// Landing page route
Route::get('/', fn () => view('landing'))->name('landing');

// Operator login form (guest only)
Route::get('/operator/login', [OperatorController::class, 'showLoginForm'])
    ->name('operator.login.form')
    ->middleware('guest');

Route::middleware(['auth', 'verified'])->group(function () {
    // Main dashboard - redirects based on role
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/operator/login', [OperatorController::class, 'login'])->name('operator.login')->middleware('throttle:5,1')->withoutMiddleware(['auth', 'verified']);

    // Role-specific dashboards
    Route::get('/dashboard/yayasan', [DashboardController::class, 'yayasan'])
        ->middleware(['can:dashboard.view', 'role:ketua-yayasan'])
        ->name('dashboard.yayasan');

    Route::get('/dashboard/kepala', [DashboardController::class, 'kepala'])
        ->middleware('can:dashboard.view')
        ->name('dashboard.kepala');

    Route::get('/dashboard/walikelas', [DashboardController::class, 'walikelas'])
        ->middleware('can:dashboard.view')
        ->name('dashboard.walikelas');

    Route::get('/dashboard/tutor', [DashboardController::class, 'tutor'])
        ->middleware('can:dashboard.view')
        ->name('dashboard.tutor');

    Route::get('/dashboard/combined', [DashboardController::class, 'combined'])
        ->middleware('can:dashboard.view')
        ->name('dashboard.combined');

    Route::get('/dashboard/siswa', [DashboardController::class, 'siswa'])
        ->middleware('can:dashboard.view')
        ->name('dashboard.siswa');

    // LMS - Kelas Saya (Student)
    Route::get('/kelas-saya', [DashboardController::class, 'kelasSaya'])
        ->middleware('can:dashboard.view')
        ->name('kelas-saya.index');
    Route::get('/kelas-saya/{penugasanMengajar}', [DashboardController::class, 'kelasSayaShow'])
        ->middleware('can:dashboard.view')
        ->name('kelas-saya.show');

    // Jadwal - view actions
    Route::middleware(['auth', 'verified', 'can:schedules.view'])->group(function () {
        Route::get('jadwal', [ScheduleController::class, 'index'])->name('jadwal.index');
        Route::get('jadwal/{schedule}', [ScheduleController::class, 'show'])->name('jadwal.show');
    });
    // Jadwal - manage actions
    Route::middleware(['auth', 'verified', 'can:schedules.manage'])->group(function () {
        Route::get('jadwal/create', [ScheduleController::class, 'create'])->name('jadwal.create');
        Route::post('jadwal', [ScheduleController::class, 'store'])->name('jadwal.store');
        Route::get('jadwal/{schedule}/edit', [ScheduleController::class, 'edit'])->name('jadwal.edit');
        Route::put('jadwal/{schedule}', [ScheduleController::class, 'update'])->name('jadwal.update');
        Route::delete('jadwal/{schedule}', [ScheduleController::class, 'destroy'])->name('jadwal.destroy');
    });

    // Absensi - manage actions
    Route::middleware(['auth', 'verified', 'can:attendance.manage'])->group(function () {
        Route::get('absensi/create', [AttendanceController::class, 'create'])->name('absensi.create');
        Route::post('absensi', [AttendanceController::class, 'store'])->name('absensi.store');
        Route::get('absensi/{attendance}/edit', [AttendanceController::class, 'edit'])->name('absensi.edit');
        Route::put('absensi/{attendance}', [AttendanceController::class, 'update'])->name('absensi.update');
        Route::delete('absensi/{attendance}', [AttendanceController::class, 'destroy'])->name('absensi.destroy');
    });
    // Absensi - view actions
    Route::middleware(['auth', 'verified', 'can:attendance.view'])->group(function () {
        Route::get('absensi', [AttendanceController::class, 'index'])->name('absensi.index');
        Route::get('attendances', [AttendanceController::class, 'index'])->name('attendances.index');
        Route::get('absensi/{attendance}', [AttendanceController::class, 'show'])->name('absensi.show');
    });

    // Nilai - manage actions (must precede dynamic {grade} route)
    Route::middleware(['auth', 'verified', 'can:grades.manage'])->group(function () {
        Route::get('nilai/create', [GradeController::class, 'create'])->name('nilai.create');
        Route::post('nilai', [GradeController::class, 'store'])->name('nilai.store');
        Route::get('nilai/{grade}/edit', [GradeController::class, 'edit'])->name('nilai.edit');
        Route::put('nilai/{grade}', [GradeController::class, 'update'])->name('nilai.update');
        Route::delete('nilai/{grade}', [GradeController::class, 'destroy'])->name('nilai.destroy');
    });
    // Nilai - view actions
    Route::middleware(['auth', 'verified', 'can:grades.view'])->group(function () {
        Route::get('nilai', [GradeController::class, 'index'])->name('nilai.index');
        Route::get('nilai/{grade}', [GradeController::class, 'show'])->name('nilai.show');
    });

    // Materi - manage actions (must precede dynamic {material} route)
    Route::middleware(['auth', 'verified', 'can:materials.manage'])->group(function () {
        Route::get('materi/create', [MaterialController::class, 'create'])->name('materi.create');
        Route::post('materi', [MaterialController::class, 'store'])->name('materi.store');
        Route::get('materi/{material}/edit', [MaterialController::class, 'edit'])->name('materi.edit');
        Route::put('materi/{material}', [MaterialController::class, 'update'])->name('materi.update');
        Route::delete('materi/{material}', [MaterialController::class, 'destroy'])->name('materi.destroy');
    });
    // Materi - view actions
    Route::middleware(['auth', 'verified', 'can:materials.view'])->group(function () {
        Route::get('materi', [MaterialController::class, 'index'])->name('materi.index');
        Route::get('materi/{material}', [MaterialController::class, 'show'])->name('materi.show');
    });

    // Quiz - manage actions (must precede dynamic {quiz} route)
    Route::middleware(['auth', 'verified', 'can:quizzes.manage'])->group(function () {
        Route::get('quiz/create', [QuizController::class, 'create'])->name('quiz.create');
        Route::post('quiz', [QuizController::class, 'store'])->name('quiz.store');
        Route::post('quiz/ai/generate', [QuizAiController::class, 'generate'])->name('quiz.ai.generate');
        Route::get('quiz/{quiz}/edit', [QuizController::class, 'edit'])->name('quiz.edit');
        Route::put('quiz/{quiz}', [QuizController::class, 'update'])->name('quiz.update');
        Route::delete('quiz/{quiz}', [QuizController::class, 'destroy'])->name('quiz.destroy');
    });
    // Quiz - view actions
    Route::middleware(['auth', 'verified', 'can:quizzes.view'])->group(function () {
        Route::get('quiz', [QuizController::class, 'index'])->name('quiz.index');
        Route::get('quiz/{quiz}', [QuizController::class, 'show'])->name('quiz.show');
    });

    Route::prefix('quiz/{quiz}')->name('quiz.')->group(function () {
        Route::resource('questions', QuestionController::class)->parameters([
            'questions' => 'question',
        ])->only(['create', 'store', 'edit', 'update', 'destroy']);

        Route::post('start', [QuizAttemptController::class, 'start'])->name('attempts.start');
        Route::post('attempts/{attempt}/answer', [QuizAttemptController::class, 'answer'])->name('attempts.answer');
        Route::post('attempts/{attempt}/submit', [QuizAttemptController::class, 'submit'])->name('attempts.submit');
        Route::get('attempts/{attempt}', [QuizAttemptController::class, 'show'])->name('attempts.show');
        Route::get('attempts/{attempt}/result', [QuizAttemptController::class, 'viewResult'])->name('attempts.result');

        // Tutor actions
        Route::middleware(['can:quizzes.manage'])->group(function () {
            Route::post('publish', [QuizController::class, 'publish'])->name('publish');
            Route::post('archive', [QuizController::class, 'archive'])->name('archive');
            Route::get('preview', [QuizController::class, 'preview'])->name('preview');
            Route::get('essay-grade', [QuizEssayGradeController::class, 'index'])->name('essay-grade');
            Route::get('essay-grade/{attempt}', [QuizEssayGradeController::class, 'show'])->name('essay-grade.show');
            Route::post('essay-grade/{attempt}', [QuizEssayGradeController::class, 'grade'])->name('essay-grade.grade');
            Route::post('essay-grade/{attempt}/complete', [QuizEssayGradeController::class, 'completeGrading'])->name('essay-grade.complete');
            Route::get('recap', [QuizController::class, 'recap'])->name('recap');
        });
    });

    // Forum - view actions (uses announcements permissions)
    Route::middleware(['auth', 'verified', 'can:announcements.view'])->group(function () {
        Route::get('forum', [ForumController::class, 'index'])->name('forum.index');
        Route::get('forum/{forum}', [ForumController::class, 'show'])->name('forum.show');
    });
    // Forum - manage actions
    Route::middleware(['auth', 'verified', 'can:announcements.manage'])->group(function () {
        Route::get('forum/create', [ForumController::class, 'create'])->name('forum.create');
        Route::post('forum', [ForumController::class, 'store'])->name('forum.store');
        Route::get('forum/{forum}/edit', [ForumController::class, 'edit'])->name('forum.edit');
        Route::put('forum/{forum}', [ForumController::class, 'update'])->name('forum.update');
        Route::delete('forum/{forum}', [ForumController::class, 'destroy'])->name('forum.destroy');
    });

    Route::post('/forum/{forum}/comments', [ForumCommentController::class, 'store'])->name('forum.comments.store');
    Route::delete('/forum/comments/{comment}', [ForumCommentController::class, 'destroy'])->name('forum.comments.destroy');

    // Tugas - management actions (must precede dynamic {assignment} route)
    Route::middleware(['auth', 'verified', 'can:assignments.manage'])->group(function () {
        Route::get('tugas/create', [AssignmentController::class, 'create'])->name('tugas.create');
        Route::post('tugas', [AssignmentController::class, 'store'])->name('tugas.store');
        Route::get('tugas/{assignment}/edit', [AssignmentController::class, 'edit'])->name('tugas.edit');
        Route::put('tugas/{assignment}', [AssignmentController::class, 'update'])->name('tugas.update');
        Route::delete('tugas/{assignment}', [AssignmentController::class, 'destroy'])->name('tugas.destroy');
    });
    // Tugas - view actions
    Route::middleware(['auth', 'verified', 'can:assignments.view'])->group(function () {
        Route::get('tugas', [AssignmentController::class, 'index'])->name('tugas.index');
        Route::get('tugas/{assignment}', [AssignmentController::class, 'show'])->name('tugas.show');
    });

    Route::get('programs', [ProgramController::class, 'index'])->name('programs.index');
    Route::get('programs/{program}', [ProgramController::class, 'show'])->name('programs.show');

    Route::resource('pengumpulan', SubmissionController::class)->only(['store', 'update', 'destroy'])->parameters([
        'pengumpulan' => 'submission',
    ]);

    Route::get('/rekap', [RekapAkademikController::class, 'index'])->name('rekap.index');
    Route::get('/rekap/{student}', [RekapAkademikController::class, 'show'])->name('rekap.show');

    Route::get('/rapor', [RaporController::class, 'index'])->name('rapor.index');
    Route::get('/rapor/{student}', [RaporController::class, 'show'])->name('rapor.show');
    Route::get('/rapor/{student}/print', [RaporController::class, 'print'])->name('rapor.print');

    // Tutors - create actions (must precede dynamic {tutor} route)
    Route::middleware(['auth', 'verified', 'can:tutors.create'])->group(function () {
        Route::get('tutors/create', [TutorController::class, 'create'])->name('tutors.create');
        Route::post('tutors', [TutorController::class, 'store'])->name('tutors.store');
    });

    // Tutors - view actions
    Route::middleware(['auth', 'verified', 'can:tutors.view'])->group(function () {
        Route::get('tutors', [TutorController::class, 'index'])->name('tutors.index');
        Route::get('tutors/{tutor}', [TutorController::class, 'show'])->name('tutors.show');
    });

    Route::middleware(['auth', 'verified', 'can:tutors.update'])->group(function () {
        Route::get('tutors/{tutor}/edit', [TutorController::class, 'edit'])->name('tutors.edit');
        Route::put('tutors/{tutor}', [TutorController::class, 'update'])->name('tutors.update');
    });

    Route::middleware(['auth', 'verified', 'can:tutors.delete'])->group(function () {
        Route::delete('tutors/{tutor}', [TutorController::class, 'destroy'])->name('tutors.destroy');
    });

    Route::get('/rombel/search-wali-kelas', [ClassroomController::class, 'searchWaliKelas'])->name('rombel.search-wali-kelas');
    // Rombel - create actions before dynamic classroom route (create can match show)
    Route::middleware(['auth', 'verified', 'can:classrooms.create'])->group(function () {
        Route::get('rombel/create', [ClassroomController::class, 'create'])->name('rombel.create');
        Route::post('rombel', [ClassroomController::class, 'store'])->name('rombel.store');
    });
    // Rombel - view actions
    Route::middleware(['auth', 'verified', 'can:classrooms.view'])->group(function () {
        Route::get('rombel', [ClassroomController::class, 'index'])->name('rombel.index');
        Route::get('rombel/{classroom}', [ClassroomController::class, 'show'])->name('rombel.show');
    });
    // Rombel - manage actions
    Route::middleware(['auth', 'verified', 'can:classrooms.manage'])->group(function () {
        Route::get('rombel/{classroom}/edit', [ClassroomController::class, 'edit'])->name('rombel.edit');
        Route::put('rombel/{classroom}', [ClassroomController::class, 'update'])->name('rombel.update');
        Route::delete('rombel/{classroom}', [ClassroomController::class, 'destroy'])->name('rombel.destroy');

        // Rombel - member management (wali kelas & operator)
        Route::get('rombel/{classroom}/unassigned-students', [ClassroomMemberController::class, 'unassignedStudents'])->name('rombel.unassigned-students');
        Route::post('rombel/{classroom}/students/assign', [ClassroomMemberController::class, 'assignExisting'])->name('rombel.students.assign');
        Route::post('rombel/{classroom}/students/quick-store', [ClassroomMemberController::class, 'quickStore'])->name('rombel.students.quick-store');
        Route::post('rombel/{classroom}/students/bulk-preview', [ClassroomMemberController::class, 'bulkPreview'])->name('rombel.students.bulk-preview');
        Route::post('rombel/{classroom}/students/bulk-store', [ClassroomMemberController::class, 'bulkStore'])->name('rombel.students.bulk-store');
        Route::post('rombel/{classroom}/students/{student}/remove', [ClassroomMemberController::class, 'remove'])->name('rombel.students.remove');
        Route::delete('rombel/{classroom}/students/{student}', [ClassroomMemberController::class, 'remove'])->name('rombel.students.destroy');
    });

    // Academic Years - view actions
    Route::middleware(['auth', 'verified', 'can:academic-years.view'])->group(function () {
        Route::get('academic-years', [AcademicYearController::class, 'index'])->name('academic-years.index');
        Route::get('academic-years/{academicYear}', [AcademicYearController::class, 'show'])->name('academic-years.show');
    });
    // Academic Years - manage actions
    Route::middleware(['auth', 'verified', 'can:academic-years.manage'])->group(function () {
        Route::get('academic-years/create', [AcademicYearController::class, 'create'])->name('academic-years.create');
        Route::post('academic-years', [AcademicYearController::class, 'store'])->name('academic-years.store');
        Route::get('academic-years/{academicYear}/edit', [AcademicYearController::class, 'edit'])->name('academic-years.edit');
        Route::put('academic-years/{academicYear}', [AcademicYearController::class, 'update'])->name('academic-years.update');
        Route::delete('academic-years/{academicYear}', [AcademicYearController::class, 'destroy'])->name('academic-years.destroy');
    });

    // Mata Pelajaran - view actions
    Route::middleware(['auth', 'verified', 'can:subjects.view'])->group(function () {
        Route::get('mata-pelajaran', [SubjectController::class, 'index'])->name('mata-pelajaran.index');
        Route::get('mata-pelajaran/{subject}', [SubjectController::class, 'show'])->name('mata-pelajaran.show');
    });
    // Mata Pelajaran - manage actions
    Route::middleware(['auth', 'verified', 'can:subjects.manage'])->group(function () {
        Route::get('mata-pelajaran/create', [SubjectController::class, 'create'])->name('mata-pelajaran.create');
        Route::post('mata-pelajaran', [SubjectController::class, 'store'])->name('mata-pelajaran.store');
        Route::get('mata-pelajaran/{subject}/edit', [SubjectController::class, 'edit'])->name('mata-pelajaran.edit');
        Route::put('mata-pelajaran/{subject}', [SubjectController::class, 'update'])->name('mata-pelajaran.update');
        Route::delete('mata-pelajaran/{subject}', [SubjectController::class, 'destroy'])->name('mata-pelajaran.destroy');
    });

    // Document Routes
    Route::prefix('documents')->name('documents.')->group(function () {
        Route::get('/', [DocumentController::class, 'index'])->name('index');
        Route::get('/create', [DocumentController::class, 'create'])->name('create')->middleware('can:documents.upload');
        Route::post('/', [DocumentController::class, 'store'])->name('store')->middleware('can:documents.upload');
        Route::get('/{document}', [DocumentController::class, 'show'])->name('show')->middleware('can:documents.view');
        Route::get('/{document}/download', [DocumentController::class, 'download'])->name('download')->middleware('can:documents.download');
        Route::delete('/{document}', [DocumentController::class, 'destroy'])->name('destroy')->middleware('can:documents.delete');

        // Verification routes (Kepala PKBM)
        Route::middleware('can:documents.verify')->group(function () {
            Route::get('/{document}/edit', [DocumentController::class, 'edit'])->name('edit');
            Route::put('/{document}', [DocumentController::class, 'update'])->name('update');
            Route::post('/{document}/verify', [DocumentController::class, 'verify'])->name('verify');
        });
    });

    // Letter Routes
    Route::prefix('letters')->name('letters.')->group(function () {
        Route::get('/', [LetterController::class, 'index'])->name('index');
        Route::get('/create', [LetterController::class, 'create'])->name('create')->middleware('can:letters.create');
        Route::post('/', [LetterController::class, 'store'])->name('store')->middleware('can:letters.create');
        Route::get('/{letter}', [LetterController::class, 'show'])->name('show')->middleware('can:letters.view');
        Route::get('/{letter}/print', [LetterController::class, 'print'])->name('print')->middleware('can:letters.print');
        Route::get('/{letter}/edit', [LetterController::class, 'edit'])->name('edit')->middleware('can:letters.manage');
        Route::put('/{letter}', [LetterController::class, 'update'])->name('update')->middleware('can:letters.manage');
        Route::delete('/{letter}', [LetterController::class, 'destroy'])->name('destroy')->middleware('can:letters.manage');
    });

    // Announcements (auth only, no email verification required)
    Route::middleware('auth')->group(function () {
        Route::resource('pengumuman', AnnouncementController::class)->parameters([
            'pengumuman' => 'announcement',
        ])->names('announcements');
    });

    // Operator Routes
    Route::prefix('operator')->name('operator.')->group(function () {
        Route::get('/dashboard', [OperatorController::class, 'dashboard'])->name('dashboard')->middleware('can:dashboard.view');
        Route::get('/profile', fn () => redirect()->route('profile.edit'))->name('profile')->middleware('can:dashboard.view');
        Route::get('/security/aes-gcm', [OperatorController::class, 'security'])->name('security')->middleware('can:dashboard.view');
        Route::post('/security/encrypt', [OperatorController::class, 'encryptData'])->name('encrypt')->middleware('can:dashboard.view');
        Route::post('/security/decrypt', [OperatorController::class, 'decryptData'])->name('decrypt')->middleware('can:dashboard.view');
        Route::post('/security/avalanche', [OperatorController::class, 'avalanche'])->name('avalanche')->middleware('can:dashboard.view');
        Route::post('/security/benchmark', [OperatorController::class, 'benchmark'])->name('benchmark')->middleware('can:dashboard.view');

        // Bulk Import Data
        Route::get('/import-data', [OperatorController::class, 'importForm'])->name('import.index')->middleware('can:operator.import');
        Route::post('/import-data/preview', [OperatorController::class, 'importPreview'])->name('import.preview')->middleware('can:operator.import');
        Route::post('/import-data/process', [OperatorController::class, 'importProcess'])->name('import.process')->middleware('can:operator.import');

        // Import Tutor khusus
        Route::get('/import-tutor', [OperatorController::class, 'importTutorForm'])->name('import-tutor.index')->middleware('can:operator.import');
        Route::post('/import-tutor/preview', [OperatorController::class, 'importTutorPreview'])->name('import-tutor.preview')->middleware('can:operator.import');
        Route::post('/import-tutor/process', [OperatorController::class, 'importTutorProcess'])->name('import-tutor.process')->middleware('can:operator.import');
        Route::get('/import-tutor/template', [OperatorController::class, 'importTutorTemplate'])->name('import-tutor.template')->middleware('can:operator.import');
    });

    // User & Role Management (Operator / Kepala PKBM)
    Route::middleware('can:users.manage')->group(function () {
        Route::get('users', [UserController::class, 'index'])->name('users.index')->middleware('can:users.view');
        Route::get('users/create', [UserController::class, 'create'])->name('users.create');
        Route::post('users', [UserController::class, 'store'])->name('users.store');
        Route::post('users/create-with-student', [UserController::class, 'storeWithStudent'])->name('users.store-with-student');
        Route::get('users/{user}', [UserController::class, 'show'])->name('users.show')->middleware('can:users.view');
        Route::get('users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
        Route::put('users/{user}', [UserController::class, 'update'])->name('users.update');
        Route::post('users/{user}/reset-password', [UserController::class, 'resetPassword'])->name('users.reset-password');
        Route::patch('users/{user}/status', [UserController::class, 'toggleStatus'])->name('users.toggle-status');
        Route::delete('users/{user}', [UserController::class, 'destroy'])->name('users.destroy');

        Route::get('roles', [RoleController::class, 'index'])->name('roles.index');
        Route::get('roles/create', [RoleController::class, 'create'])->name('roles.create');
        Route::post('roles', [RoleController::class, 'store'])->name('roles.store');
        Route::get('roles/{role}/edit', [RoleController::class, 'edit'])->name('roles.edit');
        Route::put('roles/{role}', [RoleController::class, 'update'])->name('roles.update');
        Route::delete('roles/{role}', [RoleController::class, 'destroy'])->name('roles.destroy');
    });

    // Reports
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/', [ReportController::class, 'index'])->name('index');
        Route::get('/students', [ReportController::class, 'students'])->name('students');
        Route::get('/tutors', [ReportController::class, 'tutors'])->name('tutors');
        Route::get('/classrooms', [ReportController::class, 'classrooms'])->name('classrooms');
        Route::get('/attendance', [ReportController::class, 'attendance'])->name('attendance');
        Route::get('/grades', [ReportController::class, 'grades'])->name('grades');
        Route::get('/assignments', [ReportController::class, 'assignments'])->name('assignments');
        Route::get('/learning', [ReportController::class, 'learningActivity'])->name('learning');
        Route::get('/print/{type}', [ReportController::class, 'print'])->name('print');
    });

    // Classroom Assignments
    Route::middleware(['auth', 'verified', 'can:classrooms.manage'])->group(function () {
        Route::get('/classroom-assignments', [ClassroomAssignmentController::class, 'index'])->name('classroom-assignments.index');
        Route::post('/classroom-assignments/wali-kelas', [ClassroomAssignmentController::class, 'assignWaliKelas'])->name('classroom-assignments.assign-wali-kelas');
        Route::delete('/classroom-assignments/wali-kelas/{classroom}', [ClassroomAssignmentController::class, 'removeWaliKelas'])->name('classroom-assignments.remove-wali-kelas');
        Route::post('/classroom-assignments/teaching', [ClassroomAssignmentController::class, 'storeTeachingAssignment'])->name('classroom-assignments.store-teaching');
        Route::put('/classroom-assignments/teaching/{penugasanMengajar}', [ClassroomAssignmentController::class, 'updateTeachingAssignment'])->name('classroom-assignments.update-teaching');
        Route::patch('/classroom-assignments/teaching/{penugasanMengajar}/toggle-status', [ClassroomAssignmentController::class, 'toggleTeachingStatus'])->name('classroom-assignments.toggle-teaching-status');
        Route::delete('/classroom-assignments/teaching/{penugasanMengajar}', [ClassroomAssignmentController::class, 'destroyTeachingAssignment'])->name('classroom-assignments.destroy-teaching');
        Route::get('/classroom-assignments/create-tutor-subject', [ClassroomAssignmentController::class, 'createTutorSubject'])->name('classroom-assignments.create-tutor-subject');
        Route::post('/classroom-assignments/store-tutor-subject', [ClassroomAssignmentController::class, 'storeTutorSubject'])->name('classroom-assignments.store-tutor-subject');
        Route::get('/classroom-assignments/create-tutor-classroom', [ClassroomAssignmentController::class, 'createTutorClassroom'])->name('classroom-assignments.create-tutor-classroom');
        Route::post('/classroom-assignments/store-tutor-classroom', [ClassroomAssignmentController::class, 'storeTutorClassroom'])->name('classroom-assignments.store-tutor-classroom');
        Route::get('/classroom-assignments/create-subject-classroom', [ClassroomAssignmentController::class, 'createSubjectClassroom'])->name('classroom-assignments.create-subject-classroom');
        Route::post('/classroom-assignments/store-subject-classroom', [ClassroomAssignmentController::class, 'storeSubjectClassroom'])->name('classroom-assignments.store-subject-classroom');
        Route::get('/classroom-assignments/tutor/{tutor}/assignments', [ClassroomAssignmentController::class, 'showAssignments'])->name('classroom-assignments.tutor-assignments');
    });
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::prefix('peserta-didik')->name('peserta-didik.')->group(function () {
        Route::post('quick-store', [ClassroomMemberController::class, 'quickStore'])->name('quick-store');
        Route::get('template', [StudentController::class, 'template'])->name('template');
        Route::get('template/csv', [StudentController::class, 'templateCsv'])->name('template.csv');
        Route::get('import', [StudentController::class, 'importForm'])->name('import');
        Route::post('import/preview', [StudentController::class, 'importPreview'])->name('import.preview');
        Route::post('import/process', [StudentController::class, 'importProcess'])->name('import.process');
        Route::get('penempatan-rombel', [StudentController::class, 'bulkAssignForm'])->name('bulk-assign')->middleware('can:students.update');
        Route::post('penempatan-rombel/process', [StudentController::class, 'bulkAssignProcess'])->name('bulk-assign.process')->middleware('can:students.update');

        Route::get('penautan-akun', [StudentAccountLinkController::class, 'index'])->name('link-accounts')->middleware('can:students.manage');
        Route::post('penautan-akun', [StudentAccountLinkController::class, 'store'])->name('link-accounts.store')->middleware('can:students.manage');
        Route::delete('penautan-akun/{peserta_didik}', [StudentAccountLinkController::class, 'unlink'])->name('link-accounts.destroy')->middleware('can:students.manage');
    });
    Route::resource('peserta-didik', StudentController::class)->parameters([
        'peserta-didik' => 'peserta_didik',
    ]);
});

Route::middleware(['auth', 'verified'])->group(function () {
    // Penugasan Mengajar (Teaching Assignments) - view actions
    Route::middleware('can:classrooms.manage')->group(function () {
        Route::get('/penugasan-mengajar', [PenugasanMengajarController::class, 'index'])->name('penugasan-mengajar.index');
        Route::get('/penugasan-mengajar/{penugasanMengajar}', [PenugasanMengajarController::class, 'show'])->name('penugasan-mengajar.show');
    });
    // Penugasan Mengajar - manage actions
    Route::middleware('can:classrooms.manage')->group(function () {
        Route::get('/penugasan-mengajar/create', [PenugasanMengajarController::class, 'create'])->name('penugasan-mengajar.create');
        Route::post('/penugasan-mengajar', [PenugasanMengajarController::class, 'store'])->name('penugasan-mengajar.store');
        Route::get('/penugasan-mengajar/{penugasanMengajar}/edit', [PenugasanMengajarController::class, 'edit'])->name('penugasan-mengajar.edit');
        Route::put('/penugasan-mengajar/{penugasanMengajar}', [PenugasanMengajarController::class, 'update'])->name('penugasan-mengajar.update');
        Route::delete('/penugasan-mengajar/{penugasanMengajar}', [PenugasanMengajarController::class, 'destroy'])->name('penugasan-mengajar.destroy');
    });
});

require __DIR__.'/auth.php';

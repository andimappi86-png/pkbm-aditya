<?php

use App\Http\Controllers\AnnouncementController;
use App\Http\Controllers\AssignmentController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\ClassroomAssignmentController;
use App\Http\Controllers\ClassroomController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\ForumCommentController;
use App\Http\Controllers\ForumController;
use App\Http\Controllers\GradeController;
use App\Http\Controllers\LetterController;
use App\Http\Controllers\MaterialController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\OperatorController;
use App\Http\Controllers\PenugasanMengajarController;
use App\Http\Controllers\PpdbController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\QuestionController;
use App\Http\Controllers\QuizAttemptController;
use App\Http\Controllers\QuizController;
use App\Http\Controllers\RaporController;
use App\Http\Controllers\RekapAkademikController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\SubjectController;
use App\Http\Controllers\SubmissionController;
use App\Http\Controllers\TutorController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('landing');
})->name('landing');

// Dashboard routes with RBAC
Route::middleware(['auth', 'verified'])->group(function () {
    // Main dashboard - redirects based on role
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/operator/login', [OperatorController::class, 'showLoginForm'])->name('operator.login.form')->withoutMiddleware(['auth', 'verified']);
    Route::post('/operator/login', [OperatorController::class, 'login'])->name('operator.login')->withoutMiddleware(['auth', 'verified']);

    // Role-specific dashboards
    Route::get('/dashboard/yayasan', [DashboardController::class, 'yayasan'])
        ->middleware('can:dashboard.view')
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

    Route::get('/dashboard/siswa', [DashboardController::class, 'siswa'])
        ->middleware('can:dashboard.view')
        ->name('dashboard.siswa');

    Route::resource('jadwal', ScheduleController::class)->parameters([
        'jadwal' => 'schedule',
    ]);

    Route::resource('absensi', AttendanceController::class)->parameters([
        'absensi' => 'attendance',
    ]);

    Route::resource('nilai', GradeController::class)->parameters([
        'nilai' => 'grade',
    ]);

    Route::resource('materi', MaterialController::class)->parameters([
        'materi' => 'material',
    ]);

    Route::resource('quiz', QuizController::class)->parameters([
        'quiz' => 'quiz',
    ]);

    Route::prefix('quiz/{quiz}')->name('quiz.')->group(function () {
        Route::resource('questions', QuestionController::class)->parameters([
            'questions' => 'question',
        ])->only(['create', 'store', 'edit', 'update', 'destroy']);

        Route::post('start', [QuizAttemptController::class, 'start'])->name('attempts.start');
        Route::post('attempts/{attempt}/answer', [QuizAttemptController::class, 'answer'])->name('attempts.answer');
        Route::post('attempts/{attempt}/submit', [QuizAttemptController::class, 'submit'])->name('attempts.submit');
        Route::get('attempts/{attempt}', [QuizAttemptController::class, 'show'])->name('attempts.show');
    });

    Route::resource('forum', ForumController::class)->parameters([
        'forum' => 'forum',
    ]);

    Route::post('/forum/{forum}/comments', [ForumCommentController::class, 'store'])->name('forum.comments.store');
    Route::delete('/forum/comments/{comment}', [ForumCommentController::class, 'destroy'])->name('forum.comments.destroy');

    Route::resource('tugas', AssignmentController::class)->parameters([
        'tugas' => 'assignment',
    ]);

    Route::resource('pengumpulan', SubmissionController::class)->only(['store', 'update', 'destroy'])->parameters([
        'pengumpulan' => 'submission',
    ]);

    Route::get('/rekap', [RekapAkademikController::class, 'index'])->name('rekap.index');
    Route::get('/rekap/{student}', [RekapAkademikController::class, 'show'])->name('rekap.show');

    Route::get('/rapor', [RaporController::class, 'index'])->name('rapor.index');
    Route::get('/rapor/{student}', [RaporController::class, 'show'])->name('rapor.show');
    Route::get('/rapor/{student}/print', [RaporController::class, 'print'])->name('rapor.print');

    Route::resource('tutors', TutorController::class)->parameters([
        'tutors' => 'tutor',
    ]);

    Route::resource('rombel', ClassroomController::class)->parameters([
        'rombel' => 'classroom',
    ]);

    Route::resource('mata-pelajaran', SubjectController::class)->parameters([
        'mata-pelajaran' => 'subject',
    ]);

    // PPDB Routes
    Route::prefix('ppdb')->name('ppdb.')->group(function () {
        Route::get('/', [PpdbController::class, 'index'])->name('index');
        Route::get('/daftar', [PpdbController::class, 'create'])->name('create');
        Route::post('/', [PpdbController::class, 'store'])->name('store');
        Route::get('/{ppdb}', [PpdbController::class, 'show'])->name('show');
        Route::get('/{ppdb}/bukti', [PpdbController::class, 'bukti'])->name('bukti');

        // Verification routes (Kepala PKBM)
        Route::middleware('can:admissions.manage')->group(function () {
            Route::get('/{ppdb}/edit', [PpdbController::class, 'edit'])->name('edit');
            Route::put('/{ppdb}', [PpdbController::class, 'update'])->name('update');
            Route::post('/{ppdb}/verify', [PpdbController::class, 'verify'])->name('verify');
        });
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

    // Announcements
    Route::resource('pengumuman', AnnouncementController::class)->parameters([
        'pengumuman' => 'announcement',
    ])->names('announcements');

    // Operator Routes
    Route::prefix('operator')->name('operator.')->group(function () {
        Route::get('/dashboard', [OperatorController::class, 'dashboard'])->name('dashboard')->middleware('can:dashboard.view');
        Route::get('/security/aes-gcm', [OperatorController::class, 'security'])->name('security')->middleware('can:dashboard.view');
        Route::post('/security/encrypt', [OperatorController::class, 'encryptData'])->name('encrypt')->middleware('can:dashboard.view');
        Route::post('/security/decrypt', [OperatorController::class, 'decryptData'])->name('decrypt')->middleware('can:dashboard.view');
        Route::post('/security/avalanche', [OperatorController::class, 'avalanche'])->name('avalanche')->middleware('can:dashboard.view');
        Route::post('/security/benchmark', [OperatorController::class, 'benchmark'])->name('benchmark')->middleware('can:dashboard.view');
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
        Route::get('/ppdb', [ReportController::class, 'ppdb'])->name('ppdb');
        Route::get('/learning', [ReportController::class, 'learningActivity'])->name('learning');
        Route::get('/print/{type}', [ReportController::class, 'print'])->name('print');
    });

    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('notifications.markAllRead');
    Route::post('/notifications/{notification}/mark-read', [NotificationController::class, 'markAsRead'])->name('notifications.markRead');

    // Classroom Assignments
    Route::middleware(['auth', 'verified'])->group(function () {
        Route::get('/classroom-assignments', [ClassroomAssignmentController::class, 'index'])->name('classroom-assignments.index');
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

    Route::resource('peserta-didik', StudentController::class)->parameters([
        'peserta-didik' => 'peserta_didik',
    ]);
});

Route::middleware(['auth', 'verified'])->group(function () {
    // Penugasan Mengajar (Teaching Assignments)
    Route::get('/penugasan-mengajar', [PenugasanMengajarController::class, 'index'])->name('penugasan-mengajar.index');
    Route::get('/penugasan-mengajar/create', [PenugasanMengajarController::class, 'create'])->name('penugasan-mengajar.create');
    Route::post('/penugasan-mengajar', [PenugasanMengajarController::class, 'store'])->name('penugasan-mengajar.store');
    Route::get('/penugasan-mengajar/{penugasanMengajar}', [PenugasanMengajarController::class, 'show'])->name('penugasan-mengajar.show');
    Route::get('/penugasan-mengajar/{penugasanMengajar}/edit', [PenugasanMengajarController::class, 'edit'])->name('penugasan-mengajar.edit');
    Route::put('/penugasan-mengajar/{penugasanMengajar}', [PenugasanMengajarController::class, 'update'])->name('penugasan-mengajar.update');
    Route::delete('/penugasan-mengajar/{penugasanMengajar}', [PenugasanMengajarController::class, 'destroy'])->name('penugasan-mengajar.destroy');
});

require __DIR__.'/auth.php';

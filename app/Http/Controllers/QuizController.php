<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreQuizRequest;
use App\Http\Requests\UpdateQuizRequest;
use App\Models\Classroom;
use App\Models\PenugasanMengajar;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\Tutor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\DB;

class QuizController extends Controller
{
    public function index(Request $request)
    {
        Gate::authorize('quizzes.view');

        $user = $request->user();
        $query = Quiz::with(['penugasanMengajar.subject', 'penugasanMengajar.classroom'])
            ->withCount('attempts');

        if ($request->filled('penugasan_mengajar_id')) {
            $query->where('penugasan_mengajar_id', $request->penugasan_mengajar_id);
        }

        if ($request->filled('status')) {
            $now = now();
            switch ($request->status) {
                case 'draf':
                    $query->where('status_aktif', false);
                    break;
                case 'dijadwalkan':
                    $query->where('status_aktif', true)
                        ->where('buka_waktu', '>', $now);
                    break;
                case 'terbit':
                    $query->where('status_aktif', true)
                        ->where(function ($q) use ($now) {
                            $q->whereNull('buka_waktu')
                              ->orWhere('buka_waktu', '<=', $now);
                        })
                        ->where(function ($q) use ($now) {
                            $q->whereNull('tutup_waktu')
                              ->orWhere('tutup_waktu', '>=', $now);
                        });
                    break;
                case 'ditutup':
                    $query->where(function ($q) use ($now) {
                        $q->where('status_aktif', false)
                          ->orWhere('tutup_waktu', '<', $now);
                    });
                    break;
            }
        }

        $this->filterAccess($query, $user);

        $quizzes = $query->latest()->paginate(15)->withQueryString();
        $penugasans = $this->getMyPenugasans($user);

        return view('quizzes.index', compact('quizzes', 'penugasans'));
    }

    public function create(Request $request)
    {
        Gate::authorize('create', Quiz::class);
        $penugasans = $this->getMyPenugasans($request->user());

        return view('quizzes.create', compact('penugasans'));
    }

    public function store(StoreQuizRequest $request)
    {
        Gate::authorize('create', Quiz::class);
        $validated = $request->validated();

        $penugasanId = $validated['penugasan_mengajar_id'] ?? null;
        if (! $penugasanId) {
            $user = $request->user();
            if ($user && $user->hasRole('tutor')) {
                $tutor = Tutor::where('user_id', $user->id)->first();
                $penugasanId = PenugasanMengajar::where('tutor_id', $tutor?->id)
                    ->where('status_aktif', true)
                    ->value('id') ?? PenugasanMengajar::where('tutor_id', $tutor?->id)->value('id');
            } elseif ($user && ($user->hasRole('operator') || $user->hasRole('admin') || $user->hasRole('kepala-pkbm'))) {
                $penugasanId = PenugasanMengajar::where('status_aktif', true)->value('id')
                    ?? PenugasanMengajar::value('id');
            }
        }

        if (! $penugasanId) {
            return back()->withInput()->with('error', 'Tidak ditemukan penugasan mengajar aktif untuk akun ini.');
        }

        $penugasan = PenugasanMengajar::findOrFail($penugasanId);
        $this->authorizePenugasan($penugasan, $request->user());

        $validated['penugasan_mengajar_id'] = $penugasanId;

        $quiz = Quiz::create($validated);

        $questionsData = $request->input('questions');
        if (! $questionsData && $request->filled('questions_data')) {
            $decoded = json_decode((string) $request->input('questions_data'), true);
            if (is_array($decoded)) {
                $questionsData = $decoded;
            }
        }

        if (is_array($questionsData)) {
            foreach ($questionsData as $q) {
                if (! is_array($q)) {
                    continue;
                }
                $teks = trim((string) ($q['pertanyaan'] ?? ''));
                if ($teks === '') {
                    continue;
                }

                $tipe = (string) ($q['tipe'] ?? 'pilihan_ganda');
                $bobot = max(1, (int) ($q['bobot'] ?? 10));

                $question = $quiz->questions()->create([
                    'pertanyaan' => $teks,
                    'tipe' => $tipe,
                    'bobot' => $bobot,
                ]);

                if ($tipe === 'pilihan_ganda' && ! empty($q['options']) && is_array($q['options'])) {
                    foreach ($q['options'] as $opt) {
                        if (is_array($opt)) {
                            $question->options()->create([
                                'teks' => (string) ($opt['teks'] ?? ''),
                                'is_benar' => ! empty($opt['is_benar']),
                            ]);
                        }
                    }
                } elseif ($tipe === 'benar_salah') {
                    $key = (string) ($q['benar_salah_key'] ?? 'benar');
                    $question->options()->create(['teks' => 'Benar', 'is_benar' => $key === 'benar']);
                    $question->options()->create(['teks' => 'Salah', 'is_benar' => $key === 'salah']);
                } elseif ($tipe === 'isian_singkat' && ! empty($q['kunci_jawaban'])) {
                    $question->options()->create([
                        'teks' => (string) $q['kunci_jawaban'],
                        'is_benar' => true,
                    ]);
                }
            }
        }

        return redirect()->route('quiz.index')->with('success', 'Quiz berhasil dibuat.');
    }

    public function show(Quiz $quiz)
    {
        Gate::authorize('view', $quiz);

        $quiz->load(['questions.options', 'penugasanMengajar.subject', 'penugasanMengajar.classroom']);

        $attemptStats = [
            'total' => $quiz->attempts()->count(),
            'in_progress' => $quiz->attempts()->where('selesai', false)->count(),
            'completed' => $quiz->attempts()->where('selesai', true)->count(),
            'pending_grading' => $quiz->attempts()
                ->whereHas('answers', fn ($q) => $q->where('question_id', function ($sq) {
                    $sq->select('id')->from('questions')->where('tipe', 'esai');
                }))
                ->where('selesai', true)
                ->count(),
        ];

        return view('quizzes.show', compact('quiz', 'attemptStats'));
    }

    public function edit(Quiz $quiz)
    {
        Gate::authorize('update', $quiz);
        $penugasans = $this->getMyPenugasans(auth()->user());

        return view('quizzes.edit', compact('quiz', 'penugasans'));
    }

    public function update(UpdateQuizRequest $request, Quiz $quiz)
    {
        Gate::authorize('update', $quiz);

        $quiz->update($request->validated());

        return redirect()->route('quiz.show', $quiz)->with('success', 'Quiz diperbarui.');
    }

    public function destroy(Quiz $quiz)
    {
        Gate::authorize('delete', $quiz);

        if ($quiz->attempts()->exists()) {
            return back()->with('error', 'Tidak dapat menghapus quiz yang sudah memiliki percobaan.');
        }

        $quiz->delete();

        return redirect()->route('quiz.index')->with('success', 'Quiz dihapus.');
    }

    public function publish(Quiz $quiz)
    {
        Gate::authorize('publish', $quiz);

        if ($quiz->status_aktif) {
            return back()->with('info', 'Quiz sudah diterbitkan.');
        }

        if ($quiz->questions()->count() === 0) {
            return back()->with('error', 'Quiz harus memiliki minimal 1 soal sebelum diterbitkan.');
        }

        $quiz->update(['status_aktif' => true]);

        return redirect()->route('quiz.show', $quiz)->with('success', 'Quiz berhasil diterbitkan.');
    }

    public function archive(Quiz $quiz)
    {
        Gate::authorize('archive', $quiz);

        if (! $quiz->status_aktif) {
            return back()->with('info', 'Quiz sudah diarsipkan.');
        }

        $quiz->update(['status_aktif' => false]);

        return redirect()->route('quiz.show', $quiz)->with('success', 'Quiz diarsipkan.');
    }

    public function preview(Quiz $quiz)
    {
        Gate::authorize('preview', $quiz);

        $quiz->load(['questions' => fn ($q) => $q->with('options')->inRandomOrder()]);

        return view('quizzes.preview', compact('quiz'));
    }

    public function gradeEssay(Quiz $quiz)
    {
        Gate::authorize('gradeEssay', $quiz);

        $quiz->load(['penugasanMengajar.subject', 'penugasanMengajar.classroom']);

        $attempts = QuizAttempt::with(['student.user', 'answers.question'])
            ->where('quiz_id', $quiz->id)
            ->where('selesai', true)
            ->whereHas('answers.question', fn ($q) => $q->where('tipe', 'esai'))
            ->orderBy('mulai_waktu')
            ->paginate(20);

        return view('quizzes.essay-grade.index', compact('quiz', 'attempts'));
    }

    public function recap(Quiz $quiz)
    {
        Gate::authorize('viewRecap', $quiz);

        $quiz->load(['penugasanMengajar.subject', 'penugasanMengajar.classroom', 'questions']);

        $attempts = QuizAttempt::with(['student.user', 'answers.question'])
            ->where('quiz_id', $quiz->id)
            ->where('selesai', true)
            ->orderBy('nilai', 'desc')
            ->get();

        $stats = [
            'total_peserta' => $quiz->penugasanMengajar->classroom->students()->count(),
            'sudah_mengerjakan' => $attempts->count(),
            'belum_mengerjakan' => $quiz->penugasanMengajar->classroom->students()->count() - $attempts->count(),
            'rata_rata_nilai' => $attempts->avg('nilai') ?? 0,
            'nilai_tertinggi' => $attempts->max('nilai') ?? 0,
            'nilai_terendah' => $attempts->min('nilai') ?? 0,
        ];

        return view('quizzes.recap', compact('quiz', 'attempts', 'stats'));
    }

    private function getMyPenugasans($user)
    {
        $query = PenugasanMengajar::with(['subject', 'classroom']);
        if ($user->hasRole('tutor')) {
            $tutor = Tutor::where('user_id', $user->id)->first();
            $query->where('tutor_id', $tutor?->id);
        }

        return $query->where('status_aktif', true)->get();
    }

    private function filterAccess($query, $user)
    {
        if ($user->hasRole('peserta-didik')) {
            $student = $user->student;
            $query->whereHas('penugasanMengajar', fn ($q) => $q->where('classroom_id', $student?->rombel_id));
        } elseif ($user->hasRole('tutor')) {
            $tutor = Tutor::where('user_id', $user->id)->first();
            $query->whereHas('penugasanMengajar', fn ($q) => $q->where('tutor_id', $tutor?->id));
        } elseif ($user->hasRole('wali-kelas')) {
            $classroomIds = Classroom::where('wali_kelas_id', $user->id)->pluck('id');
            $query->whereHas('penugasanMengajar', fn ($q) => $q->whereIn('classroom_id', $classroomIds));
        }
    }

    private function authorizePenugasan($penugasan, $user)
    {
        if ($user->hasRole('tutor')) {
            $tutor = Tutor::where('user_id', $user->id)->first();
            abort_if($penugasan->tutor_id !== $tutor?->id, 403);
        }
    }
}
<?php

namespace App\Policies;

use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\User;
use Illuminate\Support\Carbon;

class QuizAttemptPolicy
{
    public function view(User $user, QuizAttempt $quizAttempt): bool
    {
        if ($user->hasRole('peserta-didik')) {
            return $quizAttempt->student_id === $user->student?->id;
        }

        if ($user->hasRole('tutor')) {
            return $quizAttempt->quiz->penugasanMengajar->tutor_id === $user->tutor?->id;
        }

        if ($user->hasRole('wali-kelas')) {
            return $quizAttempt->quiz->penugasanMengajar->classroom?->wali_kelas_id === $user->id;
        }

        return $user->hasAnyRole(['operator', 'kepala-pkbm']);
    }

    public function create(User $user, Quiz $quiz): bool
    {
        if (! $user->hasRole('peserta-didik') || ! $user->can('quizzes.take')) {
            return false;
        }

        if ($user->student === null) {
            return false;
        }

        if ($user->student->rombel_id !== $quiz->penugasanMengajar->classroom_id) {
            return false;
        }

        if (! $quiz->isOpen()) {
            return false;
        }

        // Check max attempts
        $attemptCount = QuizAttempt::where('quiz_id', $quiz->id)
            ->where('student_id', $user->student->id)
            ->count();

        if ($attemptCount >= $quiz->max_attempts) {
            return false;
        }

        // Check if there's an incomplete attempt
        $incompleteAttempt = QuizAttempt::where('quiz_id', $quiz->id)
            ->where('student_id', $user->student->id)
            ->where('selesai', false)
            ->first();

        if ($incompleteAttempt && ! $incompleteAttempt->isExpired()) {
            return false;
        }

        return true;
    }

    public function update(User $user, QuizAttempt $quizAttempt): bool
    {
        if (! $user->hasRole('peserta-didik')) {
            return false;
        }

        if ($quizAttempt->student_id !== $user->student?->id) {
            return false;
        }

        if ($quizAttempt->selesai) {
            return false;
        }

        if ($quizAttempt->isExpired()) {
            return false;
        }

        // Check quiz is still open
        if (! $quizAttempt->quiz->isOpen()) {
            return false;
        }

        return true;
    }

    public function submit(User $user, QuizAttempt $quizAttempt): bool
    {
        return $this->update($user, $quizAttempt);
    }

    public function viewResult(User $user, QuizAttempt $quizAttempt): bool
    {
        if ($user->hasRole('peserta-didik')) {
            return $quizAttempt->student_id === $user->student?->id
                && $quizAttempt->quiz->tampilkan_nilai !== 'never';
        }

        if ($user->hasRole('tutor')) {
            return $quizAttempt->quiz->penugasanMengajar->tutor_id === $user->tutor?->id;
        }

        if ($user->hasRole('wali-kelas')) {
            return $quizAttempt->quiz->penugasanMengajar->classroom?->wali_kelas_id === $user->id;
        }

        return $user->hasAnyRole(['operator', 'kepala-pkbm']);
    }

    public function viewAnswers(User $user, QuizAttempt $quizAttempt): bool
    {
        if ($user->hasRole('peserta-didik')) {
            return $quizAttempt->student_id === $user->student?->id
                && $quizAttempt->quiz->tampilkan_jawaban !== 'never'
                && ($quizAttempt->quiz->tampilkan_jawaban === 'after_submit' || $quizAttempt->quiz->isClosed());
        }

        return $user->hasAnyRole(['tutor', 'wali-kelas', 'operator', 'kepala-pkbm']);
    }
}
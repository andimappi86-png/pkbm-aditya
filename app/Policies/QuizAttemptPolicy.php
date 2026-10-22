<?php

namespace App\Policies;

use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\User;

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

        return $user->student !== null
            && $user->student->rombel_id === $quiz->penugasanMengajar->classroom_id;
    }

    public function update(User $user, QuizAttempt $quizAttempt): bool
    {
        return $user->hasRole('peserta-didik')
            && $quizAttempt->student_id === $user->student?->id
            && ! $quizAttempt->selesai;
    }
}

<?php

namespace App\Notifications;

use App\Models\Quiz;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class QuizDeadlineNotification extends Notification
{
    use Queueable;

    public function __construct(public Quiz $quiz) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'title' => 'Deadline Kuis Mendekat',
            'message' => "Kuis {$this->quiz->judul} berakhir dalam 24 jam.",
            'url' => route('quiz.index'),
            'icon' => 'clock',
            'type' => 'warning',
        ];
    }
}

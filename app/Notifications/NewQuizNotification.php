<?php

namespace App\Notifications;

use App\Models\Quiz;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NewQuizNotification extends Notification
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
            'title' => 'Kuis Baru',
            'message' => "Kuis {$this->quiz->judul} telah ditambahkan.",
            'url' => route('quiz.index'),
            'icon' => 'clipboard',
            'type' => 'info',
        ];
    }
}

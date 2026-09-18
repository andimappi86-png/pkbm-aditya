<?php

namespace App\Notifications;

use App\Models\Submission;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class SubmissionGradedNotification extends Notification
{
    use Queueable;

    public function __construct(public Submission $submission) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'title' => 'Tugas Dinilai',
            'message' => "Tugas {$this->submission->assignment->judul} Anda telah dinilai.",
            'url' => route('tugas.show', $this->submission->assignment_id),
            'icon' => 'check-circle',
            'type' => 'success',
        ];
    }
}

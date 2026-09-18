<?php

namespace App\Notifications;

use App\Models\Assignment;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class AssignmentDeadlineNotification extends Notification
{
    use Queueable;

    public function __construct(public Assignment $assignment) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'title' => 'Deadline Tugas Mendekat',
            'message' => "Tugas {$this->assignment->judul} berakhir dalam 24 jam.",
            'url' => route('tugas.show', $this->assignment->id),
            'icon' => 'clock',
            'type' => 'warning',
        ];
    }
}

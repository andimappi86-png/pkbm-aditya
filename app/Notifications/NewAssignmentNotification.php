<?php

namespace App\Notifications;

use App\Models\Assignment;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NewAssignmentNotification extends Notification
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
            'title' => 'Tugas Baru',
            'message' => "Tugas {$this->assignment->judul} telah ditambahkan.",
            'url' => route('tugas.show', $this->assignment->id),
            'icon' => 'clipboard',
            'type' => 'info',
        ];
    }
}

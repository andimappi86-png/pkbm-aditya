<?php

namespace App\Notifications;

use App\Models\Grade;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NewGradeNotification extends Notification
{
    use Queueable;

    public function __construct(public Grade $grade) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'title' => 'Nilai Baru',
            'message' => "Nilai akhir untuk mata pelajaran {$this->grade->penugasanMengajar->subject->nama} telah dirilis.",
            'url' => route('nilai.index'),
            'icon' => 'chart-bar',
            'type' => 'success',
        ];
    }
}

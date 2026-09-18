<?php

namespace App\Notifications;

use App\Models\Schedule;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ScheduleReminderNotification extends Notification
{
    use Queueable;

    public function __construct(public Schedule $schedule) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'title' => 'Pengingat Jadwal',
            'message' => "Jadwal belajar {$this->schedule->penugasanMengajar->subject->nama} akan dimulai besok.",
            'url' => route('jadwal.index'),
            'icon' => 'calendar',
            'type' => 'info',
        ];
    }
}

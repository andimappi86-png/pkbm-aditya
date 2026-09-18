<?php

namespace App\Notifications;

use App\Models\Forum;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NewForumPostNotification extends Notification
{
    use Queueable;

    public function __construct(public Forum $forum) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'title' => 'Diskusi Baru',
            'message' => "Diskusi baru: {$this->forum->judul}.",
            'url' => route('forum.show', $this->forum->id),
            'icon' => 'user-group',
            'type' => 'info',
        ];
    }
}

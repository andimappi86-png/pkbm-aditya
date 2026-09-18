<?php

namespace App\Notifications;

use App\Models\Material;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NewMaterialNotification extends Notification
{
    use Queueable;

    public function __construct(public Material $material) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'title' => 'Materi Baru',
            'message' => "Materi {$this->material->judul} telah ditambahkan.",
            'url' => route('materi.show', $this->material->id),
            'icon' => 'book-open',
            'type' => 'info',
        ];
    }
}

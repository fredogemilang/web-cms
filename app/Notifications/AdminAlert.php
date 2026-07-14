<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

/**
 * Generic database-only admin alert that the NotificationsDropdown renders.
 *
 * Usage:
 *   $admin->notify(new AdminAlert(
 *       title:   'New form submission',
 *       message: 'Contact form received from John Doe',
 *       icon:    'forum',          // material-symbols-outlined name
 *       color:   'blue',           // blue|green|red|orange
 *       url:     route('admin.forms.entries', $form),
 *   ));
 */
class AdminAlert extends Notification
{
    use Queueable;

    public function __construct(
        public string $title,
        public string $message = '',
        public string $icon = 'info',
        public string $color = 'blue',
        public ?string $url = null,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => $this->title,
            'message' => $this->message,
            'icon' => $this->icon,
            'color' => $this->color,
            'url' => $this->url,
        ];
    }
}

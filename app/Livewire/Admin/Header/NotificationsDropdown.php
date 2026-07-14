<?php

namespace App\Livewire\Admin\Header;

use Livewire\Attributes\On;
use Livewire\Component;

class NotificationsDropdown extends Component
{
    public bool $open = false;

    public function toggle(): void
    {
        $this->open = ! $this->open;
    }

    public function markAsRead(string $id): void
    {
        $notification = auth()->user()?->notifications()->where('id', $id)->first();
        $notification?->markAsRead();
    }

    public function markAllAsRead(): void
    {
        auth()->user()?->unreadNotifications->markAsRead();
        $this->dispatch('notify', ['type' => 'success', 'message' => 'All notifications marked as read.']);
    }

    /** Allow other components to refresh the bell after pushing a notification. */
    #[On('notification-sent')]
    public function refresh(): void
    {
        // Livewire auto-rerender on event
    }

    public function render()
    {
        $user = auth()->user();

        return view('livewire.admin.header.notifications-dropdown', [
            'unreadCount' => $user?->unreadNotifications()->count() ?? 0,
            'recent' => $user?->notifications()->limit(8)->get() ?? collect(),
        ]);
    }
}

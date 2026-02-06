<?php

namespace Plugins\Posts\Livewire;

use Livewire\Component;
use Plugins\Posts\Models\Setting;

class Settings extends Component
{
    // General
    public $posts_per_page = 10;
    public $archive_slug = 'blog';
    public $date_format = 'M d, Y';

    // Comments
    public $enable_comments = true;
    public $comment_moderation = true;
    public $close_comments_days = 0; // 0 = never

    // Feed
    public $rss_full_text = false;
    public $rss_items = 10;

    public function mount()
    {
        $this->posts_per_page = Setting::get('posts_per_page', 10);
        $this->archive_slug = Setting::get('archive_slug', 'blog');
        $this->date_format = Setting::get('date_format', 'M d, Y');

        $this->enable_comments = (bool) Setting::get('enable_comments', true);
        $this->comment_moderation = (bool) Setting::get('comment_moderation', true);
        $this->close_comments_days = (int) Setting::get('close_comments_days', 0);

        $this->rss_full_text = (bool) Setting::get('rss_full_text', false);
        $this->rss_items = (int) Setting::get('rss_items', 10);
    }

    public function save()
    {
        $this->validate([
            'posts_per_page' => 'required|integer|min:1',
            'archive_slug' => 'required|string|max:255',
            'date_format' => 'required|string',
            'enable_comments' => 'boolean',
            'comment_moderation' => 'boolean',
            'close_comments_days' => 'integer|min:0',
            'rss_full_text' => 'boolean',
            'rss_items' => 'required|integer|min:1',
        ]);

        Setting::set('posts_per_page', $this->posts_per_page);
        Setting::set('archive_slug', $this->archive_slug);
        Setting::set('date_format', $this->date_format);

        Setting::set('enable_comments', $this->enable_comments);
        Setting::set('comment_moderation', $this->comment_moderation);
        Setting::set('close_comments_days', $this->close_comments_days);

        Setting::set('rss_full_text', $this->rss_full_text);
        Setting::set('rss_items', $this->rss_items);

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Settings saved successfully.'
        ]);
    }

    public function render()
    {
        return view('posts::livewire.settings');
    }
}

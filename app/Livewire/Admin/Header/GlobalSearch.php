<?php

namespace App\Livewire\Admin\Header;

use App\Models\CptEntry;
use App\Models\Form;
use App\Models\Page;
use App\Models\User;
use Livewire\Component;

class GlobalSearch extends Component
{
    public string $query = '';

    public bool $open = false;

    public function updatedQuery(): void
    {
        $this->open = trim($this->query) !== '';
    }

    public function close(): void
    {
        $this->open = false;
        $this->query = '';
    }

    public function render()
    {
        $q = trim($this->query);
        $results = ['pages' => [], 'users' => [], 'forms' => [], 'cpt_entries' => []];

        if ($q !== '' && strlen($q) >= 2) {
            $user = auth()->user();
            $like = '%'.str_replace(['%', '_'], ['\\%', '\\_'], $q).'%';

            if ($user?->hasPermission('pages.view') || $user?->isSuperAdmin()) {
                $results['pages'] = Page::query()
                    ->where(fn ($w) => $w->where('title', 'like', $like)->orWhere('slug', 'like', $like))
                    ->latest('updated_at')
                    ->limit(4)
                    ->get(['id', 'title', 'slug', 'status']);
            }

            if ($user?->hasPermission('users.view') || $user?->isSuperAdmin()) {
                $results['users'] = User::query()
                    ->where(fn ($w) => $w->where('name', 'like', $like)
                        ->orWhere('email', 'like', $like)
                        ->orWhere('username', 'like', $like))
                    ->limit(4)
                    ->get(['id', 'name', 'email', 'username']);
            }

            if ($user?->hasPermission('forms.view') || $user?->isSuperAdmin()) {
                $results['forms'] = Form::query()
                    ->where('name', 'like', $like)
                    ->limit(4)
                    ->get(['id', 'name', 'slug']);
            }

            $results['cpt_entries'] = CptEntry::query()
                ->with('postType:id,slug,plural_label')
                ->where(fn ($w) => $w->where('title', 'like', $like)->orWhere('slug', 'like', $like))
                ->latest('updated_at')
                ->limit(4)
                ->get(['id', 'title', 'slug', 'post_type_id', 'status']);
        }

        $totalResults = array_sum(array_map(fn ($r) => $r instanceof \Countable || is_array($r) ? count($r) : $r->count(), $results));

        return view('livewire.admin.header.global-search', [
            'results' => $results,
            'totalResults' => $totalResults,
            'isSearching' => $q !== '',
            'isTooShort' => $q !== '' && strlen($q) < 2,
        ]);
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\CptEntry;
use App\Models\CustomPostType;
use App\Models\Page;

/**
 * Public homepage controller. Pulls the active theme's home view with:
 *   - $page          — Page (slug=home) with eager-loaded top-level blocks
 *   - $testimonials  — latest 6 published testimonial CPT entries
 *   - $partners      — all published "our-partners" CPT entries
 *
 * Themes can rely on these always being defined (empty collection if no data).
 */
class HomeController extends Controller
{
    public function index()
    {
        return view('iccom::pages.home', [
            'testimonials' => $this->latestEntries('testimonials', 6),
            'partners' => $this->latestEntries('our-partners'),
            'page' => $this->loadHomePage(),
        ]);
    }

    protected function latestEntries(string $cptSlug, ?int $limit = null)
    {
        $cpt = CustomPostType::where('slug', $cptSlug)->first();
        if (! $cpt) {
            return collect();
        }

        $q = CptEntry::with('author')
            ->where('post_type_id', $cpt->id)
            ->where('status', 'published')
            ->latest();

        if ($limit) {
            $q->take($limit);
        }

        return $q->get();
    }

    protected function loadHomePage(): ?Page
    {
        return Page::with(['blocks' => function ($q) {
            $q->whereNull('parent_block_id')->orderBy('order');
        }])
            ->where('slug', 'home')
            ->first();
    }
}

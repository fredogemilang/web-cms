<?php

namespace App\Http\Controllers;

use App\Models\Page;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;

class PageController extends Controller
{
    public function show(string $slug)
    {
        $page = Page::where('slug', $slug)
            ->published()
            ->with(['blocks' => function ($q) {
                $q->whereNull('parent_block_id')
                    ->with('childBlocks')
                    ->orderBy('order');
            }])
            ->firstOrFail();

        // Check template hierarchy
        $viewName = $this->resolveTemplate($page->template, $slug);

        return view($viewName, [
            'page' => $page,
            'blocks' => $page->blocks,
        ]);
    }

    protected function resolveTemplate(string $template, string $slug): string
    {
        // Try to find the most specific template first
        $themeNamespace = 'iccom';
        
        $candidates = [
            // Theme Specific
            "{$themeNamespace}::pages.{$slug}",
            "{$themeNamespace}::pages.template-{$template}",
            "{$themeNamespace}::pages.single",

            // Default
            "pages.{$slug}",                    // page-about-us.blade.php
            "pages.template-{$template}",       // page-template-landing.blade.php
            "pages.single",                     // pages/single.blade.php
            "layouts.page",                     // layouts/page.blade.php
        ];

        foreach ($candidates as $view) {
            if (View::exists($view)) {
                return $view;
            }
        }

        // Fallback to a basic page layout
        return 'pages.single';
    }

    /**
     * Preview a page (for draft or scheduled pages)
     */
    public function preview(int $id)
    {
        // Only allow preview for authenticated users with permission
        if (!auth()->check() || !auth()->user()->hasPermission('pages.edit')) {
            abort(403);
        }

        $page = Page::with(['blocks' => function ($q) {
            $q->whereNull('parent_block_id')
                ->with('childBlocks')
                ->orderBy('order');
        }])->findOrFail($id);

        $viewName = $this->resolveTemplate($page->template, $page->slug);

        return view($viewName, [
            'page' => $page,
            'blocks' => $page->blocks,
            'isPreview' => true,
        ]);
    }
}

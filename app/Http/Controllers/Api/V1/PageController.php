<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\PageResource;
use App\Models\Page;
use Illuminate\Http\Request;

class PageController extends Controller
{
    public function index(Request $request)
    {
        $perPage = min(100, max(1, (int) $request->query('per_page', 20)));
        $q = Page::published();

        if ($search = $request->query('q')) {
            $q->where('title', 'like', "%{$search}%");
        }

        return PageResource::collection($q->orderByDesc('published_at')->paginate($perPage));
    }

    public function show(string $slug)
    {
        $page = Page::published()->where('slug', $slug)->firstOrFail();

        return (new PageResource($page->load('blocks')))->response();
    }
}

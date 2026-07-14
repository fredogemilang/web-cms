<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\CptEntryResource;
use App\Models\CptEntry;
use App\Models\CustomPostType;
use Illuminate\Http\Request;

class CptController extends Controller
{
    public function index(Request $request, string $type)
    {
        $postType = CustomPostType::where('slug', $type)->firstOrFail();
        $perPage = min(100, max(1, (int) $request->query('per_page', 20)));

        $q = CptEntry::where('post_type_id', $postType->id)
            ->where('status', 'published')
            ->where(fn ($q) => $q->whereNull('published_at')->orWhere('published_at', '<=', now()));

        if ($search = $request->query('q')) {
            $q->where('title', 'like', "%{$search}%");
        }

        return CptEntryResource::collection($q->latest('published_at')->paginate($perPage));
    }

    public function show(string $type, string $slug)
    {
        $postType = CustomPostType::where('slug', $type)->firstOrFail();
        $entry = CptEntry::where('post_type_id', $postType->id)
            ->where('status', 'published')
            ->where('slug', $slug)
            ->firstOrFail();

        return (new CptEntryResource($entry->load('postType')))->response();
    }
}

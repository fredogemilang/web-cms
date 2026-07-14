<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\MediaResource;
use App\Models\Media;
use Illuminate\Http\Request;

class MediaController extends Controller
{
    public function index(Request $request)
    {
        $perPage = min(100, max(1, (int) $request->query('per_page', 20)));
        $q = Media::query();
        if ($mime = $request->query('mime')) {
            $q->where('mime_type', 'like', $mime.'%');
        }

        return MediaResource::collection($q->latest()->paginate($perPage));
    }

    public function show(int $id)
    {
        return (new MediaResource(Media::findOrFail($id)))->response();
    }
}

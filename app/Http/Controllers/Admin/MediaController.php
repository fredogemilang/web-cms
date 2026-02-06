<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Media;
use App\Services\MediaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MediaController extends Controller
{
    protected MediaService $mediaService;

    public function __construct(MediaService $mediaService)
    {
        $this->mediaService = $mediaService;
    }

    /**
     * Display the media library.
     */
    public function index()
    {
        return view('admin.media.index');
    }

    /**
     * Show the media upload page.
     */
    public function create()
    {
        return view('admin.media.create');
    }

    /**
     * Handle file upload via AJAX.
     */
    public function upload(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'file' => [
                'required',
                'file',
                'max:' . config('media.max_file_size'),
                'mimes:' . implode(',', config('media.allowed_extensions')),
            ],
            'alt_text' => 'nullable|string|max:255',
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $media = $this->mediaService->upload(
                $request->file('file'),
                $request->only(['alt_text', 'title', 'description'])
            );

            return response()->json([
                'success' => true,
                'message' => 'File uploaded successfully',
                'data' => [
                    'id' => $media->id,
                    'filename' => $media->original_filename,
                    'url' => $media->url,
                    'webp_url' => $media->webp_url,
                    'size' => $media->human_readable_size,
                    'mime_type' => $media->mime_type,
                ],
            ]);
        } catch (\Exception $e) {
            \Log::error('Media upload failed: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Upload failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update media metadata.
     */
    public function update(Request $request, Media $media)
    {
        $validator = Validator::make($request->all(), [
            'alt_text' => 'nullable|string|max:255',
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $updatedMedia = $this->mediaService->updateMetadata(
                $media,
                $request->only(['alt_text', 'title', 'description'])
            );

            return response()->json([
                'success' => true,
                'message' => 'Media updated successfully',
                'data' => $updatedMedia,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Update failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete a media file.
     */
    public function destroy(Media $media)
    {
        try {
            $this->mediaService->delete($media);

            return response()->json([
                'success' => true,
                'message' => 'Media deleted successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Delete failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Bulk delete media files.
     */
    public function bulkDelete(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'ids' => 'required|array',
            'ids.*' => 'exists:media,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $count = $this->mediaService->bulkDelete($request->ids);

            return response()->json([
                'success' => true,
                'message' => "{$count} media file(s) deleted successfully",
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Bulk delete failed: ' . $e->getMessage(),
            ], 500);
        }
    }
}

<?php

namespace App\Services;

use App\Models\Media;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MediaService
{
    /**
     * Upload a file and create media record.
     *
     * @param UploadedFile $file
     * @param array|null $metadata
     * @return Media
     */
    public function upload(UploadedFile $file, ?array $metadata = []): Media
    {
        // Generate unique filename
        $filename = $this->generateUniqueFilename($file);
        $path = config('media.path') . '/' . $filename;

        // Store the file
        Storage::disk(config('media.disk'))->put($path, file_get_contents($file->getRealPath()));

        // Get file information
        $mimeType = $file->getMimeType();
        $size = $file->getSize();
        $extension = $file->getClientOriginalExtension();

        // Get image dimensions if it's an image
        $dimensions = $this->isImage($mimeType) ? $this->getImageDimensions($file->getRealPath()) : null;

        // Create media record
        $media = Media::create([
            'filename' => $filename,
            'original_filename' => $file->getClientOriginalName(),
            'mime_type' => $mimeType,
            'file_extension' => $extension,
            'size' => $size,
            'path' => $path,
            'width' => $dimensions['width'] ?? null,
            'height' => $dimensions['height'] ?? null,
            'alt_text' => $metadata['alt_text'] ?? null,
            'title' => $metadata['title'] ?? pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME),
            'description' => $metadata['description'] ?? null,
            'uploaded_by' => auth()->id(),
        ]);

        // Convert to WebP if it's an image and conversion is enabled
        if ($this->shouldConvertToWebp($mimeType)) {
            $webpPath = $this->convertToWebp($path);
            if ($webpPath) {
                $media->update(['webp_path' => $webpPath]);
            }
        }

        return $media;
    }

    /**
     * Generate a unique filename for the uploaded file.
     *
     * @param UploadedFile $file
     * @return string
     */
    protected function generateUniqueFilename(UploadedFile $file): string
    {
        $extension = $file->getClientOriginalExtension();
        $basename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        
        // Sanitize filename
        $basename = Str::slug($basename);
        
        // Add timestamp and random string to ensure uniqueness
        return $basename . '-' . time() . '-' . Str::random(8) . '.' . $extension;
    }

    /**
     * Convert an image to WebP format.
     *
     * @param string $path Relative path to the image
     * @return string|null Path to WebP version or null on failure
     */
    public function convertToWebp(string $path): ?string
    {
        try {
            $disk = Storage::disk(config('media.disk'));
            $fullPath = $disk->path($path);

            if (!file_exists($fullPath)) {
                return null;
            }

            // Determine image type and create image resource
            $imageInfo = getimagesize($fullPath);
            $mimeType = $imageInfo['mime'] ?? null;

            $image = match ($mimeType) {
                'image/jpeg', 'image/jpg' => imagecreatefromjpeg($fullPath),
                'image/png' => imagecreatefrompng($fullPath),
                'image/gif' => imagecreatefromgif($fullPath),
                default => null,
            };

            if (!$image) {
                return null;
            }

            // Preserve transparency for PNG
            if ($mimeType === 'image/png') {
                imagepalettetotruecolor($image);
                imagealphablending($image, true);
                imagesavealpha($image, true);
            }

            // Generate WebP filename
            $webpFilename = pathinfo($path, PATHINFO_FILENAME) . '.webp';
            $webpPath = config('media.path') . '/' . $webpFilename;
            $webpFullPath = $disk->path($webpPath);

            // Convert to WebP
            $quality = config('media.webp.quality', 80);
            $success = imagewebp($image, $webpFullPath, $quality);

            // Free up memory
            imagedestroy($image);

            return $success ? $webpPath : null;
        } catch (\Exception $e) {
            \Log::error('WebP conversion failed: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Delete a media file and its WebP version.
     *
     * @param Media $media
     * @return bool
     */
    public function delete(Media $media): bool
    {
        return $media->delete();
    }

    /**
     * Update media metadata.
     *
     * @param Media $media
     * @param array $data
     * @return Media
     */
    public function updateMetadata(Media $media, array $data): Media
    {
        $media->update([
            'alt_text' => $data['alt_text'] ?? $media->alt_text,
            'title' => $data['title'] ?? $media->title,
            'description' => $data['description'] ?? $media->description,
        ]);

        return $media->fresh();
    }

    /**
     * Get image dimensions.
     *
     * @param string $path Full path to the image
     * @return array|null
     */
    protected function getImageDimensions(string $path): ?array
    {
        try {
            $imageInfo = getimagesize($path);
            return [
                'width' => $imageInfo[0] ?? null,
                'height' => $imageInfo[1] ?? null,
            ];
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Check if the MIME type is an image.
     *
     * @param string $mimeType
     * @return bool
     */
    protected function isImage(string $mimeType): bool
    {
        return str_starts_with($mimeType, 'image/');
    }

    /**
     * Check if the image should be converted to WebP.
     *
     * @param string $mimeType
     * @return bool
     */
    protected function shouldConvertToWebp(string $mimeType): bool
    {
        if (!config('media.webp.enabled', true)) {
            return false;
        }

        $convertTypes = config('media.webp.convert_types', []);
        return in_array($mimeType, $convertTypes);
    }

    /**
     * Bulk delete media files.
     *
     * @param array $mediaIds
     * @return int Number of deleted files
     */
    public function bulkDelete(array $mediaIds): int
    {
        $media = Media::whereIn('id', $mediaIds)->get();
        $count = 0;

        foreach ($media as $item) {
            if ($this->delete($item)) {
                $count++;
            }
        }

        return $count;
    }
}

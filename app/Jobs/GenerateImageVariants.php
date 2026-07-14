<?php

namespace App\Jobs;

use App\Models\Media;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class GenerateImageVariants implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /** Variant sizes (max-width in pixels). Original is kept untouched. */
    public const SIZES = [
        'thumb' => 150,
        'sm' => 480,
        'md' => 768,
        'lg' => 1280,
        'xl' => 1920,
    ];

    public int $tries = 2;

    public function __construct(public int $mediaId) {}

    public function handle(): void
    {
        $media = Media::find($this->mediaId);
        if (! $media || ! str_starts_with($media->mime_type, 'image/')) {
            return;
        }
        if ($media->mime_type === 'image/svg+xml' || $media->mime_type === 'image/gif') {
            return;
        }

        $disk = Storage::disk(config('media.disk', 'public'));
        if (! $disk->exists($media->path)) {
            return;
        }

        $fullPath = $disk->path($media->path);
        $variants = $media->variants ?? [];

        $source = $this->createImageFrom($fullPath, $media->mime_type);
        if (! $source) {
            return;
        }

        $srcW = imagesx($source);
        $srcH = imagesy($source);

        $jpgQ = (int) (setting('img_jpg_quality', 85));
        $webpQ = (int) (setting('img_webp_quality', 80));
        $emitWebp = (bool) setting('img_auto_webp', true);

        // Pick encoder + extension based on the source mime so PNG keeps alpha
        // and bytes match the filename suffix (otherwise CDNs/browsers serve
        // the wrong Content-Type and transparency is silently lost).
        [$encoder, $variantExt] = match ($media->mime_type) {
            'image/png' => ['png',  'png'],
            'image/webp' => ['webp', 'webp'],
            default => ['jpeg', 'jpg'],
        };

        foreach (self::SIZES as $label => $targetW) {
            if ($srcW <= $targetW) {
                continue;
            }

            $targetH = (int) round($srcH * ($targetW / $srcW));
            $thumb = imagecreatetruecolor($targetW, $targetH);
            imagealphablending($thumb, false);
            imagesavealpha($thumb, true);
            // Fill with transparent for PNG; harmless for JPEG (gets overwritten).
            if ($encoder === 'png') {
                $transparent = imagecolorallocatealpha($thumb, 0, 0, 0, 127);
                imagefilledrectangle($thumb, 0, 0, $targetW, $targetH, $transparent);
            }
            imagecopyresampled($thumb, $source, 0, 0, 0, 0, $targetW, $targetH, $srcW, $srcH);

            $base = pathinfo($media->path, PATHINFO_FILENAME);
            $dir = pathinfo($media->path, PATHINFO_DIRNAME);

            $rel = "{$dir}/{$base}-{$label}.{$variantExt}";
            $absolute = $disk->path($rel);
            match ($encoder) {
                'png' => imagepng($thumb, $absolute, 6),
                'webp' => imagewebp($thumb, $absolute, $webpQ),
                default => imagejpeg($thumb, $absolute, $jpgQ),
            };
            $variant = ['width' => $targetW, 'height' => $targetH, 'path' => $rel];

            // Emit a WebP companion only when the source is JPEG/PNG (skips
            // double-encoding when the source is already WebP).
            if ($emitWebp && $encoder !== 'webp') {
                $webpRel = "{$dir}/{$base}-{$label}.webp";
                imagewebp($thumb, $disk->path($webpRel), $webpQ);
                $variant['webp'] = $webpRel;
            }

            $variants[$label] = $variant;
            imagedestroy($thumb);
        }

        // LQIP: 16px wide blur placeholder as base64 data URI.
        $lqipW = 16;
        $lqipH = max(1, (int) round($srcH * ($lqipW / $srcW)));
        $lqip = imagecreatetruecolor($lqipW, $lqipH);
        imagecopyresampled($lqip, $source, 0, 0, 0, 0, $lqipW, $lqipH, $srcW, $srcH);
        ob_start();
        imagejpeg($lqip, null, 40);
        $lqipBytes = ob_get_clean();
        imagedestroy($lqip);
        imagedestroy($source);

        $media->variants = $variants;
        $media->placeholder_data_uri = 'data:image/jpeg;base64,'.base64_encode($lqipBytes);
        $media->save();
    }

    protected function createImageFrom(string $path, string $mime)
    {
        return match ($mime) {
            'image/jpeg', 'image/jpg' => @imagecreatefromjpeg($path),
            'image/png' => @imagecreatefrompng($path),
            'image/webp' => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($path) : null,
            default => null,
        };
    }
}

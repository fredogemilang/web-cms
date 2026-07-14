<?php

namespace App\Console\Commands;

use App\Models\Media;
use App\Services\MediaService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('media:optimize {--force : Re-convert media that already has a WebP file}')]
#[Description('Generate WebP companions for existing image media. Skips files that already have webp_path.')]
class MediaOptimize extends Command
{
    public function handle(MediaService $svc): int
    {
        $query = Media::query()->where('mime_type', 'like', 'image/%');

        if (! $this->option('force')) {
            $query->where(fn ($w) => $w->whereNull('webp_path')->orWhere('webp_path', ''));
        }

        $total = $query->count();
        if ($total === 0) {
            $this->info('Nothing to optimize.');

            return self::SUCCESS;
        }

        $this->info("Optimizing {$total} image(s)...");
        $bar = $this->output->createProgressBar($total);
        $bar->start();

        $ok = $fail = 0;
        foreach ($query->cursor() as $media) {
            try {
                $webpPath = $svc->convertToWebp($media->path);
                if ($webpPath) {
                    $media->update(['webp_path' => $webpPath]);
                    $ok++;
                } else {
                    $fail++;
                }
            } catch (\Throwable $e) {
                $fail++;
                $this->newLine();
                $this->error("[#{$media->id}] {$media->original_filename}: {$e->getMessage()}");
            }
            $bar->advance();
        }
        $bar->finish();
        $this->newLine(2);
        $this->info("Done: {$ok} converted, {$fail} failed.");

        return self::SUCCESS;
    }
}

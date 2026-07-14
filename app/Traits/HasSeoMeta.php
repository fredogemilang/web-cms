<?php

namespace App\Traits;

use App\Models\SeoMeta;
use Illuminate\Database\Eloquent\Relations\MorphOne;

trait HasSeoMeta
{
    public function seoMeta(): MorphOne
    {
        return $this->morphOne(SeoMeta::class, 'seoable');
    }

    public function getOrCreateSeoMeta(): SeoMeta
    {
        return $this->seoMeta()->firstOrCreate([]);
    }

    public function getResolvedSeoTitle(): string
    {
        $custom = $this->seoMeta?->title;

        return $custom ?: ($this->title ?? config('app.name'));
    }

    public function getResolvedSeoDescription(): ?string
    {
        return $this->seoMeta?->description ?? ($this->excerpt ?? null);
    }
}

<?php

namespace App\Livewire\Admin\Seo;

use App\Models\SeoMeta;
use Livewire\Attributes\On;
use Livewire\Component;

class SeoMetaBox extends Component
{
    public string $seoableType = '';

    public ?int $seoableId = null;

    public ?string $title = null;

    public ?string $description = null;

    public ?string $canonical_url = null;

    public string $robots = 'index,follow';

    public ?string $og_title = null;

    public ?string $og_description = null;

    public ?int $og_image_id = null;

    public string $twitter_card = 'summary_large_image';

    public ?string $schema_type = null;

    public ?string $focus_keyword = null;

    public function mount(string $seoableType, ?int $seoableId = null): void
    {
        $this->seoableType = $seoableType;
        $this->seoableId = $seoableId;
        $this->loadExisting();
    }

    protected function loadExisting(): void
    {
        if (! $this->seoableId) {
            return;
        }
        $row = SeoMeta::where('seoable_type', $this->seoableType)
            ->where('seoable_id', $this->seoableId)->first();
        if (! $row) {
            return;
        }

        $this->fill($row->only([
            'title', 'description', 'canonical_url', 'robots',
            'og_title', 'og_description', 'og_image_id',
            'twitter_card', 'schema_type', 'focus_keyword',
        ]));
    }

    /**
     * Called from the parent editor after a successful save() so we can attach
     * SEO data to a freshly-created entity (when seoableId wasn't known on mount).
     */
    #[On('seo-attach')]
    public function attachTo(int $id): void
    {
        $this->seoableId = $id;
        $this->save();
    }

    public function save(): void
    {
        if (! $this->seoableId) {
            // Nothing to save yet; the parent will dispatch 'seo-attach' after creation.
            return;
        }

        SeoMeta::updateOrCreate(
            ['seoable_type' => $this->seoableType, 'seoable_id' => $this->seoableId],
            [
                'title' => $this->title,
                'description' => $this->description,
                'canonical_url' => $this->canonical_url,
                'robots' => $this->robots ?: 'index,follow',
                'og_title' => $this->og_title,
                'og_description' => $this->og_description,
                'og_image_id' => $this->og_image_id,
                'twitter_card' => $this->twitter_card ?: 'summary_large_image',
                'schema_type' => $this->schema_type,
                'focus_keyword' => $this->focus_keyword,
            ]
        );

        $this->dispatch('seo-saved');
    }

    public function getTitleLengthProperty(): int
    {
        return mb_strlen((string) $this->title);
    }

    public function getDescriptionLengthProperty(): int
    {
        return mb_strlen((string) $this->description);
    }

    public function render()
    {
        return view('livewire.admin.seo.seo-meta-box');
    }
}

<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Resources\Json\JsonResource;

class PageResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'title' => $this->title,
            'status' => $this->status,
            'template' => $this->template,
            'parent_id' => $this->parent_id,
            'menu_order' => $this->menu_order,
            'published_at' => $this->published_at?->toAtomString(),
            'updated_at' => $this->updated_at?->toAtomString(),
            'url' => method_exists($this->resource, 'getUrl') ? $this->getUrl() : null,
            'seo' => $this->seo,
            'blocks' => $this->whenLoaded('blocks', fn () => $this->blocks->map(fn ($b) => [
                'name' => $b->name, 'value' => $b->value, 'order' => $b->order,
            ])),
        ];
    }
}

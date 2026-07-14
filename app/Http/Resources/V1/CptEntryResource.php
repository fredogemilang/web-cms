<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Resources\Json\JsonResource;

class CptEntryResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'title' => $this->title,
            'status' => $this->status,
            'published_at' => $this->published_at?->toAtomString(),
            'updated_at' => $this->updated_at?->toAtomString(),
            'meta' => $this->meta_values ?? null,
            'post_type' => $this->whenLoaded('postType', fn () => [
                'id' => $this->postType->id,
                'slug' => $this->postType->slug,
            ]),
        ];
    }
}

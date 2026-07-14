<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class MediaResource extends JsonResource
{
    public function toArray($request): array
    {
        $disk = config('media.disk', 'public');

        return [
            'id' => $this->id,
            'filename' => $this->filename,
            'mime_type' => $this->mime_type,
            'size' => $this->size,
            'width' => $this->width,
            'height' => $this->height,
            'alt_text' => $this->alt_text,
            'title' => $this->title,
            'url' => Storage::disk($disk)->url($this->path),
            'webp_url' => $this->webp_path ? Storage::disk($disk)->url($this->webp_path) : null,
            'variants' => $this->variants,
            'created_at' => $this->created_at?->toAtomString(),
        ];
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Media extends Model
{
    use HasFactory;

    protected $table = 'media';

    protected $fillable = [
        'filename',
        'original_filename',
        'mime_type',
        'file_extension',
        'size',
        'path',
        'webp_path',
        'width',
        'height',
        'alt_text',
        'title',
        'description',
        'uploaded_by',
    ];

    protected $casts = [
        'size' => 'integer',
        'width' => 'integer',
        'height' => 'integer',
    ];

    /**
     * Get the user who uploaded this media.
     */
    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    /**
     * Get the full URL to the media file.
     */
    public function getUrlAttribute(): string
    {
        return Storage::url($this->path);
    }

    /**
     * Get the full URL to the WebP version (if exists).
     */
    public function getWebpUrlAttribute(): ?string
    {
        return $this->webp_path ? Storage::url($this->webp_path) : null;
    }

    /**
     * Get human-readable file size.
     */
    public function getHumanReadableSizeAttribute(): string
    {
        $bytes = $this->size;
        $units = ['B', 'KB', 'MB', 'GB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * Check if this media is an image.
     */
    public function isImage(): bool
    {
        return str_starts_with($this->mime_type, 'image/');
    }

    /**
     * Check if this media has a WebP version.
     */
    public function hasWebp(): bool
    {
        return !is_null($this->webp_path);
    }

    /**
     * Scope to filter only images.
     */
    public function scopeImages($query)
    {
        return $query->where('mime_type', 'like', 'image/%');
    }

    /**
     * Scope to filter only documents.
     */
    public function scopeDocuments($query)
    {
        return $query->where('mime_type', 'not like', 'image/%');
    }

    /**
     * Scope to filter by specific MIME type.
     */
    public function scopeByType($query, string $type)
    {
        return $query->where('mime_type', 'like', $type . '%');
    }

    /**
     * Override delete to remove files from storage.
     */
    public function delete()
    {
        $disk = Storage::disk(config('media.disk', 'public'));

        // Delete original file
        if ($disk->exists($this->path)) {
            $disk->delete($this->path);
        }

        // Delete WebP version if exists
        if ($this->webp_path && $disk->exists($this->webp_path)) {
            $disk->delete($this->webp_path);
        }

        return parent::delete();
    }

}

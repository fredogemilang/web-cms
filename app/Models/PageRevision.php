<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PageRevision extends Model
{
    protected $fillable = [
        'page_id',
        'user_id',
        'title',
        'slug',
        'status',
        'blocks',
        'seo',
        'change_summary',
        'is_autosave',
    ];

    protected function casts(): array
    {
        return [
            'blocks' => 'array',
            'seo' => 'array',
            'is_autosave' => 'boolean',
        ];
    }

    // === RELATIONSHIPS ===

    public function page(): BelongsTo
    {
        return $this->belongsTo(Page::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // === HELPERS ===

    public function restore(): bool
    {
        // Restore page data from this revision
        $this->page->update([
            'title' => $this->title,
            'slug' => $this->slug,
            'status' => $this->status,
            'seo' => $this->seo,
        ]);

        // Delete existing blocks
        $this->page->allBlocks()->delete();

        // Recreate blocks from snapshot
        $this->restoreBlocks($this->blocks);

        return true;
    }

    protected function restoreBlocks(array $blocks, ?int $parentBlockId = null): void
    {
        foreach ($blocks as $blockData) {
            // Extract children if present
            $children = $blockData['children'] ?? [];
            unset($blockData['children']);
            unset($blockData['id']); // Remove old ID

            // Create the block
            $block = PageBlock::create([
                'page_id' => $this->page->id,
                'parent_block_id' => $parentBlockId,
                'name' => $blockData['name'],
                'type' => $blockData['type'],
                'label' => $blockData['label'] ?? null,
                'value' => $blockData['value'] ?? null,
                'options' => $blockData['options'] ?? null,
                'order' => $blockData['order'] ?? 0,
                'is_active' => $blockData['is_active'] ?? true,
            ]);

            // Recursively restore children
            if (!empty($children)) {
                $this->restoreBlocks($children, $block->id);
            }
        }
    }

    public function getFormattedDate(): string
    {
        return $this->created_at->format('M d, Y \a\t g:i A');
    }

    public function getRelativeDate(): string
    {
        return $this->created_at->diffForHumans();
    }

    public function getBlockCount(): int
    {
        return count($this->blocks ?? []);
    }
}

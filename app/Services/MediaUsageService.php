<?php

namespace App\Services;

use App\Models\CptEntry;
use App\Models\CustomPostType;
use App\Models\Media;
use App\Models\MetaField;
use App\Models\Page;
use App\Models\PageBlock;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Scans content tables to figure out which Media rows are referenced anywhere
 * and which are orphans. Result is cached for a short TTL since the scan is
 * non-trivial.
 *
 * References checked:
 *   - pages.featured_image (path string)
 *   - pages.seo->'og_image'
 *   - page_blocks.value (media id for type='media', JSON array of ids for type='gallery')
 *   - cpt_entries.featured_image
 *   - cpt_entries.content (img src=… scan)
 *   - cpt_entries.meta (deep scan for media/gallery field references)
 *   - users.avatar (path string)
 */
class MediaUsageService
{
    protected const CACHE_KEY = 'media:usage-map';

    protected const CACHE_TTL = 300; // 5 minutes

    /**
     * Map of media_id => total references count.
     *
     * @return array<int, int>
     */
    public function usageMap(): array
    {
        return Cache::remember(self::CACHE_KEY, self::CACHE_TTL, function () {
            $map = [];
            $bump = function (int|string|null $id) use (&$map) {
                if (! $id || ! is_numeric($id)) {
                    return;
                }
                $id = (int) $id;
                $map[$id] = ($map[$id] ?? 0) + 1;
            };

            // Build path → media id lookup; both `path` (original) and `webp_path` (companion)
            // can be referenced by content rows, so include both.
            $pathToId = [];
            foreach (Media::query()->get(['id', 'path', 'webp_path']) as $m) {
                if ($m->path) {
                    $pathToId[$m->path] = $m->id;
                }
                if ($m->webp_path) {
                    $pathToId[$m->webp_path] = $m->id;
                }
            }
            $resolveByPath = fn ($v) => is_string($v) ? ($pathToId[ltrim($v, '/')] ?? $pathToId[$v] ?? null) : null;

            foreach (Page::select('featured_image', 'seo')->get() as $p) {
                $bump($resolveByPath($p->featured_image));
                if (is_array($p->seo) && ! empty($p->seo['og_image'])) {
                    $bump($resolveByPath($p->seo['og_image']));
                }
            }

            // PageBlock values
            foreach (PageBlock::select('type', 'value')->get() as $b) {
                $val = $b->value;
                if ($b->type === 'media') {
                    // value can be a media id or a path
                    if (is_numeric($val)) {
                        $bump($val);
                    } else {
                        $bump($resolveByPath($val));
                    }
                } elseif ($b->type === 'gallery') {
                    $arr = is_array($val) ? $val : (json_decode((string) $val, true) ?: []);
                    foreach ($arr as $v) {
                        is_numeric($v) ? $bump($v) : $bump($resolveByPath($v));
                    }
                }
            }

            // Map of post_type_id => [field_names that hold media references]
            $mediaFieldsByCpt = MetaField::query()
                ->where('fieldable_type', CustomPostType::class)
                ->whereIn('type', ['media', 'gallery', 'image'])
                ->get(['fieldable_id', 'name', 'type'])
                ->groupBy('fieldable_id')
                ->map(fn ($rows) => $rows->mapWithKeys(fn ($r) => [$r->name => $r->type])->all())
                ->all();

            // CptEntries
            foreach (CptEntry::select('id', 'post_type_id', 'featured_image', 'content', 'meta')->get() as $e) {
                $bump($resolveByPath($e->featured_image));

                // img src= references in HTML content
                if ($e->content) {
                    preg_match_all('/<img[^>]+src=("|\')([^"\']+)\1/i', $e->content, $m);
                    foreach ($m[2] ?? [] as $src) {
                        $bump($resolveByPath($src));
                    }
                }

                // Meta media fields — resolve by known schema
                if (is_array($e->meta) && isset($mediaFieldsByCpt[$e->post_type_id])) {
                    foreach ($mediaFieldsByCpt[$e->post_type_id] as $field => $type) {
                        $value = $e->meta[$field] ?? null;
                        if ($value === null || $value === '') {
                            continue;
                        }

                        if ($type === 'gallery' && is_array($value)) {
                            foreach ($value as $v) {
                                is_numeric($v) ? $bump($v) : $bump($resolveByPath($v));
                            }
                        } else {
                            // media/image — single value (id or path)
                            is_numeric($value) ? $bump($value) : $bump($resolveByPath($value));
                        }
                    }
                }
            }

            // User avatars (string path)
            foreach (DB::table('users')->whereNotNull('avatar')->pluck('avatar') as $av) {
                $bump($resolveByPath($av));
            }

            return $map;
        });
    }

    public function usageCount(int $mediaId): int
    {
        return $this->usageMap()[$mediaId] ?? 0;
    }

    /** Media row IDs that aren't referenced anywhere. */
    public function orphanIds(): array
    {
        $used = array_keys($this->usageMap());

        return Media::whereNotIn('id', $used)->pluck('id')->all();
    }

    public function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    protected function scanMetaForMediaIds(array $meta, callable $bump): void
    {
        foreach ($meta as $value) {
            if (is_array($value)) {
                // Possibly a gallery field (array of ids) or a repeater (nested)
                $allInt = ! empty($value) && array_filter($value, fn ($v) => is_int($v) || (is_string($v) && ctype_digit($v))) === array_values($value);
                if ($allInt) {
                    foreach ($value as $id) {
                        $bump($id);
                    }
                } else {
                    $this->scanMetaForMediaIds($value, $bump);
                }
            }
            // Heuristic: integer scalar might be a media id — but too noisy to assume,
            // so we don't bump on bare integers without context.
        }
    }
}

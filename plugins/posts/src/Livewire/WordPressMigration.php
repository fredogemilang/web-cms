<?php

namespace Plugins\Posts\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Plugins\Posts\Models\Post;
use Plugins\Posts\Models\Category;
use Plugins\Posts\Models\Tag;
use App\Models\Media;

class WordPressMigration extends Component
{
    // URL Input
    public $wpUrl = '';
    public $isValidUrl = false;
    
    // Basic Info (NOT storing full posts data to avoid payload issues)
    public $totalPosts = 0;
    public $totalPages = 0;
    public $perPage = 10;
    
    // Preview posts (only first 5 for display, not stored in state)
    public $previewPosts = [];
    
    // Field Mappings
    public $fieldMappings = [
        'title' => true,
        'slug' => true,
        'content' => true,
        'excerpt' => true,
        'published_at' => true,
        'featured_image' => 'download',
        'content_images' => true,
        'categories' => true,
        'tags' => true,
    ];
    
    // Import State
    public $step = 1; // 1: Input URL, 2: Configure & Import, 3: Results
    public $isLoading = false;
    public $importProgress = 0;
    public $currentPageImporting = 0;
    public $importResults = [];
    public $errorMessage = '';
    
    protected $rules = [
        'wpUrl' => 'required|url',
    ];

    public function validateUrl()
    {
        $this->validate();
        
        // Normalize URL
        $url = rtrim($this->wpUrl, '/');
        
        // Check if it's a WP REST API URL
        if (!Str::contains($url, '/wp-json/wp/v2/posts')) {
            if (Str::contains($url, '/wp-json')) {
                $url = Str::before($url, '/wp-json') . '/wp-json/wp/v2/posts';
            } else {
                $url = $url . '/wp-json/wp/v2/posts';
            }
        }
        
        $this->wpUrl = $url;
        $this->isValidUrl = true;
    }

    public function fetchPostsInfo()
    {
        $this->isLoading = true;
        $this->errorMessage = '';
        
        try {
            $this->validateUrl();
            
            // Fetch first page to get total count
            $response = Http::timeout(30)->get($this->wpUrl, [
                'per_page' => $this->perPage,
                'page' => 1,
                '_embed' => true,
            ]);
            
            if ($response->failed()) {
                throw new \Exception('Failed to fetch posts from WordPress API. Status: ' . $response->status());
            }
            
            $posts = $response->json();
            $this->totalPosts = (int) $response->header('X-WP-Total', count($posts));
            $this->totalPages = (int) $response->header('X-WP-TotalPages', 1);
            
            // Store only preview posts (first 5 for display)
            $this->previewPosts = collect($posts)->take(5)->map(function($post) {
                return [
                    'id' => $post['id'],
                    'title' => html_entity_decode(strip_tags($post['title']['rendered'] ?? ''), ENT_QUOTES, 'UTF-8'),
                    'slug' => $post['slug'] ?? '',
                    'date' => $post['date'] ?? null,
                    'status' => $post['status'] ?? 'publish',
                ];
            })->toArray();
            
            if ($this->totalPosts === 0) {
                $this->errorMessage = 'No posts found at this URL.';
            } else {
                $this->step = 2;
            }
            
        } catch (\Exception $e) {
            $this->errorMessage = $e->getMessage();
        }
        
        $this->isLoading = false;
    }

    public function importAllPosts()
    {
        $this->isLoading = true;
        $this->importProgress = 0;
        $this->currentPageImporting = 0;
        $this->importResults = [
            'success' => 0,
            'failed' => 0,
            'skipped' => 0,
            'skipped_posts' => [],
            'errors' => [],
        ];
        
        try {
            // Process page by page to avoid memory issues
            for ($page = 1; $page <= $this->totalPages; $page++) {
                $this->currentPageImporting = $page;
                
                // Fetch posts for current page
                $response = Http::timeout(60)->get($this->wpUrl, [
                    'per_page' => $this->perPage,
                    'page' => $page,
                    '_embed' => true,
                ]);
                
                if ($response->failed()) {
                    Log::warning('Failed to fetch page ' . $page);
                    continue;
                }
                
                $posts = $response->json();
                
                // Import each post in this page
                foreach ($posts as $wpPost) {
                    try {
                        $result = $this->importSinglePost($wpPost);
                        
                        if ($result === 'success') {
                            $this->importResults['success']++;
                        } elseif ($result === 'skipped') {
                            $this->importResults['skipped']++;
                            $this->importResults['skipped_posts'][] = [
                                'title' => html_entity_decode(strip_tags($wpPost['title']['rendered'] ?? 'Unknown'), ENT_QUOTES, 'UTF-8'),
                                'slug' => $wpPost['slug'] ?? '',
                                'reason' => 'Slug already exists',
                            ];
                        }
                    } catch (\Exception $e) {
                        $this->importResults['failed']++;
                        $this->importResults['errors'][] = [
                            'title' => $wpPost['title']['rendered'] ?? 'Unknown',
                            'error' => $e->getMessage(),
                        ];
                        Log::error('WordPress import failed', [
                            'title' => $wpPost['title']['rendered'] ?? 'Unknown',
                            'error' => $e->getMessage()
                        ]);
                    }
                }
                
                // Update progress
                $this->importProgress = round(($page / $this->totalPages) * 100);
            }
            
        } catch (\Exception $e) {
            $this->errorMessage = 'Import failed: ' . $e->getMessage();
            Log::error('WordPress import failed', ['error' => $e->getMessage()]);
        }
        
        $this->step = 3;
        $this->isLoading = false;
    }

    protected function importSinglePost($wpPost)
    {
        // Extract title
        $title = html_entity_decode(strip_tags($wpPost['title']['rendered'] ?? ''), ENT_QUOTES, 'UTF-8');
        
        // Check if post with same slug already exists
        $slug = $wpPost['slug'] ?? Str::slug($title);
        if (Post::where('slug', $slug)->exists()) {
            return 'skipped';
        }
        
        // Extract content
        $content = $wpPost['content']['rendered'] ?? '';
        
        // Process content images if enabled
        if ($this->fieldMappings['content_images'] ?? false) {
            $content = $this->processContentImages($content);
        }
        
        // Extract excerpt
        $excerpt = '';
        if ($this->fieldMappings['excerpt']) {
            $excerpt = strip_tags($wpPost['excerpt']['rendered'] ?? '');
            $excerpt = html_entity_decode(trim($excerpt), ENT_QUOTES, 'UTF-8');
        }
        
        // Handle published date - PRESERVE ORIGINAL!
        $publishedAt = now();
        if ($this->fieldMappings['published_at']) {
            $publishedAt = Carbon::parse($wpPost['date'] ?? now());
        }
        
        // Handle featured image
        $featuredImage = null;
        if ($this->fieldMappings['featured_image'] !== 'skip') {
            $featuredImage = $this->getFeaturedImage($wpPost);
        }
        
        // Create the post
        $post = Post::create([
            'title' => $title,
            'slug' => $this->ensureUniqueSlug($slug),
            'content' => $content,
            'excerpt' => $excerpt,
            'featured_image' => $featuredImage,
            'status' => 'published',
            'published_at' => $publishedAt,
            'author_id' => auth()->id(),
            'meta' => [
                'wp_original_id' => $wpPost['id'],
                'wp_original_url' => $wpPost['link'] ?? null,
            ],
        ]);
        
        // Force set created_at to preserve original date for SEO
        if ($this->fieldMappings['published_at']) {
            $post->created_at = $publishedAt;
            $post->save();
        }
        
        // Handle categories
        if ($this->fieldMappings['categories']) {
            $this->attachCategories($post, $wpPost);
        }
        
        // Handle tags
        if ($this->fieldMappings['tags']) {
            $this->attachTags($post, $wpPost);
        }
        
        return 'success';
    }

    /**
     * Process content to download and replace image URLs
     */
    protected function processContentImages($content)
    {
        // First, remove srcset and sizes attributes from all img tags
        $content = preg_replace('/\s+srcset=["\'][^"\']*["\']/', '', $content);
        $content = preg_replace('/\s+sizes=["\'][^"\']*["\']/', '', $content);
        
        // Find all img tags and their src attributes
        preg_match_all('/<img[^>]+src=["\']([^"\']+)["\'][^>]*>/i', $content, $matches);
        
        if (empty($matches[1])) {
            return $content;
        }
        
        $replacements = [];
        
        foreach ($matches[1] as $originalUrl) {
            // Skip if already a local URL
            if (Str::startsWith($originalUrl, '/storage/') || Str::startsWith($originalUrl, '/media/')) {
                continue;
            }
            
            if (Str::startsWith($originalUrl, '/') && !Str::startsWith($originalUrl, '//')) {
                continue;
            }
            
            // Skip data URLs
            if (Str::startsWith($originalUrl, 'data:')) {
                continue;
            }
            
            try {
                $newPath = $this->downloadImage($originalUrl);
                
                if ($newPath && $newPath !== $originalUrl && !Str::startsWith($newPath, 'http')) {
                    $newUrl = '/storage/' . $newPath;
                    $replacements[$originalUrl] = $newUrl;
                }
            } catch (\Exception $e) {
                Log::warning('Failed to download content image: ' . $originalUrl);
            }
        }
        
        // Replace all URLs in content
        foreach ($replacements as $oldUrl => $newUrl) {
            $content = str_replace($oldUrl, $newUrl, $content);
        }
        
        return $content;
    }

    protected function getFeaturedImage($wpPost)
    {
        // Try to get from embedded data first
        if (isset($wpPost['_embedded']['wp:featuredmedia'][0]['source_url'])) {
            $imageUrl = $wpPost['_embedded']['wp:featuredmedia'][0]['source_url'];
            
            if ($this->fieldMappings['featured_image'] === 'download') {
                return $this->downloadImage($imageUrl);
            }
            
            return $imageUrl;
        }
        
        // If no embedded media, try to fetch it
        if (!empty($wpPost['featured_media'])) {
            $baseUrl = Str::before($this->wpUrl, '/wp-json');
            $mediaUrl = $baseUrl . '/wp-json/wp/v2/media/' . $wpPost['featured_media'];
            
            try {
                $response = Http::timeout(10)->get($mediaUrl);
                if ($response->successful()) {
                    $media = $response->json();
                    $imageUrl = $media['source_url'] ?? null;
                    
                    if ($imageUrl) {
                        if ($this->fieldMappings['featured_image'] === 'download') {
                            return $this->downloadImage($imageUrl);
                        }
                        return $imageUrl;
                    }
                }
            } catch (\Exception $e) {
                Log::warning('Failed to fetch featured media: ' . $e->getMessage());
            }
        }
        
        return null;
    }

    protected function downloadImage($imageUrl)
    {
        try {
            if (Str::startsWith($imageUrl, '//')) {
                $imageUrl = 'https:' . $imageUrl;
            }
            
            $response = Http::timeout(60)->withOptions([
                'verify' => false,
            ])->get($imageUrl);
            
            if (!$response->successful()) {
                return null;
            }
            
            $urlPath = parse_url($imageUrl, PHP_URL_PATH);
            $originalFilename = basename($urlPath);
            $extension = strtolower(pathinfo($urlPath, PATHINFO_EXTENSION));
            
            if (empty($extension) || !in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'])) {
                $contentType = $response->header('Content-Type');
                $extension = $this->getExtensionFromMimeType($contentType) ?? 'jpg';
            }
            
            $filename = 'wp-import-' . time() . '-' . Str::random(8) . '.' . $extension;
            $path = config('media.path', 'media') . '/' . $filename;
            
            $disk = Storage::disk(config('media.disk', 'public'));
            
            $directory = dirname($path);
            if (!$disk->exists($directory)) {
                $disk->makeDirectory($directory);
            }
            
            $disk->put($path, $response->body());
            
            if (!$disk->exists($path)) {
                return null;
            }
            
            $fullPath = $disk->path($path);
            $imageInfo = @getimagesize($fullPath);
            $fileSize = $disk->size($path);
            
            $mimeType = $imageInfo['mime'] ?? $this->getMimeTypeFromExtension($extension);
            
            Media::create([
                'filename' => $filename,
                'original_filename' => $originalFilename ?: $filename,
                'mime_type' => $mimeType,
                'file_extension' => $extension,
                'size' => $fileSize,
                'path' => $path,
                'width' => $imageInfo[0] ?? null,
                'height' => $imageInfo[1] ?? null,
                'alt_text' => null,
                'title' => pathinfo($originalFilename ?: $filename, PATHINFO_FILENAME),
                'description' => 'Imported from WordPress',
                'uploaded_by' => auth()->id(),
            ]);
            
            return $path;
            
        } catch (\Exception $e) {
            Log::error('WordPress image download failed', ['url' => $imageUrl, 'error' => $e->getMessage()]);
            return null;
        }
    }

    protected function getExtensionFromMimeType($mimeType)
    {
        $map = [
            'image/jpeg' => 'jpg',
            'image/jpg' => 'jpg',
            'image/png' => 'png',
            'image/gif' => 'gif',
            'image/webp' => 'webp',
            'image/svg+xml' => 'svg',
        ];
        
        return $map[$mimeType] ?? null;
    }

    protected function getMimeTypeFromExtension($extension)
    {
        $mimeTypes = [
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'webp' => 'image/webp',
            'svg' => 'image/svg+xml',
        ];
        
        return $mimeTypes[$extension] ?? 'image/jpeg';
    }

    protected function ensureUniqueSlug($slug)
    {
        $originalSlug = $slug;
        $counter = 1;
        
        while (Post::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }
        
        return $slug;
    }

    protected function attachCategories($post, $wpPost)
    {
        if (empty($wpPost['_embedded']['wp:term'][0])) {
            return;
        }
        
        $wpCategories = $wpPost['_embedded']['wp:term'][0];
        
        // Ensure it's an array before iterating
        if (!is_array($wpCategories)) {
            return;
        }
        
        $categoryIds = [];
        
        foreach ($wpCategories as $wpCat) {
            if (($wpCat['taxonomy'] ?? '') !== 'category') {
                continue;
            }
            
            $catName = html_entity_decode($wpCat['name'], ENT_QUOTES, 'UTF-8');
            $catSlug = Str::slug($catName);
            
            $category = Category::where('slug', $catSlug)->first();
            
            if (!$category) {
                $category = Category::whereRaw('LOWER(name) = ?', [strtolower($catName)])->first();
            }
            
            if (!$category) {
                $category = Category::create([
                    'name' => $catName,
                    'slug' => $catSlug,
                    'description' => $wpCat['description'] ?? '',
                ]);
            }
            
            $categoryIds[] = $category->id;
        }
        
        if (!empty($categoryIds)) {
            $post->categories()->sync($categoryIds);
        }
    }

    protected function attachTags($post, $wpPost)
    {
        if (empty($wpPost['_embedded']['wp:term'][1])) {
            return;
        }
        
        $wpTags = $wpPost['_embedded']['wp:term'][1];
        
        // Ensure it's an array before iterating
        if (!is_array($wpTags)) {
            return;
        }
        
        $tagIds = [];
        
        foreach ($wpTags as $wpTag) {
            if (($wpTag['taxonomy'] ?? '') !== 'post_tag') {
                continue;
            }
            
            $tagName = html_entity_decode($wpTag['name'], ENT_QUOTES, 'UTF-8');
            $tagSlug = Str::slug($tagName);
            
            $tag = Tag::where('slug', $tagSlug)->first();
            
            if (!$tag) {
                $tag = Tag::whereRaw('LOWER(name) = ?', [strtolower($tagName)])->first();
            }
            
            if (!$tag) {
                $tag = Tag::create([
                    'name' => $tagName,
                    'slug' => $tagSlug,
                ]);
            }
            
            $tagIds[] = $tag->id;
        }
        
        if (!empty($tagIds)) {
            $post->tags()->sync($tagIds);
        }
    }

    public function resetMigration()
    {
        $this->step = 1;
        $this->wpUrl = '';
        $this->totalPosts = 0;
        $this->totalPages = 0;
        $this->previewPosts = [];
        $this->importProgress = 0;
        $this->currentPageImporting = 0;
        $this->importResults = [];
        $this->errorMessage = '';
    }

    public function render()
    {
        return view('posts::livewire.wordpress-migration');
    }
}

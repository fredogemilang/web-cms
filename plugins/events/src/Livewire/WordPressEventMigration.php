<?php

namespace Plugins\Events\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Plugins\Events\Models\Event;
use App\Models\Media;

class WordPressEventMigration extends Component
{
    // URL Input
    public $wpUrl = '';
    public $isValidUrl = false;
    
    // WordPress Discovery
    public $availablePostTypes = [];
    public $selectedWpPostType = ''; 
    public $wpEventFields = []; // Sample fields from WP
    
    // CMS Event Fields (Fixed)
    public $cmsEventFields = [];
    
    // Field Mappings
    public $fieldMappings = [];
    
    // Import Options
    public $downloadFeaturedImage = true;
    public $downloadContentImages = true;
    public $defaultStatus = 'published';
    
    // Basic Info
    public $totalPosts = 0;
    public $totalPages = 0;
    public $perPage = 10;
    
    // Import State
    public $step = 1; // 1: URL, 2: Select WP Type, 3: Mapping, 4: Results
    public $isLoading = false;
    public $importProgress = 0;
    public $currentPageImporting = 0;
    public $importResults = [];
    public $errorMessage = '';
    
    public function mount()
    {
        // Define standard Event fields to map to
        $this->cmsEventFields = [
            ['key' => 'title', 'label' => 'Title', 'required' => true],
            ['key' => 'slug', 'label' => 'Slug', 'required' => false],
            ['key' => 'content', 'label' => 'Content', 'required' => false],
            ['key' => 'description', 'label' => 'Description (Excerpt)', 'required' => false],
            ['key' => 'start_date', 'label' => 'Start Date', 'required' => true],
            ['key' => 'end_date', 'label' => 'End Date', 'required' => false],
            ['key' => 'location', 'label' => 'Location Name', 'required' => false],
            ['key' => 'location_address', 'label' => 'Location Address', 'required' => false],
            ['key' => 'location_url', 'label' => 'Location URL (Map)', 'required' => false],
            ['key' => 'online_meeting_url', 'label' => 'Online Meeting URL', 'required' => false],
            ['key' => 'featured_image', 'label' => 'Featured Image', 'required' => false],
        ];
    }

    public function validateUrl()
    {
        $this->validate([
            'wpUrl' => 'required|url',
        ]);
        
        $url = rtrim($this->wpUrl, '/');
        if (Str::contains($url, '/wp-json')) {
            $url = Str::before($url, '/wp-json');
        }
        
        $this->wpUrl = $url;
        $this->isValidUrl = true;
    }

    public function fetchPostTypes()
    {
        $this->isLoading = true;
        $this->errorMessage = '';
        
        try {
            $this->validateUrl();
            
            $response = Http::timeout(30)->get($this->wpUrl . '/wp-json/wp/v2/types');
            
            if ($response->failed()) {
                throw new \Exception('Failed to fetch post types from WordPress API.');
            }
            
            $types = $response->json();
            $this->availablePostTypes = [];
            
            // Allow selecting standard posts or custom types (like tribe_events)
            foreach ($types as $slug => $type) {
                if (!in_array($slug, ['attachment', 'revision', 'nav_menu_item', 'wp_block', 'wp_template', 'wp_template_part', 'wp_navigation'])) {
                    $this->availablePostTypes[] = [
                        'slug' => $slug,
                        'name' => $type['name'] ?? $slug,
                        'rest_base' => $type['rest_base'] ?? $slug,
                    ];
                }
            }
            
            $this->step = 2;
            
        } catch (\Exception $e) {
            $this->errorMessage = $e->getMessage();
        }
        
        $this->isLoading = false;
    }

    public function selectWpPostType()
    {
        $this->isLoading = true;
        $this->errorMessage = '';
        
        try {
            if (empty($this->selectedWpPostType)) {
                throw new \Exception('Please select a WordPress post type.');
            }
            
            $selectedType = collect($this->availablePostTypes)->firstWhere('slug', $this->selectedWpPostType);
            $restBase = $selectedType['rest_base'] ?? $this->selectedWpPostType;
            
            // Fetch sample to discover fields
            $response = Http::timeout(30)->get($this->wpUrl . '/wp-json/wp/v2/' . $restBase, [
                'per_page' => 1,
                '_embed' => true,
            ]);
            
            if ($response->failed()) {
                throw new \Exception('Failed to fetch sample data.');
            }
            
            $posts = $response->json();
            $this->totalPosts = (int) $response->header('X-WP-Total', count($posts));
            $this->totalPages = (int) $response->header('X-WP-TotalPages', 1);
            
            $this->wpEventFields = [];
            if (!empty($posts[0])) {
                $this->discoverFields($posts[0]);
            }
            
            $this->initializeFieldMappings();
            $this->step = 3;
            
        } catch (\Exception $e) {
            $this->errorMessage = $e->getMessage();
        }
        
        $this->isLoading = false;
    }

    protected function discoverFields($samplePost, $prefix = '')
    {
        $ignoredFields = ['_links', '_embedded', 'guid', 'type', 'link', 'template'];
        
        foreach ($samplePost as $key => $value) {
            if (in_array($key, $ignoredFields)) {
                continue;
            }
            
            $fieldPath = $prefix ? $prefix . '.' . $key : $key;
            
            if (is_array($value) && !empty($value)) {
                if (isset($value['rendered'])) {
                    $this->wpEventFields[] = [
                        'path' => $fieldPath . '.rendered',
                        'label' => ucfirst(str_replace('_', ' ', $key)) . ' (rendered)',
                        'sample' => Str::limit(strip_tags($value['rendered']), 30),
                    ];
                } else if ($key === 'meta' || $key === 'acf') {
                    $this->discoverFields($value, $fieldPath);
                }
            } else if (is_scalar($value)) {
                $this->wpEventFields[] = [
                    'path' => $fieldPath,
                    'label' => ucfirst(str_replace('_', ' ', $key)),
                    'sample' => Str::limit((string) $value, 30),
                ];
            }
        }
    }

    protected function initializeFieldMappings()
    {
        // Smart defaults
        $this->fieldMappings = [
            'title' => 'title.rendered',
            'slug' => 'slug',
            'content' => 'content.rendered',
            'description' => 'excerpt.rendered',
            'start_date' => 'date', // Default to publish date if no specific start date
            'featured_image' => 'featured_media',
        ];
    }

    public function importAllEvents()
    {
        $this->isLoading = true;
        $this->importProgress = 0;
        $this->currentPageImporting = 0;
        $this->importResults = [
            'success' => 0,
            'failed' => 0,
            'skipped' => 0,
            'skipped_items' => [],
            'errors' => [],
        ];
        
        try {
            $selectedType = collect($this->availablePostTypes)->firstWhere('slug', $this->selectedWpPostType);
            $restBase = $selectedType['rest_base'] ?? $this->selectedWpPostType;
            
            for ($page = 1; $page <= $this->totalPages; $page++) {
                $this->currentPageImporting = $page;
                
                $response = Http::timeout(60)->get($this->wpUrl . '/wp-json/wp/v2/' . $restBase, [
                    'per_page' => $this->perPage,
                    'page' => $page,
                    '_embed' => true,
                ]);
                
                if ($response->failed()) {
                    Log::warning('Failed to fetch events page ' . $page);
                    continue;
                }
                
                $posts = $response->json();
                
                foreach ($posts as $wpPost) {
                    try {
                        $result = $this->importSingleEvent($wpPost);
                        
                        if ($result === 'success') {
                            $this->importResults['success']++;
                        } elseif ($result === 'skipped') {
                            $this->importResults['skipped']++;
                            $this->importResults['skipped_items'][] = [
                                'title' => $this->getWpFieldValue($wpPost, 'title.rendered') ?? 'Unknown',
                                'reason' => 'Slug exists',
                            ];
                        }
                    } catch (\Exception $e) {
                        $this->importResults['failed']++;
                        $this->importResults['errors'][] = [
                            'title' => $this->getWpFieldValue($wpPost, 'title.rendered') ?? 'Unknown',
                            'error' => $e->getMessage(),
                        ];
                    }
                }
                
                $this->importProgress = round(($page / $this->totalPages) * 100);
            }
            
        } catch (\Exception $e) {
            $this->errorMessage = 'Import failed: ' . $e->getMessage();
        }
        
        $this->step = 4;
        $this->isLoading = false;
    }

    protected function importSingleEvent($wpPost)
    {
        $title = html_entity_decode(strip_tags($this->getWpFieldValue($wpPost, $this->fieldMappings['title'] ?? 'title.rendered') ?? ''), ENT_QUOTES, 'UTF-8');
        $slug = $this->getWpFieldValue($wpPost, $this->fieldMappings['slug'] ?? 'slug') ?? Str::slug($title);
        
        if (Event::where('slug', $slug)->exists()) {
            return 'skipped';
        }
        
        // Extract fields using mapping
        $content = $this->getWpFieldValue($wpPost, $this->fieldMappings['content'] ?? 'content.rendered') ?? '';
        if ($this->downloadContentImages) {
            $content = $this->processContentImages($content);
        }
        
        $description = strip_tags($this->getWpFieldValue($wpPost, $this->fieldMappings['description'] ?? 'excerpt.rendered') ?? '');
        $description = html_entity_decode(trim($description), ENT_QUOTES, 'UTF-8');
        
        // Dates
        $startDateVal = $this->getWpFieldValue($wpPost, $this->fieldMappings['start_date']);
        $startDate = $startDateVal ? Carbon::parse($startDateVal) : now();
        
        $endDateVal = $this->getWpFieldValue($wpPost, $this->fieldMappings['end_date'] ?? null);
        $endDate = $endDateVal ? Carbon::parse($endDateVal) : null;
        
        // ensure valid date range
        if ($endDate && $endDate->lt($startDate)) {
            $endDate = $startDate->copy()->addHour();
        }
        
        // Location
        $location = $this->getWpFieldValue($wpPost, $this->fieldMappings['location'] ?? null);
        $locationAddress = $this->getWpFieldValue($wpPost, $this->fieldMappings['location_address'] ?? null);
        $locationUrl = $this->getWpFieldValue($wpPost, $this->fieldMappings['location_url'] ?? null);
        $onlineUrl = $this->getWpFieldValue($wpPost, $this->fieldMappings['online_meeting_url'] ?? null);
        
        // Featured Image
        $featuredImageId = null;
        if ($this->downloadFeaturedImage) {
            $featuredImageId = $this->downloadFeaturedImage($wpPost);
        }
        
        Event::create([
            'title' => $title,
            'slug' => $slug,
            'content' => $content,
            'description' => $description,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'location' => $location,
            'location_address' => $locationAddress,
            'location_address' => $locationAddress,
            'location_url' => $locationUrl,
            'online_meeting_url' => $onlineUrl,
            'featured_image_id' => $featuredImageId,
            'status' => $this->defaultStatus,
            'event_type' => $this->determineEventType($wpPost),
            'category_id' => $this->determineCategoryId($title),
            'published_at' => $this->getWpFieldValue($wpPost, 'date') ? Carbon::parse($this->getWpFieldValue($wpPost, 'date')) : now(),
            'author_id' => auth()->id(),
            'is_all_day' => false, // Default
            'requires_registration' => false,
        ]);
        
        return 'success';
    }

    protected function getWpFieldValue($wpPost, $fieldPath)
    {
        if (empty($fieldPath)) return null;
        
        $parts = explode('.', $fieldPath);
        $value = $wpPost;
        
        foreach ($parts as $part) {
            if (is_array($value) && isset($value[$part])) {
                $value = $value[$part];
            } else {
                return null;
            }
        }
        
        return $value;
    }

    protected function processContentImages($content)
    {
        $content = preg_replace('/\s+srcset=["\'][^"\']*["\']/', '', $content);
        $content = preg_replace('/\s+sizes=["\'][^"\']*["\']/', '', $content);
        
        preg_match_all('/<img[^>]+src=["\']([^"\']+)["\'][^>]*>/i', $content, $matches);
        
        if (empty($matches[1])) return $content;
        
        foreach ($matches[1] as $originalUrl) {
            // Basic filtering of what to download logic (same as CPT migration)
            if (Str::startsWith($originalUrl, ['/storage/', '/media/', 'data:'])) continue;
            
            try {
                $newPath = $this->downloadImage($originalUrl);
                if ($newPath) {
                   $content = str_replace($originalUrl, '/storage/' . $newPath, $content);
                }
            } catch (\Exception $e) {}
        }
        
        return $content;
    }

    protected function downloadFeaturedImage($wpPost)
    {
        // Try embedded
        $url = $wpPost['_embedded']['wp:featuredmedia'][0]['source_url'] ?? null;
        
        if (!$url && !empty($wpPost['featured_media'])) {
            // Try fetching
            try {
               $resp = Http::get($this->wpUrl . '/wp-json/wp/v2/media/' . $wpPost['featured_media']);
               $url = $resp->json()['source_url'] ?? null;
            } catch (\Exception $e) {}
        }
        
        if ($url) {
            $path = $this->downloadImage($url);
            if ($path) {
                // Determine mime type and other info for Media record
                $disk = Storage::disk(config('media.disk', 'public'));
                $fullPath = $disk->path($path);
                $size = $disk->size($path);
                $info = @getimagesize($fullPath);
                
                $media = Media::create([
                    'filename' => basename($path),
                    'original_filename' => basename($url),
                    'mime_type' => $info['mime'] ?? 'image/jpeg',
                    'file_extension' => pathinfo($path, PATHINFO_EXTENSION),
                    'size' => $size,
                    'path' => $path,
                    'uploaded_by' => auth()->id(),
                    'description' => 'Imported Event Image',
                ]);
                return $media->id;
            }
        }
        
        return null;
    }

    protected function downloadImage($url)
    {
        try {
            if (Str::startsWith($url, '//')) $url = 'https:' . $url;
            
            $content = Http::withOptions(['verify' => false])->get($url)->body();
            if (!$content) return null;
            
            $name = 'event-import-' . Str::random(10) . '.' . pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION) ?: 'jpg';
            $path = config('media.path', 'media') . '/' . $name;
            
            Storage::disk(config('media.disk', 'public'))->put($path, $content);
            
            return $path;
        } catch (\Exception $e) {
            return null;
        }
    }

    public function resetMigration()
    {
        $this->step = 1;
        $this->availablePostTypes = [];
        $this->wpEventFields = [];
        $this->importResults = [];
    }

    public function goBack()
    {
        if ($this->step > 1) $this->step--;
    }

    protected function determineEventType($wpPost)
    {
        // Default to offline
        $type = 'offline';

        // Check _embedded terms (Event Categories, Tags, etc.)
        if (isset($wpPost['_embedded']['wp:term']) && is_array($wpPost['_embedded']['wp:term'])) {
            foreach ($wpPost['_embedded']['wp:term'] as $taxonomyTerms) {
                foreach ($taxonomyTerms as $term) {
                    $name = strtolower($term['name'] ?? '');
                    
                    // Logic: Search for keywords in the category/term name
                    if (Str::contains($name, ['online', 'webinar', 'virtual', 'zoom', 'live stream', 'livestream'])) {
                        return 'online';
                    }
                    
                    if (Str::contains($name, ['hybrid', 'mixed'])) {
                        return 'hybrid';
                    }
                }
            }
        }
        
        return $type;
    }

    protected function determineCategoryId($title)
    {
        $title = strtolower($title);
        $categoryName = 'Others';

        if (Str::contains($title, 'ic-meethub')) {
            $categoryName = 'iC-MeetHub';
        } elseif (Str::contains($title, 'ic-talk')) {
            $categoryName = 'iC-Talk';
        } elseif (Str::contains($title, 'ic-connect')) {
            $categoryName = 'iC-Connect';
        } elseif (Str::contains($title, 'ic-class')) {
            $categoryName = 'iC-Class';
        }

        // Try to find the category by name, creating it if it doesn't exist? 
        // User said "kategori yang ada di laravel sekarang", implying they exist.
        // But for safety, we find by name or slug.
        
        $category = \Plugins\Events\Models\EventCategory::where('name', 'LIKE', $categoryName)->first();
        
        if (!$category && $categoryName !== 'Others') {
             // Fallback to Others if the specific one isn't found
             $category = \Plugins\Events\Models\EventCategory::where('name', 'Others')->first();
        }

        return $category ? $category->id : null;
    }

    public function render()
    {
        return view('events::livewire.wordpress-event-migration');
    }
}

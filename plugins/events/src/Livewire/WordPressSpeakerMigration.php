<?php

namespace Plugins\Events\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Plugins\Events\Models\Speaker;
use App\Models\Media;

class WordPressSpeakerMigration extends Component
{
    // URL Input
    public $wpUrl = '';
    public $isValidUrl = false;
    
    // WordPress Discovery
    public $availablePostTypes = [];
    public $selectedWpPostType = ''; 
    public $wpSpeakerFields = []; // Sample fields from WP
    
    // CMS Speaker Fields (Fixed)
    public $cmsSpeakerFields = [];
    
    // Field Mappings
    public $fieldMappings = [];
    
    // Import Options
    public $downloadPhoto = true;
    public $defaultStatus = true; // Active
    
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
        // Define standard Speaker fields to map to
        $this->cmsSpeakerFields = [
            ['key' => 'name', 'label' => 'Name', 'required' => true],
            ['key' => 'slug', 'label' => 'Slug', 'required' => false],
            ['key' => 'bio', 'label' => 'Bio', 'required' => false],
            ['key' => 'title', 'label' => 'Job Title', 'required' => false],
            ['key' => 'company', 'label' => 'Company', 'required' => false],
            ['key' => 'email', 'label' => 'Email', 'required' => false],
            ['key' => 'phone', 'label' => 'Phone', 'required' => false],
            ['key' => 'linkedin_url', 'label' => 'LinkedIn URL', 'required' => false],
            ['key' => 'twitter_url', 'label' => 'Twitter/X URL', 'required' => false],
            ['key' => 'facebook_url', 'label' => 'Facebook URL', 'required' => false],
            ['key' => 'instagram_url', 'label' => 'Instagram URL', 'required' => false],
            ['key' => 'website', 'label' => 'Website URL', 'required' => false],
            ['key' => 'photo', 'label' => 'Photo', 'required' => false],
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
            
            $this->wpSpeakerFields = [];
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
                    $this->wpSpeakerFields[] = [
                        'path' => $fieldPath . '.rendered',
                        'label' => ucfirst(str_replace('_', ' ', $key)) . ' (rendered)',
                        'sample' => Str::limit(strip_tags($value['rendered']), 30),
                    ];
                } else if ($key === 'meta' || $key === 'acf') {
                    $this->discoverFields($value, $fieldPath);
                }
            } else if (is_scalar($value)) {
                $this->wpSpeakerFields[] = [
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
            'name' => 'title.rendered',
            'slug' => 'slug',
            'bio' => 'content.rendered',
            'photo' => 'featured_media',
        ];
    }

    public function importAllSpeakers()
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
                    Log::warning('Failed to fetch speakers page ' . $page);
                    continue;
                }
                
                $posts = $response->json();
                
                foreach ($posts as $wpPost) {
                    try {
                        $result = $this->importSingleSpeaker($wpPost);
                        
                        if ($result === 'success') {
                            $this->importResults['success']++;
                        } elseif ($result === 'skipped') {
                            $this->importResults['skipped']++;
                            $this->importResults['skipped_items'][] = [
                                'name' => $this->getWpFieldValue($wpPost, 'title.rendered') ?? 'Unknown',
                                'reason' => 'Slug exists',
                            ];
                        }
                    } catch (\Exception $e) {
                        $this->importResults['failed']++;
                        $this->importResults['errors'][] = [
                            'name' => $this->getWpFieldValue($wpPost, 'title.rendered') ?? 'Unknown',
                            'error' => $e->getMessage(),
                        ];
                        // Log::error('Speaker import failed: ' . $e->getMessage());
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

    protected function importSingleSpeaker($wpPost)
    {
        $name = html_entity_decode(strip_tags($this->getWpFieldValue($wpPost, $this->fieldMappings['name'] ?? 'title.rendered') ?? ''), ENT_QUOTES, 'UTF-8');
        $slug = $this->getWpFieldValue($wpPost, $this->fieldMappings['slug'] ?? 'slug') ?? Str::slug($name);
        
        if (Speaker::withTrashed()->where('slug', $slug)->exists()) {
            return 'skipped';
        }
        
        $bio = $this->getWpFieldValue($wpPost, $this->fieldMappings['bio'] ?? 'content.rendered') ?? '';
        // Could process images in bio if needed, but skipping for now for speakers
        
        $title = $this->getWpFieldValue($wpPost, $this->fieldMappings['title'] ?? null);
        $company = $this->getWpFieldValue($wpPost, $this->fieldMappings['company'] ?? null);
        $email = $this->getWpFieldValue($wpPost, $this->fieldMappings['email'] ?? null);
        $phone = $this->getWpFieldValue($wpPost, $this->fieldMappings['phone'] ?? null);
        
        $socials = [
            'linkedin_url' => $this->getWpFieldValue($wpPost, $this->fieldMappings['linkedin_url'] ?? null),
            'twitter_url' => $this->getWpFieldValue($wpPost, $this->fieldMappings['twitter_url'] ?? null),
            'facebook_url' => $this->getWpFieldValue($wpPost, $this->fieldMappings['facebook_url'] ?? null),
            'instagram_url' => $this->getWpFieldValue($wpPost, $this->fieldMappings['instagram_url'] ?? null),
            'website' => $this->getWpFieldValue($wpPost, $this->fieldMappings['website'] ?? null),
        ];

        // Photo
        $photoId = null;
        if ($this->downloadPhoto) {
            $photoId = $this->downloadPhoto($wpPost);
        }
        
        Speaker::create(array_merge([
            'name' => $name,
            'slug' => $slug,
            'bio' => $bio,
            'title' => $title,
            'company' => $company,
            'email' => $email,
            'phone' => $phone,
            'photo_id' => $photoId,
            'is_active' => $this->defaultStatus,
        ], $socials));
        
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

    protected function downloadPhoto($wpPost)
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
                    'description' => 'Imported Speaker Photo',
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
            
            $name = 'speaker-import-' . Str::random(10) . '.' . pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION) ?: 'jpg';
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
        $this->wpSpeakerFields = [];
        $this->importResults = [];
    }

    public function goBack()
    {
        if ($this->step > 1) $this->step--;
    }

    public function render()
    {
        return view('events::livewire.wordpress-speaker-migration');
    }
}

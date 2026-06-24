<?php

namespace Plugins\Events\Livewire;

use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\Attributes\On;
use Plugins\Events\Models\Event;
use Plugins\Events\Models\EventCategory;
use Plugins\Events\Models\Speaker;
use App\Models\Media;
use Illuminate\Support\Str;

class EventConsoleGeneral extends Component
{
    use WithFileUploads;

    public $eventId;
    public $event;

    // Basic Info
    public $title = '';
    public $slug = '';
    public $description = '';
    public $content = '';

    // Publishing
    public $status = 'draft';
    public $category_id = null;

    // Location
    public $event_type = 'offline';
    public $location = '';
    public $location_address = '';
    public $location_url = '';
    public $latitude;
    public $longitude;
    public $online_meeting_url = '';



    // Speakers
    public $speakers = [];

    // Media
    public $featured_image;
    public $featured_image_id;
    public $existingFeaturedImage;
    public $gallery_images = [];

    // SEO
    public $meta_title = '';
    public $meta_description = '';
    public $meta_keywords = '';

    protected $listeners = ['console-save' => 'save'];

    #[On('media-selected')]
    public function onMediaSelected($field, $mediaId, $mediaPath, $mediaUrl = null)
    {
        if ($field === 'featured_image') {
            $this->featured_image_id = $mediaId;
            $this->existingFeaturedImage = Media::find($mediaId);
        } elseif ($field === 'gallery_images') {
            if (!in_array($mediaPath, $this->gallery_images)) {
                $this->gallery_images[] = $mediaPath;
            }
        }
    }

    #[On('media-removed')]
    public function onMediaRemoved($field)
    {
        if ($field === 'featured_image') {
            $this->featured_image_id = null;
            $this->existingFeaturedImage = null;
        }
    }

    public function mount($eventId)
    {
        $this->eventId = $eventId;
        $this->event = Event::with(['category', 'featuredImage', 'speakers'])->findOrFail($eventId);
        $this->loadData();
    }

    protected function loadData()
    {
        $e = $this->event;

        $this->title = $e->title;
        $this->slug = $e->slug;
        $this->description = $e->description;
        $this->content = $e->content;
        $this->status = $e->status;
        $this->category_id = $e->category_id;

        // Location
        $this->event_type = $e->event_type ?? 'offline';
        $this->location = $e->location;
        $this->location_address = $e->location_address;
        $this->location_url = $e->location_url;
        $this->latitude = $e->latitude;
        $this->longitude = $e->longitude;
        $this->online_meeting_url = $e->online_meeting_url;



        // Speakers
        $this->speakers = $e->speakers->pluck('id')->toArray();

        // Media
        $this->featured_image_id = $e->featured_image_id;
        $this->existingFeaturedImage = $e->featuredImage;
        $this->gallery_images = $e->gallery_images ?? [];

        // SEO
        $this->meta_title = $e->meta_title;
        $this->meta_description = $e->meta_description;
        $this->meta_keywords = is_array($e->meta_keywords) ? implode(', ', $e->meta_keywords) : ($e->meta_keywords ?? '');
    }

    public function updatedTitle($value)
    {
        // Auto-generate slug only if slug matches old title slug
        $oldSlug = Str::slug($this->event->title);
        if ($this->slug === $oldSlug || empty($this->slug)) {
            $this->slug = $this->makeUniqueSlug(Str::slug($value));
        }
    }

    public function updatedSlug($value)
    {
        // Sanitize slug input
        $this->slug = Str::slug($value);
    }

    public function generateSlug()
    {
        $this->slug = $this->makeUniqueSlug(Str::slug($this->title));
    }

    protected function makeUniqueSlug(string $slug): string
    {
        if (empty($slug)) return '';

        $original = $slug;
        $counter = 2;

        while (Event::where('slug', $slug)->where('id', '!=', $this->eventId)->exists()) {
            $slug = $original . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    public function removeFeaturedImage()
    {
        $this->featured_image_id = null;
        $this->existingFeaturedImage = null;
        $this->featured_image = null;
    }

    public function removeGalleryImage($index)
    {
        if (isset($this->gallery_images[$index])) {
            unset($this->gallery_images[$index]);
            $this->gallery_images = array_values($this->gallery_images);
        }
    }

    public function save()
    {
        $this->validate([
            'title' => 'required|min:3|max:255',
            'slug' => 'required|unique:events,slug,' . $this->eventId,
            'status' => 'required|in:draft,published,cancelled,completed',
            'category_id' => 'nullable|exists:event_categories,id',
        ]);

        // Handle file upload
        if ($this->featured_image) {
            $path = $this->featured_image->store('events', 'public');
            $media = Media::create([
                'file_name' => $this->featured_image->getClientOriginalName(),
                'file_path' => $path,
                'file_type' => $this->featured_image->getClientMimeType(),
                'file_size' => $this->featured_image->getSize(),
                'disk' => 'public',
            ]);
            $this->featured_image_id = $media->id;
            $this->existingFeaturedImage = $media;
        }

        $this->event->update([
            'title' => $this->title,
            'slug' => Str::slug($this->slug ?: $this->title),
            'description' => $this->description,
            'content' => $this->content,
            'status' => $this->status,
            'category_id' => $this->category_id,
            'event_type' => $this->event_type,
            'location' => $this->location,
            'location_address' => $this->location_address,
            'location_url' => $this->location_url,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'online_meeting_url' => $this->online_meeting_url,

            'featured_image_id' => $this->featured_image_id,
            'gallery_images' => $this->gallery_images,
            'meta_title' => $this->meta_title,
            'meta_description' => $this->meta_description,
            'meta_keywords' => !empty($this->meta_keywords) ? array_map('trim', explode(',', $this->meta_keywords)) : [],
            'published_at' => $this->status === 'published' ? ($this->event->published_at ?? now()) : $this->event->published_at,
        ]);

        $this->event->speakers()->sync($this->speakers);

        $this->dispatch('notify', message: 'General settings saved successfully');
    }

    public function render()
    {
        $categories = EventCategory::with('image')->orderBy('order')->get();
        $availableSpeakers = Speaker::where('is_active', true)->orderBy('name')->get();

        return view('events::livewire.event-console-general', [
            'categories' => $categories,
            'availableSpeakers' => $availableSpeakers,
        ]);
    }
}

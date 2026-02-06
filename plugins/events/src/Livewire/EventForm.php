<?php

namespace Plugins\Events\Livewire;

use Livewire\Component;
use Livewire\WithFileUploads;
use Plugins\Events\Models\Event;
use Plugins\Events\Models\EventCategory;
use Plugins\Events\Models\Speaker;
use App\Models\Media;
use Illuminate\Support\Str;
use Carbon\Carbon;

class EventForm extends Component
{
    use WithFileUploads;

    // Event ID for editing
    public $eventId;
    public $event;

    // Basic Info
    public $title = '';
    public $slug = '';
    public $description = '';
    public $content = '';
    public $category_id = null;
    public $status = 'draft';

    // Date & Time
    public $start_date;
    public $start_time;
    public $end_date;
    public $end_time;
    public $is_all_day = false;
    public $timezone = 'Asia/Jakarta';

    // Location
    public $event_type = 'offline';
    public $location = '';
    public $location_address = '';
    public $location_url = '';
    public $latitude;
    public $longitude;
    public $online_meeting_url = '';

    // Registration
    public $requires_registration = false;
    public $registration_requires_approval = false;
    public $max_participants;
    public $registration_deadline;

    // Media
    public $featured_image;
    public $featured_image_id;
    public $existingFeaturedImage;
    
    // Gallery
    public $gallery_images = []; // Existing image paths

    // SEO
    public $meta_title = '';
    public $meta_description = '';
    public $meta_keywords = '';

    // Relational
    public $speakers = []; // Array of speaker IDs

    // UI State
    public $activeTab = 'basic';

    protected $listeners = [
        'mediaSelected' => 'handleMediaSelected',
        'media-selected' => 'handleMediaPickerSelected',
    ];

    public function mount($eventId = null)
    {
        $this->eventId = $eventId;
        
        if ($eventId) {
            $this->event = Event::with(['category', 'featuredImage'])->findOrFail($eventId);
            $this->loadEventData();
        } else {
            // Set defaults for new event
            $this->start_date = now()->format('Y-m-d');
            $this->start_time = '09:00';
            $this->timezone = config('app.timezone', 'Asia/Jakarta');
        }
    }

    protected function loadEventData()
    {
        $this->title = $this->event->title;
        $this->slug = $this->event->slug;
        $this->description = $this->event->description;
        $this->content = $this->event->content;
        $this->category_id = $this->event->category_id;
        $this->status = $this->event->status;
        $this->gallery_images = $this->event->gallery_images ?? [];

        // Date & Time
        if ($this->event->start_date) {
            $this->start_date = $this->event->start_date->format('Y-m-d');
            $this->start_time = $this->event->start_date->format('H:i');
        }
        if ($this->event->end_date) {
            $this->end_date = $this->event->end_date->format('Y-m-d');
            $this->end_time = $this->event->end_date->format('H:i');
        }
        $this->is_all_day = $this->event->is_all_day;
        $this->timezone = $this->event->timezone ?? 'Asia/Jakarta';

        // Location
        $this->event_type = $this->event->event_type;
        $this->location = $this->event->location;
        $this->location_address = $this->event->location_address;
        $this->location_url = $this->event->location_url;
        $this->latitude = $this->event->latitude;
        $this->longitude = $this->event->longitude;
        $this->online_meeting_url = $this->event->online_meeting_url;

        // Registration
        $this->requires_registration = $this->event->requires_registration;
        $this->registration_requires_approval = $this->event->registration_requires_approval ?? false;
        $this->max_participants = $this->event->max_participants;
        $this->registration_deadline = $this->event->registration_deadline?->format('Y-m-d');

        // Media
        $this->featured_image_id = $this->event->featured_image_id;
        $this->existingFeaturedImage = $this->event->featuredImage;

        // Speakers
        $this->speakers = $this->event->speakers->pluck('id')->toArray();

        // SEO
        $this->meta_title = $this->event->meta_title;
        $this->meta_description = $this->event->meta_description;
        $this->meta_keywords = $this->event->meta_keywords;
    }

    public function updatedTitle($value)
    {
        if (!$this->eventId) {
            $this->slug = Str::slug($value);
        }
    }

    public function handleMediaSelected($mediaId)
    {
        $this->featured_image_id = $mediaId;
        $this->existingFeaturedImage = Media::find($mediaId);
    }

    public function handleMediaPickerSelected($field, $mediaId, $mediaPath, $mediaUrl = null)
    {
        if ($field === 'gallery_images') {
            // Append to gallery images if not already there
            // We store the path relative to storage root (public/...)
            // The MediaPicker returns full path or relative? 
            // Looking at MediaPicker, it returns $media->path or $media->webp_path.
            // Usually 'events/filename.jpg'. 
            // our gallery_images expects 'events/gallery/filename.jpg' (without public/ prefix usually?)
            
            // Let's check what MediaPicker sends. It sends $media->path.
            // If Media model stores 'events/foo.jpg', that's what we get.
            
            if (!in_array($mediaPath, $this->gallery_images)) {
                $this->gallery_images[] = $mediaPath;
            }
        }
    }

    public function removeFeaturedImage()
    {
        $this->featured_image_id = null;
        $this->existingFeaturedImage = null;
        $this->featured_image = null;
    }

    public function save()
    {
        $this->validate([
            'title' => 'required|min:3|max:255',
            'slug' => 'required|unique:events,slug,' . ($this->eventId ?? 'NULL'),
            'start_date' => 'required|date',
            'start_time' => 'required',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'event_type' => 'required|in:online,offline,hybrid',
            'status' => 'required|in:draft,published,cancelled,completed',
            'category_id' => 'nullable|exists:event_categories,id',
        ]);

        // Handle file upload
        if ($this->featured_image) {
            $path = $this->featured_image->store('events', 'public');
            $media = Media::create([
                'file_name' => $this->featured_image->getClientOriginalName(),
                'file_path' => $path,
                'file_type' => $this->featured_image->getMimeType(),
                'file_size' => $this->featured_image->getSize(),
                'disk' => 'public',
            ]);
            $this->featured_image_id = $media->id;
        }

        // Combine date and time
        $startDateTime = Carbon::parse($this->start_date . ' ' . $this->start_time, $this->timezone);
        $endDateTime = null;
        if ($this->end_date && $this->end_time) {
            $endDateTime = Carbon::parse($this->end_date . ' ' . $this->end_time, $this->timezone);
        }
        if ($this->end_date && $this->end_time) {
            $endDateTime = Carbon::parse($this->end_date . ' ' . $this->end_time, $this->timezone);
        }
        
        // Gallery images are already in $this->gallery_images (updated via listener)

        // Prepare data
        $data = [
            'title' => $this->title,
            'slug' => Str::slug($this->slug ?: $this->title),
            'description' => $this->description,
            'content' => $this->content,
            'category_id' => $this->category_id,
            'status' => $this->status,
            'start_date' => $startDateTime,
            'end_date' => $endDateTime,
            'is_all_day' => $this->is_all_day,
            'timezone' => $this->timezone,
            'event_type' => $this->event_type,
            'location' => $this->location,
            'location_address' => $this->location_address,
            'location_url' => $this->location_url,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'online_meeting_url' => $this->online_meeting_url,
            'requires_registration' => $this->requires_registration,
            'registration_requires_approval' => $this->registration_requires_approval,
            'max_participants' => $this->max_participants,
            'registration_deadline' => $this->registration_deadline ? Carbon::parse($this->registration_deadline) : null,
            'featured_image_id' => $this->featured_image_id,
            'meta_title' => $this->meta_title,
            'meta_description' => $this->meta_description,
            'meta_keywords' => !empty($this->meta_keywords) ? array_map('trim', explode(',', $this->meta_keywords)) : [],
            'meta_keywords' => !empty($this->meta_keywords) ? array_map('trim', explode(',', $this->meta_keywords)) : [],
            'gallery_images' => $this->gallery_images,
        ];

        if ($this->eventId) {
            // Update existing event
            $this->event->update($data);
            $this->event->speakers()->sync($this->speakers);
            session()->flash('success', 'Event updated successfully!');
        } else {
            // Create new event
            $data['author_id'] = auth()->id();
            $data['published_at'] = $this->status === 'published' ? now() : null;
            $event = Event::create($data);
            $event->speakers()->sync($this->speakers);
            $this->eventId = $event->id;
            session()->flash('success', 'Event created successfully!');
            return redirect()->route('admin.events.edit', $event->id);
        }
        
        // Reset uploads
        // $this->gallery_images is kept to show the current state
    }

    public function removeGalleryImage($index)
    {
        if (isset($this->gallery_images[$index])) {
            unset($this->gallery_images[$index]);
            $this->gallery_images = array_values($this->gallery_images); // Reindex
        }
    }

    public function render()
    {
        $categories = EventCategory::with('image')->orderBy('order')->get();
        $timezones = timezone_identifiers_list();
        $availableSpeakers = Speaker::where('is_active', true)->orderBy('name')->get();

        return view('events::livewire.event-form', [
            'categories' => $categories,
            'timezones' => $timezones,
            'availableSpeakers' => $availableSpeakers,
        ]);
    }
}

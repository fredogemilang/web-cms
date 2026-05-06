<?php

namespace Plugins\Events\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use App\Models\User;
use App\Models\Media;
use Carbon\Carbon;

class Event extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'title',
        'slug',
        'description',
        'content',
        'start_date',
        'end_date',
        'is_all_day',
        'timezone',
        'location',
        'location_address',
        'location_url',
        'latitude',
        'longitude',
        'event_type',
        'online_meeting_url',
        'category_id',
        'requires_registration',
        'registration_requires_approval',
        'requires_corporate_email',
        'max_participants',
        'registered_count',
        'registration_deadline',
        'registration_start_date',
        'registration_end_date',
        'featured_image_id',
        'banner_image',
        'gallery_images',
        'status',
        'success_title',
        'success_desc',
        'success_button',
        'success_link_type',
        'success_link',
        'show_registered_count',
        'enable_track_session',
        'wizard_step',
        'published_at',
        'author_id',
        'sending_email',
        'sender_email',
        'sender_name',
        'cc_to_email',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'settings',
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'registration_deadline' => 'datetime',
        'registration_start_date' => 'datetime',
        'registration_end_date' => 'datetime',
        'published_at' => 'datetime',
        'is_all_day' => 'boolean',
        'requires_registration' => 'boolean',
        'registration_requires_approval' => 'boolean',
        'requires_corporate_email' => 'boolean',
        'sending_email' => 'boolean',
        'show_registered_count' => 'boolean',
        'enable_track_session' => 'boolean',
        'max_participants' => 'integer',
        'registered_count' => 'integer',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'gallery_images' => 'array',
        'meta_keywords' => 'array',
        'settings' => 'array',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($event) {
            if (empty($event->slug)) {
                $event->slug = static::generateUniqueSlug($event->title, $event->id);
            }
        });

        static::updating(function ($event) {
            // Re-generate slug if title changed and slug was auto-generated
            if ($event->isDirty('title') && !$event->getOriginal('slug')) {
                $event->slug = static::generateUniqueSlug($event->title, $event->id);
            }
        });
    }

    /**
     * Generate a unique slug from title.
     * Appends numeric suffix if slug already exists.
     * Compatible with random alphanumeric slug from ERS reference (8-char).
     */
    public static function generateUniqueSlug(string $title, ?int $excludeId = null): string
    {
        $slug = Str::slug($title);
        $originalSlug = $slug;
        $counter = 1;

        $query = static::where('slug', $slug);
        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        while ($query->exists()) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
            $query = static::where('slug', $slug);
            if ($excludeId) {
                $query->where('id', '!=', $excludeId);
            }
        }

        return $slug;
    }

    /**
     * Get the event's category.
     */
    public function category()
    {
        return $this->belongsTo(EventCategory::class, 'category_id');
    }

    /**
     * Get the event's author.
     */
    public function author()
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    /**
     * Get the featured image.
     */
    public function featuredImage()
    {
        return $this->belongsTo(Media::class, 'featured_image_id');
    }

    /**
     * Get event registrations.
     */
    public function registrations()
    {
        return $this->hasMany(EventRegistration::class, 'event_id');
    }

    /**
     * Get confirmed registrations.
     */
    public function confirmedRegistrations()
    {
        return $this->registrations()->where('status', 'confirmed');
    }

    /**
     * Scope: Published events.
     */
    public function scopePublished($query)
    {
        return $query->where('status', 'published')
                     ->where('published_at', '<=', now());
    }

    /**
     * Scope: Upcoming events.
     */
    public function scopeUpcoming($query)
    {
        return $query->where('start_date', '>', now())
                     ->orderBy('start_date', 'asc');
    }

    /**
     * Scope: Past events.
     */
    public function scopePast($query)
    {
        return $query->where('end_date', '<', now())
                     ->orderBy('start_date', 'desc');
    }

    /**
     * Scope: Ongoing events.
     */
    public function scopeOngoing($query)
    {
        return $query->where('start_date', '<=', now())
                     ->where(function($q) {
                         $q->whereNull('end_date')
                           ->orWhere('end_date', '>=', now());
                     });
    }

    /**
     * Scope: By event type.
     */
    public function scopeByType($query, $type)
    {
        return $query->where('event_type', $type);
    }

    /**
     * Check if event is upcoming.
     */
    public function getIsUpcomingAttribute()
    {
        return $this->start_date->isFuture();
    }

    /**
     * Check if event is past.
     */
    public function getIsPastAttribute()
    {
        return $this->end_date ? $this->end_date->isPast() : $this->start_date->isPast();
    }

    /**
     * Check if event is ongoing.
     */
    public function getIsOngoingAttribute()
    {
        $now = now();
        return $this->start_date->isPast() && 
               ($this->end_date ? $this->end_date->isFuture() : true);
    }

    /**
     * Check if registration is open.
     * Respects registration_start_date and registration_end_date per PRD 01.
     */
    public function getIsRegistrationOpenAttribute(): bool
    {
        if (!$this->requires_registration) {
            return false;
        }

        // Check registration start date (when registration opens)
        if ($this->registration_start_date && $this->registration_start_date->isFuture()) {
            return false;
        }

        // Check registration end date (deadline)
        $effectiveEnd = $this->registration_end_date ?? $this->registration_deadline;
        if ($effectiveEnd && $effectiveEnd->isPast()) {
            return false;
        }

        // Check participant capacity
        if ($this->max_participants && $this->registered_count >= $this->max_participants) {
            return false;
        }

        return true;
    }

    /**
     * Get available slots.
     */
    public function getAvailableSlotsAttribute()
    {
        if (!$this->max_participants) {
            return null;
        }

        return max(0, $this->max_participants - $this->registered_count);
    }

    /**
     * Get formatted date range.
     */
    public function getFormattedDateRangeAttribute()
    {
        if ($this->is_all_day) {
            if ($this->end_date && !$this->start_date->isSameDay($this->end_date)) {
                return $this->start_date->format('M d') . ' - ' . $this->end_date->format('M d, Y');
            }
            return $this->start_date->format('M d, Y');
        }

        if ($this->end_date) {
            if ($this->start_date->isSameDay($this->end_date)) {
                return $this->start_date->format('M d, Y') . ' • ' . 
                       $this->start_date->format('g:i A') . ' - ' . 
                       $this->end_date->format('g:i A');
            }
            return $this->start_date->format('M d, g:i A') . ' - ' . 
                   $this->end_date->format('M d, g:i A');
        }

        return $this->start_date->format('M d, Y • g:i A');
    }

    /**
     * Increment registered count.
     */
    public function incrementRegisteredCount()
    {
        $this->increment('registered_count');
    }

    /**
     * Decrement registered count.
     */
    public function decrementRegisteredCount()
    {
        $this->decrement('registered_count');
    }

    /**
     * The speakers that belong to the event.
     */
    public function speakers()
    {
        return $this->belongsToMany(Speaker::class, 'event_speaker')
                    ->withPivot('order')
                    ->orderByPivot('order');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PageBlock extends Model
{
    protected $fillable = [
        'page_id',
        'parent_block_id',
        'name',
        'type',
        'label',
        'value',
        'options',
        'order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'options' => 'array',
            'order' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    // Available block types configuration
    public static array $blockTypes = [
        'text' => [
            'label' => 'Text',
            'icon' => 'format_align_left',
            'color' => 'blue',
            'description' => 'Single line text input',
        ],
        'textarea' => [
            'label' => 'Textarea',
            'icon' => 'subject',
            'color' => 'purple',
            'description' => 'Multi-line text area',
        ],
        'wysiwyg' => [
            'label' => 'WYSIWYG',
            'icon' => 'edit_note',
            'color' => 'emerald',
            'description' => 'Rich text editor',
        ],
        'number' => [
            'label' => 'Number',
            'icon' => 'pin',
            'color' => 'orange',
            'description' => 'Numeric input',
        ],
        'select' => [
            'label' => 'Select',
            'icon' => 'list_alt',
            'color' => 'pink',
            'description' => 'Dropdown selection',
        ],
        'radio' => [
            'label' => 'Radio',
            'icon' => 'radio_button_checked',
            'color' => 'indigo',
            'description' => 'Radio button group',
        ],
        'checkbox' => [
            'label' => 'Checkbox',
            'icon' => 'check_box',
            'color' => 'cyan',
            'description' => 'Multiple checkboxes',
        ],
        'switcher' => [
            'label' => 'Switcher',
            'icon' => 'toggle_on',
            'color' => 'amber',
            'description' => 'Toggle switch',
        ],
        'media' => [
            'label' => 'Media',
            'icon' => 'perm_media',
            'color' => 'violet',
            'description' => 'Single file upload',
        ],
        'gallery' => [
            'label' => 'Gallery',
            'icon' => 'photo_library',
            'color' => 'rose',
            'description' => 'Multiple images',
        ],
        'date' => [
            'label' => 'Date',
            'icon' => 'calendar_month',
            'color' => 'lime',
            'description' => 'Date picker',
        ],
        'time' => [
            'label' => 'Time',
            'icon' => 'schedule',
            'color' => 'teal',
            'description' => 'Time picker',
        ],
        'datetime' => [
            'label' => 'DateTime',
            'icon' => 'event_repeat',
            'color' => 'yellow',
            'description' => 'Date & time picker',
        ],
        'icon' => [
            'label' => 'Icon Picker',
            'icon' => 'sentiment_satisfied',
            'color' => 'fuchsia',
            'description' => 'Icon selection',
        ],
        'color' => [
            'label' => 'Color Picker',
            'icon' => 'palette',
            'color' => 'red',
            'description' => 'Color selection',
        ],
        'posts' => [
            'label' => 'Posts',
            'icon' => 'article',
            'color' => 'sky',
            'description' => 'Blog post selector',
        ],
        'repeater' => [
            'label' => 'Repeater',
            'icon' => 'repeat',
            'color' => 'neutral',
            'description' => 'Repeatable field group',
        ],
    ];

    // Color mappings for Tailwind classes
    public static array $colorClasses = [
        'blue' => 'bg-blue-500/10 text-blue-500',
        'purple' => 'bg-purple-500/10 text-purple-500',
        'emerald' => 'bg-emerald-500/10 text-emerald-500',
        'orange' => 'bg-orange-500/10 text-orange-500',
        'pink' => 'bg-pink-500/10 text-pink-500',
        'indigo' => 'bg-indigo-500/10 text-indigo-500',
        'cyan' => 'bg-cyan-500/10 text-cyan-500',
        'amber' => 'bg-amber-500/10 text-amber-500',
        'violet' => 'bg-violet-500/10 text-violet-500',
        'rose' => 'bg-rose-500/10 text-rose-500',
        'lime' => 'bg-lime-500/10 text-lime-500',
        'teal' => 'bg-teal-500/10 text-teal-500',
        'yellow' => 'bg-yellow-500/10 text-yellow-500',
        'fuchsia' => 'bg-fuchsia-500/10 text-fuchsia-500',
        'red' => 'bg-red-500/10 text-red-500',
        'sky' => 'bg-sky-500/10 text-sky-500',
        'neutral' => 'bg-neutral-500/10 text-neutral-400',
    ];

    // === RELATIONSHIPS ===

    public function page(): BelongsTo
    {
        return $this->belongsTo(Page::class);
    }

    public function parentBlock(): BelongsTo
    {
        return $this->belongsTo(PageBlock::class, 'parent_block_id');
    }

    public function childBlocks(): HasMany
    {
        return $this->hasMany(PageBlock::class, 'parent_block_id')->orderBy('order');
    }

    // === HELPERS ===

    public function getTypeConfig(): array
    {
        return static::$blockTypes[$this->type] ?? [];
    }

    public function getTypeLabel(): string
    {
        return $this->getTypeConfig()['label'] ?? ucfirst($this->type);
    }

    public function getTypeIcon(): string
    {
        return $this->getTypeConfig()['icon'] ?? 'help';
    }

    public function getTypeColor(): string
    {
        return $this->getTypeConfig()['color'] ?? 'gray';
    }

    public function getColorClasses(): string
    {
        $color = $this->getTypeColor();
        return static::$colorClasses[$color] ?? 'bg-gray-500/10 text-gray-500';
    }

    public function isRepeater(): bool
    {
        return $this->type === 'repeater';
    }

    public function getDecodedValue()
    {
        // For checkbox, gallery, posts - decode JSON arrays
        if (in_array($this->type, ['checkbox', 'gallery', 'posts'])) {
            return is_string($this->value) ? json_decode($this->value, true) : $this->value;
        }

        // For switcher - return boolean
        if ($this->type === 'switcher') {
            return filter_var($this->value, FILTER_VALIDATE_BOOLEAN);
        }

        // For number - return numeric value
        if ($this->type === 'number') {
            return is_numeric($this->value) ? (float) $this->value : null;
        }

        return $this->value;
    }

    public function getOption(string $key, $default = null)
    {
        return $this->options[$key] ?? $default;
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class MetaField extends Model
{
    protected $fillable = [
        'name',
        'label',
        'type',
        'description',
        'options',
        'validation',
        'default_value',
        'is_required',
        'order',
        'fieldable_type',
        'fieldable_id',
        'field_group',
        'conditional_logic',
        'is_active',
    ];

    protected $casts = [
        'options' => 'array',
        'validation' => 'array',
        'is_required' => 'boolean',
        'order' => 'integer',
        'conditional_logic' => 'array',
        'is_active' => 'boolean',
    ];

    /**
     * Available field types
     */
    public static array $fieldTypes = [
        'text' => [
            'label' => 'Text',
            'icon' => 'text_fields',
            'description' => 'Single line text input',
        ],
        'textarea' => [
            'label' => 'Textarea',
            'icon' => 'notes',
            'description' => 'Multi-line text input',
        ],
        'wysiwyg' => [
            'label' => 'WYSIWYG Editor',
            'icon' => 'edit_note',
            'description' => 'Rich text editor with formatting',
        ],
        'number' => [
            'label' => 'Number',
            'icon' => 'pin',
            'description' => 'Numeric input with optional min/max',
        ],
        'email' => [
            'label' => 'Email',
            'icon' => 'email',
            'description' => 'Email address input',
        ],
        'url' => [
            'label' => 'URL',
            'icon' => 'link',
            'description' => 'URL/Link input',
        ],
        'date' => [
            'label' => 'Date',
            'icon' => 'calendar_today',
            'description' => 'Date picker',
        ],
        'datetime' => [
            'label' => 'Date & Time',
            'icon' => 'schedule',
            'description' => 'Date and time picker',
        ],
        'time' => [
            'label' => 'Time',
            'icon' => 'access_time',
            'description' => 'Time picker',
        ],
        'select' => [
            'label' => 'Select',
            'icon' => 'arrow_drop_down_circle',
            'description' => 'Dropdown selection',
        ],
        'radio' => [
            'label' => 'Radio',
            'icon' => 'radio_button_checked',
            'description' => 'Radio button selection',
        ],
        'checkbox' => [
            'label' => 'Checkbox',
            'icon' => 'check_box',
            'description' => 'Multiple checkbox selection',
        ],
        'switcher' => [
            'label' => 'Switcher',
            'icon' => 'toggle_on',
            'description' => 'On/Off toggle switch',
        ],
        'media' => [
            'label' => 'Media',
            'icon' => 'image',
            'description' => 'Single image/file picker',
        ],
        'gallery' => [
            'label' => 'Gallery',
            'icon' => 'photo_library',
            'description' => 'Multiple images picker',
        ],
        'repeater' => [
            'label' => 'Repeater',
            'icon' => 'repeat',
            'description' => 'Repeatable group of fields',
        ],
        'color' => [
            'label' => 'Color',
            'icon' => 'palette',
            'description' => 'Color picker',
        ],
    ];

    /**
     * Get the parent model (CPT or Taxonomy)
     */
    public function fieldable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Scope for active fields
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for fields in a specific group
     */
    public function scopeInGroup($query, string $group)
    {
        return $query->where('field_group', $group);
    }

    /**
     * Get field type info
     */
    public function getTypeInfoAttribute(): array
    {
        return self::$fieldTypes[$this->type] ?? [
            'label' => ucfirst($this->type),
            'icon' => 'help',
            'description' => '',
        ];
    }

    /**
     * Generate validation rules for this field
     */
    public function getValidationRulesAttribute(): array
    {
        $rules = [];

        if ($this->is_required) {
            $rules[] = 'required';
        } else {
            $rules[] = 'nullable';
        }

        // Type-specific rules
        switch ($this->type) {
            case 'email':
                $rules[] = 'email';
                break;
            case 'url':
                $rules[] = 'url';
                break;
            case 'number':
                $rules[] = 'numeric';
                if (isset($this->options['min'])) {
                    $rules[] = 'min:' . $this->options['min'];
                }
                if (isset($this->options['max'])) {
                    $rules[] = 'max:' . $this->options['max'];
                }
                break;
            case 'date':
            case 'datetime':
                $rules[] = 'date';
                break;
        }

        // Merge with custom validation rules
        if (!empty($this->validation)) {
            $rules = array_merge($rules, $this->validation);
        }

        return $rules;
    }
}

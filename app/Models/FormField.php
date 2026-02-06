<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FormField extends Model
{
    use HasFactory;

    protected $fillable = [
        'form_id',
        'label',
        'field_id',
        'type',
        'options',
        'validation',
        'order',
        'is_required',
        'placeholder',
        'help_text',
        'default_value',
        'conditional_logic',
        'column_width',
        'step_index',
        'advanced_settings',
        'css_class',
        'is_hidden',
    ];

    protected $casts = [
        'options' => 'array',
        'validation' => 'array',
        'conditional_logic' => 'array',
        'advanced_settings' => 'array',
        'is_required' => 'boolean',
        'is_hidden' => 'boolean',
    ];

    /**
     * All available field types with their metadata.
     */
    public const FIELD_TYPES = [
        // Basic Fields
        'text' => ['label' => 'Text', 'icon' => 'title', 'category' => 'basic'],
        'email' => ['label' => 'Email', 'icon' => 'mail', 'category' => 'basic'],
        'tel' => ['label' => 'Phone', 'icon' => 'call', 'category' => 'basic'],
        'textarea' => ['label' => 'Long Text', 'icon' => 'notes', 'category' => 'basic'],
        'number' => ['label' => 'Number', 'icon' => 'numbers', 'category' => 'basic'],
        'date' => ['label' => 'Date', 'icon' => 'calendar_today', 'category' => 'basic'],
        'select' => ['label' => 'Dropdown', 'icon' => 'arrow_drop_down_circle', 'category' => 'basic'],
        'radio' => ['label' => 'Radio Buttons', 'icon' => 'radio_button_checked', 'category' => 'basic'],
        'checkbox' => ['label' => 'Checkboxes', 'icon' => 'check_box', 'category' => 'basic'],
        'file' => ['label' => 'File Upload', 'icon' => 'upload_file', 'category' => 'basic'],
        
        // Advanced Fields
        'name' => ['label' => 'Name (First/Last)', 'icon' => 'person', 'category' => 'advanced'],
        'address' => ['label' => 'Address', 'icon' => 'location_on', 'category' => 'advanced'],
        'url' => ['label' => 'Website URL', 'icon' => 'link', 'category' => 'advanced'],
        'password' => ['label' => 'Password', 'icon' => 'lock', 'category' => 'advanced'],
        'hidden' => ['label' => 'Hidden Field', 'icon' => 'visibility_off', 'category' => 'advanced'],
        'time' => ['label' => 'Time Picker', 'icon' => 'schedule', 'category' => 'advanced'],
        'datetime' => ['label' => 'Date & Time', 'icon' => 'event', 'category' => 'advanced'],
        'color' => ['label' => 'Color Picker', 'icon' => 'palette', 'category' => 'advanced'],
        'range' => ['label' => 'Range Slider', 'icon' => 'tune', 'category' => 'advanced'],
        'rating' => ['label' => 'Star Rating', 'icon' => 'star', 'category' => 'advanced'],
        'signature' => ['label' => 'Signature', 'icon' => 'draw', 'category' => 'advanced'],
        'image' => ['label' => 'Image Upload', 'icon' => 'image', 'category' => 'advanced'],
        'mask' => ['label' => 'Mask Input', 'icon' => 'pin', 'category' => 'advanced'],
        
        // Layout Fields
        'section' => ['label' => 'Section Break', 'icon' => 'horizontal_rule', 'category' => 'layout'],
        'html' => ['label' => 'Custom HTML', 'icon' => 'code', 'category' => 'layout'],
        'divider' => ['label' => 'Divider', 'icon' => 'remove', 'category' => 'layout'],
        
        // Special Fields
        'gdpr' => ['label' => 'GDPR Consent', 'icon' => 'gpp_good', 'category' => 'special'],
        'terms' => ['label' => 'Terms & Conditions', 'icon' => 'description', 'category' => 'special'],
        'nps' => ['label' => 'Net Promoter Score', 'icon' => 'speed', 'category' => 'special'],
        'repeater' => ['label' => 'Repeater', 'icon' => 'repeat', 'category' => 'special'],
    ];

    /**
     * Get the form that owns the field.
     */
    public function form()
    {
        return $this->belongsTo(Form::class);
    }

    /**
     * Render the field HTML.
     */
    public function renderField()
    {
        // Layout fields don't need wrapping
        if (in_array($this->type, ['section', 'divider', 'html'])) {
            return $this->renderLayoutField();
        }

        $widthClass = $this->getWidthClass();
        $html = "<div class=\"form-group mb-3 {$widthClass}\">";
        
        // Most fields need a label (except hidden)
        if ($this->type !== 'hidden') {
            $html .= "<label for=\"{$this->field_id}\" class=\"form-label\">";
            $html .= e($this->label);
            if ($this->is_required) {
                $html .= ' <span class="text-danger">*</span>';
            }
            $html .= '</label>';
        }

        switch ($this->type) {
            // Basic fields
            case 'textarea':
                $html .= $this->renderTextarea();
                break;
            case 'select':
                $html .= $this->renderSelect();
                break;
            case 'radio':
                $html .= $this->renderRadio();
                break;
            case 'checkbox':
                $html .= $this->renderCheckbox();
                break;
            case 'file':
            case 'image':
                $html .= $this->renderFile();
                break;
            
            // Advanced fields
            case 'name':
                $html .= $this->renderNameField();
                break;
            case 'address':
                $html .= $this->renderAddressField();
                break;
            case 'rating':
                $html .= $this->renderRating();
                break;
            case 'range':
                $html .= $this->renderRange();
                break;
            case 'color':
                $html .= $this->renderColor();
                break;
            case 'signature':
                $html .= $this->renderSignature();
                break;
            case 'nps':
                $html .= $this->renderNps();
                break;
            case 'hidden':
                $html .= $this->renderHidden();
                break;
                
            // Special fields
            case 'gdpr':
            case 'terms':
                $html .= $this->renderConsent();
                break;
                
            // Standard input types
            default:
                $html .= $this->renderInput();
        }

        if ($this->help_text && $this->type !== 'hidden') {
            $html .= "<small class=\"form-text text-muted\">{$this->help_text}</small>";
        }

        $html .= '</div>';

        return $html;
    }
    
    /**
     * Get CSS class for column width.
     */
    protected function getWidthClass()
    {
        return match($this->column_width) {
            'half' => 'col-md-6',
            'third' => 'col-md-4',
            'quarter' => 'col-md-3',
            default => 'col-12',
        };
    }

    /**
     * Render input field.
     */
    protected function renderInput()
    {
        $required = $this->is_required ? 'required' : '';
        $placeholder = $this->placeholder ? "placeholder=\"{$this->placeholder}\"" : '';
        $value = $this->default_value ? "value=\"{$this->default_value}\"" : '';

        return "<input type=\"{$this->type}\" name=\"{$this->field_id}\" id=\"{$this->field_id}\" class=\"form-control\" {$placeholder} {$value} {$required}>";
    }

    /**
     * Render textarea field.
     */
    protected function renderTextarea()
    {
        $required = $this->is_required ? 'required' : '';
        $placeholder = $this->placeholder ? "placeholder=\"{$this->placeholder}\"" : '';

        return "<textarea name=\"{$this->field_id}\" id=\"{$this->field_id}\" class=\"form-control\" rows=\"4\" {$placeholder} {$required}>{$this->default_value}</textarea>";
    }

    /**
     * Render select field.
     */
    protected function renderSelect()
    {
        $required = $this->is_required ? 'required' : '';
        $html = "<select name=\"{$this->field_id}\" id=\"{$this->field_id}\" class=\"form-select\" {$required}>";
        
        if ($this->placeholder) {
            $html .= "<option value=\"\">{$this->placeholder}</option>";
        }

        if ($this->options) {
            foreach ($this->options as $option) {
                $selected = ($this->default_value == $option['value']) ? 'selected' : '';
                $html .= "<option value=\"{$option['value']}\" {$selected}>{$option['label']}</option>";
            }
        }

        $html .= '</select>';
        return $html;
    }

    /**
     * Render radio buttons.
     */
    protected function renderRadio()
    {
        $html = '<div>';
        if ($this->options) {
            foreach ($this->options as $index => $option) {
                $checked = ($this->default_value == $option['value']) ? 'checked' : '';
                $required = $this->is_required ? 'required' : '';
                $html .= '<div class="form-check">';
                $html .= "<input type=\"radio\" name=\"{$this->field_id}\" id=\"{$this->field_id}_{$index}\" value=\"{$option['value']}\" class=\"form-check-input\" {$checked} {$required}>";
                $html .= "<label class=\"form-check-label\" for=\"{$this->field_id}_{$index}\">{$option['label']}</label>";
                $html .= '</div>';
            }
        }
        $html .= '</div>';
        return $html;
    }

    /**
     * Render checkboxes.
     */
    protected function renderCheckbox()
    {
        $html = '<div>';
        if ($this->options) {
            foreach ($this->options as $index => $option) {
                $defaultValues = is_array($this->default_value) ? $this->default_value : [];
                $checked = in_array($option['value'], $defaultValues) ? 'checked' : '';
                $html .= '<div class="form-check">';
                $html .= "<input type=\"checkbox\" name=\"{$this->field_id}[]\" id=\"{$this->field_id}_{$index}\" value=\"{$option['value']}\" class=\"form-check-input\" {$checked}>";
                $html .= "<label class=\"form-check-label\" for=\"{$this->field_id}_{$index}\">{$option['label']}</label>";
                $html .= '</div>';
            }
        }
        $html .= '</div>';
        return $html;
    }

    /**
     * Render file input.
     */
    protected function renderFile()
    {
        $required = $this->is_required ? 'required' : '';
        
        // Handle accept attribute from settings
        $accept = '';
        if ($this->type === 'image') {
            $accept = 'accept="image/*"';
        } elseif (!empty($this->advanced_settings['allowed_file_types'])) {
            $types = $this->advanced_settings['allowed_file_types'];
            // Ensure types start with . if they are extensions
            $types = array_map(function($type) {
                return str_starts_with($type, '.') ? $type : '.' . $type;
            }, explode(',', str_replace(' ', '', $types)));
            $accept = 'accept="' . implode(',', $types) . '"';
        }
        
        // Handle multiple files
        $multiple = !empty($this->advanced_settings['max_files']) && $this->advanced_settings['max_files'] > 1 ? 'multiple' : '';
        $name = $multiple ? "{$this->field_id}[]" : $this->field_id;
        
        return "<input type=\"file\" name=\"{$name}\" id=\"{$this->field_id}\" class=\"form-control\" {$accept} {$multiple} {$required}>";
    }
    
    /**
     * Render star rating field.
     */
    protected function renderRating()
    {
        $maxRating = $this->advanced_settings['max_rating'] ?? 5;
        $required = $this->is_required ? 'required' : '';
        
        $html = '<div class="rating-field" data-field="' . $this->field_id . '">';
        $html .= "<input type=\"hidden\" name=\"{$this->field_id}\" id=\"{$this->field_id}\" value=\"{$this->default_value}\" {$required}>";
        $html .= '<div class="rating-stars d-flex gap-1">';
        
        for ($i = 1; $i <= $maxRating; $i++) {
            $active = ($this->default_value >= $i) ? 'active text-warning' : 'text-muted';
            $html .= "<span class=\"rating-star {$active}\" data-value=\"{$i}\" style=\"cursor:pointer;font-size:1.5rem;\">★</span>";
        }
        
        $html .= '</div></div>';
        $html .= '<script>
            document.querySelectorAll(".rating-field[data-field=\'' . $this->field_id . '\'] .rating-star").forEach(star => {
                star.addEventListener("click", function() {
                    const value = this.dataset.value;
                    document.getElementById("' . $this->field_id . '").value = value;
                    this.parentElement.querySelectorAll(".rating-star").forEach((s, i) => {
                        s.classList.toggle("active", i < value);
                        s.classList.toggle("text-warning", i < value);
                        s.classList.toggle("text-muted", i >= value);
                    });
                });
            });
        </script>';
        
        return $html;
    }
    
    /**
     * Render range slider field.
     */
    protected function renderRange()
    {
        $min = $this->advanced_settings['min'] ?? 0;
        $max = $this->advanced_settings['max'] ?? 100;
        $step = $this->advanced_settings['step'] ?? 1;
        $value = $this->default_value ?? $min;
        
        $html = '<div class="range-field">';
        $html .= "<input type=\"range\" name=\"{$this->field_id}\" id=\"{$this->field_id}\" class=\"form-range\" min=\"{$min}\" max=\"{$max}\" step=\"{$step}\" value=\"{$value}\" oninput=\"document.getElementById('{$this->field_id}_value').textContent = this.value\">";
        $html .= "<div class=\"d-flex justify-content-between\"><span>{$min}</span><span id=\"{$this->field_id}_value\">{$value}</span><span>{$max}</span></div>";
        $html .= '</div>';
        
        return $html;
    }
    
    /**
     * Render color picker field.
     */
    protected function renderColor()
    {
        $value = $this->default_value ?? '#000000';
        return "<input type=\"color\" name=\"{$this->field_id}\" id=\"{$this->field_id}\" class=\"form-control form-control-color\" value=\"{$value}\" style=\"width:100%;height:40px;\">";
    }
    
    /**
     * Render signature field.
     */
    protected function renderSignature()
    {
        $required = $this->is_required ? 'required' : '';
        
        $html = '<div class="signature-field">';
        $html .= "<canvas id=\"{$this->field_id}_canvas\" width=\"400\" height=\"150\" style=\"border:1px solid #ccc;border-radius:4px;background:#fff;cursor:crosshair;\"></canvas>";
        $html .= "<input type=\"hidden\" name=\"{$this->field_id}\" id=\"{$this->field_id}\" {$required}>";
        $html .= '<div class="mt-2"><button type="button" class="btn btn-sm btn-outline-secondary" onclick="clearSignature(\'' . $this->field_id . '\')">Clear</button></div>';
        $html .= '</div>';
        $html .= '<script>
            (function() {
                const canvas = document.getElementById("' . $this->field_id . '_canvas");
                const ctx = canvas.getContext("2d");
                let drawing = false;
                canvas.addEventListener("mousedown", () => drawing = true);
                canvas.addEventListener("mouseup", () => { drawing = false; ctx.beginPath(); saveSignature(); });
                canvas.addEventListener("mousemove", draw);
                function draw(e) {
                    if (!drawing) return;
                    const rect = canvas.getBoundingClientRect();
                    ctx.lineWidth = 2;
                    ctx.lineCap = "round";
                    ctx.strokeStyle = "#000";
                    ctx.lineTo(e.clientX - rect.left, e.clientY - rect.top);
                    ctx.stroke();
                    ctx.beginPath();
                    ctx.moveTo(e.clientX - rect.left, e.clientY - rect.top);
                }
                function saveSignature() {
                    document.getElementById("' . $this->field_id . '").value = canvas.toDataURL();
                }
                window.clearSignature = function(id) {
                    ctx.clearRect(0, 0, canvas.width, canvas.height);
                    document.getElementById(id).value = "";
                };
            })();
        </script>';
        
        return $html;
    }
    
    /**
     * Render NPS (Net Promoter Score) field.
     */
    protected function renderNps()
    {
        $required = $this->is_required ? 'required' : '';
        
        $html = '<div class="nps-field">';
        $html .= "<input type=\"hidden\" name=\"{$this->field_id}\" id=\"{$this->field_id}\" {$required}>";
        $html .= '<div class="d-flex justify-content-between mb-1">';
        
        for ($i = 0; $i <= 10; $i++) {
            $color = $i <= 6 ? 'danger' : ($i <= 8 ? 'warning' : 'success');
            $html .= "<button type=\"button\" class=\"btn btn-outline-{$color} btn-sm nps-btn\" data-value=\"{$i}\" style=\"min-width:36px;\" onclick=\"selectNps('{$this->field_id}', {$i})\">{$i}</button>";
        }
        
        $html .= '</div>';
        $html .= '<div class="d-flex justify-content-between"><small class="text-muted">Not likely</small><small class="text-muted">Very likely</small></div>';
        $html .= '</div>';
        $html .= '<script>
            window.selectNps = function(id, value) {
                document.getElementById(id).value = value;
                document.querySelectorAll("#" + id + "_container .nps-btn, [name=\'" + id + "\']").forEach(btn => btn.classList.remove("active"));
                event.target.classList.add("active");
            };
        </script>';
        
        return $html;
    }
    
    /**
     * Render name field (first name / last name).
     */
    protected function renderNameField()
    {
        $required = $this->is_required ? 'required' : '';
        
        $html = '<div class="row g-2">';
        $html .= '<div class="col-md-6">';
        $html .= "<input type=\"text\" name=\"{$this->field_id}[first_name]\" id=\"{$this->field_id}_first\" class=\"form-control\" placeholder=\"First Name\" {$required}>";
        $html .= '</div>';
        $html .= '<div class="col-md-6">';
        $html .= "<input type=\"text\" name=\"{$this->field_id}[last_name]\" id=\"{$this->field_id}_last\" class=\"form-control\" placeholder=\"Last Name\" {$required}>";
        $html .= '</div></div>';
        
        return $html;
    }
    
    /**
     * Render address field (multiple lines).
     */
    protected function renderAddressField()
    {
        $required = $this->is_required ? 'required' : '';
        
        $html = '<div class="address-field space-y-2">';
        $html .= "<input type=\"text\" name=\"{$this->field_id}[street]\" class=\"form-control mb-2\" placeholder=\"Street Address\" {$required}>";
        $html .= '<div class="row g-2">';
        $html .= "<div class=\"col-md-6\"><input type=\"text\" name=\"{$this->field_id}[city]\" class=\"form-control\" placeholder=\"City\" {$required}></div>";
        $html .= "<div class=\"col-md-3\"><input type=\"text\" name=\"{$this->field_id}[state]\" class=\"form-control\" placeholder=\"State\"></div>";
        $html .= "<div class=\"col-md-3\"><input type=\"text\" name=\"{$this->field_id}[zip]\" class=\"form-control\" placeholder=\"ZIP\"></div>";
        $html .= '</div></div>';
        
        return $html;
    }
    
    /**
     * Render consent checkbox (GDPR/Terms).
     */
    protected function renderConsent()
    {
        $required = $this->is_required ? 'required' : '';
        $text = $this->type === 'gdpr' 
            ? ($this->advanced_settings['consent_text'] ?? 'I consent to having my data processed.')
            : ($this->advanced_settings['terms_text'] ?? 'I agree to the Terms & Conditions.');
        
        $html = '<div class="form-check">';
        $html .= "<input type=\"checkbox\" name=\"{$this->field_id}\" id=\"{$this->field_id}\" value=\"1\" class=\"form-check-input\" {$required}>";
        $html .= "<label class=\"form-check-label\" for=\"{$this->field_id}\">{$text}</label>";
        $html .= '</div>';
        
        return $html;
    }
    
    /**
     * Render hidden field.
     */
    protected function renderHidden()
    {
        $value = e($this->default_value);
        return "<input type=\"hidden\" name=\"{$this->field_id}\" id=\"{$this->field_id}\" value=\"{$value}\">";
    }
    
    /**
     * Render layout fields (section, divider, HTML).
     */
    protected function renderLayoutField()
    {
        switch ($this->type) {
            case 'section':
                $html = '<div class="section-break my-4 border-top pt-4">';
                $html .= "<h4>{$this->label}</h4>";
                if ($this->help_text) {
                    $html .= "<p class=\"text-muted\">{$this->help_text}</p>";
                }
                $html .= '</div>';
                return $html;
                
            case 'divider':
                return '<hr class="my-4">';
                
            case 'html':
                return '<div class="custom-html-content">' . ($this->advanced_settings['html_content'] ?? '') . '</div>';
                
            default:
                return '';
        }
    }

    /**
     * Validate field value.
     */
    public function validateValue($value)
    {
        // Layout fields don't need validation
        if (in_array($this->type, ['section', 'divider', 'html'])) {
            return true;
        }
        
        // Required validation - handle different value types
        if ($this->is_required) {
            $isEmpty = $this->isValueEmpty($value);
            if ($isEmpty) {
                return "{$this->label} is required.";
            }
        }

        // Type-specific validation
        if (!$this->isValueEmpty($value)) {
            switch ($this->type) {
                case 'email':
                    if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                        return "{$this->label} must be a valid email address.";
                    }
                    break;
                    
                case 'number':
                    if (!is_numeric($value)) {
                        return "{$this->label} must be a number.";
                    }
                    break;
                    
                case 'tel':
                    if (!preg_match('/^[0-9\-\+\(\)\s]+$/', $value)) {
                        return "{$this->label} must be a valid phone number.";
                    }
                    break;
                    
                case 'url':
                    if (!filter_var($value, FILTER_VALIDATE_URL)) {
                        return "{$this->label} must be a valid URL.";
                    }
                    break;
                    
                case 'rating':
                    $maxRating = $this->advanced_settings['max_rating'] ?? 5;
                    if (!is_numeric($value) || $value < 1 || $value > $maxRating) {
                        return "{$this->label} must be between 1 and {$maxRating}.";
                    }
                    break;
                    
                case 'range':
                    $min = $this->advanced_settings['min'] ?? 0;
                    $max = $this->advanced_settings['max'] ?? 100;
                    if (!is_numeric($value) || $value < $min || $value > $max) {
                        return "{$this->label} must be between {$min} and {$max}.";
                    }
                    break;
                    
                case 'nps':
                    if (!is_numeric($value) || $value < 0 || $value > 10) {
                        return "{$this->label} must be between 0 and 10.";
                    }
                    break;
                    
                case 'color':
                    if (!preg_match('/^#[0-9A-Fa-f]{6}$/', $value)) {
                        return "{$this->label} must be a valid hex color code.";
                    }
                    break;
                    
                case 'date':
                    if (!strtotime($value) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
                        return "{$this->label} must be a valid date.";
                    }
                    break;
                    
                case 'time':
                    if (!preg_match('/^\d{2}:\d{2}(:\d{2})?$/', $value)) {
                        return "{$this->label} must be a valid time.";
                    }
                    break;
                    
                case 'datetime':
                    if (!strtotime($value)) {
                        return "{$this->label} must be a valid date and time.";
                    }
                    break;
                    
                case 'name':
                    if (!is_array($value) || empty($value['first_name'])) {
                        return "{$this->label} first name is required.";
                    }
                    break;
                    
                case 'address':
                    if (!is_array($value) || empty($value['street']) || empty($value['city'])) {
                        return "{$this->label} street and city are required.";
                    }
                    break;
                    
                case 'gdpr':
                case 'terms':
                    if ($this->is_required && empty($value)) {
                        return "You must accept the {$this->label}.";
                    }
                    break;
                    
                case 'file':
                case 'image':
                    // File validation is handled differently because $value is usually temporary path or object
                    // In a real scenario, this validation happens in the controller using Laravel's validation rules
                    // But we can check metadata here if available
                    break;
            }

            // Custom validation rules
            if ($this->validation && is_string($value)) {
                if (isset($this->validation['min']) && strlen($value) < $this->validation['min']) {
                    return "{$this->label} must be at least {$this->validation['min']} characters.";
                }
                if (isset($this->validation['max']) && strlen($value) > $this->validation['max']) {
                    return "{$this->label} must not exceed {$this->validation['max']} characters.";
                }
                if (isset($this->validation['pattern']) && !preg_match($this->validation['pattern'], $value)) {
                    return "{$this->label} format is invalid.";
                }
            }
        }

        return true;
    }
    
    /**
     * Check if a value is empty (handles arrays and strings).
     */
    protected function isValueEmpty($value)
    {
        if (is_array($value)) {
            // For arrays, check if all values are empty
            return empty(array_filter($value, fn($v) => !empty($v)));
        }
        return empty($value) && $value !== '0' && $value !== 0;
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Form extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'is_active',
        'settings',
        'form_type',
        'steps',
        'notifications',
        'confirmations',
        'spam_protection',
        'has_conditional_logic',
        'total_entries',
        'submit_button_text',
        'styling',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'has_conditional_logic' => 'boolean',
        'settings' => 'array',
        'steps' => 'array',
        'notifications' => 'array',
        'confirmations' => 'array',
        'spam_protection' => 'array',
        'styling' => 'array',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($form) {
            if (empty($form->slug)) {
                $form->slug = Str::slug($form->name);
            }
        });
    }

    /**
     * Get the fields for the form.
     */
    public function fields()
    {
        return $this->hasMany(FormField::class)->orderBy('order');
    }

    /**
     * Get the entries for the form.
     */
    public function entries()
    {
        return $this->hasMany(FormEntry::class);
    }

    /**
     * Render the form HTML.
     */
    public function renderForm($attributes = [])
    {
        $defaultAttributes = [
            'method' => 'POST',
            'action' => route('forms.submit', $this->slug),
            'class' => 'form-dynamic',
        ];

        $attributes = array_merge($defaultAttributes, $attributes);
        $attributeString = collect($attributes)
            ->map(fn($value, $key) => "{$key}=\"{$value}\"")
            ->implode(' ');

        $html = "<form {$attributeString}>";
        $html .= csrf_field();
        
        // Spam protection - honeypot field
        if ($this->spam_protection['honeypot'] ?? false) {
            $html .= '<div style="display:none;"><input type="text" name="website_url" tabindex="-1" autocomplete="off"></div>';
        }
        
        // Render fields with row wrapper for multi-column support
        $html .= '<div class="row">';
        foreach ($this->fields as $field) {
            // Wrap each field with data attribute for conditional logic
            $fieldHtml = $field->renderField();
            $fieldHtml = str_replace(
                '<div class="form-group',
                '<div data-field-id="' . $field->field_id . '" class="form-group',
                $fieldHtml
            );
            $html .= $fieldHtml;
        }
        $html .= '</div>';
        
        // Render CAPTCHA widget if configured
        $captchaProvider = $this->spam_protection['captcha_provider'] ?? 'none';
        if ($captchaProvider !== 'none') {
            $captchaService = new \App\Services\CaptchaService();
            $html .= $captchaService->renderWidget($captchaProvider);
        }

        // Submit button with custom text
        $buttonText = $this->submit_button_text ?? 'Submit';
        $html .= '<button type="submit" class="btn btn-primary">' . e($buttonText) . '</button>';
        $html .= '</form>';
        
        // Add conditional logic JavaScript if any field has conditions
        $hasConditions = $this->fields->some(fn($f) => !empty($f->conditional_logic['conditions']));
        if ($hasConditions) {
            $conditionalLogic = new \App\Services\FormConditionalLogic();
            $html .= $conditionalLogic->renderJavaScript($this);
        }

        return $html;
    }

    /**
     * Process form submission.
     */
    public function processSubmission(array $data, $request = null)
    {
        $validatedData = [];
        $errors = [];
        
        // Check honeypot spam protection
        if ($this->spam_protection['honeypot'] ?? false) {
            $honeypotValue = $data['website_url'] ?? null;
            if (!empty($honeypotValue)) {
                // Bot detected - silently reject
                return ['success' => false, 'errors' => ['spam' => 'Submission rejected.']];
            }
            // Remove honeypot from data
            unset($data['website_url']);
        }
        
        // Verify CAPTCHA if configured
        $captchaProvider = $this->spam_protection['captcha_provider'] ?? 'none';
        if ($captchaProvider !== 'none') {
            $captchaService = new \App\Services\CaptchaService();
            $responseField = $captchaService->getResponseFieldName($captchaProvider);
            $captchaResponse = $data[$responseField] ?? '';
            
            $ip = $request ? $request->ip() : null;
            if (!$captchaService->verify($captchaProvider, $captchaResponse, $ip)) {
                return ['success' => false, 'errors' => ['captcha' => 'CAPTCHA verification failed. Please try again.']];
            }
            
            // Remove captcha response from data
            unset($data[$responseField]);
        }
        
        // Initialize conditional logic evaluator
        $conditionalLogic = new \App\Services\FormConditionalLogic();

        foreach ($this->fields as $field) {
            $value = $data[$field->field_id] ?? null;
            
            // Check if field is visible based on conditional logic
            $isVisible = $conditionalLogic->evaluateVisibility($field, $data);
            
            // Skip validation for hidden fields
            if (!$isVisible) {
                continue;
            }
            
            // Validate field
            $validation = $field->validateValue($value);
            if ($validation !== true) {
                $errors[$field->field_id] = $validation;
            } else {
                $validatedData[$field->field_id] = $value;
            }
        }

        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }

        // Create entry
        $entry = $this->entries()->create([
            'data' => $validatedData,
            'ip_address' => $request ? $request->ip() : null,
            'user_agent' => $request ? $request->userAgent() : null,
            'user_id' => auth()->id(),
        ]);
        
        // Send notifications (admin & user confirmation)
        $notificationService = new \App\Services\FormNotificationService();
        $notificationService->sendNotifications($this, $entry);

        return ['success' => true, 'entry' => $entry];
    }

    /**
     * Get form statistics.
     */
    public function getStats()
    {
        return [
            'total_entries' => $this->entries()->count(),
            'entries_today' => $this->entries()->whereDate('created_at', today())->count(),
            'entries_this_week' => $this->entries()->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
            'entries_this_month' => $this->entries()->whereMonth('created_at', now()->month)->count(),
        ];
    }
}

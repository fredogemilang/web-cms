<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CaptchaService
{
    /**
     * Supported CAPTCHA providers.
     */
    public const PROVIDERS = [
        'none' => 'No CAPTCHA',
        'recaptcha_v2' => 'Google reCAPTCHA v2',
        'recaptcha_v3' => 'Google reCAPTCHA v3',
        'turnstile' => 'Cloudflare Turnstile',
    ];
    
    /**
     * Verify CAPTCHA response.
     */
    public function verify(string $provider, string $response, ?string $ip = null): bool
    {
        if (empty($response)) {
            return false;
        }
        
        return match($provider) {
            'recaptcha_v2', 'recaptcha_v3' => $this->verifyRecaptcha($response, $ip),
            'turnstile' => $this->verifyTurnstile($response, $ip),
            default => true, // No captcha = always pass
        };
    }
    
    /**
     * Verify Google reCAPTCHA.
     */
    protected function verifyRecaptcha(string $response, ?string $ip = null): bool
    {
        $secretKey = config('services.recaptcha.secret_key');
        
        if (empty($secretKey)) {
            Log::warning('reCAPTCHA secret key not configured');
            return true; // Pass if not configured
        }
        
        try {
            $result = Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
                'secret' => $secretKey,
                'response' => $response,
                'remoteip' => $ip,
            ]);
            
            $data = $result->json();
            
            // For v3, also check score
            if (isset($data['score'])) {
                $minScore = config('services.recaptcha.min_score', 0.5);
                return ($data['success'] ?? false) && $data['score'] >= $minScore;
            }
            
            return $data['success'] ?? false;
        } catch (\Exception $e) {
            Log::error('reCAPTCHA verification failed: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Verify Cloudflare Turnstile.
     */
    protected function verifyTurnstile(string $response, ?string $ip = null): bool
    {
        $secretKey = config('services.turnstile.secret_key');
        
        if (empty($secretKey)) {
            Log::warning('Turnstile secret key not configured');
            return true; // Pass if not configured
        }
        
        try {
            $result = Http::asForm()->post('https://challenges.cloudflare.com/turnstile/v0/siteverify', [
                'secret' => $secretKey,
                'response' => $response,
                'remoteip' => $ip,
            ]);
            
            $data = $result->json();
            return $data['success'] ?? false;
        } catch (\Exception $e) {
            Log::error('Turnstile verification failed: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Render CAPTCHA widget HTML.
     */
    public function renderWidget(string $provider): string
    {
        return match($provider) {
            'recaptcha_v2' => $this->renderRecaptchaV2(),
            'recaptcha_v3' => $this->renderRecaptchaV3(),
            'turnstile' => $this->renderTurnstile(),
            default => '',
        };
    }
    
    /**
     * Render reCAPTCHA v2 widget.
     */
    protected function renderRecaptchaV2(): string
    {
        $siteKey = config('services.recaptcha.site_key');
        if (empty($siteKey)) return '';
        
        return '
        <div class="mb-3">
            <div class="g-recaptcha" data-sitekey="' . e($siteKey) . '"></div>
        </div>
        <script src="https://www.google.com/recaptcha/api.js" async defer></script>';
    }
    
    /**
     * Render reCAPTCHA v3 widget (invisible).
     */
    protected function renderRecaptchaV3(): string
    {
        $siteKey = config('services.recaptcha.site_key');
        if (empty($siteKey)) return '';
        
        return '
        <input type="hidden" name="g-recaptcha-response" id="g-recaptcha-response">
        <script src="https://www.google.com/recaptcha/api.js?render=' . e($siteKey) . '"></script>
        <script>
            grecaptcha.ready(function() {
                document.querySelectorAll("form.form-dynamic").forEach(function(form) {
                    form.addEventListener("submit", function(e) {
                        e.preventDefault();
                        grecaptcha.execute("' . e($siteKey) . '", {action: "submit"}).then(function(token) {
                            document.getElementById("g-recaptcha-response").value = token;
                            form.submit();
                        });
                    });
                });
            });
        </script>';
    }
    
    /**
     * Render Cloudflare Turnstile widget.
     */
    protected function renderTurnstile(): string
    {
        $siteKey = config('services.turnstile.site_key');
        if (empty($siteKey)) return '';
        
        return '
        <div class="mb-3">
            <div class="cf-turnstile" data-sitekey="' . e($siteKey) . '" data-theme="auto"></div>
        </div>
        <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>';
    }
    
    /**
     * Get the response field name for a provider.
     */
    public function getResponseFieldName(string $provider): string
    {
        return match($provider) {
            'recaptcha_v2', 'recaptcha_v3' => 'g-recaptcha-response',
            'turnstile' => 'cf-turnstile-response',
            default => '',
        };
    }
}

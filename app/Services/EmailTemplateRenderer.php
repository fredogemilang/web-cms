<?php

namespace App\Services;

use App\Models\EmailTemplate;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Mail;

class EmailTemplateRenderer
{
    /**
     * Substitute `{{ path.to.var }}` placeholders in an HTML context — values
     * are HTML-escaped to prevent injection in the rendered body.
     */
    public function render(string $template, array $variables): string
    {
        return $this->substitute($template, $variables, escape: true);
    }

    /**
     * Substitute `{{ path.to.var }}` placeholders in a plain-text context
     * (e.g. mail subject, headers, plain-text body). No HTML escaping so
     * `Tom & Jerry` does not become `Tom &amp; Jerry` in the recipient's
     * inbox listing.
     */
    public function renderRaw(string $template, array $variables): string
    {
        return $this->substitute($template, $variables, escape: false);
    }

    protected function substitute(string $template, array $variables, bool $escape): string
    {
        return preg_replace_callback('/\{\{\s*([a-zA-Z0-9_.]+)\s*\}\}/', function ($m) use ($variables, $escape) {
            $value = Arr::get($variables, $m[1]);
            if (is_object($value) && method_exists($value, '__toString')) {
                $value = (string) $value;
            }
            if (! is_scalar($value)) {
                return '';
            }
            $string = (string) $value;

            return $escape ? e($string) : $string;
        }, $template);
    }

    /**
     * Send a transactional email using the named template (key_name).
     * Returns true if dispatched, false if template missing.
     */
    public function send(string $key, string|array $to, array $variables = []): bool
    {
        $tpl = EmailTemplate::findByKey($key);
        if (! $tpl) {
            \Log::warning("EmailTemplate not found: {$key}");

            return false;
        }

        // Inject common variables for every template.
        $variables = array_replace_recursive([
            'site' => [
                'name' => setting('site_name', config('app.name')),
                'url' => url('/'),
            ],
        ], $variables);

        // Subject and plain-text body are non-HTML contexts → no escaping.
        $subject = $this->renderRaw($tpl->subject, $variables);
        $html = $this->render($tpl->body_html, $variables);
        $text = $tpl->body_text ? $this->renderRaw($tpl->body_text, $variables) : null;

        Mail::send([], [], function ($message) use ($to, $subject, $html, $text) {
            $message->to($to)->subject($subject);
            $message->html($html);
            if ($text) {
                $message->text($text);
            }
        });

        return true;
    }
}

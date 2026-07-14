<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;

class RobotsController extends Controller
{
    public function index(): Response
    {
        $allow = (bool) setting('seo_allow_indexing', true);
        $sitemapOn = (bool) setting('seo_sitemap_enabled', true);
        $extra = (string) setting('seo_robots_extra', '');
        $adminPath = '/'.trim(config('admin.path', 'admin'), '/');

        $lines = ['User-agent: *'];

        if (! $allow) {
            $lines[] = 'Disallow: /';
        } else {
            $lines[] = "Disallow: {$adminPath}";
            $lines[] = 'Disallow: /forms/';
        }

        if ($extra !== '') {
            $lines[] = '';
            $lines[] = trim($extra);
        }

        if ($allow && $sitemapOn) {
            $lines[] = '';
            $lines[] = 'Sitemap: '.url('/sitemap.xml');
        }

        return response(implode("\n", $lines)."\n", 200, [
            'Content-Type' => 'text/plain; charset=UTF-8',
        ]);
    }
}

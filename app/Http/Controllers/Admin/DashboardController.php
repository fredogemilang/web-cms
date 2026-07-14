<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\CptEntry;
use App\Models\FormEntry;
use App\Models\Media;
use App\Models\Page;
use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
        $sinceWeek = now()->subDays(7);

        $stats = [
            'total_users' => User::count(),
            'users_change' => User::where('created_at', '>=', $sinceWeek)->count(),
            'total_pages' => Page::where('status', 'published')->count(),
            'pages_change' => Page::where('created_at', '>=', $sinceWeek)->count(),
            'total_entries' => FormEntry::count(),
            'entries_change' => FormEntry::where('created_at', '>=', $sinceWeek)->count(),
        ];

        // Content created per day for last 7 days (pages + cpt entries + form submissions)
        $performance = collect(range(6, 0))->map(function ($daysAgo) {
            $day = now()->subDays($daysAgo);
            $date = $day->toDateString();
            $count = Page::whereDate('created_at', $date)->count()
                + CptEntry::whereDate('created_at', $date)->count()
                + FormEntry::whereDate('created_at', $date)->count();

            return ['day' => $day->format('D'), 'value' => $count];
        });
        $maxVal = max($performance->pluck('value')->max() ?: 1, 1);
        $performance = $performance->map(fn ($d) => $d + [
            'height' => round(max($d['value'] / $maxVal * 100, 4)).'%',
        ])->all();

        // Recent activity — pull directly from audit log
        $recentActivities = Activity::with('user:id,name')
            ->recent()
            ->take(7)
            ->get()
            ->map(function ($a) {
                $color = match (true) {
                    str_contains($a->action, 'created') => 'text-emerald-500 bg-emerald-500/15',
                    str_contains($a->action, 'updated') => 'text-blue-500 bg-blue-500/15',
                    str_contains($a->action, 'deleted') => 'text-red-500 bg-red-500/15',
                    str_contains($a->action, 'login') => 'text-purple-500 bg-purple-500/15',
                    str_contains($a->action, 'logout') => 'text-gray-500 bg-gray-500/15',
                    default => 'text-[#6F767E] bg-gray-500/15',
                };

                return [
                    // Show name as "Administrator —" then the full sentence
                    'name' => $a->user?->name ?? 'System',
                    'action' => '',
                    'target' => $a->description ?? $a->action,
                    'time' => $a->created_at,
                    'time_human' => $a->created_at->diffForHumans(),
                    'type' => ucfirst(explode('.', $a->action)[0] ?? 'event'),
                    'typeColor' => $color,
                ];
            })
            ->all();

        // Quick inquiries — latest 3 form entries
        $inquiries = FormEntry::with('form')->latest()->take(3)->get()->map(function ($e) {
            $data = is_array($e->data) ? $e->data : [];
            // Try to find a name-ish & message-ish value
            $name = null;
            $message = null;
            foreach ($data as $k => $v) {
                if (! is_string($v)) {
                    continue;
                }
                $key = strtolower((string) $k);
                if (! $name && str_contains($key, 'name')) {
                    $name = $v;
                }
                if (! $message && (str_contains($key, 'message') || str_contains($key, 'inquir') || str_contains($key, 'note'))) {
                    $message = $v;
                }
            }

            return [
                'name' => $name ?: ($e->form?->name ?? 'Submission #'.$e->id),
                'message' => $message ?: 'New form submission',
                'time' => $e->created_at->diffForHumans(),
            ];
        })->all();

        // Site status
        $maintenance = app()->isDownForMaintenance();

        // Media alt-text coverage
        $totalImages = Media::where('mime_type', 'like', 'image/%')->count();
        $withAlt = Media::where('mime_type', 'like', 'image/%')
            ->whereNotNull('alt_text')->where('alt_text', '!=', '')->count();
        $altPct = $totalImages ? (int) round($withAlt / $totalImages * 100) : 0;

        return view('admin.dashboard', compact(
            'stats', 'performance', 'recentActivities', 'inquiries', 'maintenance', 'altPct', 'totalImages'
        ));
    }
}

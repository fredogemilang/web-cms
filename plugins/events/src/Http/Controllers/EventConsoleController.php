<?php

namespace Plugins\Events\Http\Controllers;

use App\Http\Controllers\Controller;
use Plugins\Events\Models\ApprovalType;
use Plugins\Events\Models\Event;

class EventConsoleController extends Controller
{
    /**
     * Shared data for all console pages.
     */
    private function getConsoleData(Event $event): array
    {
        $event->loadCount([
            'registrations',
            'registrations as pending_count' => fn ($q) => $q->where('status', 'pending'),
            'registrations as approved_count' => fn ($q) => $q->where('status', 'approved'),
            'registrations as checkedin_count' => fn ($q) => $q->where('check_in', true),
        ]);

        return [
            'event' => $event,
            'stats' => [
                'registered' => $event->registrations_count,
                'pending' => $event->pending_count,
                'approved' => $event->approved_count,
                'checkedIn' => $event->checkedin_count,
                'quota' => $event->max_participants,
            ],
        ];
    }

    public function overview(Event $event)
    {
        $consoleData = $this->getConsoleData($event);

        // Get registrations count for the last 30 days
        $thirtyDaysAgo = now()->subDays(29)->startOfDay();
        $registrationsTrend = $event->registrations()
            ->where('created_at', '>=', $thirtyDaysAgo)
            ->selectRaw('DATE(created_at) as date, count(*) as count')
            ->groupBy('date')
            ->pluck('count', 'date')
            ->toArray();

        $trendData = [];
        for ($i = 29; $i >= 0; $i--) {
            $day = now()->subDays($i);
            $dateStr = $day->toDateString();
            $trendData[] = [
                'date' => $dateStr,
                'label' => $day->format('d M'),
                'count' => $registrationsTrend[$dateStr] ?? 0,
            ];
        }

        $consoleData['trendData'] = $trendData;

        return view('events::admin.console.overview', $consoleData);
    }

    public function general(Event $event)
    {
        return view('events::admin.console.general', $this->getConsoleData($event));
    }

    public function datetime(Event $event)
    {
        return view('events::admin.console.datetime', $this->getConsoleData($event));
    }

    public function emails(Event $event)
    {
        return view('events::admin.console.emails', $this->getConsoleData($event));
    }

    public function questions(Event $event)
    {
        return view('events::admin.console.questions', $this->getConsoleData($event));
    }

    public function attendees(Event $event)
    {
        $approvalTypes = ApprovalType::where('event_id', $event->id)->get();

        return view('events::admin.console.attendees', array_merge(
            $this->getConsoleData($event),
            ['approvalTypes' => $approvalTypes]
        ));
    }

    public function feedback(Event $event)
    {
        return view('events::admin.console.feedback', $this->getConsoleData($event));
    }

    public function doorprize(Event $event)
    {
        return view('events::admin.console.doorprize', $this->getConsoleData($event));
    }

    public function referrals(Event $event)
    {
        return view('events::admin.console.referrals', $this->getConsoleData($event));
    }
}

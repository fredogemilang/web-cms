<?php

namespace Plugins\Events\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Plugins\Events\Models\Event;
use Plugins\Events\Models\DoorprizeSession;
use Plugins\Events\Models\DoorprizePrize;
use Plugins\Events\Models\DoorprizeWinner;
use Plugins\Events\Models\DoorprizeBan;
use Plugins\Events\Models\EventRegistration;
use Plugins\Events\Models\EventFeedbackResponse;
use Carbon\Carbon;

class DoorprizeDisplayController extends Controller
{
    /**
     * Show the fullscreen doorprize display page.
     */
    public function show(string $slug)
    {
        $event = Event::where('slug', $slug)->firstOrFail();

        $sessions = DoorprizeSession::where('event_id', $event->id)
            ->with(['prizes.winners.registration', 'bans'])
            ->orderBy('order')
            ->get();

        // Build sessions data as JSON for the JS frontend
        $sessionsData = $sessions->map(function ($session) use ($event) {
            return [
                'id' => $session->id,
                'name' => $session->name,
                'require_checkin' => $session->require_checkin,
                'require_feedback' => $session->require_feedback,
                'prizes' => $session->prizes->map(function ($prize) {
                    return [
                        'id' => $prize->id,
                        'name' => $prize->name,
                        'gift_description' => $prize->gift_description,
                        'max_winners' => $prize->max_winners,
                        'winners_count' => $prize->winners->count(),
                        'remaining' => $prize->getRemainingSlots(),
                        'winners' => $prize->winners->map(fn($w) => [
                            'id' => $w->id,
                            'name' => $w->registration->name ?? $w->registration->full_name ?? 'Unknown',
                            'email' => $w->registration->email ?? '',
                            'organization' => $w->registration->organization ?? $w->registration->company_name ?? '',
                            'won_at' => $w->won_at?->format('H:i'),
                        ]),
                    ];
                }),
            ];
        });

        // Get all eligible names for rolling animation
        $eligibleNames = $this->getEligibleNames($event);

        return view('events::frontend.doorprize-display', [
            'event' => $event,
            'sessionsJson' => $sessionsData->toJson(),
            'eligibleNamesJson' => json_encode($eligibleNames),
        ]);
    }

    /**
     * AJAX: Draw a winner for the given session + prize.
     */
    public function draw(Request $request, string $slug)
    {
        $event = Event::where('slug', $slug)->firstOrFail();

        $request->validate([
            'session_id' => 'required|integer',
            'prize_id' => 'required|integer',
        ]);

        $session = DoorprizeSession::where('event_id', $event->id)
            ->where('id', $request->session_id)
            ->first();

        $prize = DoorprizePrize::where('id', $request->prize_id)
            ->where('session_id', $session?->id)
            ->first();

        if (!$session || !$prize) {
            return response()->json(['error' => 'Session or prize not found'], 404);
        }

        if (!$prize->has_available_slots) {
            return response()->json(['error' => 'All winner slots have been filled for this prize'], 400);
        }

        // Build eligible pool
        $query = EventRegistration::where('event_id', $event->id)
            ->where('status', 'approved');

        if ($session->require_checkin) {
            $query->where('check_in', true);
        }

        if ($session->require_feedback) {
            $query->where('feedback_submitted', true);
        }

        // Exclude already won in this session
        $sessionPrizeIds = $session->prizes->pluck('id');
        $alreadyWonIds = DoorprizeWinner::whereIn('prize_id', $sessionPrizeIds)
            ->pluck('registration_id')
            ->toArray();

        if (!empty($alreadyWonIds)) {
            $query->whereNotIn('id', $alreadyWonIds);
        }

        // Exclude banned
        $bannedIds = $session->bans->pluck('registration_id')->toArray();
        if (!empty($bannedIds)) {
            $query->whereNotIn('id', $bannedIds);
        }

        $eligible = $query->get();

        if ($eligible->isEmpty()) {
            return response()->json(['error' => 'No eligible participants remaining'], 400);
        }

        // Random pick
        $winner = $eligible->random();

        // Record winner
        DoorprizeWinner::create([
            'prize_id' => $prize->id,
            'registration_id' => $winner->id,
            'won_at' => Carbon::now(),
        ]);

        // Refresh eligible names (excluding new winner)
        $eligibleNames = $this->getEligibleNames($event);

        // Refresh session data
        $session->load(['prizes.winners.registration', 'bans']);
        $updatedPrize = $session->prizes->firstWhere('id', $prize->id);

        return response()->json([
            'success' => true,
            'winner' => [
                'name' => $winner->name ?? $winner->full_name,
                'email' => $winner->email,
                'organization' => $winner->organization ?? $winner->company_name ?? '',
            ],
            'prize' => [
                'name' => $prize->name,
                'remaining' => $updatedPrize->getRemainingSlots(),
                'winners_count' => $updatedPrize->winners->count(),
            ],
            'eligibleNames' => $eligibleNames,
            'poolSize' => $eligible->count(),
        ]);
    }

    /**
     * AJAX: Draw winners for all available slots in the given session.
     */
    public function drawSession(Request $request, string $slug)
    {
        $event = Event::where('slug', $slug)->firstOrFail();

        $request->validate([
            'session_id' => 'required|integer',
        ]);

        $session = DoorprizeSession::where('event_id', $event->id)
            ->where('id', $request->session_id)
            ->with(['prizes.winners', 'bans'])
            ->first();

        if (!$session) {
            return response()->json(['error' => 'Session not found'], 404);
        }

        // Build eligible pool
        $query = EventRegistration::where('event_id', $event->id)
            ->where('status', 'approved');

        if ($session->require_checkin) {
            $query->where('check_in', true);
        }

        if ($session->require_feedback) {
            $query->where('feedback_submitted', true);
        }

        // Exclude already won in this event (doorprize winners)
        $allWinnersQuery = DoorprizeWinner::whereHas('prize.session', function($q) use ($event) {
            $q->where('event_id', $event->id);
        });
        $alreadyWonIds = $allWinnersQuery->pluck('registration_id')->toArray();

        if (!empty($alreadyWonIds)) {
            $query->whereNotIn('id', $alreadyWonIds);
        }

        // Exclude banned
        $bannedIds = $session->bans->pluck('registration_id')->toArray();
        if (!empty($bannedIds)) {
            $query->whereNotIn('id', $bannedIds);
        }

        $eligiblePool = $query->get();

        if ($eligiblePool->isEmpty()) {
            return response()->json(['error' => 'No eligible participants remaining'], 400);
        }

        $newWinners = [];
        \DB::beginTransaction();
        try {
            foreach ($session->prizes as $prize) {
                $remaining = $prize->getRemainingSlots();
                for ($i = 0; $i < $remaining; $i++) {
                    if ($eligiblePool->isEmpty()) {
                        break;
                    }
                    $winner = $eligiblePool->random();
                    // Remove from pool to prevent double winner in this run
                    $eligiblePool = $eligiblePool->reject(fn($item) => $item->id === $winner->id);

                    // Record winner
                    DoorprizeWinner::create([
                        'prize_id' => $prize->id,
                        'registration_id' => $winner->id,
                        'won_at' => Carbon::now(),
                    ]);

                    $newWinners[] = [
                        'name' => $winner->name ?? $winner->full_name,
                        'email' => $winner->email,
                        'organization' => $winner->organization ?? $winner->company_name ?? '',
                        'prize_id' => $prize->id,
                        'prize_name' => $prize->name,
                    ];
                }
            }
            \DB::commit();
        } catch (\Exception $e) {
            \DB::rollBack();
            return response()->json(['error' => 'Error recording winners: ' . $e->getMessage()], 500);
        }

        if (empty($newWinners)) {
            return response()->json(['error' => 'No winners could be drawn'], 400);
        }

        // Refresh eligible names (excluding new winners)
        $eligibleNames = $this->getEligibleNames($event);

        // Refresh session data
        $session->load(['prizes.winners.registration', 'bans']);
        $prizesData = $session->prizes->map(function ($prize) {
            return [
                'id' => $prize->id,
                'name' => $prize->name,
                'max_winners' => $prize->max_winners,
                'winners_count' => $prize->winners->count(),
                'remaining' => $prize->getRemainingSlots(),
            ];
        });

        return response()->json([
            'success' => true,
            'winners' => $newWinners,
            'prizes' => $prizesData,
            'eligibleNames' => $eligibleNames,
            'poolSize' => $eligiblePool->count(),
        ]);
    }

    /**
     * Get all eligible participant names for the rolling animation.
     */
    private function getEligibleNames(Event $event): array
    {
        $registrations = EventRegistration::where('event_id', $event->id)
            ->where('status', 'approved')
            ->get(['id', 'name', 'full_name', 'organization', 'company_name']);

        return $registrations->map(fn($r) => [
            'name' => $r->name ?? $r->full_name ?? 'Unknown',
            'organization' => $r->organization ?? $r->company_name ?? '',
        ])->values()->toArray();
    }
}

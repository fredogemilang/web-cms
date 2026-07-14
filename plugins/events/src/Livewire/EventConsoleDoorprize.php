<?php

namespace Plugins\Events\Livewire;

use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Plugins\Events\Models\DoorprizeBan;
use Plugins\Events\Models\DoorprizePrize;
use Plugins\Events\Models\DoorprizeSession;
use Plugins\Events\Models\DoorprizeWinner;
use Plugins\Events\Models\Event;
use Plugins\Events\Models\EventRegistration;

class EventConsoleDoorprize extends Component
{
    use WithFileUploads, WithPagination;

    // ─── Core ───
    public Event $event;

    public string $activeSubTab = 'sessions'; // sessions | winners

    // ─── Session CRUD ───
    public bool $showSessionModal = false;

    public ?int $editingSessionId = null;

    public string $sessionName = '';

    public bool $sessionRequireCheckin = true;

    public bool $sessionRequireFeedback = false;

    // ─── Global Settings & Exclusions ───
    public bool $defaultRequireCheckin = true;

    public bool $defaultRequireFeedback = false;

    public string $globalBanSearch = '';

    public string $eligibleSearch = '';

    protected $paginationTheme = 'tailwind';

    public function updatingEligibleSearch()
    {
        $this->resetPage('eligiblePage');
    }

    public function updatedActiveSubTab()
    {
        if ($this->activeSubTab === 'eligible') {
            $this->resetPage('eligiblePage');
        }
    }

    // ─── Prize CRUD ───
    public bool $showPrizeModal = false;

    public ?int $activePrizeSessionId = null;

    public ?int $editingPrizeId = null;

    public string $prizeName = '';

    public string $prizeDescription = '';

    public int $prizeMaxWinners = 1;

    // ─── Raffle ───
    public bool $showRaffleModal = false;

    public ?int $raffleSessionId = null;

    public ?int $rafflePrizeId = null;

    public ?array $raffleResult = null;

    public bool $isRaffling = false;

    // ─── Delete ───
    public bool $showDeleteModal = false;

    public string $deleteType = ''; // session | prize

    public ?int $deletingId = null;

    // ─── Reset All ───
    public bool $showResetAllModal = false;

    // ─── Ban ───
    public bool $showBanModal = false;

    public ?int $banSessionId = null;

    public string $banSearch = '';

    // ─── Background Upload ───
    public $backgroundUpload;

    protected $listeners = [];

    public function mount(Event $event)
    {
        $this->event = $event;
        $settings = $event->settings ?? [];
        $this->defaultRequireCheckin = $settings['doorprize_default_require_checkin'] ?? true;
        $this->defaultRequireFeedback = $settings['doorprize_default_require_feedback'] ?? false;
    }

    // ═══════════════════════════════════════════════════════
    // COMPUTED PROPERTIES
    // ═══════════════════════════════════════════════════════

    public function getSessionsProperty()
    {
        return DoorprizeSession::where('event_id', $this->event->id)
            ->with(['prizes.winners.registration', 'bans.registration'])
            ->orderBy('order')
            ->get();
    }

    public function getAllWinnersProperty()
    {
        return DoorprizeWinner::whereHas('prize', function ($q) {
            $q->whereHas('session', fn ($s) => $s->where('event_id', $this->event->id));
        })->with(['prize.session', 'registration'])
            ->orderByDesc('won_at')
            ->get();
    }

    // ═══════════════════════════════════════════════════════
    // SESSION CRUD
    // ═══════════════════════════════════════════════════════

    public function openAddSession()
    {
        $this->resetSessionForm();
        $this->sessionRequireCheckin = $this->defaultRequireCheckin;
        $this->sessionRequireFeedback = $this->defaultRequireFeedback;
        $this->showSessionModal = true;
    }

    public function openEditSession(int $id)
    {
        $session = DoorprizeSession::find($id);
        if (! $session) {
            return;
        }

        $this->editingSessionId = $session->id;
        $this->sessionName = $session->name;
        $this->sessionRequireCheckin = $session->require_checkin;
        $this->sessionRequireFeedback = $session->require_feedback;
        $this->showSessionModal = true;
    }

    public function saveSession()
    {
        $this->validate([
            'sessionName' => 'required|string|max:255',
        ]);

        $data = [
            'event_id' => $this->event->id,
            'name' => $this->sessionName,
            'require_checkin' => $this->sessionRequireCheckin,
            'require_feedback' => $this->sessionRequireFeedback,
        ];

        if ($this->editingSessionId) {
            DoorprizeSession::find($this->editingSessionId)->update($data);
        } else {
            $maxOrder = DoorprizeSession::where('event_id', $this->event->id)->max('order') ?? 0;
            $data['order'] = $maxOrder + 1;
            DoorprizeSession::create($data);
        }

        $this->showSessionModal = false;
        $this->resetSessionForm();
        $this->dispatch('notify', type: 'success', message: 'Session saved');
    }

    private function resetSessionForm()
    {
        $this->editingSessionId = null;
        $this->sessionName = '';
        $this->sessionRequireCheckin = true;
        $this->sessionRequireFeedback = false;
    }

    // ═══════════════════════════════════════════════════════
    // PRIZE CRUD
    // ═══════════════════════════════════════════════════════

    public function openAddPrize(int $sessionId)
    {
        $this->resetPrizeForm();
        $this->activePrizeSessionId = $sessionId;
        $this->showPrizeModal = true;
    }

    public function openEditPrize(int $id)
    {
        $prize = DoorprizePrize::find($id);
        if (! $prize) {
            return;
        }

        $this->editingPrizeId = $prize->id;
        $this->activePrizeSessionId = $prize->session_id;
        $this->prizeName = $prize->name;
        $this->prizeDescription = $prize->gift_description ?? '';
        $this->prizeMaxWinners = $prize->max_winners;
        $this->showPrizeModal = true;
    }

    public function savePrize()
    {
        $this->validate([
            'prizeName' => 'required|string|max:255',
            'prizeMaxWinners' => 'required|integer|min:1',
        ]);

        $data = [
            'session_id' => $this->activePrizeSessionId,
            'name' => $this->prizeName,
            'gift_description' => $this->prizeDescription,
            'max_winners' => $this->prizeMaxWinners,
        ];

        if ($this->editingPrizeId) {
            DoorprizePrize::find($this->editingPrizeId)->update($data);
        } else {
            $maxOrder = DoorprizePrize::where('session_id', $this->activePrizeSessionId)->max('order') ?? 0;
            $data['order'] = $maxOrder + 1;
            DoorprizePrize::create($data);
        }

        $this->showPrizeModal = false;
        $this->resetPrizeForm();
        $this->dispatch('notify', type: 'success', message: 'Prize saved');
    }

    private function resetPrizeForm()
    {
        $this->editingPrizeId = null;
        $this->activePrizeSessionId = null;
        $this->prizeName = '';
        $this->prizeDescription = '';
        $this->prizeMaxWinners = 1;
    }

    // ═══════════════════════════════════════════════════════
    // DELETE
    // ═══════════════════════════════════════════════════════

    public function confirmDelete(string $type, int $id)
    {
        $this->deleteType = $type;
        $this->deletingId = $id;
        $this->showDeleteModal = true;
    }

    public function deleteItem()
    {
        if ($this->deleteType === 'session') {
            $session = DoorprizeSession::find($this->deletingId);
            if ($session) {
                // Delete all prizes and their winners
                foreach ($session->prizes as $prize) {
                    $prize->winners()->delete();
                    $prize->delete();
                }
                $session->bans()->delete();
                $session->delete();
            }
        } elseif ($this->deleteType === 'prize') {
            $prize = DoorprizePrize::find($this->deletingId);
            if ($prize) {
                $prize->winners()->delete();
                $prize->delete();
            }
        }

        $this->showDeleteModal = false;
        $this->deletingId = null;
        $this->dispatch('notify', type: 'success', message: ucfirst($this->deleteType).' deleted');
    }

    // ═══════════════════════════════════════════════════════
    // RAFFLE ENGINE
    // ═══════════════════════════════════════════════════════

    public function openRaffle(int $sessionId, int $prizeId)
    {
        $this->raffleSessionId = $sessionId;
        $this->rafflePrizeId = $prizeId;
        $this->raffleResult = null;
        $this->isRaffling = false;
        $this->showRaffleModal = true;
    }

    public function drawWinner()
    {
        $session = DoorprizeSession::find($this->raffleSessionId);
        $prize = DoorprizePrize::find($this->rafflePrizeId);

        if (! $session || ! $prize) {
            $this->raffleResult = ['error' => 'Session or prize not found'];

            return;
        }

        // Check remaining slots
        if (! $prize->has_available_slots) {
            $this->raffleResult = ['error' => 'All winner slots have been filled for this prize'];

            return;
        }

        // Build eligible pool
        $query = EventRegistration::where('event_id', $this->event->id)
            ->where('status', 'approved');

        // Require check-in?
        if ($session->require_checkin) {
            $query->where('check_in', true);
        }

        // Require feedback?
        if ($session->require_feedback) {
            $query->where('feedback_submitted', true);
        }

        // Exclude already won in this event (doorprize winners)
        $alreadyWonIds = DoorprizeWinner::whereHas('prize.session', function ($q) {
            $q->where('event_id', $this->event->id);
        })->pluck('registration_id')->toArray();

        if (! empty($alreadyWonIds)) {
            $query->whereNotIn('id', $alreadyWonIds);
        }

        // Exclude banned from this session
        $bannedIds = $session->bans->pluck('registration_id')->toArray();
        // Exclude globally banned
        $globalBannedIds = $this->event->settings['doorprize_global_banned_ids'] ?? [];
        $allBannedIds = array_unique(array_merge($bannedIds, $globalBannedIds));

        if (! empty($allBannedIds)) {
            $query->whereNotIn('id', $allBannedIds);
        }

        $eligible = $query->get();

        if ($eligible->isEmpty()) {
            $this->raffleResult = ['error' => 'No eligible participants remaining'];

            return;
        }

        // Random pick
        $winner = $eligible->random();

        // Record winner
        DoorprizeWinner::create([
            'prize_id' => $prize->id,
            'registration_id' => $winner->id,
            'won_at' => Carbon::now(),
        ]);

        $this->raffleResult = [
            'success' => true,
            'name' => $winner->name ?? $winner->full_name,
            'email' => $winner->email,
            'organization' => $winner->organization ?? $winner->company_name ?? '',
            'prize' => $prize->name,
            'remaining' => $prize->getRemainingSlots() - 1,
            'poolSize' => $eligible->count(),
        ];

        $this->dispatch('notify', type: 'success', message: 'Winner drawn: '.($winner->name ?? $winner->full_name));
    }

    // ═══════════════════════════════════════════════════════
    // BAN MANAGEMENT
    // ═══════════════════════════════════════════════════════

    public function openBanManager(int $sessionId)
    {
        $this->banSessionId = $sessionId;
        $this->banSearch = '';
        $this->showBanModal = true;
    }

    public function getBanCandidatesProperty()
    {
        if (! $this->banSessionId || ! $this->banSearch || strlen($this->banSearch) < 2) {
            return collect();
        }

        $bannedIds = DoorprizeBan::where('session_id', $this->banSessionId)
            ->pluck('registration_id');

        return EventRegistration::where('event_id', $this->event->id)
            ->where('status', 'approved')
            ->whereNotIn('id', $bannedIds)
            ->where(function ($q) {
                $q->where('name', 'like', "%{$this->banSearch}%")
                    ->orWhere('email', 'like', "%{$this->banSearch}%");
            })
            ->limit(10)
            ->get();
    }

    public function banRegistration(int $registrationId)
    {
        DoorprizeBan::create([
            'session_id' => $this->banSessionId,
            'registration_id' => $registrationId,
            'reason' => 'Manually excluded',
        ]);
        $this->dispatch('notify', type: 'success', message: 'Participant excluded from raffle');
    }

    public function unban(int $banId)
    {
        DoorprizeBan::destroy($banId);
        $this->dispatch('notify', type: 'success', message: 'Participant re-included');
    }

    public function removeWinner(int $winnerId)
    {
        DoorprizeWinner::destroy($winnerId);
        $this->dispatch('notify', type: 'success', message: 'Winner removed');
    }

    public function markAsRedraw(int $winnerId)
    {
        $winner = DoorprizeWinner::find($winnerId);
        if ($winner) {
            $status = $winner->status === 'redraw' ? 'active' : 'redraw';
            $winner->update(['status' => $status]);
            $this->dispatch('notify', type: 'success', message: 'Winner status updated to '.$status);
        }
    }

    public function confirmResetAllWinners()
    {
        $this->showResetAllModal = true;
    }

    public function resetAllWinners()
    {
        $count = DoorprizeWinner::whereHas('prize.session', function ($q) {
            $q->where('event_id', $this->event->id);
        })->count();

        DoorprizeWinner::whereHas('prize.session', function ($q) {
            $q->where('event_id', $this->event->id);
        })->delete();

        $this->showResetAllModal = false;
        $this->dispatch('notify', type: 'success', message: "All {$count} winner(s) have been reset");
    }

    // ═══════════════════════════════════════════════════════
    // BACKGROUND IMAGE
    // ═══════════════════════════════════════════════════════

    public function uploadBackground()
    {
        $this->validate(['backgroundUpload' => 'image|max:2048']);

        // Delete old background
        if ($this->event->doorprize_background) {
            Storage::disk('public')->delete($this->event->doorprize_background);
        }

        $path = $this->backgroundUpload->store('events/doorprize', 'public');
        $this->event->update(['doorprize_background' => $path]);
        $this->backgroundUpload = null;
        $this->dispatch('notify', type: 'success', message: 'Background uploaded');
    }

    public function removeBackground()
    {
        if ($this->event->doorprize_background) {
            Storage::disk('public')->delete($this->event->doorprize_background);
            $this->event->update(['doorprize_background' => null]);
        }
        $this->dispatch('notify', type: 'success', message: 'Background removed');
    }

    public function getDisplayUrlProperty(): string
    {
        return route('events.doorprize.display', $this->event->slug);
    }

    // ═══════════════════════════════════════════════════════
    // GLOBAL SETTINGS & EXCLUSIONS
    // ═══════════════════════════════════════════════════════

    public function updatedDefaultRequireCheckin($value)
    {
        $this->saveGlobalSettings();
    }

    public function updatedDefaultRequireFeedback($value)
    {
        $this->saveGlobalSettings();
    }

    public function saveGlobalSettings()
    {
        $settings = $this->event->settings ?? [];
        $settings['doorprize_default_require_checkin'] = $this->defaultRequireCheckin;
        $settings['doorprize_default_require_feedback'] = $this->defaultRequireFeedback;
        $this->event->update(['settings' => $settings]);
        $this->dispatch('notify', type: 'success', message: 'Global defaults updated');
    }

    public function getGlobalBansProperty()
    {
        $globalBannedIds = $this->event->settings['doorprize_global_banned_ids'] ?? [];
        if (empty($globalBannedIds)) {
            return collect();
        }

        return EventRegistration::whereIn('id', $globalBannedIds)
            ->where('event_id', $this->event->id)
            ->get();
    }

    public function getGlobalBanCandidatesProperty()
    {
        if (! $this->globalBanSearch || strlen($this->globalBanSearch) < 2) {
            return collect();
        }

        $globalBannedIds = $this->event->settings['doorprize_global_banned_ids'] ?? [];

        return EventRegistration::where('event_id', $this->event->id)
            ->where('status', 'approved')
            ->whereNotIn('id', $globalBannedIds)
            ->where(function ($q) {
                $q->where('name', 'like', "%{$this->globalBanSearch}%")
                    ->orWhere('email', 'like', "%{$this->globalBanSearch}%");
            })
            ->limit(5)
            ->get();
    }

    public function banRegistrationGlobally(int $registrationId)
    {
        $settings = $this->event->settings ?? [];
        $globalBannedIds = $settings['doorprize_global_banned_ids'] ?? [];

        if (! in_array($registrationId, $globalBannedIds)) {
            $globalBannedIds[] = $registrationId;
        }

        $settings['doorprize_global_banned_ids'] = $globalBannedIds;
        $this->event->update(['settings' => $settings]);

        $this->globalBanSearch = '';
        $this->dispatch('notify', type: 'success', message: 'Participant excluded globally');
    }

    public function unbanGlobally(int $registrationId)
    {
        $settings = $this->event->settings ?? [];
        $globalBannedIds = $settings['doorprize_global_banned_ids'] ?? [];

        $globalBannedIds = array_values(array_filter($globalBannedIds, fn ($id) => $id !== $registrationId));

        $settings['doorprize_global_banned_ids'] = $globalBannedIds;
        $this->event->update(['settings' => $settings]);

        $this->dispatch('notify', type: 'success', message: 'Participant re-included globally');
    }

    public function getEligibleUsersProperty()
    {
        $query = EventRegistration::where('event_id', $this->event->id)
            ->where('status', 'approved');

        if ($this->defaultRequireCheckin) {
            $query->where('check_in', true);
        }

        if ($this->defaultRequireFeedback) {
            $query->where('feedback_submitted', true);
        }

        $globalBannedIds = $this->event->settings['doorprize_global_banned_ids'] ?? [];
        if (! empty($globalBannedIds)) {
            $query->whereNotIn('id', $globalBannedIds);
        }

        $alreadyWonIds = DoorprizeWinner::whereHas('prize.session', function ($q) {
            $q->where('event_id', $this->event->id);
        })->pluck('registration_id')->toArray();

        if (! empty($alreadyWonIds)) {
            $query->whereNotIn('id', $alreadyWonIds);
        }

        if ($this->eligibleSearch) {
            $query->where(function ($q) {
                $q->where('name', 'like', "%{$this->eligibleSearch}%")
                    ->orWhere('email', 'like', "%{$this->eligibleSearch}%");
            });
        }

        return $query->paginate(10, pageName: 'eligiblePage');
    }

    // ═══════════════════════════════════════════════════════
    // RENDER
    // ═══════════════════════════════════════════════════════

    public function render()
    {
        return view('events::livewire.event-console-doorprize');
    }
}

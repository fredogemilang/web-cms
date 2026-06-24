<?php

namespace Plugins\Events\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Plugins\Events\Models\Event;
use Plugins\Events\Models\EventFeedbackQuestion;
use Plugins\Events\Models\EventFeedbackOption;
use Plugins\Events\Models\EventFeedbackResponse;
use Illuminate\Support\Facades\DB;

class EventConsoleFeedback extends Component
{
    use WithPagination, WithFileUploads;

    // ─── Core ───
    public Event $event;
    public string $activeSubTab = 'builder'; // builder | responses | analytics

    // ─── Form Builder State ───
    public int $stepCount = 1;
    public int $activeStep = 1;

    // ─── Question Modal ───
    public bool $showQuestionModal = false;
    public ?int $editingQuestionId = null;
    public string $qQuestion = '';
    public string $qShortLabel = '';
    public string $qType = 'rating';
    public bool $qIsRequired = true;
    public int $qRatingMin = 1;
    public int $qRatingMax = 5;
    public bool $qIsConditional = false;
    public ?int $qParentQuestionId = null;
    public string $qConditionOperator = 'equals';
    public string $qConditionValue = '';
    public array $qOptions = [''];
    public array $qLeadsFlags = [];

    // ─── Settings ───
    public bool $showSettingsModal = false;
    public string $feedbackPrimaryColor = '#2563EB';
    public string $feedbackRedirectUrl = '';
    public bool $feedbackRequireCheckin = false;
    public $feedbackBackground; // temporary uploaded file
    public $feedbackForeground; // temporary uploaded file
    public $currentFeedbackBackground; // currently saved path
    public $currentFeedbackForeground; // currently saved path

    // ─── Delete Confirmation ───
    public bool $showDeleteModal = false;
    public ?int $deletingQuestionId = null;

    // ─── Responses Tab ───
    public string $responseSearch = '';
    public string $responseDateFrom = '';
    public string $responseDateTo = '';

    protected $paginationTheme = 'tailwind';

    protected $listeners = ['console-save' => 'saveSettings'];

    public function mount(Event $event)
    {
        $this->event = $event;
        $this->stepCount = max(1, $event->feedback_step_count ?? 1);
        $this->feedbackPrimaryColor = $event->feedback_primary_color ?? '#2563EB';
        $this->feedbackRedirectUrl = $event->feedback_redirect_url ?? '';
        $this->feedbackRequireCheckin = (bool) $event->feedback_require_checkin;
        $this->currentFeedbackBackground = $event->feedback_background;
        $this->currentFeedbackForeground = $event->feedback_foreground;
    }

    // ═══════════════════════════════════════════════════════
    // FORM BUILDER
    // ═══════════════════════════════════════════════════════

    public function getQuestionsProperty()
    {
        return EventFeedbackQuestion::where('event_id', $this->event->id)
            ->with('options')
            ->ordered()
            ->get()
            ->groupBy('step');
    }

    public function getParentQuestionsProperty()
    {
        return EventFeedbackQuestion::where('event_id', $this->event->id)
            ->whereNotIn('type', ['rating', 'digits'])
            ->orderBy('step')
            ->orderBy('sort_order')
            ->get();
    }

    public function addStep()
    {
        $this->stepCount++;
        $this->event->update(['feedback_step_count' => $this->stepCount]);
        $this->activeStep = $this->stepCount;
    }

    public function removeStep(int $step)
    {
        if ($this->stepCount <= 1) return;

        // Move questions from this step to previous step
        EventFeedbackQuestion::where('event_id', $this->event->id)
            ->where('step', $step)
            ->update(['step' => max(1, $step - 1)]);

        // Shift questions from higher steps down
        EventFeedbackQuestion::where('event_id', $this->event->id)
            ->where('step', '>', $step)
            ->decrement('step');

        $this->stepCount--;
        $this->event->update(['feedback_step_count' => $this->stepCount]);
        $this->activeStep = min($this->activeStep, $this->stepCount);
    }

    // ─── Question CRUD ───

    public function openAddQuestion(int $step = 1)
    {
        $this->resetQuestionForm();
        $this->activeStep = $step;
        $this->showQuestionModal = true;
    }

    public function openEditQuestion(int $questionId)
    {
        $question = EventFeedbackQuestion::with('options')->find($questionId);
        if (!$question) return;

        $this->editingQuestionId = $question->id;
        $this->qQuestion = $question->question;
        $this->qShortLabel = $question->short_label;
        $this->qType = $question->type;
        $this->qIsRequired = $question->is_required;
        $this->qRatingMin = $question->rating_min_value ?? 1;
        $this->qRatingMax = $question->rating_max_value ?? 5;
        $this->qIsConditional = $question->is_conditional;
        $this->qParentQuestionId = $question->parent_question_id;
        $this->qConditionOperator = $question->condition_operator ?? 'equals';
        $this->qConditionValue = $question->condition_value ?? '';
        $this->activeStep = $question->step;

        // Load options
        $this->qOptions = $question->options->pluck('option_label')->toArray();
        $this->qLeadsFlags = $question->options->pluck('is_leads_flag')->toArray();

        if (empty($this->qOptions)) {
            $this->qOptions = [''];
        }

        $this->showQuestionModal = true;
    }

    public function saveQuestion()
    {
        $this->validate([
            'qQuestion' => 'required|string|max:500',
            'qShortLabel' => 'required|string|max:100|regex:/^[a-zA-Z0-9_]+$/',
            'qType' => 'required|in:rating,single_select,multi_select,text,textarea,digits',
        ]);

        $data = [
            'event_id' => $this->event->id,
            'step' => $this->activeStep,
            'question' => $this->qQuestion,
            'short_label' => $this->qShortLabel,
            'type' => $this->qType,
            'is_required' => $this->qIsRequired,
            'rating_min_value' => $this->qType === 'rating' ? $this->qRatingMin : null,
            'rating_max_value' => $this->qType === 'rating' ? $this->qRatingMax : null,
            'is_conditional' => $this->qIsConditional,
            'parent_question_id' => $this->qIsConditional ? $this->qParentQuestionId : null,
            'condition_operator' => $this->qIsConditional ? $this->qConditionOperator : null,
            'condition_value' => $this->qIsConditional ? $this->qConditionValue : null,
        ];

        if ($this->editingQuestionId) {
            $question = EventFeedbackQuestion::find($this->editingQuestionId);
            $question->update($data);
        } else {
            $maxSort = EventFeedbackQuestion::where('event_id', $this->event->id)
                ->where('step', $this->activeStep)
                ->max('sort_order') ?? 0;
            $data['sort_order'] = $maxSort + 1;
            $question = EventFeedbackQuestion::create($data);
        }

        // Save options for select types
        if (in_array($this->qType, ['single_select', 'multi_select'])) {
            // Delete old options
            EventFeedbackOption::where('question_id', $question->id)->delete();

            foreach ($this->qOptions as $i => $optionLabel) {
                if (trim($optionLabel) === '') continue;
                EventFeedbackOption::create([
                    'question_id' => $question->id,
                    'option_label' => trim($optionLabel),
                    'is_leads_flag' => ($this->qLeadsFlags[$i] ?? false) ? true : false,
                    'sort_order' => $i + 1,
                ]);
            }
        } else {
            // Remove options if type changed away from select
            EventFeedbackOption::where('question_id', $question->id)->delete();
        }

        $this->showQuestionModal = false;
        $this->resetQuestionForm();
        $this->dispatch('notify', type: 'success', message: 'Question saved successfully');
    }

    public function confirmDelete(int $questionId)
    {
        $this->deletingQuestionId = $questionId;
        $this->showDeleteModal = true;
    }

    public function deleteQuestion()
    {
        if (!$this->deletingQuestionId) return;

        // Clear any child conditional references
        EventFeedbackQuestion::where('parent_question_id', $this->deletingQuestionId)
            ->update([
                'is_conditional' => false,
                'parent_question_id' => null,
                'condition_operator' => null,
                'condition_value' => null,
            ]);

        EventFeedbackOption::where('question_id', $this->deletingQuestionId)->delete();
        EventFeedbackQuestion::destroy($this->deletingQuestionId);

        $this->showDeleteModal = false;
        $this->deletingQuestionId = null;
        $this->dispatch('notify', type: 'success', message: 'Question deleted');
    }

    public function addOption()
    {
        $this->qOptions[] = '';
    }

    public function removeOption(int $index)
    {
        if (count($this->qOptions) <= 1) return;
        array_splice($this->qOptions, $index, 1);
        array_splice($this->qLeadsFlags, $index, 1);
    }

    public function updateQuestionOrder(array $items)
    {
        foreach ($items as $item) {
            EventFeedbackQuestion::where('id', $item['value'])
                ->update(['sort_order' => $item['order']]);
        }
    }

    private function resetQuestionForm()
    {
        $this->editingQuestionId = null;
        $this->qQuestion = '';
        $this->qShortLabel = '';
        $this->qType = 'rating';
        $this->qIsRequired = true;
        $this->qRatingMin = 1;
        $this->qRatingMax = 5;
        $this->qIsConditional = false;
        $this->qParentQuestionId = null;
        $this->qConditionOperator = 'equals';
        $this->qConditionValue = '';
        $this->qOptions = [''];
        $this->qLeadsFlags = [];
    }

    // ─── Settings ───

    public function openSettings()
    {
        $this->feedbackPrimaryColor = $this->event->feedback_primary_color ?? '#2563EB';
        $this->feedbackRedirectUrl = $this->event->feedback_redirect_url ?? '';
        $this->feedbackRequireCheckin = (bool) $this->event->feedback_require_checkin;
        $this->currentFeedbackBackground = $this->event->feedback_background;
        $this->currentFeedbackForeground = $this->event->feedback_foreground;
        $this->feedbackBackground = null;
        $this->feedbackForeground = null;
        $this->showSettingsModal = true;
    }

    public function saveSettings()
    {
        $this->validate([
            'feedbackBackground' => 'nullable|image|max:1024', // max 1MB
            'feedbackForeground' => 'nullable|image|max:1024', // max 1MB
            'feedbackPrimaryColor' => 'required|string|max:7',
            'feedbackRedirectUrl' => 'nullable|url|max:500',
        ]);

        $data = [
            'feedback_primary_color' => $this->feedbackPrimaryColor,
            'feedback_redirect_url' => $this->feedbackRedirectUrl,
            'feedback_require_checkin' => $this->feedbackRequireCheckin,
            'feedback_step_count' => $this->stepCount,
        ];

        if ($this->feedbackBackground) {
            $folder = $this->event->slug ?: \Illuminate\Support\Str::slug($this->event->title ?: 'event');
            $data['feedback_background'] = $this->feedbackBackground->store("events/{$folder}/feedback", 'public');
            $this->currentFeedbackBackground = $data['feedback_background'];
            $this->feedbackBackground = null;
        }

        if ($this->feedbackForeground) {
            $folder = $this->event->slug ?: \Illuminate\Support\Str::slug($this->event->title ?: 'event');
            $data['feedback_foreground'] = $this->feedbackForeground->store("events/{$folder}/feedback", 'public');
            $this->currentFeedbackForeground = $data['feedback_foreground'];
            $this->feedbackForeground = null;
        }

        $this->event->update($data);

        $this->showSettingsModal = false;
        $this->dispatch('notify', type: 'success', message: 'Feedback settings saved');
    }

    public function removeBannerImage()
    {
        if ($this->currentFeedbackBackground) {
            $this->event->update(['feedback_background' => null]);
            $this->currentFeedbackBackground = null;
            $this->dispatch('notify', type: 'success', message: 'Banner image removed');
        }
    }

    public function removeLogoImage()
    {
        if ($this->currentFeedbackForeground) {
            $this->event->update(['feedback_foreground' => null]);
            $this->currentFeedbackForeground = null;
            $this->dispatch('notify', type: 'success', message: 'Logo image removed');
        }
    }

    // ═══════════════════════════════════════════════════════
    // RESPONSES
    // ═══════════════════════════════════════════════════════

    public function getResponseDataProperty()
    {
        $questions = EventFeedbackQuestion::where('event_id', $this->event->id)
            ->ordered()
            ->get();

        // Get unique respondents (grouped by registration_id + submitted_at)
        $query = EventFeedbackResponse::where('event_id', $this->event->id)
            ->with('registration');

        if ($this->responseDateFrom) {
            $query->whereDate('submitted_at', '>=', $this->responseDateFrom);
        }
        if ($this->responseDateTo) {
            $query->whereDate('submitted_at', '<=', $this->responseDateTo);
        }

        $allResponses = $query->get();

        // Group by registration_id
        $grouped = $allResponses->groupBy('event_registration_id');

        // Build rows
        $rows = [];
        foreach ($grouped as $regId => $responses) {
            $registration = $responses->first()->registration;
            if (!$registration) continue;

            // Filter by search
            if ($this->responseSearch) {
                $search = strtolower($this->responseSearch);
                $name = strtolower($registration->name ?? '');
                $email = strtolower($registration->email ?? '');
                if (!str_contains($name, $search) && !str_contains($email, $search)) {
                    continue;
                }
            }

            $row = [
                'name' => $registration->name ?? 'Unknown',
                'email' => $registration->email ?? '',
                'submitted_at' => $responses->max('submitted_at'),
                'answers' => [],
            ];

            foreach ($questions as $q) {
                $answer = $responses->firstWhere('question_id', $q->id);
                $row['answers'][$q->id] = $answer ? $answer->answer : '-';
            }

            $rows[] = $row;
        }

        // Sort by submitted_at descending
        usort($rows, fn($a, $b) => strtotime($b['submitted_at']) - strtotime($a['submitted_at']));

        return [
            'questions' => $questions,
            'rows' => $rows,
            'total' => count($rows),
        ];
    }

    public function exportExcel()
    {
        $data = $this->responseData;
        $questions = $data['questions'];
        $rows = $data['rows'];

        $header = ['Name', 'Email', 'Submitted At'];
        foreach ($questions as $q) {
            $header[] = $q->short_label ?: $q->question;
        }

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->fromArray($header, null, 'A1');

        $rowNumber = 2;
        foreach ($rows as $row) {
            $excelRow = [$row['name'], $row['email'], $row['submitted_at']];
            foreach ($questions as $q) {
                $excelRow[] = $row['answers'][$q->id] ?? '';
            }
            $sheet->fromArray($excelRow, null, 'A' . $rowNumber);
            $rowNumber++;
        }

        $filename = 'feedback-' . $this->event->slug . '-' . now()->format('Ymd') . '.xlsx';

        return response()->streamDownload(function () use ($spreadsheet) {
            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    // ═══════════════════════════════════════════════════════
    // ANALYTICS
    // ═══════════════════════════════════════════════════════

    public function getAnalyticsProperty()
    {
        $questions = EventFeedbackQuestion::where('event_id', $this->event->id)
            ->with('options')
            ->ordered()
            ->get();

        $totalRespondents = EventFeedbackResponse::where('event_id', $this->event->id)
            ->distinct('event_registration_id')
            ->count('event_registration_id');

        $totalRegistrations = $this->event->registrations()->count();

        $questionAnalytics = [];

        foreach ($questions as $q) {
            $responses = EventFeedbackResponse::where('question_id', $q->id)->get();
            $analytics = [
                'question' => $q,
                'totalResponses' => $responses->count(),
            ];

            if ($q->type === 'rating' || $q->type === 'digits') {
                $numericValues = $responses->map(fn($r) => (float) $r->answer)->filter(fn($v) => $v > 0);
                $analytics['average'] = $numericValues->isNotEmpty() ? round($numericValues->avg(), 2) : 0;
                $analytics['min'] = $numericValues->min() ?? 0;
                $analytics['max'] = $numericValues->max() ?? 0;

                // Distribution
                $distribution = [];
                $ratingMin = $q->rating_min_value ?? 1;
                $ratingMax = $q->rating_max_value ?? 5;
                for ($i = $ratingMin; $i <= $ratingMax; $i++) {
                    $distribution[$i] = $numericValues->filter(fn($v) => (int) $v === $i)->count();
                }
                $analytics['distribution'] = $distribution;
            } elseif (in_array($q->type, ['single_select', 'multi_select'])) {
                $optionCounts = [];
                foreach ($q->options as $opt) {
                    $count = $responses->filter(function ($r) use ($opt) {
                        $answer = $r->answer;
                        // Multi-select answers may be JSON or comma-separated
                        if (str_contains($answer, ',')) {
                            return in_array($opt->option_label, array_map('trim', explode(',', $answer)));
                        }
                        return $answer === $opt->option_label;
                    })->count();
                    $optionCounts[$opt->option_label] = $count;
                }
                $analytics['optionCounts'] = $optionCounts;
            } elseif (in_array($q->type, ['text', 'textarea'])) {
                $analytics['recentAnswers'] = $responses->sortByDesc('submitted_at')
                    ->take(10)
                    ->pluck('answer')
                    ->filter(fn($a) => !empty(trim($a)))
                    ->values()
                    ->toArray();
            }

            $questionAnalytics[] = $analytics;
        }

        return [
            'totalRespondents' => $totalRespondents,
            'totalRegistrations' => $totalRegistrations,
            'responseRate' => $totalRegistrations > 0 ? round(($totalRespondents / $totalRegistrations) * 100, 1) : 0,
            'questionAnalytics' => $questionAnalytics,
        ];
    }

    // ═══════════════════════════════════════════════════════
    // RENDER
    // ═══════════════════════════════════════════════════════

    public function render()
    {
        return view('events::livewire.event-console-feedback');
    }
}

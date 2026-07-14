<?php

namespace Plugins\Events\Livewire;

use Illuminate\Support\Collection;
use Livewire\Component;
use Livewire\WithFileUploads;
use Plugins\Events\Models\Event;
use Plugins\Events\Models\EventCustomQuestion;

class QuestionsManager extends Component
{
    use WithFileUploads;

    /** @var Event */
    public $event;

    /** @var Collection */
    public $questions;

    /** @var EventCustomQuestion|null */
    public $editingQuestion = null;

    /** @var bool */
    public $showModal = false;

    /** @var bool */
    public $showCloneModal = false;

    /** @var Event|null */
    public $targetEvent = null;

    // Form fields
    public $question_text = '';

    public $question_description = '';

    public $short_label = '';

    public $type = 'text';

    public $required = false;

    public $options = [];

    public $image;

    public $target_event_id = '';

    protected function rules(): array
    {
        return [
            'question_text' => 'required|max:255',
            'short_label' => 'required|max:50|alpha_dash|unique:event_custom_questions,short_label'.
                ($this->editingQuestion ? ','.$this->editingQuestion->id : ''),
            'type' => 'required|in:text,textarea,single_select,multi_select,email,phone,date',
            'options' => $this->type === 'single_select' || $this->type === 'multi_select'
                ? 'required|array|min:1|min:1'
                : [],
        ];
    }

    protected function messages(): array
    {
        return [
            'short_label.unique' => 'This short label is already in use by another question.',
            'options.required' => 'At least one option is required for select-type questions.',
        ];
    }

    public function mount(Event $event)
    {
        $this->event = $event;
        $this->loadQuestions();
    }

    public function loadQuestions()
    {
        $this->questions = $this->event->customQuestions()->with('options')->ordered()->get();
    }

    public function openAddModal()
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function editQuestion(int $questionId)
    {
        $question = EventCustomQuestion::with('options')->find($questionId);
        $this->editingQuestion = $question;
        $this->question_text = $question->question;
        $this->question_description = $question->question_description ?? '';
        $this->short_label = $question->short_label;
        $this->type = $question->type;
        $this->required = (bool) $question->required;
        $this->options = $question->options->pluck('option_text')->toArray();
        $this->image = null;
        $this->showModal = true;
    }

    public function saveQuestion()
    {
        $rules = [
            'question_text' => 'required|max:255',
            'type' => 'required|in:text,textarea,single_select,multi_select,email,phone,date',
            'required' => 'boolean',
        ];

        // Short label validation - only enforce unique if creating new
        if (! $this->editingQuestion) {
            $rules['short_label'] = 'required|max:50|alpha_dash|unique:event_custom_questions,short_label,NULL,id,event_id,'.$this->event->id;
        } else {
            $rules['short_label'] = 'required|max:50|alpha_dash';
        }

        // Options required for select types
        if ($this->type === 'single_select' || $this->type === 'multi_select') {
            $validOptions = array_filter($this->options);
            if (empty($validOptions)) {
                $this->addError('options', 'At least one option is required for select-type questions.');

                return;
            }
        }

        $this->validate($rules);

        if ($this->editingQuestion) {
            // Check short_label uniqueness only if changed
            if ($this->editingQuestion->short_label !== $this->short_label) {
                $exists = EventCustomQuestion::where('event_id', $this->event->id)
                    ->where('short_label', $this->short_label)
                    ->where('id', '!=', $this->editingQuestion->id)
                    ->exists();
                if ($exists) {
                    $this->addError('short_label', 'This short label is already in use by another question in this event.');

                    return;
                }
            }
        } else {
            // Creating new — check uniqueness in this event
            $exists = EventCustomQuestion::where('event_id', $this->event->id)
                ->where('short_label', $this->short_label)
                ->exists();
            if ($exists) {
                $this->addError('short_label', 'This short label is already in use by another question in this event.');

                return;
            }
        }

        $question = EventCustomQuestion::updateOrCreate(
            ['id' => $this->editingQuestion?->id],
            [
                'event_id' => $this->event->id,
                'type' => $this->type,
                'question' => $this->question_text,
                'question_description' => $this->question_description ?: null,
                'short_label' => $this->short_label,
                'required' => $this->required,
                'order' => $this->editingQuestion ? $this->editingQuestion->order : EventCustomQuestion::getNextOrder($this->event->id),
            ]
        );

        // Handle image upload
        if ($this->image) {
            $filename = $question->id.'_'.time().'.'.$this->image->getClientOriginalExtension();
            $path = $this->image->storeAs('event-questions/images', $filename, 'public');
            $question->image = $path;
            $question->save();
        }

        // Handle options for select types
        if ($this->type === 'single_select' || $this->type === 'multi_select') {
            $question->options()->delete();
            foreach (array_values(array_filter($this->options)) as $index => $optionText) {
                if (! empty(trim($optionText))) {
                    $question->options()->create([
                        'option_text' => trim($optionText),
                        'order' => $index,
                    ]);
                }
            }
        }

        $this->resetForm();
        $this->loadQuestions();
        $this->showModal = false;
    }

    public function deleteQuestion(int $questionId)
    {
        EventCustomQuestion::find($questionId)?->delete();
        $this->loadQuestions();
    }

    public function addOption()
    {
        $this->options[] = '';
    }

    public function removeOption(int $index)
    {
        if (isset($this->options[$index])) {
            unset($this->options[$index]);
            $this->options = array_values($this->options);
        }
    }

    public function updateQuestionsOrder($orderedIds)
    {
        foreach ($orderedIds as $index => $id) {
            EventCustomQuestion::where('id', $id)->update(['order' => $index]);
        }
        $this->loadQuestions();
    }

    public function openCloneModal(int $questionId)
    {
        $this->editingQuestion = EventCustomQuestion::with('options')->find($questionId);
        $this->target_event_id = '';
        $this->showCloneModal = true;
    }

    public function cloneQuestion()
    {
        if (! $this->target_event_id) {
            $this->addError('target_event_id', 'Please select a target event.');

            return;
        }

        $targetEvent = Event::find($this->target_event_id);
        if (! $targetEvent) {
            $this->addError('target_event_id', 'Target event not found.');

            return;
        }

        if ($targetEvent->id === $this->event->id) {
            $this->addError('target_event_id', 'Cannot clone question to the same event.');

            return;
        }

        // Check short_label uniqueness in target event
        $exists = EventCustomQuestion::where('event_id', $targetEvent->id)
            ->where('short_label', $this->editingQuestion->short_label.'_copy')
            ->exists();
        if ($exists) {
            $this->addError('target_event_id', 'A question with this short label already exists in the target event.');

            return;
        }

        $clone = EventCustomQuestion::create([
            'event_id' => $targetEvent->id,
            'type' => $this->editingQuestion->type,
            'question' => $this->editingQuestion->question.' (Copy)',
            'question_description' => $this->editingQuestion->question_description,
            'short_label' => $this->editingQuestion->short_label.'_copy',
            'required' => $this->editingQuestion->required,
            'order' => EventCustomQuestion::getNextOrder($targetEvent->id),
            'image' => $this->editingQuestion->image,
        ]);

        // Clone options
        if ($this->editingQuestion->options->count() > 0) {
            foreach ($this->editingQuestion->options as $index => $option) {
                $clone->options()->create([
                    'option_text' => $option->option_text,
                    'order' => $index,
                ]);
            }
        }

        $this->showCloneModal = false;
        $this->resetForm();
    }

    private function resetForm()
    {
        $this->editingQuestion = null;
        $this->question_text = '';
        $this->question_description = '';
        $this->short_label = '';
        $this->type = 'text';
        $this->required = false;
        $this->options = [];
        $this->image = null;
        $this->showModal = false;
        $this->showCloneModal = false;
        $this->target_event_id = '';
        $this->targetEvent = null;
    }

    public function render()
    {
        // Load target events for clone modal (exclude current event)
        $availableTargetEvents = Event::where('id', '!=', $this->event->id)
            ->orderBy('title')
            ->get(['id', 'title']);

        return view('events::livewire.questions-manager', [
            'availableTargetEvents' => $availableTargetEvents,
        ]);
    }
}

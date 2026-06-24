<div x-on:console-save.window="$wire.saveSettings()">
    {{-- Sub-tab Navigation --}}
    <div class="flex items-center justify-between mb-6">
        <div class="flex gap-2">
            @foreach(['builder' => 'Form Builder', 'responses' => 'Responses', 'analytics' => 'Analytics'] as $key => $label)
                <button wire:click="$set('activeSubTab', '{{ $key }}')"
                    class="px-4 py-2 rounded-xl text-sm font-semibold transition-all border
                        {{ $activeSubTab === $key
                            ? 'border-[#2563EB] text-text-primary bg-[#2563EB]/10'
                            : 'border-dark-border text-text-secondary hover:text-text-primary bg-dark-surface' }}">
                    {{ $label }}
                </button>
            @endforeach
        </div>
        @if($activeSubTab === 'builder')
            <button wire:click="openSettings" class="px-4 py-2 rounded-xl text-sm font-semibold text-text-secondary hover:text-text-primary bg-dark-surface border border-dark-border transition-all flex items-center gap-2">
                <span class="material-symbols-outlined text-sm">settings</span> Settings
            </button>
        @endif
    </div>

    {{-- ═══ FORM BUILDER TAB ═══ --}}
    @if($activeSubTab === 'builder')
        {{-- Step Tabs --}}
        <div class="flex items-center gap-2 mb-6">
            @for($s = 1; $s <= $stepCount; $s++)
                <button wire:click="$set('activeStep', {{ $s }})"
                    class="px-4 py-2 rounded-lg text-sm font-semibold transition-all
                        {{ $activeStep === $s ? 'bg-[#2563EB] text-white' : 'bg-dark-surface border border-dark-border text-text-secondary hover:text-text-primary' }}">
                    Step {{ $s }}
                </button>
            @endfor
            <button wire:click="addStep" class="px-3 py-2 rounded-lg text-sm text-text-secondary hover:text-text-primary bg-dark-surface border border-dark-border transition-all">
                <span class="material-symbols-outlined text-sm">add</span>
            </button>
            @if($stepCount > 1)
                <button wire:click="removeStep({{ $activeStep }})" class="px-3 py-2 rounded-lg text-sm text-red-500 hover:text-red-400 bg-dark-surface border border-dark-border transition-all">
                    <span class="material-symbols-outlined text-sm">delete</span>
                </button>
            @endif
        </div>

        {{-- Questions List --}}
        <div class="glass-panel rounded-2xl p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-base font-bold text-text-primary">Step {{ $activeStep }} Questions</h3>
                <button wire:click="openAddQuestion({{ $activeStep }})" class="px-4 py-2 rounded-xl text-sm font-bold text-white bg-[#2563EB] hover:bg-blue-600 transition-all flex items-center gap-1.5">
                    <span class="material-symbols-outlined text-sm">add</span> Add Question
                </button>
            </div>

            @php $stepQuestions = $this->questions[$activeStep] ?? collect(); @endphp

            @if($stepQuestions->isEmpty())
                <div class="text-center py-12">
                    <span class="material-symbols-outlined text-4xl text-text-secondary mb-2">quiz</span>
                    <p class="text-sm font-semibold text-text-primary">No questions in this step</p>
                    <p class="text-xs text-text-secondary mt-1">Add questions to build your feedback form</p>
                </div>
            @else
                <div class="space-y-3">
                    @foreach($stepQuestions->sortBy('sort_order') as $question)
                        <div class="flex items-center gap-3 p-4 rounded-xl bg-dark-surface border border-dark-border group hover:border-[#2563EB]/30 transition-all">
                            <span class="material-symbols-outlined text-text-secondary cursor-grab">drag_indicator</span>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2">
                                    <span class="text-sm font-semibold text-text-primary truncate">{{ $question->question }}</span>
                                    @if($question->is_required)
                                        <span class="text-red-500 text-xs">*</span>
                                    @endif
                                </div>
                                <div class="flex items-center gap-2 mt-1">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-[#2563EB]/10 text-[#2563EB]">{{ str_replace('_', ' ', $question->type) }}</span>
                                    <span class="text-[10px] text-text-secondary">{{ $question->short_label }}</span>
                                    @if($question->is_conditional)
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-amber-500/10 text-amber-600 dark:text-amber-400">conditional</span>
                                    @endif
                                </div>
                            </div>
                            <div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                <button wire:click="openEditQuestion({{ $question->id }})" class="p-2 rounded-lg text-text-secondary hover:text-text-primary hover:bg-dark-surface-lighter transition-all">
                                    <span class="material-symbols-outlined text-sm">edit</span>
                                </button>
                                <button wire:click="confirmDelete({{ $question->id }})" class="p-2 rounded-lg text-text-secondary hover:text-red-500 hover:bg-red-500/10 transition-all">
                                    <span class="material-symbols-outlined text-sm">delete</span>
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

    {{-- ═══ RESPONSES TAB ═══ --}}
    @elseif($activeSubTab === 'responses')
        @php $rd = $this->responseData; @endphp
        <div class="glass-panel rounded-2xl p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-base font-bold text-text-primary">{{ $rd['total'] }} Response(s)</h3>
                @if($rd['total'] > 0)
                    <button wire:click="exportExcel" class="px-4 py-2 rounded-xl text-sm font-semibold text-text-secondary hover:text-text-primary bg-dark-surface border border-dark-border transition-all flex items-center gap-2">
                        <span class="material-symbols-outlined text-sm">download</span> Export Excel
                    </button>
                @endif
            </div>

            {{-- Filters --}}
            <div class="flex items-center gap-3 mb-4">
                <div class="relative flex-1">
                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-text-secondary text-lg">search</span>
                    <input type="text" wire:model.live.debounce.300ms="responseSearch" placeholder="Search name or email..."
                        class="w-full h-10 pl-10 pr-4 rounded-xl border border-dark-border bg-dark-surface text-text-primary text-sm focus:ring-1 focus:ring-[#2563EB] focus:border-[#2563EB]"/>
                </div>
                <input type="date" wire:model.live="responseDateFrom" class="h-10 px-3 rounded-xl border border-dark-border bg-dark-surface text-text-primary text-sm"/>
                <span class="text-text-secondary text-xs">to</span>
                <input type="date" wire:model.live="responseDateTo" class="h-10 px-3 rounded-xl border border-dark-border bg-dark-surface text-text-primary text-sm"/>
            </div>

            @if($rd['total'] === 0)
                <div class="text-center py-12">
                    <span class="material-symbols-outlined text-4xl text-text-secondary mb-2">inbox</span>
                    <p class="text-sm font-semibold text-text-primary">No responses yet</p>
                </div>
            @else
                <div class="overflow-x-auto rounded-xl border border-dark-border">
                    <table class="w-full text-sm text-text-primary border-collapse">
                        <thead>
                            <tr class="border-b border-dark-border bg-dark-surface-lighter">
                                <th class="px-4 py-3 text-left text-xs font-bold text-text-secondary uppercase">Name</th>
                                <th class="px-4 py-3 text-left text-xs font-bold text-text-secondary uppercase">Email</th>
                                @foreach($rd['questions'] as $q)
                                    <th class="px-4 py-3 text-left text-xs font-bold text-text-secondary uppercase truncate max-w-[150px]">{{ $q->short_label ?: Str::limit($q->question, 20) }}</th>
                                @endforeach
                                <th class="px-4 py-3 text-left text-xs font-bold text-text-secondary uppercase">Date</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-dark-border">
                            @foreach($rd['rows'] as $row)
                                <tr class="hover:bg-dark-surface-lighter transition-colors">
                                    <td class="px-4 py-3 font-medium">{{ $row['name'] }}</td>
                                    <td class="px-4 py-3 text-text-secondary">{{ $row['email'] }}</td>
                                    @foreach($rd['questions'] as $q)
                                        <td class="px-4 py-3 max-w-[200px] truncate">{{ $row['answers'][$q->id] ?? '-' }}</td>
                                    @endforeach
                                    <td class="px-4 py-3 text-text-secondary text-xs">{{ \Carbon\Carbon::parse($row['submitted_at'])->format('d M Y H:i') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

    {{-- ═══ ANALYTICS TAB ═══ --}}
    @elseif($activeSubTab === 'analytics')
        @php $an = $this->analytics; @endphp
        {{-- Summary Cards --}}
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
            <div class="glass-panel rounded-2xl p-5">
                <span class="text-xs font-bold text-text-secondary uppercase">Total Responses</span>
                <h3 class="text-3xl font-extrabold text-text-primary mt-1">{{ $an['totalRespondents'] }}</h3>
            </div>
            <div class="glass-panel rounded-2xl p-5">
                <span class="text-xs font-bold text-text-secondary uppercase">Total Registrations</span>
                <h3 class="text-3xl font-extrabold text-text-primary mt-1">{{ $an['totalRegistrations'] }}</h3>
            </div>
            <div class="glass-panel rounded-2xl p-5">
                <span class="text-xs font-bold text-text-secondary uppercase">Response Rate</span>
                <h3 class="text-3xl font-extrabold text-text-primary mt-1">{{ $an['responseRate'] }}%</h3>
            </div>
        </div>

        @if(empty($an['questionAnalytics']))
            <div class="glass-panel rounded-2xl p-12 text-center">
                <span class="material-symbols-outlined text-4xl text-text-secondary mb-2">bar_chart</span>
                <p class="text-sm font-semibold text-text-primary">No analytics available yet</p>
                <p class="text-xs text-text-secondary mt-1">Create questions and collect responses to see analytics</p>
            </div>
        @else
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                @foreach($an['questionAnalytics'] as $qa)
                    <div class="glass-panel rounded-2xl p-6">
                        <div class="flex items-center gap-2 mb-4">
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-[#2563EB]/10 text-[#2563EB]">{{ str_replace('_', ' ', $qa['question']->type) }}</span>
                            <span class="text-xs text-text-secondary">{{ $qa['totalResponses'] }} responses</span>
                        </div>
                        <h4 class="text-sm font-bold text-text-primary mb-4">{{ $qa['question']->question }}</h4>

                        @if(in_array($qa['question']->type, ['rating', 'digits']) && !empty($qa['distribution']))
                            <div class="flex items-center gap-2 mb-3">
                                <span class="text-2xl font-extrabold text-[#2563EB]">{{ $qa['average'] }}</span>
                                <span class="text-xs text-text-secondary">avg</span>
                            </div>
                            <div class="space-y-1.5">
                                @foreach($qa['distribution'] as $val => $count)
                                    @php $pct = $qa['totalResponses'] > 0 ? round(($count / $qa['totalResponses']) * 100) : 0; @endphp
                                    <div class="flex items-center gap-2 text-xs">
                                        <span class="w-4 text-right text-text-secondary font-semibold">{{ $val }}</span>
                                        <div class="flex-1 h-4 rounded-full bg-dark-surface-lighter overflow-hidden">
                                            <div class="h-full rounded-full bg-[#2563EB] transition-all" style="width: {{ $pct }}%"></div>
                                        </div>
                                        <span class="w-8 text-right text-text-secondary">{{ $count }}</span>
                                    </div>
                                @endforeach
                            </div>
                        @elseif(in_array($qa['question']->type, ['single_select', 'multi_select']) && !empty($qa['optionCounts']))
                            @php $maxCount = max(array_values($qa['optionCounts']) ?: [1]); @endphp
                            <div class="space-y-2">
                                @foreach($qa['optionCounts'] as $label => $count)
                                    @php $pct = $maxCount > 0 ? round(($count / $maxCount) * 100) : 0; @endphp
                                    <div>
                                        <div class="flex items-center justify-between text-xs mb-0.5">
                                            <span class="text-text-primary font-medium truncate">{{ $label }}</span>
                                            <span class="text-text-secondary ml-2">{{ $count }}</span>
                                        </div>
                                        <div class="h-3 rounded-full bg-dark-surface-lighter overflow-hidden">
                                            <div class="h-full rounded-full bg-emerald-500 transition-all" style="width: {{ $pct }}%"></div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @elseif(in_array($qa['question']->type, ['text', 'textarea']) && !empty($qa['recentAnswers']))
                            <div class="space-y-2 max-h-[200px] overflow-y-auto">
                                @foreach($qa['recentAnswers'] as $answer)
                                    <div class="p-3 rounded-lg bg-dark-surface-lighter text-xs text-text-primary">{{ Str::limit($answer, 200) }}</div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-xs text-text-secondary italic">No data yet</p>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif
    @endif

    {{-- ═══ QUESTION MODAL ═══ --}}
    @if($showQuestionModal)
        <div class="fixed inset-0 bg-black/60 flex items-center justify-center z-[70] p-4" wire:click.self="$set('showQuestionModal', false)">
            <div class="bg-dark-surface border border-dark-border rounded-2xl max-w-2xl w-full max-h-[90vh] flex flex-col shadow-xl text-text-primary" @click.stop>
                <div class="flex items-center justify-between p-6 border-b border-dark-border">
                    <h3 class="text-lg font-bold text-text-primary">{{ $editingQuestionId ? 'Edit' : 'Add' }} Question</h3>
                    <button wire:click="$set('showQuestionModal', false)" class="p-1 rounded-lg hover:bg-dark-surface-lighter text-text-secondary"><span class="material-symbols-outlined">close</span></button>
                </div>
                <div class="p-6 overflow-y-auto space-y-4">
                    <div>
                        <label class="block text-sm font-bold text-text-primary mb-1.5">Question Text *</label>
                        <input type="text" wire:model="qQuestion" class="w-full h-12 rounded-xl border border-dark-border bg-console-input px-4 text-sm text-text-primary focus:ring-1 focus:ring-[#2563EB]" placeholder="e.g. How would you rate this event?"/>
                        @error('qQuestion') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-bold text-text-primary mb-1.5">Short Label *</label>
                            <input type="text" wire:model="qShortLabel" class="w-full h-12 rounded-xl border border-dark-border bg-console-input px-4 text-sm text-text-primary focus:ring-1 focus:ring-[#2563EB]" placeholder="e.g. overall_rating"/>
                            @error('qShortLabel') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-text-primary mb-1.5">Type *</label>
                            <select wire:model.live="qType" class="w-full h-12 rounded-xl border border-dark-border bg-console-input px-4 text-sm text-text-primary focus:ring-1 focus:ring-[#2563EB]">
                                <option value="rating">Rating</option>
                                <option value="single_select">Single Select</option>
                                <option value="multi_select">Multi Select</option>
                                <option value="text">Short Text</option>
                                <option value="textarea">Long Text</option>
                                <option value="digits">Number</option>
                            </select>
                        </div>
                    </div>

                    @if($qType === 'rating')
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-bold text-text-primary mb-1.5">Min Value</label>
                                <input type="number" wire:model="qRatingMin" class="w-full h-12 rounded-xl border border-dark-border bg-console-input px-4 text-sm text-text-primary"/>
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-text-primary mb-1.5">Max Value</label>
                                <input type="number" wire:model="qRatingMax" class="w-full h-12 rounded-xl border border-dark-border bg-console-input px-4 text-sm text-text-primary"/>
                            </div>
                        </div>
                    @endif

                    @if(in_array($qType, ['single_select', 'multi_select']))
                        <div>
                            <label class="block text-sm font-bold text-text-primary mb-1.5">Options</label>
                            <div class="space-y-2">
                                @foreach($qOptions as $i => $opt)
                                    <div class="flex items-center gap-2">
                                        <input type="text" wire:model="qOptions.{{ $i }}" class="flex-1 h-10 rounded-xl border border-dark-border bg-console-input px-4 text-sm text-text-primary" placeholder="Option {{ $i + 1 }}"/>
                                        @if(count($qOptions) > 1)
                                            <button wire:click="removeOption({{ $i }})" class="p-2 rounded-lg text-red-500 hover:bg-red-500/10"><span class="material-symbols-outlined text-sm">close</span></button>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                            <button wire:click="addOption" class="mt-2 text-xs text-[#2563EB] font-semibold hover:underline">+ Add Option</button>
                        </div>
                    @endif

                    <div class="flex items-center gap-3">
                        <input type="checkbox" wire:model="qIsRequired" id="qRequired" class="rounded border-dark-border"/>
                        <label for="qRequired" class="text-sm text-text-primary">Required</label>
                    </div>

                    {{-- Conditional Logic --}}
                    <div class="border-t border-dark-border pt-4">
                        <div class="flex items-center gap-3 mb-3">
                            <input type="checkbox" wire:model.live="qIsConditional" id="qConditional" class="rounded border-dark-border"/>
                            <label for="qConditional" class="text-sm font-semibold text-text-primary">Conditional Logic</label>
                        </div>
                        @if($qIsConditional)
                            <div class="grid grid-cols-3 gap-3">
                                <select wire:model="qParentQuestionId" class="h-10 rounded-xl border border-dark-border bg-console-input px-3 text-xs text-text-primary">
                                    <option value="">Parent Question</option>
                                    @foreach($this->parentQuestions as $pq)
                                        @if($pq->id !== $editingQuestionId)
                                            <option value="{{ $pq->id }}">{{ Str::limit($pq->question, 30) }}</option>
                                        @endif
                                    @endforeach
                                </select>
                                <select wire:model="qConditionOperator" class="h-10 rounded-xl border border-dark-border bg-console-input px-3 text-xs text-text-primary">
                                    <option value="equals">Equals</option>
                                    <option value="not_equals">Not Equals</option>
                                    <option value="contains">Contains</option>
                                </select>
                                <input type="text" wire:model="qConditionValue" class="h-10 rounded-xl border border-dark-border bg-console-input px-3 text-xs text-text-primary" placeholder="Value"/>
                            </div>
                        @endif
                    </div>
                </div>
                <div class="flex items-center justify-end gap-3 p-6 border-t border-dark-border">
                    <button wire:click="$set('showQuestionModal', false)" class="px-5 py-2 rounded-xl text-sm font-semibold text-text-secondary hover:text-text-primary transition-all">Cancel</button>
                    <button wire:click="saveQuestion" class="px-5 py-2 rounded-xl text-sm font-bold text-white bg-[#2563EB] hover:bg-blue-600 transition-all">{{ $editingQuestionId ? 'Update' : 'Add' }} Question</button>
                </div>
            </div>
        </div>
    @endif

    {{-- ═══ DELETE MODAL ═══ --}}
    @if($showDeleteModal)
        <div class="fixed inset-0 bg-black/60 flex items-center justify-center z-[70] p-4">
            <div class="bg-dark-surface border border-dark-border rounded-2xl max-w-sm w-full p-6 shadow-xl text-text-primary">
                <h3 class="text-lg font-bold text-text-primary mb-2">Delete Question?</h3>
                <p class="text-sm text-text-secondary mb-6">This will permanently remove this question and all its responses.</p>
                <div class="flex justify-end gap-3">
                    <button wire:click="$set('showDeleteModal', false)" class="px-4 py-2 rounded-xl text-sm font-semibold text-text-secondary">Cancel</button>
                    <button wire:click="deleteQuestion" class="px-4 py-2 rounded-xl text-sm font-bold text-white bg-red-600 hover:bg-red-700 transition-all">Delete</button>
                </div>
            </div>
        </div>
    @endif

    {{-- ═══ SETTINGS MODAL ═══ --}}
    @if($showSettingsModal)
        <div class="fixed inset-0 bg-black/60 flex items-center justify-center z-[70] p-4" wire:click.self="$set('showSettingsModal', false)">
            <div class="bg-dark-surface border border-dark-border rounded-2xl max-w-lg w-full p-6 shadow-xl text-text-primary">
                <h3 class="text-lg font-bold text-text-primary mb-4">Feedback Settings</h3>
                <div class="space-y-4">
                    {{-- Banner Image --}}
                    <div>
                        <label class="block text-sm font-bold text-text-primary mb-1.5">Banner Image (Feedback Background)</label>
                        @if($feedbackBackground)
                            <div class="relative w-full h-32 rounded-xl overflow-hidden mb-2 border border-dark-border">
                                <img src="{{ $feedbackBackground->temporaryUrl() }}" class="w-full h-full object-cover"/>
                                <button type="button" wire:click="$set('feedbackBackground', null)" class="absolute top-2 right-2 bg-red-600 text-white rounded-full p-1.5 hover:bg-red-700 transition-all flex items-center justify-center shadow-lg">
                                    <span class="material-symbols-outlined text-sm">delete</span>
                                </button>
                            </div>
                        @elseif($currentFeedbackBackground)
                            <div class="relative w-full h-32 rounded-xl overflow-hidden mb-2 border border-dark-border">
                                <img src="{{ asset('storage/' . $currentFeedbackBackground) }}" class="w-full h-full object-cover"/>
                                <button type="button" wire:click="removeBannerImage" class="absolute top-2 right-2 bg-red-600 text-white rounded-full p-1.5 hover:bg-red-700 transition-all flex items-center justify-center shadow-lg">
                                    <span class="material-symbols-outlined text-sm">delete</span>
                                </button>
                            </div>
                        @else
                            <div class="border-2 border-dashed border-dark-border rounded-xl p-4 text-center cursor-pointer hover:border-[#2563EB] transition-all relative bg-console-input">
                                <input type="file" wire:model="feedbackBackground" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" accept="image/*"/>
                                <span class="material-symbols-outlined text-2xl text-text-secondary mb-1">upload_file</span>
                                <p class="text-xs text-text-secondary font-medium">Click or drag banner image (max 1MB)</p>
                            </div>
                        @endif
                        @error('feedbackBackground') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    {{-- Logo Image --}}
                    <div>
                        <label class="block text-sm font-bold text-text-primary mb-1.5">Logo Image (Feedback Foreground)</label>
                        @if($feedbackForeground)
                            <div class="relative w-full h-24 rounded-xl overflow-hidden mb-2 border border-dark-border bg-dark-surface-lighter flex items-center justify-center p-2">
                                <img src="{{ $feedbackForeground->temporaryUrl() }}" class="max-h-full object-contain"/>
                                <button type="button" wire:click="$set('feedbackForeground', null)" class="absolute top-2 right-2 bg-red-600 text-white rounded-full p-1.5 hover:bg-red-700 transition-all flex items-center justify-center shadow-lg">
                                    <span class="material-symbols-outlined text-sm">delete</span>
                                </button>
                            </div>
                        @elseif($currentFeedbackForeground)
                            <div class="relative w-full h-24 rounded-xl overflow-hidden mb-2 border border-dark-border bg-dark-surface-lighter flex items-center justify-center p-2">
                                <img src="{{ asset('storage/' . $currentFeedbackForeground) }}" class="max-h-full object-contain"/>
                                <button type="button" wire:click="removeLogoImage" class="absolute top-2 right-2 bg-red-600 text-white rounded-full p-1.5 hover:bg-red-700 transition-all flex items-center justify-center shadow-lg">
                                    <span class="material-symbols-outlined text-sm">delete</span>
                                </button>
                            </div>
                        @else
                            <div class="border-2 border-dashed border-dark-border rounded-xl p-4 text-center cursor-pointer hover:border-[#2563EB] transition-all relative bg-console-input">
                                <input type="file" wire:model="feedbackForeground" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" accept="image/*"/>
                                <span class="material-symbols-outlined text-2xl text-text-secondary mb-1">upload_file</span>
                                <p class="text-xs text-text-secondary font-medium">Click or drag logo image (max 1MB)</p>
                            </div>
                        @endif
                        @error('feedbackForeground') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div wire:loading wire:target="feedbackBackground, feedbackForeground" class="text-xs text-[#2563EB] font-semibold mt-1 flex items-center gap-1.5">
                        <span class="animate-spin h-3.5 w-3.5 border-2 border-[#2563EB] border-t-transparent rounded-full"></span>
                        Uploading image...
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-text-primary mb-1.5">Primary Color</label>
                        <input type="color" wire:model="feedbackPrimaryColor" class="w-full h-12 rounded-xl border border-dark-border bg-console-input"/>
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-text-primary mb-1.5">Redirect URL (after submit)</label>
                        <input type="url" wire:model="feedbackRedirectUrl" class="w-full h-12 rounded-xl border border-dark-border bg-console-input px-4 text-sm text-text-primary" placeholder="https://..."/>
                    </div>
                    <div class="flex items-center gap-3">
                        <input type="checkbox" wire:model="feedbackRequireCheckin" id="reqCheckin" class="rounded border-dark-border"/>
                        <label for="reqCheckin" class="text-sm text-text-primary">Require check-in before feedback</label>
                    </div>
                </div>
                <div class="flex justify-end gap-3 mt-6">
                    <button wire:click="$set('showSettingsModal', false)" class="px-4 py-2 rounded-xl text-sm font-semibold text-text-secondary">Cancel</button>
                    <button wire:click="saveSettings" class="px-4 py-2 rounded-xl text-sm font-bold text-white bg-[#2563EB] hover:bg-blue-600 transition-all">Save Settings</button>
                </div>
            </div>
        </div>
    @endif
</div>

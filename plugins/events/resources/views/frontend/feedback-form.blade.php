<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Feedback — {{ $event->title }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
    @vite(['resources/css/app.css'])
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .fb-primary { background-color: {{ $event->feedback_primary_color ?? '#2563EB' }}; }
        .fb-primary-text { color: {{ $event->feedback_primary_color ?? '#2563EB' }}; }
        .fb-primary-ring:focus { --tw-ring-color: {{ $event->feedback_primary_color ?? '#2563EB' }}; border-color: {{ $event->feedback_primary_color ?? '#2563EB' }}; }
    </style>
</head>
<body class="bg-gray-50 min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-2xl" x-data="feedbackForm()">
        {{-- Header --}}
        <div class="text-center mb-8">
            <h1 class="text-2xl font-bold text-gray-900">{{ $event->title }}</h1>
            <p class="text-sm text-gray-500 mt-1">Event Feedback Form</p>
        </div>

        {{-- Success Message --}}
        @if(session('success'))
            <div class="bg-white rounded-2xl shadow-lg p-12 text-center">
                <div class="h-16 w-16 rounded-full bg-emerald-100 flex items-center justify-center mx-auto mb-4">
                    <span class="material-symbols-outlined text-3xl text-emerald-600">check_circle</span>
                </div>
                <h2 class="text-xl font-bold text-gray-900 mb-2">Thank You!</h2>
                <p class="text-gray-500">{{ session('success') }}</p>
            </div>

        {{-- Eligibility Error (UUID link but not eligible) --}}
        @elseif(!empty($eligibilityError))
            <div class="bg-white rounded-2xl shadow-lg p-12 text-center">
                <div class="h-16 w-16 rounded-full bg-red-100 flex items-center justify-center mx-auto mb-4">
                    <span class="material-symbols-outlined text-3xl text-red-600">error</span>
                </div>
                <h2 class="text-xl font-bold text-gray-900 mb-2">Cannot Submit Feedback</h2>
                <p class="text-gray-500">{{ $eligibilityError }}</p>
            </div>

        {{-- EMAIL GATE: Generic link — ask for email first --}}
        @elseif($needsEmail)
            <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
                <div class="p-8 text-center border-b border-gray-100">
                    <div class="h-14 w-14 rounded-full bg-blue-50 flex items-center justify-center mx-auto mb-4">
                        <span class="material-symbols-outlined text-2xl fb-primary-text">mail</span>
                    </div>
                    <h2 class="text-lg font-bold text-gray-900 mb-1">Verify Your Identity</h2>
                    <p class="text-sm text-gray-500">Enter the email you used to register for this event.</p>
                </div>
                <form action="{{ route('events.feedback.verify', $event->slug) }}" method="POST" class="p-6">
                    @csrf
                    <div class="mb-4">
                        <label class="block text-sm font-bold text-gray-700 mb-1.5">Email Address</label>
                        <input type="email" name="email" value="{{ old('email') }}" required autofocus
                            class="w-full h-12 rounded-xl border border-gray-200 px-4 text-sm focus:ring-2 fb-primary-ring"
                            placeholder="your@email.com"/>
                        @error('email')
                            <p class="text-red-500 text-xs mt-2 flex items-center gap-1">
                                <span class="material-symbols-outlined text-sm">warning</span>
                                {{ $message }}
                            </p>
                        @enderror
                    </div>
                    <button type="submit" class="w-full py-3 rounded-xl text-sm font-bold text-white fb-primary hover:opacity-90 transition-all">
                        Continue to Feedback Form
                    </button>
                </form>
            </div>

        {{-- FEEDBACK FORM: UUID-identified user --}}
        @else
            @if(isset($registration))
                <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
                    {{-- Identified user banner --}}
                    <div class="px-6 py-4 bg-blue-50 border-b border-blue-100 flex items-center gap-3">
                        <div class="h-10 w-10 rounded-full fb-primary flex items-center justify-center text-white font-bold text-sm">
                            {{ strtoupper(substr($registration->name ?? $registration->full_name ?? 'U', 0, 1)) }}
                        </div>
                        <div>
                            <p class="text-sm font-bold text-gray-900">{{ $registration->name ?? $registration->full_name }}</p>
                            <p class="text-xs text-gray-500">{{ $registration->email }}</p>
                        </div>
                    </div>

                    <form action="{{ route('events.feedback.submit', [$event->slug, $registration->uuid]) }}" method="POST">
                        @csrf

                        @error('general')
                            <div class="mx-6 mt-4 p-3 rounded-xl bg-red-50 border border-red-200 text-red-700 text-sm">{{ $message }}</div>
                        @enderror

                        {{-- Step Indicator --}}
                        @if($stepCount > 1)
                            <div class="px-6 py-3 bg-gray-50 flex items-center gap-2">
                                @for($s = 1; $s <= $stepCount; $s++)
                                    <button type="button" @click="currentStep = {{ $s }}"
                                        class="px-3 py-1 rounded-lg text-xs font-semibold transition-all"
                                        :class="currentStep === {{ $s }} ? 'fb-primary text-white' : 'bg-gray-200 text-gray-600'">
                                        Step {{ $s }}
                                    </button>
                                @endfor
                            </div>
                        @endif

                        {{-- Questions --}}
                        <div class="p-6 space-y-6">
                            @for($s = 1; $s <= $stepCount; $s++)
                                <div x-show="currentStep === {{ $s }}" x-transition>
                                    @foreach(($questions[$s] ?? collect()) as $question)
                                        <div class="mb-6"
                                            @if($question->is_conditional && $question->parent_question_id)
                                                x-data="{ visible: false }"
                                                x-effect="visible = checkCondition({{ $question->parent_question_id }}, '{{ $question->condition_operator }}', '{{ addslashes($question->condition_value) }}')"
                                                x-show="visible"
                                            @endif
                                        >
                                            <label class="block text-sm font-bold text-gray-700 mb-2">
                                                {{ $question->question }}
                                                @if($question->is_required)<span class="text-red-500">*</span>@endif
                                            </label>

                                            @if($question->type === 'rating')
                                                <div class="flex gap-2" x-data="{ rating: {{ (int) old("answers.{$question->id}", 0) }} }">
                                                    @for($r = ($question->rating_min_value ?? 1); $r <= ($question->rating_max_value ?? 5); $r++)
                                                        <button type="button" @click="rating = {{ $r }}"
                                                            class="h-10 w-10 rounded-lg border-2 text-sm font-bold transition-all"
                                                            :class="rating >= {{ $r }} ? 'border-current fb-primary-text bg-blue-50' : 'border-gray-200 text-gray-400'">
                                                            {{ $r }}
                                                        </button>
                                                    @endfor
                                                    <input type="hidden" name="answers[{{ $question->id }}]" x-model="rating"/>
                                                </div>
                                            @elseif($question->type === 'single_select')
                                                <div class="space-y-2">
                                                    @foreach($question->options as $option)
                                                        <label class="flex items-center gap-3 p-3 rounded-xl border border-gray-200 hover:border-blue-300 cursor-pointer transition-all">
                                                            <input type="radio" name="answers[{{ $question->id }}]" value="{{ $option->option_label }}"
                                                                {{ old("answers.{$question->id}") === $option->option_label ? 'checked' : '' }}
                                                                @change="setAnswer({{ $question->id }}, '{{ addslashes($option->option_label) }}')"
                                                                class="text-blue-600"/>
                                                            <span class="text-sm text-gray-700">{{ $option->option_label }}</span>
                                                        </label>
                                                    @endforeach
                                                </div>
                                            @elseif($question->type === 'multi_select')
                                                <div class="space-y-2">
                                                    @foreach($question->options as $option)
                                                        <label class="flex items-center gap-3 p-3 rounded-xl border border-gray-200 hover:border-blue-300 cursor-pointer transition-all">
                                                            <input type="checkbox" name="answers[{{ $question->id }}][]" value="{{ $option->option_label }}"
                                                                class="rounded text-blue-600"/>
                                                            <span class="text-sm text-gray-700">{{ $option->option_label }}</span>
                                                        </label>
                                                    @endforeach
                                                </div>
                                            @elseif($question->type === 'textarea')
                                                <textarea name="answers[{{ $question->id }}]" rows="3"
                                                    class="w-full rounded-xl border border-gray-200 px-4 py-3 text-sm focus:ring-2 fb-primary-ring resize-none"
                                                    placeholder="Your answer...">{{ old("answers.{$question->id}") }}</textarea>
                                            @elseif($question->type === 'digits')
                                                <input type="number" name="answers[{{ $question->id }}]" value="{{ old("answers.{$question->id}") }}"
                                                    class="w-full h-12 rounded-xl border border-gray-200 px-4 text-sm focus:ring-2 fb-primary-ring"/>
                                            @else
                                                <input type="text" name="answers[{{ $question->id }}]" value="{{ old("answers.{$question->id}") }}"
                                                    class="w-full h-12 rounded-xl border border-gray-200 px-4 text-sm focus:ring-2 fb-primary-ring"
                                                    placeholder="Your answer..."/>
                                            @endif

                                            @error("answers.{$question->id}")
                                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                            @enderror
                                        </div>
                                    @endforeach
                                </div>
                            @endfor
                        </div>

                        {{-- Navigation / Submit --}}
                        <div class="p-6 border-t border-gray-100 flex justify-between">
                            @if($stepCount > 1)
                                <button type="button" x-show="currentStep > 1" @click="currentStep--"
                                    class="px-6 py-3 rounded-xl text-sm font-semibold text-gray-600 border border-gray-200 hover:bg-gray-50 transition-all">
                                    Previous
                                </button>
                                <button type="button" x-show="currentStep < {{ $stepCount }}" @click="currentStep++"
                                    class="px-6 py-3 rounded-xl text-sm font-bold text-white fb-primary hover:opacity-90 transition-all ml-auto">
                                    Next Step
                                </button>
                            @endif
                            <button type="submit" x-show="currentStep === {{ $stepCount }}"
                                class="px-8 py-3 rounded-xl text-sm font-bold text-white fb-primary hover:opacity-90 shadow-lg transition-all ml-auto">
                                Submit Feedback
                            </button>
                        </div>
                    </form>
                </div>
            @endif
        @endif
    </div>

    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script>
        function feedbackForm() {
            return {
                currentStep: 1,
                answers: {},
                setAnswer(questionId, value) {
                    this.answers[questionId] = value;
                },
                checkCondition(parentId, operator, conditionValue) {
                    const val = this.answers[parentId] || '';
                    if (operator === 'equals') return val === conditionValue;
                    if (operator === 'not_equals') return val !== conditionValue;
                    if (operator === 'contains') return val.includes(conditionValue);
                    return true;
                }
            }
        }
    </script>
</body>
</html>

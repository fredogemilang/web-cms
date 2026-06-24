<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Feedback — {{ $event->title }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200&display=swap" rel="stylesheet"/>
    @vite(['resources/css/app.css'])
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .fb-primary { background-color: {{ $event->feedback_primary_color ?? '#2563EB' }}; }
        .fb-primary-text { color: {{ $event->feedback_primary_color ?? '#2563EB' }}; }
        .fb-primary-ring:focus { 
            border-color: {{ $event->feedback_primary_color ?? '#2563EB' }}; 
            box-shadow: 0 0 0 4px {{ $event->feedback_primary_color ?? '#2563EB' }}20; 
        }
    </style>
</head>
<body class="bg-[#f6f8fc] min-h-screen flex items-center justify-center p-3 sm:p-6 md:p-8 relative overflow-x-hidden">
    <!-- Ambient Glow Elements -->
    <div class="absolute top-0 left-1/4 w-[300px] sm:w-[500px] h-[300px] sm:h-[500px] rounded-full opacity-10 blur-[80px] sm:blur-[100px] pointer-events-none transition-all duration-1000" style="background-color: {{ $event->feedback_primary_color ?? '#2563EB' }};"></div>
    <div class="absolute bottom-0 right-1/4 w-[300px] sm:w-[500px] h-[300px] sm:h-[500px] rounded-full opacity-10 blur-[80px] sm:blur-[100px] pointer-events-none transition-all duration-1000" style="background-color: {{ $event->feedback_primary_color ?? '#2563EB' }};"></div>

    <div class="w-full max-w-2xl z-10" x-data="feedbackForm()">
        {{-- Header / Breadcrumb outside card --}}
        <div class="text-center mb-5 sm:mb-6">
            <p class="text-[10px] sm:text-xs uppercase tracking-widest text-gray-400 font-bold mb-1">Feedback Portal</p>
            <h1 class="text-lg sm:text-xl font-extrabold text-gray-800 tracking-tight px-2">{{ $event->title }}</h1>
        </div>

        {{-- Success Message --}}
        @if(session('success'))
            <div class="bg-white/80 backdrop-blur-md rounded-2xl sm:rounded-3xl shadow-[0_20px_50px_rgba(0,0,0,0.05)] border border-white/20 overflow-hidden text-center">
                @if($event->feedback_background)
                    <div class="w-full h-32 sm:h-44 overflow-hidden bg-gray-100">
                        <img src="{{ asset('storage/' . $event->feedback_background) }}" class="w-full h-full object-cover" alt="Banner"/>
                    </div>
                @endif
                <div class="p-6 sm:p-10 md:p-14">
                    <div class="h-14 w-14 sm:h-16 sm:w-16 rounded-2xl bg-emerald-50 border border-emerald-100/50 flex items-center justify-center mx-auto mb-5 sm:mb-6 shadow-sm">
                        <span class="material-symbols-outlined text-2xl sm:text-3xl text-emerald-500">check_circle</span>
                    </div>
                    <h2 class="text-xl sm:text-2xl font-extrabold text-gray-900 mb-2">Thank You!</h2>
                    <p class="text-gray-500 text-xs sm:text-sm max-w-md mx-auto px-2">{{ session('success') }}</p>
                </div>
            </div>

        {{-- Eligibility Error (UUID link but not eligible) --}}
        @elseif(!empty($eligibilityError))
            <div class="bg-white/80 backdrop-blur-md rounded-2xl sm:rounded-3xl shadow-[0_20px_50px_rgba(0,0,0,0.05)] border border-white/20 overflow-hidden text-center">
                @if($event->feedback_background)
                    <div class="w-full h-32 sm:h-44 overflow-hidden bg-gray-100">
                        <img src="{{ asset('storage/' . $event->feedback_background) }}" class="w-full h-full object-cover" alt="Banner"/>
                    </div>
                @endif
                <div class="p-6 sm:p-10 md:p-14">
                    <div class="h-14 w-14 sm:h-16 sm:w-16 rounded-2xl bg-red-50 border border-red-100/50 flex items-center justify-center mx-auto mb-5 sm:mb-6 shadow-sm">
                        <span class="material-symbols-outlined text-2xl sm:text-3xl text-red-500">error</span>
                    </div>
                    <h2 class="text-xl sm:text-2xl font-extrabold text-gray-900 mb-2">Access Denied</h2>
                    <p class="text-gray-500 text-xs sm:text-sm max-w-md mx-auto px-2">{{ $eligibilityError }}</p>
                </div>
            </div>

        {{-- EMAIL GATE: Generic link — ask for email first --}}
        @elseif($needsEmail)
            <div class="bg-white/80 backdrop-blur-md rounded-2xl sm:rounded-3xl shadow-[0_20px_50px_rgba(0,0,0,0.05)] border border-white/20 overflow-hidden">
                {{-- Banner/Logo header block --}}
                @if($event->feedback_background)
                    <div class="relative w-full h-36 sm:h-48 bg-gray-100">
                        <img src="{{ asset('storage/' . $event->feedback_background) }}" class="w-full h-full object-cover" alt="Banner"/>
                        <div class="absolute inset-0 bg-gradient-to-t from-black/40 via-transparent to-transparent"></div>
                        @if($event->feedback_foreground)
                            <div class="absolute -bottom-6 left-6 sm:-bottom-8 sm:left-8 p-1 bg-white rounded-xl sm:rounded-2xl shadow-md border border-gray-100">
                                <img src="{{ asset('storage/' . $event->feedback_foreground) }}" class="h-12 w-12 sm:h-16 sm:w-16 rounded-lg sm:rounded-xl object-contain bg-white" alt="Logo"/>
                            </div>
                        @endif
                    </div>
                    <div class="h-6 sm:h-8"></div>
                @elseif($event->feedback_foreground)
                    <div class="relative w-full h-24 sm:h-28 flex items-center px-6 sm:px-8 border-b border-gray-100/50 bg-slate-50/50">
                        <div class="p-1 bg-white rounded-xl sm:rounded-2xl shadow-md border border-gray-100">
                            <img src="{{ asset('storage/' . $event->feedback_foreground) }}" class="h-11 w-11 sm:h-14 sm:w-14 rounded-lg sm:rounded-xl object-contain bg-white" alt="Logo"/>
                        </div>
                    </div>
                @else
                    <div class="p-6 sm:p-8 text-center border-b border-gray-100/50">
                        <div class="h-12 w-12 sm:h-14 sm:w-14 rounded-xl sm:rounded-2xl bg-blue-50 border border-blue-100/50 flex items-center justify-center mx-auto mb-2 sm:mb-3">
                            <span class="material-symbols-outlined text-xl sm:text-2xl fb-primary-text">verified_user</span>
                        </div>
                    </div>
                @endif

                <div class="p-6 sm:p-8 text-center {{ $event->feedback_background ? '' : 'pt-4' }}">
                    <h2 class="text-lg sm:text-xl font-extrabold text-gray-900">Verify Your Identity</h2>
                    <p class="text-xs sm:text-sm text-gray-500 mt-1.5 max-w-sm mx-auto">Please enter the email address you registered with to access the feedback form.</p>
                </div>

                <form action="{{ route('events.feedback.verify', $event->slug) }}" method="POST" class="p-6 sm:p-8 pt-2">
                    @csrf
                    <div class="mb-5 sm:mb-6">
                        <label class="block text-[10px] sm:text-xs font-bold uppercase tracking-wider text-gray-400 mb-2">Email Address</label>
                        <div class="relative">
                            <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 text-lg">mail</span>
                            <input type="email" name="email" value="{{ old('email') }}" required autofocus
                                class="w-full h-11 sm:h-12 pl-11 pr-4 rounded-xl sm:rounded-2xl border border-gray-200 bg-gray-50/30 text-xs sm:text-sm focus:bg-white focus:outline-none focus:ring-4 fb-primary-ring transition-all"
                                placeholder="name@domain.com"/>
                        </div>
                        @error('email')
                            <p class="text-red-500 text-[11px] sm:text-xs mt-2.5 flex items-center gap-1.5 font-semibold">
                                <span class="material-symbols-outlined text-sm">warning</span>
                                {{ $message }}
                            </p>
                        @enderror
                    </div>
                    <button type="submit" class="w-full py-3 sm:py-3.5 rounded-xl sm:rounded-2xl text-xs sm:text-sm font-bold text-white fb-primary hover:opacity-95 shadow-lg shadow-blue-500/10 hover:scale-[1.01] active:scale-[0.99] transition-all">
                        Continue to Feedback Form
                    </button>
                </form>
            </div>

        {{-- FEEDBACK FORM: UUID-identified user --}}
        @else
            @if(isset($registration))
                <div class="bg-white/80 backdrop-blur-md rounded-2xl sm:rounded-3xl shadow-[0_20px_50px_rgba(0,0,0,0.05)] border border-white/20 overflow-hidden">
                    {{-- Banner/Logo header block --}}
                    @if($event->feedback_background)
                        <div class="relative w-full h-36 sm:h-48 bg-gray-100">
                            <img src="{{ asset('storage/' . $event->feedback_background) }}" class="w-full h-full object-cover" alt="Banner"/>
                            <div class="absolute inset-0 bg-gradient-to-t from-black/40 via-transparent to-transparent"></div>
                            @if($event->feedback_foreground)
                                <div class="absolute -bottom-6 left-6 sm:-bottom-8 sm:left-8 p-1 bg-white rounded-xl sm:rounded-2xl shadow-md border border-gray-100">
                                    <img src="{{ asset('storage/' . $event->feedback_foreground) }}" class="h-12 w-12 sm:h-16 sm:w-16 rounded-lg sm:rounded-xl object-contain bg-white" alt="Logo"/>
                                </div>
                            @endif
                        </div>
                        <div class="h-6 sm:h-8"></div>
                    @elseif($event->feedback_foreground)
                        <div class="relative w-full h-24 sm:h-28 flex items-center px-6 sm:px-8 border-b border-gray-100/50 bg-slate-50/50">
                            <div class="p-1 bg-white rounded-xl sm:rounded-2xl shadow-md border border-gray-100">
                                <img src="{{ asset('storage/' . $event->feedback_foreground) }}" class="h-11 w-11 sm:h-14 sm:w-14 rounded-lg sm:rounded-xl object-contain bg-white" alt="Logo"/>
                            </div>
                        </div>
                    @endif

                    {{-- Identified user banner --}}
                    <div class="px-6 sm:px-8 py-4 sm:py-5 bg-slate-50/80 border-b border-gray-100/50 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            @if(!$event->feedback_background && !$event->feedback_foreground)
                                <div class="h-9 w-9 sm:h-10 sm:w-10 rounded-xl fb-primary flex items-center justify-center text-white font-bold text-xs sm:text-sm shadow-sm">
                                    {{ strtoupper(substr($registration->name ?? $registration->full_name ?? 'U', 0, 1)) }}
                                </div>
                            @endif
                            <div>
                                <p class="text-xs sm:text-sm font-bold text-gray-900 leading-none">{{ $registration->name ?? $registration->full_name }}</p>
                                <p class="text-[10px] sm:text-xs text-gray-450 font-semibold mt-1">{{ $registration->email }}</p>
                            </div>
                        </div>
                        <div class="px-2 py-0.5 sm:px-2.5 sm:py-1 rounded-full bg-emerald-50 text-emerald-650 border border-emerald-100/50 text-[9px] sm:text-[10px] font-extrabold uppercase tracking-wider">
                            Verified Guest
                        </div>
                    </div>

                    <form action="{{ route('events.feedback.submit', [$event->slug, $registration->uuid]) }}" method="POST">
                        @csrf

                        @error('general')
                            <div class="mx-6 sm:mx-8 mt-5 sm:mt-6 p-3.5 sm:p-4 rounded-xl sm:rounded-2xl bg-red-50 border border-red-100 text-red-650 text-xs sm:text-sm font-medium flex items-center gap-2">
                                <span class="material-symbols-outlined text-base sm:text-lg">warning</span>
                                {{ $message }}
                            </div>
                        @enderror

                        {{-- Step Indicator --}}
                        @if($stepCount > 1)
                            <div class="px-6 sm:px-8 py-3 sm:py-4 bg-gray-50/30 border-b border-gray-100/50 flex items-center gap-2 sm:gap-3 overflow-x-auto">
                                @for($s = 1; $s <= $stepCount; $s++)
                                    <div class="flex items-center gap-1 sm:gap-1.5 shrink-0">
                                        <button type="button" @click="currentStep = {{ $s }}"
                                            class="h-6 sm:h-7 px-3 sm:px-3.5 rounded-full text-[10px] sm:text-xs font-extrabold transition-all duration-300"
                                            :class="currentStep === {{ $s }} ? 'fb-primary text-white shadow-md' : 'bg-gray-100 text-gray-555 hover:bg-gray-200'">
                                            Step {{ $s }}
                                        </button>
                                        @if($s < $stepCount)
                                            <span class="material-symbols-outlined text-gray-300 text-xs sm:text-sm">chevron_right</span>
                                        @endif
                                    </div>
                                @endfor
                            </div>
                        @endif

                        {{-- Questions --}}
                        <div class="p-6 sm:p-8 space-y-6 sm:space-y-8">
                            @for($s = 1; $s <= $stepCount; $s++)
                                <div x-show="currentStep === {{ $s }}" x-transition>
                                    @foreach(($questions[$s] ?? collect()) as $question)
                                        <div class="mb-6 sm:mb-8"
                                            @if($question->is_conditional && $question->parent_question_id)
                                                x-data="{ visible: false }"
                                                x-effect="visible = checkCondition({{ $question->parent_question_id }}, '{{ $question->condition_operator }}', '{{ addslashes($question->condition_value) }}')"
                                                x-show="visible"
                                            @endif
                                        >
                                            <label class="block text-xs sm:text-sm font-bold text-gray-800">
                                                {{ $question->question }}
                                                @if($question->is_required)<span class="text-red-500 ml-0.5">*</span>@endif
                                            </label>

                                            @if($question->type === 'rating')
                                                <div class="flex flex-wrap gap-1.5 sm:gap-2.5 mt-2.5 sm:mt-3" x-data="{ rating: {{ (int) old("answers.{$question->id}", 0) }} }">
                                                    @for($r = ($question->rating_min_value ?? 1); $r <= ($question->rating_max_value ?? 5); $r++)
                                                        <button type="button" @click="rating = {{ $r }}; setAnswer({{ $question->id }}, {{ $r }})"
                                                            class="h-9 w-9 sm:h-11 sm:w-11 rounded-full border-2 text-xs sm:text-sm font-extrabold transition-all duration-200 flex items-center justify-center hover:scale-105 shrink-0"
                                                            :class="rating === {{ $r }} ? 'text-white border-transparent shadow-lg shadow-blue-500/20' : (rating > {{ $r }} ? 'border-blue-400 text-blue-600 bg-blue-50/50' : 'border-gray-200 text-gray-400 bg-white')]"
                                                            :style="rating === {{ $r }} ? 'background-color: {{ $event->feedback_primary_color ?? '#2563EB' }}' : ''">
                                                            {{ $r }}
                                                        </button>
                                                    @endfor
                                                    <input type="hidden" name="answers[{{ $question->id }}]" x-model="rating"/>
                                                </div>
                                            @elseif($question->type === 'single_select')
                                                <div class="space-y-2 mt-2.5 sm:mt-3">
                                                    @foreach($question->options as $option)
                                                        <label class="flex items-center gap-3 p-3 sm:p-4 rounded-xl sm:rounded-2xl border border-gray-200/80 bg-gray-50/30 hover:bg-white hover:border-gray-300 hover:shadow-sm cursor-pointer transition-all">
                                                            <input type="radio" name="answers[{{ $question->id }}]" value="{{ $option->option_label }}"
                                                                {{ old("answers.{$question->id}") === $option->option_label ? 'checked' : '' }}
                                                                @change="setAnswer({{ $question->id }}, '{{ addslashes($option->option_label) }}')"
                                                                class="text-blue-600 focus:ring-blue-500"
                                                                style="accent-color: {{ $event->feedback_primary_color ?? '#2563EB' }}"/>
                                                            <span class="text-xs sm:text-sm font-semibold text-gray-700">{{ $option->option_label }}</span>
                                                        </label>
                                                    @endforeach
                                                </div>
                                            @elseif($question->type === 'multi_select')
                                                <div class="space-y-2 mt-2.5 sm:mt-3">
                                                    @foreach($question->options as $option)
                                                        <label class="flex items-center gap-3 p-3 sm:p-4 rounded-xl sm:rounded-2xl border border-gray-200/80 bg-gray-50/30 hover:bg-white hover:border-gray-300 hover:shadow-sm cursor-pointer transition-all">
                                                            <input type="checkbox" name="answers[{{ $question->id }}][]" value="{{ $option->option_label }}"
                                                                class="rounded text-blue-600 focus:ring-blue-500"
                                                                style="accent-color: {{ $event->feedback_primary_color ?? '#2563EB' }}"/>
                                                            <span class="text-xs sm:text-sm font-semibold text-gray-700">{{ $option->option_label }}</span>
                                                        </label>
                                                    @endforeach
                                                </div>
                                            @elseif($question->type === 'textarea')
                                                <div class="mt-2.5 sm:mt-3">
                                                    <textarea name="answers[{{ $question->id }}]" rows="3"
                                                        class="w-full rounded-xl sm:rounded-2xl border border-gray-200 bg-gray-50/30 px-3.5 sm:px-4 py-3 sm:py-3.5 text-xs sm:text-sm focus:bg-white focus:outline-none focus:ring-4 fb-primary-ring resize-none transition-all"
                                                        placeholder="Your response here...">{{ old("answers.{$question->id}") }}</textarea>
                                                </div>
                                            @elseif($question->type === 'digits')
                                                <div class="mt-2.5 sm:mt-3">
                                                    <input type="number" name="answers[{{ $question->id }}]" value="{{ old("answers.{$question->id}") }}"
                                                        class="w-full h-11 sm:h-12 rounded-xl sm:rounded-2xl border border-gray-200 bg-gray-50/30 px-3.5 sm:px-4 text-xs sm:text-sm focus:bg-white focus:outline-none focus:ring-4 fb-primary-ring transition-all"/>
                                                </div>
                                            @else
                                                <div class="mt-2.5 sm:mt-3">
                                                    <input type="text" name="answers[{{ $question->id }}]" value="{{ old("answers.{$question->id}") }}"
                                                        class="w-full h-11 sm:h-12 rounded-xl sm:rounded-2xl border border-gray-200 bg-gray-50/30 px-3.5 sm:px-4 text-xs sm:text-sm focus:bg-white focus:outline-none focus:ring-4 fb-primary-ring transition-all"
                                                        placeholder="Your response here..."/>
                                                </div>
                                            @endif

                                            @error("answers.{$question->id}")
                                                <p class="text-red-500 text-[10px] sm:text-xs mt-2 font-semibold">{{ $message }}</p>
                                            @enderror
                                        </div>
                                    @endforeach
                                </div>
                            @endfor
                        </div>

                        {{-- Navigation / Submit --}}
                        <div class="p-6 sm:p-8 border-t border-gray-100 flex items-center justify-between">
                            @if($stepCount > 1)
                                <button type="button" x-show="currentStep > 1" @click="currentStep--"
                                    class="px-4 py-2.5 sm:px-5 sm:py-3 rounded-xl sm:rounded-2xl text-xs sm:text-sm font-bold text-gray-500 border border-gray-200 hover:bg-gray-50 transition-all">
                                    Previous
                                </button>
                                <button type="button" x-show="currentStep < {{ $stepCount }}" @click="currentStep++"
                                    class="px-5 py-2.5 sm:px-6 sm:py-3 rounded-xl sm:rounded-2xl text-xs sm:text-sm font-bold text-white fb-primary hover:opacity-95 shadow-md shadow-blue-500/10 hover:scale-[1.01] transition-all ml-auto">
                                    Next Step
                                </button>
                            @endif
                            <button type="submit" x-show="currentStep === {{ $stepCount }}"
                                class="px-6 py-3 sm:px-8 sm:py-3.5 rounded-xl sm:rounded-2xl text-xs sm:text-sm font-bold text-white fb-primary hover:opacity-95 shadow-lg shadow-blue-500/15 hover:scale-[1.01] transition-all ml-auto">
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
                    if (operator === 'equals') return val.toString() === conditionValue.toString();
                    if (operator === 'not_equals') return val.toString() !== conditionValue.toString();
                    if (operator === 'contains') return val.toString().includes(conditionValue.toString());
                    return true;
                }
            }
        }
    </script>
</body>
</html>

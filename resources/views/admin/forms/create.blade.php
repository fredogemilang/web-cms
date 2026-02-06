@extends('layouts.admin')

@section('title', 'Create Form')
@section('hide-header', true)

@section('content')
<div class="flex flex-col h-full" x-data="formBuilder()">
    {{-- Header --}}
    <header class="sticky top-0 z-30 flex flex-col gap-6 md:flex-row md:items-center md:justify-between px-6 py-6 md:px-10 md:pt-8 md:pb-6 bg-[#F4F5F6]/95 dark:bg-[#0B0B0B]/95 backdrop-blur-sm border-b border-gray-200 dark:border-[#272B30]">
        <div class="flex items-center gap-4">
            <a class="h-10 w-10 flex items-center justify-center rounded-xl bg-white dark:bg-[#1A1A1A] border border-gray-200 dark:border-[#272B30] text-[#6F767E] hover:text-[#111827] dark:hover:text-[#FCFCFC] transition-all"
                href="{{ route('admin.forms.index') }}">
                <span class="material-symbols-outlined text-xl">arrow_back</span>
            </a>
            <div>
                <h1 class="text-xl font-bold text-[#111827] dark:text-[#FCFCFC]">
                    Create New Form
                </h1>
                <div class="flex items-center gap-2 text-xs text-[#6F767E] mt-0.5" x-cloak>
                    <span class="w-2 h-2 rounded-full inline-block"
                        :class="isActive ? 'bg-green-500' : 'bg-gray-400'"></span>
                    <span x-text="isActive ? 'Active' : 'Inactive'"></span>
                </div>
            </div>
        </div>
        <div class="flex items-center gap-4">
            <div class="flex items-center gap-2">
                <!-- Theme Toggle -->
                <button 
                    x-data="{ 
                        darkMode: document.documentElement.classList.contains('dark'),
                        toggle() {
                            this.darkMode = !this.darkMode;
                            document.documentElement.classList.toggle('dark');
                            localStorage.setItem('theme', this.darkMode ? 'dark' : 'light');
                        }
                    }"
                    @click="toggle()"
                    class="flex h-12 w-12 items-center justify-center rounded-full bg-white text-[#6F767E] shadow-sm hover:bg-gray-50 hover:text-[#111827] dark:bg-[#272B30] dark:text-[#FCFCFC] transition-colors focus:outline-none ml-2">
                    <span class="material-symbols-outlined text-[24px]" x-show="!darkMode" x-cloak>dark_mode</span>
                    <span class="material-symbols-outlined text-[24px]" x-show="darkMode" x-cloak>light_mode</span>
                </button>
            </div>
            
            <div class="h-8 w-px bg-gray-200 dark:bg-[#272B30]"></div>

            <div class="flex items-center gap-3">
                <button @click="submitForm" :disabled="isSaving"
                    class="px-6 py-2 rounded-lg text-sm font-bold text-white bg-primary hover:bg-blue-600 shadow-lg shadow-primary/20 transition-all flex items-center gap-2 disabled:opacity-50">
                    <span class="material-symbols-outlined text-[18px]" x-show="!isSaving">save</span>
                    <span x-show="!isSaving">Save Form</span>
                    <span x-show="isSaving">Saving...</span>
                </button>
            </div>
        </div>
    </header>

    {{-- Main Content --}}
    <div class="flex-1 flex overflow-hidden">
        {{-- Left Panel: Builder --}}
        <div class="flex-1 overflow-y-auto p-6 md:p-10 no-scrollbar">
            <div class="max-w-4xl mx-auto space-y-10">
                {{-- Name & Slug --}}
                <div class="space-y-4">
                    <input x-model="name" @input="generateSlug"
                        class="w-full bg-transparent border-none text-4xl md:text-5xl font-extrabold text-[#111827] dark:text-[#FCFCFC] placeholder-gray-400 dark:placeholder-[#272B30] focus:ring-0 px-0"
                        placeholder="Form Name" type="text" />
                    
                    <div class="flex items-center gap-2 text-sm text-[#6F767E] font-medium bg-white dark:bg-[#1A1A1A] w-fit px-3 py-1.5 rounded-lg border border-gray-200 dark:border-[#272B30]">
                        <span class="material-symbols-outlined text-[16px]">link</span>
                        <input x-model="slug"
                            class="bg-transparent border-none text-primary focus:ring-0 p-0 text-sm font-medium w-auto min-w-[150px]"
                            type="text" placeholder="form-slug" />
                    </div>
                </div>

                {{-- Fields Builder --}}
                <div class="space-y-6">
                    <div class="flex items-center justify-between">
                        <h3 class="text-sm font-bold text-[#6F767E] uppercase tracking-widest">Form Fields</h3>
                        <div class="text-xs text-[#6F767E]" x-text="fields.length + ' fields'"></div>
                    </div>

                    <div class="builder-dropzone min-h-[400px] rounded-3xl p-6 md:p-8 flex flex-col gap-6 border border-gray-200 dark:border-[#272B30]/30 relative"
                        style="background-image: radial-gradient(#E5E7EB 1px, transparent 1px); background-size: 24px 24px;"
                        :style="document.documentElement.classList.contains('dark') ? 'background-image: radial-gradient(#272B30 1px, transparent 1px)' : ''"
                        x-init="
                            Sortable.create($el, {
                                animation: 150,
                                handle: '.cursor-grab',
                                draggable: '.group',
                                ghostClass: 'opacity-50',
                                onEnd: (evt) => {
                                    if (evt.oldIndex === evt.newIndex) return;
                                    
                                    // Use draggable indices to ignore template tags
                                    const oldIndex = evt.oldDraggableIndex;
                                    const newIndex = evt.newDraggableIndex;
                                    
                                    // Move item in array
                                    const item = fields[oldIndex];
                                    fields.splice(oldIndex, 1);
                                    fields.splice(newIndex, 0, item);
                                    
                                    // Force reactivity
                                    fields = [...fields];
                                }
                            })
                        ">

                        <template x-if="fields.length === 0">
                            <div class="text-center py-12 text-[#6F767E]">
                                <span class="material-symbols-outlined text-5xl mb-4 block opacity-30">view_list</span>
                                <p class="font-medium">No fields yet</p>
                                <p class="text-sm">Click "Add Field" to start building your form</p>
                            </div>
                        </template>

                        <template x-for="(field, index) in fields" :key="field.field_id || index">
                            <div class="group bg-white dark:bg-[#1A1A1A] border border-gray-200 dark:border-[#272B30] rounded-2xl p-6 shadow-sm hover:border-primary/50 transition-all relative"
                                :data-id="field.field_id">
                                {{-- Drag/Actions Handle (Placeholder for ordering) --}}
                                <div class="absolute top-4 right-4 flex items-center gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                    <button @click="removeField(index)" class="p-1.5 rounded-lg hover:bg-red-50 text-gray-400 hover:text-red-500 transition-colors">
                                        <span class="material-symbols-outlined text-[20px]">delete</span>
                                    </button>
                                </div>
                                <div class="absolute -left-3 top-6 bg-white dark:bg-[#1A1A1A] border border-gray-200 dark:border-[#272B30] rounded-lg p-1 text-[#6F767E] cursor-grab opacity-0 group-hover:opacity-100 transition-opacity shadow-sm">
                                    <span class="material-symbols-outlined text-lg">drag_indicator</span>
                                </div>

                                <div class="space-y-4">
                                    <div class="flex items-start gap-4">
                                        {{-- Field Type Icon --}}
                                        <div class="h-10 w-10 rounded-xl bg-gray-50 dark:bg-[#272B30] flex items-center justify-center shrink-0">
                                            <span class="material-symbols-outlined text-[#6F767E]" x-text="getFieldIcon(field.type)"></span>
                                        </div>
                                        
                                        <div class="flex-1 grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <div>
                                                <label class="block text-xs font-bold text-[#6F767E] uppercase tracking-wider mb-1">Label</label>
                                                <input x-model="field.label" type="text" 
                                                    x-on:input="field.field_id = $event.target.value.toLowerCase().replace(/[^a-z0-9\s]/g, '').replace(/\s+/g, '_').replace(/_+/g, '_').replace(/^_|_$/g, '')"
                                                    class="w-full bg-[#F4F5F6] dark:bg-[#0B0B0B] border-none text-sm font-semibold rounded-lg focus:ring-2 focus:ring-primary px-3 py-2 text-[#111827] dark:text-[#FCFCFC]" 
                                                    placeholder="Enter Field Label" required>
                                            </div>
                                            <div>
                                                <label class="block text-xs font-bold text-[#6F767E] uppercase tracking-wider mb-1">Field ID</label>
                                                <input x-model="field.field_id" type="text" 
                                                    class="w-full bg-[#F4F5F6] dark:bg-[#0B0B0B] border-none text-sm font-medium rounded-lg focus:ring-2 focus:ring-primary px-3 py-2 text-[#111827] dark:text-[#FCFCFC]" 
                                                    placeholder="unique_id" required>
                                            </div>
                                        </div>
                                    </div>


                                    {{-- Expanded Settings --}}
                                    <div class="pl-[56px] space-y-4">
                                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                            <div>
                                                <label class="block text-xs font-bold text-[#6F767E] uppercase tracking-wider mb-1">Placeholder</label>
                                                <input x-model="field.placeholder" type="text" 
                                                    class="w-full bg-transparent border border-gray-200 dark:border-[#272B30] text-sm rounded-lg focus:ring-2 focus:ring-primary px-3 py-2 text-[#111827] dark:text-[#FCFCFC]">
                                            </div>
                                            <div>
                                                <label class="block text-xs font-bold text-[#6F767E] uppercase tracking-wider mb-1">Help Text</label>
                                                <input x-model="field.help_text" type="text" 
                                                    class="w-full bg-transparent border border-gray-200 dark:border-[#272B30] text-sm rounded-lg focus:ring-2 focus:ring-primary px-3 py-2 text-[#111827] dark:text-[#FCFCFC]">
                                            </div>
                                            <div>
                                                <label class="block text-xs font-bold text-[#6F767E] uppercase tracking-wider mb-1">Width</label>
                                                <select x-model="field.column_width" 
                                                    class="w-full bg-transparent border border-gray-200 dark:border-[#272B30] text-sm rounded-lg focus:ring-2 focus:ring-primary px-3 py-2 text-[#111827] dark:text-[#FCFCFC]">
                                                    <option value="full">Full Width</option>
                                                    <option value="half">Half (1/2)</option>
                                                    <option value="third">Third (1/3)</option>
                                                    <option value="quarter">Quarter (1/4)</option>
                                                </select>
                                            </div>
                                        </div>

                                        {{-- Options for select/radio/checkbox --}}
                                        <div x-show="['select', 'radio', 'checkbox'].includes(field.type)" class="bg-[#F4F5F6] dark:bg-[#0B0B0B] rounded-xl p-4">
                                            <div class="flex justify-between items-center mb-2">
                                                <label class="text-xs font-bold text-[#111827] dark:text-[#FCFCFC]">Options</label>
                                                <div class="text-[10px] text-[#6F767E]">Format: Label|Value (one per line)</div>
                                            </div>
                                            <textarea x-model="field.options_text" rows="3"
                                                class="w-full bg-white dark:bg-[#1A1A1A] border-none text-sm font-mono rounded-lg focus:ring-2 focus:ring-primary px-3 py-2"
                                                placeholder="Option 1|val1&#10;Option 2|val2"></textarea>
                                        </div>
                                        
                                        {{-- Rating field settings --}}
                                        <div x-show="field.type === 'rating'" class="bg-[#F4F5F6] dark:bg-[#0B0B0B] rounded-xl p-4">
                                            <label class="text-xs font-bold text-[#111827] dark:text-[#FCFCFC] mb-2 block">Rating Settings</label>
                                            <div class="grid grid-cols-2 gap-4">
                                                <div>
                                                    <label class="text-xs text-[#6F767E]">Max Stars</label>
                                                    <input x-model="field.advanced_settings.max_rating" type="number" min="1" max="10" 
                                                        class="w-full bg-white dark:bg-[#1A1A1A] border-none text-sm rounded-lg focus:ring-2 focus:ring-primary px-3 py-2" value="5">
                                                </div>
                                            </div>
                                        </div>
                                        
                                        {{-- Range slider settings --}}
                                        <div x-show="field.type === 'range'" class="bg-[#F4F5F6] dark:bg-[#0B0B0B] rounded-xl p-4">
                                            <label class="text-xs font-bold text-[#111827] dark:text-[#FCFCFC] mb-2 block">Range Settings</label>
                                            <div class="grid grid-cols-3 gap-4">
                                                <div>
                                                    <label class="text-xs text-[#6F767E]">Min</label>
                                                    <input x-model="field.advanced_settings.min" type="number" 
                                                        class="w-full bg-white dark:bg-[#1A1A1A] border-none text-sm rounded-lg focus:ring-2 focus:ring-primary px-3 py-2" value="0">
                                                </div>
                                                <div>
                                                    <label class="text-xs text-[#6F767E]">Max</label>
                                                    <input x-model="field.advanced_settings.max" type="number" 
                                                        class="w-full bg-white dark:bg-[#1A1A1A] border-none text-sm rounded-lg focus:ring-2 focus:ring-primary px-3 py-2" value="100">
                                                </div>
                                                <div>
                                                    <label class="text-xs text-[#6F767E]">Step</label>
                                                    <input x-model="field.advanced_settings.step" type="number" 
                                                        class="w-full bg-white dark:bg-[#1A1A1A] border-none text-sm rounded-lg focus:ring-2 focus:ring-primary px-3 py-2" value="1">
                                                </div>
                                            </div>
                                        </div>
                                        
                                        {{-- Mask input settings --}}
                                        <div x-show="field.type === 'mask'" class="bg-[#F4F5F6] dark:bg-[#0B0B0B] rounded-xl p-4">
                                            <label class="text-xs font-bold text-[#111827] dark:text-[#FCFCFC] mb-2 block">Input Mask</label>
                                            <select x-model="field.advanced_settings.mask_pattern" 
                                                class="w-full bg-white dark:bg-[#1A1A1A] border-none text-sm rounded-lg focus:ring-2 focus:ring-primary px-3 py-2">
                                                <option value="phone">Phone (000) 000-0000</option>
                                                <option value="date">Date DD/MM/YYYY</option>
                                                <option value="ssn">SSN 000-00-0000</option>
                                                <option value="credit_card">Credit Card 0000 0000 0000 0000</option>
                                                <option value="custom">Custom...</option>
                                            </select>
                                        </div>
                                        
                                        {{-- Custom HTML settings --}}
                                        <div x-show="field.type === 'html'" class="bg-[#F4F5F6] dark:bg-[#0B0B0B] rounded-xl p-4">
                                            <label class="text-xs font-bold text-[#111827] dark:text-[#FCFCFC] mb-2 block">HTML Content</label>
                                            <textarea x-model="field.advanced_settings.html_content" rows="4"
                                                class="w-full bg-white dark:bg-[#1A1A1A] border-none text-sm font-mono rounded-lg focus:ring-2 focus:ring-primary px-3 py-2"
                                                placeholder="<p>Your custom HTML here...</p>"></textarea>
                                        </div>

                                        {{-- File Upload Settings --}}
                                        <div x-show="['file', 'image'].includes(field.type)" class="bg-[#F4F5F6] dark:bg-[#0B0B0B] rounded-xl p-4">
                                            <label class="text-xs font-bold text-[#111827] dark:text-[#FCFCFC] mb-2 block">File Settings</label>
                                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                                <div class="md:col-span-2" x-show="field.type !== 'image'">
                                                    <label class="text-xs text-[#6F767E]">Allowed File Types</label>
                                                    <input x-model="field.advanced_settings.allowed_file_types" type="text" 
                                                        class="w-full bg-white dark:bg-[#1A1A1A] border-none text-sm rounded-lg focus:ring-2 focus:ring-primary px-3 py-2" 
                                                        placeholder="e.g. pdf, doc, docx">
                                                    <p class="text-[10px] text-[#6F767E] mt-1">Comma separated extensions (leave empty for all)</p>
                                                </div>
                                                <div>
                                                    <label class="text-xs text-[#6F767E]">Max File Size (MB)</label>
                                                    <input x-model="field.advanced_settings.max_file_size" type="number" min="1" max="50"
                                                        class="w-full bg-white dark:bg-[#1A1A1A] border-none text-sm rounded-lg focus:ring-2 focus:ring-primary px-3 py-2" 
                                                        placeholder="e.g. 5">
                                                </div>
                                                <div>
                                                    <label class="text-xs text-[#6F767E]">Max Files</label>
                                                    <input x-model="field.advanced_settings.max_files" type="number" min="1" max="10"
                                                        class="w-full bg-white dark:bg-[#1A1A1A] border-none text-sm rounded-lg focus:ring-2 focus:ring-primary px-3 py-2" 
                                                        placeholder="e.g. 1" value="1">
                                                </div>
                                            </div>
                                        </div>

                                        {{-- Terms & GDPR Settings --}}
                                        <div x-show="['gdpr', 'terms'].includes(field.type)" class="bg-[#F4F5F6] dark:bg-[#0B0B0B] rounded-xl p-4">
                                            <div x-show="field.type === 'gdpr'">
                                                <label class="text-xs font-bold text-[#111827] dark:text-[#FCFCFC] mb-2 block">Consent Text</label>
                                                <textarea x-model="field.advanced_settings.consent_text" rows="3"
                                                    class="w-full bg-white dark:bg-[#1A1A1A] border-none text-sm font-mono rounded-lg focus:ring-2 focus:ring-primary px-3 py-2"
                                                    placeholder="Enter consent text here..."></textarea>
                                            </div>

                                            <div x-show="field.type === 'terms'">
                                                <label class="text-xs font-bold text-[#111827] dark:text-[#FCFCFC] mb-2 block">Terms Content</label>
                                                <textarea x-model="field.advanced_settings.terms_text" rows="3"
                                                    class="w-full bg-white dark:bg-[#1A1A1A] border-none text-sm font-mono rounded-lg focus:ring-2 focus:ring-primary px-3 py-2"
                                                    placeholder="Enter terms content here..."></textarea>
                                            </div>
                                            <p class="text-[10px] text-[#6F767E] mt-1">HTML allowed (e.g. &lt;a href="..."&gt;Link&lt;/a&gt;)</p>
                                        </div>

                                        {{-- Toggles --}}
                                        <div class="flex items-center gap-6 pt-2">
                                            <label class="flex items-center gap-2 cursor-pointer">
                                                <input type="checkbox" x-model="field.is_required" class="rounded border-gray-300 text-primary focus:ring-primary">
                                                <span class="text-sm font-medium text-[#111827] dark:text-[#FCFCFC]">Required</span>
                                            </label>
                                        </div>
                                        
                                        {{-- Conditional Logic --}}
                                        <div class="pt-4 border-t border-gray-200 dark:border-[#272B30]" x-show="!['section', 'divider', 'html'].includes(field.type)">
                                            <button type="button" @click="field.showLogic = !field.showLogic" 
                                                class="flex items-center gap-2 text-sm font-medium text-[#6F767E] hover:text-primary transition-colors">
                                                <span class="material-symbols-outlined text-base" x-text="field.showLogic ? 'expand_less' : 'expand_more'"></span>
                                                <span class="material-symbols-outlined text-base">rule</span>
                                                Conditional Logic
                                                <span x-show="field.conditional_logic?.conditions?.length > 0" class="px-1.5 py-0.5 text-[10px] bg-primary text-white rounded-full" x-text="field.conditional_logic?.conditions?.length || 0"></span>
                                            </button>
                                            
                                            <div x-show="field.showLogic" x-collapse class="mt-3 space-y-3">
                                                {{-- Action & Match Type --}}
                                                <div class="grid grid-cols-2 gap-3">
                                                    <div>
                                                        <label class="text-xs text-[#6F767E] mb-1 block">Action</label>
                                                        <select x-model="field.conditional_logic.action" 
                                                            class="w-full bg-[#F4F5F6] dark:bg-[#0B0B0B] border-none text-sm rounded-lg px-3 py-2">
                                                            <option value="show">Show this field if...</option>
                                                            <option value="hide">Hide this field if...</option>
                                                        </select>
                                                    </div>
                                                    <div>
                                                        <label class="text-xs text-[#6F767E] mb-1 block">Match</label>
                                                        <select x-model="field.conditional_logic.match_type" 
                                                            class="w-full bg-[#F4F5F6] dark:bg-[#0B0B0B] border-none text-sm rounded-lg px-3 py-2">
                                                            <option value="all">All conditions</option>
                                                            <option value="any">Any condition</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                
                                                {{-- Conditions List --}}
                                                <div class="space-y-2">
                                                    <template x-for="(condition, condIndex) in (field.conditional_logic.conditions || [])" :key="condIndex">
                                                        <div class="flex gap-2 items-center bg-[#F4F5F6] dark:bg-[#0B0B0B] rounded-lg p-2">
                                                            <select x-model="condition.field_id" 
                                                                class="flex-1 bg-white dark:bg-[#1A1A1A] border-none text-xs rounded-lg px-2 py-1.5">
                                                                <option value="">Select field...</option>
                                                                <template x-for="(f, fIndex) in fields.filter((ff, fi) => fi !== index)" :key="fIndex">
                                                                    <option :value="f.field_id" x-text="f.label || f.field_id || 'Unnamed field'"></option>
                                                                </template>
                                                            </select>
                                                            <select x-model="condition.operator" 
                                                                class="w-28 bg-white dark:bg-[#1A1A1A] border-none text-xs rounded-lg px-2 py-1.5">
                                                                <option value="equals">Equals</option>
                                                                <option value="not_equals">Not Equals</option>
                                                                <option value="contains">Contains</option>
                                                                <option value="not_contains">Not Contains</option>
                                                                <option value="is_empty">Is Empty</option>
                                                                <option value="is_not_empty">Is Not Empty</option>
                                                                <option value="is_checked">Is Checked</option>
                                                                <option value="is_not_checked">Not Checked</option>
                                                                <option value="greater_than">Greater Than</option>
                                                                <option value="less_than">Less Than</option>
                                                            </select>
                                                            <input x-model="condition.value" type="text" placeholder="Value"
                                                                x-show="!['is_empty', 'is_not_empty', 'is_checked', 'is_not_checked'].includes(condition.operator)"
                                                                class="w-24 bg-white dark:bg-[#1A1A1A] border-none text-xs rounded-lg px-2 py-1.5">
                                                            <button type="button" @click="field.conditional_logic.conditions.splice(condIndex, 1)"
                                                                class="p-1 text-red-500 hover:bg-red-100 dark:hover:bg-red-900/20 rounded">
                                                                <span class="material-symbols-outlined text-sm">close</span>
                                                            </button>
                                                        </div>
                                                    </template>
                                                </div>
                                                
                                                {{-- Add Condition Button --}}
                                                <button type="button" @click="addCondition(index)"
                                                    class="flex items-center gap-1 text-xs font-medium text-primary hover:text-primary/80">
                                                    <span class="material-symbols-outlined text-sm">add</span>
                                                    Add Condition
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </template>

                        {{-- Add Field Button --}}
                        <button @click="showFieldSelector = true"
                            class="w-full h-16 rounded-2xl border-2 border-dashed border-gray-300 dark:border-[#272B30] hover:border-primary/50 text-[#6F767E] hover:text-primary transition-all flex items-center justify-center gap-2">
                            <span class="material-symbols-outlined">add_circle</span>
                            <span class="font-bold">Add Field</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Right Panel: Settings --}}
        <aside class="w-[360px] bg-white dark:bg-[#1A1A1A] border-l border-gray-200 dark:border-[#272B30] overflow-y-auto no-scrollbar hidden lg:block">
            <div class="p-6 space-y-6">
                {{-- Settings Tabs --}}
                <div class="flex gap-1 p-1 bg-[#F4F5F6] dark:bg-[#0B0B0B] rounded-xl">
                    <button @click="settingsTab = 'general'" 
                        :class="settingsTab === 'general' ? 'bg-white dark:bg-[#272B30] shadow-sm' : 'text-[#6F767E]'"
                        class="flex-1 px-3 py-2 rounded-lg text-xs font-bold transition-all">General</button>
                    <button @click="settingsTab = 'notifications'" 
                        :class="settingsTab === 'notifications' ? 'bg-white dark:bg-[#272B30] shadow-sm' : 'text-[#6F767E]'"
                        class="flex-1 px-3 py-2 rounded-lg text-xs font-bold transition-all">Notify</button>
                    <button @click="settingsTab = 'confirmations'" 
                        :class="settingsTab === 'confirmations' ? 'bg-white dark:bg-[#272B30] shadow-sm' : 'text-[#6F767E]'"
                        class="flex-1 px-3 py-2 rounded-lg text-xs font-bold transition-all">Confirm</button>
                </div>
                
                {{-- General Settings Tab --}}
                <div x-show="settingsTab === 'general'" class="space-y-4">
                    <div class="space-y-2">
                        <label class="text-xs font-bold text-[#111827] dark:text-[#FCFCFC]">Status</label>
                        <select x-model="isActive"
                            class="w-full h-10 rounded-lg bg-[#F4F5F6] dark:bg-[#0B0B0B] border-none text-sm font-medium text-[#111827] dark:text-[#FCFCFC] focus:ring-2 focus:ring-primary">
                            <option value="1">Active</option>
                            <option value="0">Inactive</option>
                        </select>
                    </div>

                    <div class="space-y-2">
                        <label class="text-xs font-bold text-[#111827] dark:text-[#FCFCFC]">Description</label>
                        <textarea x-model="description" rows="3"
                            class="w-full rounded-lg bg-[#F4F5F6] dark:bg-[#0B0B0B] border-none text-sm font-medium text-[#111827] dark:text-[#FCFCFC] focus:ring-2 focus:ring-primary p-3 resize-none"
                            placeholder="Describe this form..."></textarea>
                    </div>
                    
                    <div class="space-y-2">
                        <label class="text-xs font-bold text-[#111827] dark:text-[#FCFCFC]">Submit Button Text</label>
                        <input x-model="submitButtonText" type="text"
                            class="w-full h-10 rounded-lg bg-[#F4F5F6] dark:bg-[#0B0B0B] border-none text-sm font-medium text-[#111827] dark:text-[#FCFCFC] focus:ring-2 focus:ring-primary px-3"
                            placeholder="Submit">
                    </div>
                    
                    {{-- Spam Protection --}}
                    <div class="pt-4 border-t border-gray-200 dark:border-[#272B30]">
                        <h5 class="text-[11px] font-bold text-[#6F767E] uppercase tracking-widest mb-3">Spam Protection</h5>
                        <label class="flex items-center gap-3 cursor-pointer p-3 rounded-xl bg-[#F4F5F6] dark:bg-[#0B0B0B] mb-3">
                            <input type="checkbox" x-model="spamProtection.honeypot" class="rounded border-gray-300 text-primary focus:ring-primary">
                            <div>
                                <span class="text-sm font-medium text-[#111827] dark:text-[#FCFCFC]">Honeypot</span>
                                <p class="text-xs text-[#6F767E]">Invisible trap field for bots</p>
                            </div>
                        </label>
                        
                        <div class="space-y-2">
                            <label class="text-xs font-bold text-[#111827] dark:text-[#FCFCFC]">CAPTCHA Provider</label>
                            <select x-model="spamProtection.captcha_provider"
                                class="w-full h-10 rounded-lg bg-[#F4F5F6] dark:bg-[#0B0B0B] border-none text-sm font-medium text-[#111827] dark:text-[#FCFCFC] focus:ring-2 focus:ring-primary px-3">
                                <option value="none">No CAPTCHA</option>
                                <option value="recaptcha_v2">Google reCAPTCHA v2</option>
                                <option value="recaptcha_v3">Google reCAPTCHA v3 (Invisible)</option>
                                <option value="turnstile">Cloudflare Turnstile</option>
                            </select>
                            <p class="text-xs text-[#6F767E]" x-show="spamProtection.captcha_provider !== 'none'">
                                Configure API keys in .env file
                            </p>
                        </div>
                    </div>
                </div>
                
                {{-- Notifications Tab --}}
                <div x-show="settingsTab === 'notifications'" class="space-y-4">
                    <label class="flex items-center gap-3 cursor-pointer p-3 rounded-xl bg-[#F4F5F6] dark:bg-[#0B0B0B]">
                        <input type="checkbox" x-model="notifications.enabled" class="rounded border-gray-300 text-primary focus:ring-primary">
                        <div>
                            <span class="text-sm font-medium text-[#111827] dark:text-[#FCFCFC]">Email Notifications</span>
                            <p class="text-xs text-[#6F767E]">Send email on form submission</p>
                        </div>
                    </label>
                    
                    <div x-show="notifications.enabled" class="space-y-4">
                        <div class="space-y-2">
                            <label class="text-xs font-bold text-[#111827] dark:text-[#FCFCFC]">Admin Email</label>
                            <input x-model="notifications.admin_email" type="email"
                                class="w-full h-10 rounded-lg bg-[#F4F5F6] dark:bg-[#0B0B0B] border-none text-sm font-medium text-[#111827] dark:text-[#FCFCFC] focus:ring-2 focus:ring-primary px-3"
                                placeholder="admin@example.com">
                        </div>
                        
                        <div class="space-y-2">
                            <label class="text-xs font-bold text-[#111827] dark:text-[#FCFCFC]">Email Subject</label>
                            <input x-model="notifications.subject" type="text"
                                class="w-full h-10 rounded-lg bg-[#F4F5F6] dark:bg-[#0B0B0B] border-none text-sm font-medium text-[#111827] dark:text-[#FCFCFC] focus:ring-2 focus:ring-primary px-3"
                                placeholder="New Form Submission">
                        </div>
                        
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" x-model="notifications.send_to_user" class="rounded border-gray-300 text-primary focus:ring-primary">
                            <span class="text-sm text-[#111827] dark:text-[#FCFCFC]">Send confirmation to user</span>
                        </label>
                    </div>
                </div>
                
                {{-- Confirmations Tab --}}
                <div x-show="settingsTab === 'confirmations'" class="space-y-4">
                    <div class="space-y-2">
                        <label class="text-xs font-bold text-[#111827] dark:text-[#FCFCFC]">After Submission</label>
                        <select x-model="confirmations.type"
                            class="w-full h-10 rounded-lg bg-[#F4F5F6] dark:bg-[#0B0B0B] border-none text-sm font-medium text-[#111827] dark:text-[#FCFCFC] focus:ring-2 focus:ring-primary">
                            <option value="message">Show Message</option>
                            <option value="redirect">Redirect to URL</option>
                            <option value="success_page">Redirect to Success Page</option>
                        </select>
                    </div>
                    
                    <div x-show="confirmations.type === 'message'" class="space-y-2">
                        <label class="text-xs font-bold text-[#111827] dark:text-[#FCFCFC]">Success Message</label>
                        <textarea x-model="confirmations.message" rows="3"
                            class="w-full rounded-lg bg-[#F4F5F6] dark:bg-[#0B0B0B] border-none text-sm font-medium text-[#111827] dark:text-[#FCFCFC] focus:ring-2 focus:ring-primary p-3 resize-none"
                            placeholder="Thank you for your submission!"></textarea>
                    </div>
                    
                    <div x-show="confirmations.type === 'redirect'" class="space-y-2">
                        <label class="text-xs font-bold text-[#111827] dark:text-[#FCFCFC]">Redirect URL</label>
                        <input x-model="confirmations.redirect_url" type="url"
                            class="w-full h-10 rounded-lg bg-[#F4F5F6] dark:bg-[#0B0B0B] border-none text-sm font-medium text-[#111827] dark:text-[#FCFCFC] focus:ring-2 focus:ring-primary px-3"
                            placeholder="https://example.com/thank-you">
                    </div>
                    
                    <div x-show="confirmations.type === 'success_page'" class="space-y-4">
                        <div class="space-y-2">
                            <label class="text-xs font-bold text-[#111827] dark:text-[#FCFCFC]">Title</label>
                            <input x-model="confirmations.success_title" type="text"
                                class="w-full h-10 rounded-lg bg-[#F4F5F6] dark:bg-[#0B0B0B] border-none text-sm font-medium text-[#111827] dark:text-[#FCFCFC] focus:ring-2 focus:ring-primary px-3"
                                placeholder="Thank You!">
                        </div>
                        <div class="space-y-2">
                            <label class="text-xs font-bold text-[#111827] dark:text-[#FCFCFC]">Description</label>
                            <textarea x-model="confirmations.success_description" rows="3"
                                class="w-full rounded-lg bg-[#F4F5F6] dark:bg-[#0B0B0B] border-none text-sm font-medium text-[#111827] dark:text-[#FCFCFC] focus:ring-2 focus:ring-primary p-3 resize-none"
                                placeholder="Thank you for your submission!"></textarea>
                        </div>
                    </div>
                </div>
            </div>
        </aside>
    </div>

    {{-- Field Selector Modal --}}
    <div x-show="showFieldSelector" style="display: none;"
        class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-gray-900/50 dark:bg-[#0B0B0B]/80 backdrop-blur-sm"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0">
        
        <div class="w-full max-w-[800px] bg-white dark:bg-[#1A1A1A] border border-gray-200 dark:border-[#272B30] rounded-[32px] shadow-2xl flex flex-col max-h-[90vh]"
            @click.outside="showFieldSelector = false"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100">
            
            <div class="flex items-center justify-between p-6 border-b border-gray-100 dark:border-[#272B30]">
                <div>
                    <h3 class="text-xl font-bold text-[#111827] dark:text-[#FCFCFC]">Add Field</h3>
                    <p class="text-sm text-[#6F767E]">Select a field type to add to your form</p>
                </div>
                <button @click="showFieldSelector = false"
                    class="h-10 w-10 flex items-center justify-center rounded-full hover:bg-gray-100 dark:hover:bg-[#272B30] text-[#6F767E] transition-colors">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>
            
            {{-- Search --}}
            <div class="px-6 pt-4">
                <div class="relative">
                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-[#6F767E]">search</span>
                    <input x-model="fieldSearch" type="text" placeholder="Search fields..." 
                        class="w-full pl-10 pr-4 py-2.5 bg-[#F4F5F6] dark:bg-[#0B0B0B] border-none rounded-xl text-sm focus:ring-2 focus:ring-primary">
                </div>
            </div>
            
            {{-- Category Tabs --}}
            <div class="px-6 pt-4 flex gap-2 overflow-x-auto no-scrollbar">
                <template x-for="cat in fieldCategories" :key="cat.id">
                    <button @click="activeCategory = cat.id"
                        :class="activeCategory === cat.id ? 'bg-primary text-white' : 'bg-[#F4F5F6] dark:bg-[#272B30] text-[#6F767E] hover:text-[#111827] dark:hover:text-[#FCFCFC]'"
                        class="px-4 py-2 rounded-xl text-xs font-bold whitespace-nowrap transition-all">
                        <span x-text="cat.label"></span>
                    </button>
                </template>
            </div>
            
            <div class="flex-1 overflow-y-auto p-6 no-scrollbar">
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3">
                    <template x-for="(fieldData, type) in getFilteredFields()" :key="type">
                        <button @click="addField(type)"
                            class="group flex flex-col items-center gap-2 p-4 rounded-2xl border border-gray-200 dark:border-[#272B30] bg-white dark:bg-[#1A1A1A] hover:bg-gray-50 dark:hover:bg-[#272B30] hover:border-primary transition-all">
                            <div class="h-10 w-10 rounded-xl bg-gray-50 dark:bg-[#272B30] text-[#6F767E] group-hover:text-primary group-hover:bg-primary/10 flex items-center justify-center group-hover:scale-110 transition-transform">
                                <span class="material-symbols-outlined text-xl" x-text="fieldData.icon"></span>
                            </div>
                            <span class="text-xs font-bold text-[#111827] dark:text-[#FCFCFC] text-center" x-text="fieldData.label"></span>
                        </button>
                    </template>
                </div>
                
                {{-- Empty State --}}
                <template x-if="Object.keys(getFilteredFields()).length === 0">
                    <div class="text-center py-8 text-[#6F767E]">
                        <span class="material-symbols-outlined text-4xl mb-2 block opacity-30">search_off</span>
                        <p class="text-sm">No fields match your search</p>
                    </div>
                </template>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function formBuilder() {
        return {
            name: '',
            slug: '',
            description: '',
            isActive: '1',
            fields: [],
            isSaving: false,
            showFieldSelector: false,
            
            fieldTypes: {
                // Basic Fields
                'text': { label: 'Text', icon: 'title', category: 'basic' },
                'email': { label: 'Email', icon: 'mail', category: 'basic' },
                'tel': { label: 'Phone', icon: 'call', category: 'basic' },
                'textarea': { label: 'Long Text', icon: 'notes', category: 'basic' },
                'number': { label: 'Number', icon: 'numbers', category: 'basic' },
                'date': { label: 'Date', icon: 'calendar_today', category: 'basic' },
                'select': { label: 'Dropdown', icon: 'arrow_drop_down_circle', category: 'basic' },
                'radio': { label: 'Radio Buttons', icon: 'radio_button_checked', category: 'basic' },
                'checkbox': { label: 'Checkboxes', icon: 'check_box', category: 'basic' },
                'file': { label: 'File Upload', icon: 'upload_file', category: 'basic' },
                
                // Advanced Fields
                'name': { label: 'Name (First/Last)', icon: 'person', category: 'advanced' },
                'address': { label: 'Address', icon: 'location_on', category: 'advanced' },
                'url': { label: 'Website URL', icon: 'link', category: 'advanced' },
                'password': { label: 'Password', icon: 'lock', category: 'advanced' },
                'hidden': { label: 'Hidden Field', icon: 'visibility_off', category: 'advanced' },
                'time': { label: 'Time Picker', icon: 'schedule', category: 'advanced' },
                'datetime': { label: 'Date & Time', icon: 'event', category: 'advanced' },
                'color': { label: 'Color Picker', icon: 'palette', category: 'advanced' },
                'range': { label: 'Range Slider', icon: 'tune', category: 'advanced' },
                'rating': { label: 'Star Rating', icon: 'star', category: 'advanced' },
                'signature': { label: 'Signature', icon: 'draw', category: 'advanced' },
                'image': { label: 'Image Upload', icon: 'image', category: 'advanced' },
                'mask': { label: 'Mask Input', icon: 'pin', category: 'advanced' },
                
                // Layout Fields
                'section': { label: 'Section Break', icon: 'horizontal_rule', category: 'layout' },
                'html': { label: 'Custom HTML', icon: 'code', category: 'layout' },
                'divider': { label: 'Divider', icon: 'remove', category: 'layout' },
                
                // Special Fields
                'gdpr': { label: 'GDPR Consent', icon: 'gpp_good', category: 'special' },
                'terms': { label: 'Terms & Conditions', icon: 'description', category: 'special' },
                'nps': { label: 'Net Promoter Score', icon: 'speed', category: 'special' },
                'repeater': { label: 'Repeater', icon: 'repeat', category: 'special' },
            },
            
            fieldCategories: [
                { id: 'all', label: 'All Fields' },
                { id: 'basic', label: 'Basic' },
                { id: 'advanced', label: 'Advanced' },
                { id: 'layout', label: 'Layout' },
                { id: 'special', label: 'Special' },
            ],
            
            activeCategory: 'all',
            fieldSearch: '',
            settingsTab: 'general',
            submitButtonText: 'Submit',
            
            // Notifications settings
            notifications: {
                enabled: false,
                admin_email: '',
                subject: 'New Form Submission',
                send_to_user: false,
            },
            
            // Confirmation settings
            confirmations: {
                type: 'message',
                message: 'Thank you for your submission!',
                redirect_url: '',
            },
            
            // Spam protection
            spamProtection: {
                honeypot: true,
                captcha_provider: 'none',
            },

            init() {
                // Initialize default state if needed
            },

            getFieldIcon(type) {
                return this.fieldTypes[type]?.icon || 'text_fields';
            },
            
            getFilteredFields() {
                let filtered = {};
                const search = this.fieldSearch.toLowerCase();
                
                for (const [type, data] of Object.entries(this.fieldTypes)) {
                    const matchesCategory = this.activeCategory === 'all' || data.category === this.activeCategory;
                    const matchesSearch = !search || data.label.toLowerCase().includes(search) || type.toLowerCase().includes(search);
                    
                    if (matchesCategory && matchesSearch) {
                        filtered[type] = data;
                    }
                }
                
                return filtered;
            },

            generateSlug() {
                this.slug = this.name.toLowerCase()
                    .replace(/[^a-z0-9]+/g, '-')
                    .replace(/^-+|-+$/g, '');
            },

            addField(type) {
                const fieldData = this.fieldTypes[type];
                // Generate a unique ID
                const label = fieldData?.label || 'field';
                // Simple slugify + random string
                const defaultId = label.toLowerCase()
                    .replace(/[^a-z0-9]+/g, '_')
                    .replace(/^_+|_+$/g, '')
                    + '_' + Math.random().toString(36).substring(2, 7);
                
                let advancedSettings = {};
                if (type === 'gdpr') {
                    advancedSettings.consent_text = 'I consent to having my data processed.';
                } else if (type === 'terms') {
                    advancedSettings.terms_text = 'I agree to the <a href="#" target="_blank">Terms & Conditions</a>.';
                }

                this.fields.push({
                    type: type,
                    label: fieldData?.label || '',
                    field_id: defaultId,
                    placeholder: '',
                    help_text: '',
                    is_required: false,
                    options_text: '',
                    column_width: 'full',
                    advanced_settings: advancedSettings,
                    conditional_logic: {
                        action: 'show',
                        match_type: 'all',
                        conditions: [],
                    },
                    showLogic: false,
                });
                this.showFieldSelector = false;
                this.fieldSearch = '';
                this.activeCategory = 'all';
            },
            
            addCondition(fieldIndex) {
                if (!this.fields[fieldIndex].conditional_logic) {
                    this.fields[fieldIndex].conditional_logic = {
                        action: 'show',
                        match_type: 'all',
                        conditions: [],
                    };
                }
                this.fields[fieldIndex].conditional_logic.conditions.push({
                    field_id: '',
                    operator: 'equals',
                    value: '',
                });
            },

            removeField(index) {
                this.fields.splice(index, 1);
            },

            async submitForm() {
                if (!this.name) {
                    window.dispatchEvent(new CustomEvent('notify', { 
                        detail: [{ type: 'error', message: 'Form name is required' }] 
                    }));
                    return;
                }

                if (this.fields.length === 0) {
                     window.dispatchEvent(new CustomEvent('notify', { 
                        detail: [{ type: 'error', message: 'Add at least one field' }] 
                    }));
                    return;
                }

                this.isSaving = true;

                try {
                    // Process fields
                    const processedFields = this.fields.map(field => {
                        const fieldData = { ...field };
                        
                        // Ensure field_id exists
                        if (!fieldData.field_id) {
                             const label = fieldData.label || 'field';
                             fieldData.field_id = label.toLowerCase()
                                .replace(/[^a-z0-9]+/g, '_')
                                .replace(/^_+|_+$/g, '')
                                + '_' + Math.random().toString(36).substring(2, 7);
                        }

                        if (['select', 'radio', 'checkbox'].includes(field.type) && field.options_text) {
                            fieldData.options = field.options_text.split('\n')
                                .map(line => {
                                    const parts = line.split('|');
                                    return { 
                                        label: parts[0].trim(), 
                                        value: (parts[1] || parts[0]).trim() 
                                    };
                                })
                                .filter(opt => opt.label);
                        }
                        return fieldData;
                    });

                    const payload = {
                        name: this.name,
                        slug: this.slug,
                        description: this.description,
                        is_active: this.isActive == '1' || this.isActive === true,
                        fields: processedFields,
                        submit_button_text: this.submitButtonText,
                        notifications: this.notifications,
                        confirmations: this.confirmations,
                        spam_protection: this.spamProtection,
                    };

                    const response = await fetch("{{ route('admin.forms.store') }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify(payload)
                    });

                    const result = await response.json();

                    if (result.success) {
                        window.dispatchEvent(new CustomEvent('notify', { 
                            detail: [{ type: 'success', message: 'Form created successfully!' }] 
                        }));
                        setTimeout(() => {
                            window.location.href = "{{ route('admin.forms.index') }}";
                        }, 1000);
                    } else {
                        throw new Error(result.message || 'Failed to create form');
                    }
                } catch (error) {
                    console.error(error);
                     window.dispatchEvent(new CustomEvent('notify', { 
                        detail: [{ type: 'error', message: error.message || 'An error occurred' }] 
                    }));
                } finally {
                    this.isSaving = false;
                }
            }
        };
    }
</script>
@endpush
@endsection

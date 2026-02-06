{{-- Posts Block Entry --}}
@php
    $selectedPosts = json_decode($block['value'] ?? '[]', true) ?? [];
    $postType = $block['options']['post_type'] ?? '';
@endphp
<div class="space-y-3">
    <label class="text-[10px] font-bold text-[#6F767E] uppercase tracking-wider">Selected Posts ({{ count($selectedPosts) }})</label>

    @if($postType)
        @php
            $cpt = \App\Models\CustomPostType::where('slug', $postType)->first();
            $availablePosts = $cpt ? \App\Models\CustomPostTypeEntry::where('custom_post_type_id', $cpt->id)
                ->where('status', 'published')
                ->orderBy('title')
                ->get() : collect();
        @endphp

        <select wire:model="blocks.{{ $index }}.value" multiple
            class="w-full h-32 rounded-lg bg-[#F4F5F6] dark:bg-[#0B0B0B] border-none text-sm text-[#111827] dark:text-[#FCFCFC] focus:ring-2 focus:ring-primary px-4 py-2">
            @foreach($availablePosts as $post)
                <option value="{{ $post->id }}">{{ $post->title }}</option>
            @endforeach
        </select>
        <p class="text-[10px] text-[#6F767E]">Hold Ctrl/Cmd to select multiple posts</p>
    @else
        <div class="p-4 rounded-lg bg-amber-50 dark:bg-amber-500/10 text-amber-600 dark:text-amber-400 text-sm">
            <span class="material-symbols-outlined text-lg align-middle mr-1">warning</span>
            No post type configured. Edit settings to select a post type.
        </div>
    @endif
</div>

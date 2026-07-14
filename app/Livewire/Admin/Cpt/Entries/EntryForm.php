<?php

namespace App\Livewire\Admin\Cpt\Entries;

use App\Models\CptEntry;
use App\Models\CustomPostType;
use App\Models\CustomTaxonomy;
use App\Models\TaxonomyTerm;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithFileUploads;

class EntryForm extends Component
{
    use WithFileUploads;

    public CustomPostType $postType;

    public ?int $entryId = null;

    public bool $isEdit = false;

    // Core Fields
    public string $title = '';

    public string $slug = '';

    public string $content = '';

    public string $excerpt = '';

    public ?string $featuredImage = null;

    public string $status = 'draft';

    public ?string $publishedAt = null;

    public ?int $parentId = null;

    public int $menuOrder = 0;

    // Meta Fields
    public array $meta = [];

    // Taxonomy Terms
    public array $selectedTerms = [];

    public array $newTermInput = [];

    // UI State
    public bool $showMediaPicker = false;

    // === Translations state ===
    public string $editingLocale = '';

    /** Per-locale snapshots of translatable form fields: {locale: {title, slug, content, excerpt}} */
    public array $localizedSnapshots = [];

    public array $availableLocales = [];

    protected function rules(): array
    {
        $isDefaultLocale = $this->editingLocale === CptEntry::defaultLocale();

        $rules = [
            'title' => $isDefaultLocale ? 'required|string|max:255' : 'nullable|string|max:255',
            'slug' => $isDefaultLocale ? 'required|string|max:255' : 'nullable|string|max:255',
            'content' => 'nullable|string',
            'excerpt' => 'nullable|string|max:500',
            'status' => 'required|in:draft,published,scheduled,archived',
            'publishedAt' => 'nullable|date',
            'parentId' => 'nullable|integer|exists:cpt_entries,id',
            'menuOrder' => 'integer|min:0',
        ];

        // Add validation for meta fields
        foreach ($this->postType->metaFields as $field) {
            $fieldRules = [];
            if ($field->is_required) {
                $fieldRules[] = 'required';
            } else {
                $fieldRules[] = 'nullable';
            }

            // Type-specific validation
            switch ($field->type) {
                case 'email':
                    $fieldRules[] = 'email';
                    break;
                case 'url':
                    $fieldRules[] = 'url';
                    break;
                case 'number':
                    $fieldRules[] = 'numeric';
                    break;
                case 'repeater':
                    if (isset($field->options['repeater_fields'])) {
                        foreach ($field->options['repeater_fields'] as $subField) {
                            $subFieldId = $subField['id'] ?? Str::snake($subField['label'] ?? '');
                            if (! empty($subFieldId) && ! empty($subField['is_required'])) {
                                $rules['meta.'.$field->name.'.*.'.$subFieldId] = 'required';
                            }
                        }
                    }
                    $fieldRules[] = 'array';
                    break;
            }

            $rules['meta.'.$field->name] = $fieldRules;
        }

        return $rules;
    }

    public function mount(CustomPostType $postType, ?int $id = null)
    {
        $this->postType = $postType;
        $this->availableLocales = available_locales();
        $this->editingLocale = CptEntry::defaultLocale();

        // Initialize meta fields with defaults
        foreach ($postType->metaFields as $field) {
            $defaultValue = $field->default_value;

            // Handle options-based defaults (Select, Radio, Checkbox)
            if (in_array($field->type, ['select', 'radio', 'checkbox']) && isset($field->options['options_list'])) {
                $optionsList = $field->options['options_list'];

                if ($field->type === 'checkbox') {
                    // For checkbox, default is an array of selected values
                    $defaultValues = [];
                    foreach ($optionsList as $option) {
                        if (! empty($option['is_default'])) {
                            $defaultValues[] = $option['value'];
                        }
                    }
                    $defaultValue = $defaultValues;
                } else {
                    // For select/radio, find the first default
                    foreach ($optionsList as $option) {
                        if (! empty($option['is_default'])) {
                            $defaultValue = $option['value'];
                            break;
                        }
                    }
                }
            }

            $this->meta[$field->name] = $defaultValue ?? '';

            // Ensure array for checkbox/gallery/repeater if empty logic
            if (($field->type === 'checkbox' || $field->type === 'gallery' || $field->type === 'repeater') && ! is_array($this->meta[$field->name])) {
                $this->meta[$field->name] = [];
            }
        }

        if ($id) {
            $this->entryId = $id;
            $this->isEdit = true;
            $this->loadEntry();
        }
    }

    public function addRepeaterRow($fieldName)
    {
        // Find the field definition
        $field = $this->postType->metaFields->where('name', $fieldName)->first();

        if ($field && $field->type === 'repeater' && isset($field->options['repeater_fields'])) {
            $newRow = [];
            foreach ($field->options['repeater_fields'] as $subField) {
                // Initialize based on sub-field type
                $rowKey = $subField['id'] ?? Str::snake($subField['label'] ?? 'field_'.$loop->index);

                // Determine default value
                $defaultValue = '';
                if (isset($subField['options']['options_list']) && is_array($subField['options']['options_list'])) {
                    foreach ($subField['options']['options_list'] as $option) {
                        if (isset($option['is_default']) && $option['is_default']) {
                            $defaultValue = $option['value'];
                            break;
                        }
                    }
                }

                $newRow[$rowKey] = $defaultValue;
            }

            // Ensure the meta field is an array
            if (! isset($this->meta[$fieldName]) || ! is_array($this->meta[$fieldName])) {
                $this->meta[$fieldName] = [];
            }

            $this->meta[$fieldName][] = $newRow;
        }
    }

    public function removeRepeaterRow($fieldName, $index)
    {
        if (isset($this->meta[$fieldName][$index])) {
            unset($this->meta[$fieldName][$index]);
            $this->meta[$fieldName] = array_values($this->meta[$fieldName]);
        }
    }

    #[On('media-selected')]
    public function onMediaSelected($field, $mediaId, $mediaPath, $mediaUrl)
    {
        if ($field === 'featured_image') {
            $this->featuredImage = $mediaPath;
        }
        // Handle Meta Fields
        elseif (str_starts_with($field, 'meta.')) {
            $fieldName = str_replace('meta.', '', $field);
            $this->meta[$fieldName] = $mediaPath;
        }
        // Handle Gallery Addition
        elseif (str_starts_with($field, 'gallery_add.')) {
            $fieldName = str_replace('gallery_add.', '', $field);
            if (! isset($this->meta[$fieldName])) {
                $this->meta[$fieldName] = [];
            }
            $this->meta[$fieldName][] = $mediaPath;
        }
    }

    #[On('media-removed')]
    public function onMediaRemoved($field)
    {
        if ($field === 'featured_image') {
            $this->featuredImage = null;
        }
        // Handle Meta Fields
        elseif (str_starts_with($field, 'meta.')) {
            $fieldName = str_replace('meta.', '', $field);
            $this->meta[$fieldName] = null;
        }
    }

    public function removeGalleryImage($fieldName, $index)
    {
        if (isset($this->meta[$fieldName][$index])) {
            unset($this->meta[$fieldName][$index]);
            $this->meta[$fieldName] = array_values($this->meta[$fieldName]);
        }
    }

    protected function loadEntry()
    {
        $entry = CptEntry::with('terms')->findOrFail($this->entryId);

        $this->title = $entry->title;
        $this->slug = $entry->slug;
        $this->content = $entry->content ?? '';
        $this->excerpt = $entry->excerpt ?? '';
        $this->featuredImage = $entry->featured_image;
        $this->status = $entry->status;
        $this->publishedAt = $entry->published_at?->format('Y-m-d\TH:i');
        $this->parentId = $entry->parent_id;
        $this->menuOrder = $entry->menu_order;

        // Load meta values
        if ($entry->meta) {
            foreach ($entry->meta as $key => $value) {
                $this->meta[$key] = $value;
            }
        }

        // Load selected terms by taxonomy
        foreach ($entry->terms as $term) {
            $this->selectedTerms[$term->taxonomy_id][] = $term->id;
        }

        // Hydrate per-locale snapshots from the translations JSON column.
        $translations = $entry->translations ?? [];
        foreach ($translations as $locale => $fields) {
            if ($locale === CptEntry::defaultLocale()) {
                continue;
            }
            $this->localizedSnapshots[$locale] = [
                'title' => $fields['title'] ?? '',
                'slug' => $fields['slug'] ?? '',
                'content' => $fields['content'] ?? '',
                'excerpt' => $fields['excerpt'] ?? '',
            ];
        }
    }

    /** Switch the form between locale tabs (mirrors PageForm pattern). */
    public function switchLocale(string $newLocale): void
    {
        if ($newLocale === $this->editingLocale) {
            return;
        }
        if (! in_array($newLocale, $this->availableLocales, true)) {
            return;
        }

        // Snapshot current form into the OLD locale's slot
        $this->localizedSnapshots[$this->editingLocale] = $this->currentLocaleFormSnapshot();

        // Load NEW locale's snapshot (blank if none yet)
        $next = $this->localizedSnapshots[$newLocale] ?? [];
        $this->title = $next['title'] ?? '';
        $this->slug = $next['slug'] ?? '';
        $this->content = $next['content'] ?? '';
        $this->excerpt = $next['excerpt'] ?? '';

        $this->editingLocale = $newLocale;
        $this->resetErrorBag();
    }

    protected function currentLocaleFormSnapshot(): array
    {
        return [
            'title' => $this->title,
            'slug' => $this->slug,
            'content' => $this->content,
            'excerpt' => $this->excerpt,
        ];
    }

    public function updatedTitle($value)
    {
        if (! $this->isEdit && empty($this->slug)) {
            $this->slug = $this->ensureUniqueSlug(Str::slug($value));
        }
    }

    public function generateSlug()
    {
        $this->slug = $this->ensureUniqueSlug(Str::slug($this->title));
    }

    protected function ensureUniqueSlug($slug)
    {
        $originalSlug = $slug;
        $counter = 1;

        while (true) {
            $slugQuery = CptEntry::withTrashed()
                ->where('post_type_id', $this->postType->id)
                ->where('slug', $slug);

            if ($this->isEdit) {
                $slugQuery->where('id', '!=', $this->entryId);
            }

            if (! $slugQuery->exists()) {
                break;
            }

            $counter++;
            $slug = $originalSlug.'-'.$counter;
        }

        return $slug;
    }

    public function toggleTerm(int $taxonomyId, int $termId)
    {
        if (! isset($this->selectedTerms[$taxonomyId])) {
            $this->selectedTerms[$taxonomyId] = [];
        }

        if (in_array($termId, $this->selectedTerms[$taxonomyId])) {
            $this->selectedTerms[$taxonomyId] = array_values(
                array_filter($this->selectedTerms[$taxonomyId], fn ($id) => $id !== $termId)
            );
        } else {
            $this->selectedTerms[$taxonomyId][] = $termId;
        }
    }

    public function setFeaturedImage(?string $path)
    {
        $this->featuredImage = $path;
        $this->showMediaPicker = false;
    }

    public function removeFeaturedImage()
    {
        $this->featuredImage = null;
    }

    public function save()
    {
        // Mirror current form into the active locale's snapshot before validating
        $this->localizedSnapshots[$this->editingLocale] = $this->currentLocaleFormSnapshot();

        try {
            $this->validate();
        } catch (ValidationException $e) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'There are validation errors. Please check the form.',
            ]);
            throw $e;
        }

        $defaultLocale = CptEntry::defaultLocale();
        $defaultSnap = $this->localizedSnapshots[$defaultLocale] ?? $this->currentLocaleFormSnapshot();

        // Default-locale slug uniqueness — only enforce when we have a real slug to dedupe
        if (! empty($defaultSnap['slug'])) {
            $defaultSnap['slug'] = $this->ensureUniqueSlug($defaultSnap['slug']);
        }

        // Build translations JSON from non-default locale snapshots
        $translations = [];
        foreach ($this->localizedSnapshots as $locale => $snap) {
            if ($locale === $defaultLocale) {
                continue;
            }
            $localeFields = array_filter([
                'title' => ($snap['title'] ?? '') ?: null,
                'slug' => ($snap['slug'] ?? '') ?: null,
                'content' => ($snap['content'] ?? '') ?: null,
                'excerpt' => ($snap['excerpt'] ?? '') ?: null,
            ], fn ($v) => $v !== null);
            if (! empty($localeFields)) {
                $translations[$locale] = $localeFields;
            }
        }

        $data = [
            'post_type_id' => $this->postType->id,
            'title' => $defaultSnap['title'] ?? '',
            'slug' => $defaultSnap['slug'] ?? '',
            'content' => ($defaultSnap['content'] ?? '') ?: null,
            'excerpt' => ($defaultSnap['excerpt'] ?? '') ?: null,
            'featured_image' => $this->featuredImage,
            'status' => $this->status,
            'published_at' => $this->status === 'published' && ! $this->publishedAt
                ? now()
                : ($this->publishedAt ? Carbon::parse($this->publishedAt) : null),
            'parent_id' => $this->parentId,
            'menu_order' => $this->menuOrder,
            'meta' => $this->meta,
            'translations' => $translations ?: null,
        ];

        if ($this->isEdit) {
            $entry = CptEntry::findOrFail($this->entryId);
            $entry->update($data);
        } else {
            $entry = CptEntry::create($data);
            $this->entryId = $entry->id;
        }

        // Sync taxonomy terms
        $allTerms = [];
        foreach ($this->selectedTerms as $termIds) {
            $allTerms = array_merge($allTerms, $termIds);
        }
        $entry->terms()->sync($allTerms);

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => $this->isEdit
                ? "'{$this->title}' updated successfully."
                : "'{$this->title}' created successfully.",
        ]);

        if ($this->isEdit) {
            // If already editing, just notify and stay (or reload to be safe, but staying is faster)
            // User requested to "move to ... edit", which implies they want to be on the edit page.
            // If we are already there, we can just return null or redirect to self.
            // Redirecting to self ensures the URL is correct if they came from somewhere else and keeps logic consistent.
            return redirect()->route('admin.cpt.entries.edit', ['postTypeSlug' => $this->postType->slug, 'id' => $entry->id]);
        }

        // If creating, we MUST redirect to the edit page
        return redirect()->route('admin.cpt.entries.edit', ['postTypeSlug' => $this->postType->slug, 'id' => $entry->id]);
    }

    public function saveAsDraft()
    {
        $originalStatus = $this->status;
        $this->status = 'draft';

        try {
            $this->save();
        } catch (\Exception $e) {
            $this->status = $originalStatus;
            throw $e;
        }
    }

    public function publish()
    {
        $originalStatus = $this->status;
        $originalPublishedAt = $this->publishedAt;

        $this->status = 'published';
        $this->publishedAt = now()->format('Y-m-d\TH:i');

        try {
            $this->save();
        } catch (\Exception $e) {
            $this->status = $originalStatus;
            $this->publishedAt = $originalPublishedAt;
            throw $e;
        }
    }

    public function createTerm(int $taxonomyId)
    {
        $name = trim($this->newTermInput[$taxonomyId] ?? '');

        if (empty($name)) {
            return;
        }

        // Check for duplicate name in this taxonomy to prevent errors
        $exists = TaxonomyTerm::where('taxonomy_id', $taxonomyId)
            ->where('name', $name)
            ->exists();

        if ($exists) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => "Term '{$name}' already exists.",
            ]);

            return;
        }

        // Create term with auto-slug
        // Assuming slug should be unique per taxonomy
        $slug = Str::slug($name);
        // Handle slug collision simplistically if needed, but for now simple slug

        $term = TaxonomyTerm::create([
            'taxonomy_id' => $taxonomyId,
            'name' => $name,
            'slug' => $slug,
            // 'order' => 0, // default
        ]);

        // Auto-select the new term
        if (! isset($this->selectedTerms[$taxonomyId])) {
            $this->selectedTerms[$taxonomyId] = [];
        }
        // Assuming selectedTerms is array of IDs
        $this->selectedTerms[$taxonomyId][] = $term->id;

        // Clear input
        $this->newTermInput[$taxonomyId] = '';

        // Notify
        $this->dispatch('notify', [
            'type' => 'success',
            'message' => "Term '{$term->name}' created.",
        ]);
    }

    public function render()
    {
        $taxonomies = CustomTaxonomy::active()
            ->forPostType($this->postType->slug)
            ->with(['metaFields'])
            ->get();

        // Get terms for each taxonomy
        $taxonomyTerms = [];
        foreach ($taxonomies as $taxonomy) {
            $allTerms = TaxonomyTerm::ofTaxonomy($taxonomy->id)
                ->orderBy('order')
                ->get();

            if ($taxonomy->is_hierarchical) {
                $taxonomyTerms[$taxonomy->id] = $this->flattenTerms($allTerms);
            } else {
                $taxonomyTerms[$taxonomy->id] = $allTerms;
                foreach ($taxonomyTerms[$taxonomy->id] as $term) {
                    $term->depth = 0;
                }
            }
        }

        // Get possible parents for hierarchical post types
        $possibleParents = [];
        if ($this->postType->is_hierarchical) {
            $query = CptEntry::where('post_type_id', $this->postType->id)
                ->where('status', '!=', 'archived')
                ->orderBy('title');

            if ($this->isEdit) {
                $query->where('id', '!=', $this->entryId);
            }

            $possibleParents = $query->get();
        }

        // Get metaboxes and group fields
        $metaBoxes = $this->postType->settings['meta_boxes'] ?? [];
        $groupedFields = [];

        foreach ($this->postType->metaFields as $field) {
            $group = $field->field_group ?: 'default';
            $groupedFields[$group][] = $field;
        }

        return view('livewire.admin.cpt.entries.entry-form', [
            'taxonomies' => $taxonomies,
            'taxonomyTerms' => $taxonomyTerms,
            'possibleParents' => $possibleParents,
            'metaBoxes' => $metaBoxes,
            'groupedFields' => $groupedFields,
        ]);
    }

    private function flattenTerms($allTerms, $parentId = null, $depth = 0)
    {
        $result = collect();
        $items = $allTerms->where('parent_id', $parentId);

        foreach ($items as $item) {
            $item->depth = $depth;
            $result->push($item);
            $result = $result->merge($this->flattenTerms($allTerms, $item->id, $depth + 1));
        }

        return $result;
    }
}

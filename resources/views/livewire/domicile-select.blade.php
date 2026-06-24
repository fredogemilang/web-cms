<div>
    <style>
        .domicile-option:hover, .hover-bg-light:hover {
            background-color: #f8f9fa !important;
            color: #f39233 !important;
        }
    </style>

    <div class="position-relative" x-data="{ open: false }" @click.away="open = false">
        <input 
            type="text" 
            wire:model.live.debounce.150ms="domicileSearch"
            placeholder="{{ $placeholder }}"
            class="form-control form-control-flushed @if($hasError) is-invalid @endif"
            @focus="open = true"
            @keydown.escape="open = false"
            autocomplete="off"
        />
        
        {{-- Hidden field to submit selected domicile name --}}
        <input type="hidden" name="{{ $fieldName }}" value="{{ $domicile }}" />

        <div x-show="open" 
             class="position-absolute bg-white border border-light-subtle rounded-3 shadow-lg w-100 z-3 overflow-y-auto"
             style="max-height: 250px; top: 100%; left: 0; display: none;"
             x-transition>
            @if(strlen($domicileSearch) < 2)
                <div class="px-3 py-2 text-muted" style="font-size: 0.85rem;">
                    Type 2 or more characters to search...
                </div>
            @elseif(empty($domicileOptions))
                <div class="px-3 py-2 text-muted" style="font-size: 0.85rem;">
                    No results found.
                </div>
            @else
                @foreach($domicileOptions as $option)
                    <button type="button" 
                            wire:click="selectDomicile('{{ addslashes($option['value']) }}', '{{ addslashes($option['label']) }}')"
                            @click="open = false"
                            class="d-block w-100 text-start border-0 bg-transparent px-3 py-2 text-dark domicile-option"
                            style="font-size: 0.9rem; transition: background-color 0.1s;">
                        {{ $option['label'] }}
                    </button>
                @endforeach
            @endif
            <button type="button" 
                    wire:click="selectDomicile('Other', 'Other')"
                    @click="open = false"
                    class="d-block w-100 text-start border-0 bg-transparent px-3 py-2 text-[#f39233] fw-bold hover-bg-light border-top border-light-subtle"
                    style="font-size: 0.9rem;">
                Other (Lainnya)
            </button>
        </div>
    </div>

    @if($hasError && $errorMessage)
        <div class="text-danger" style="font-size: 0.85rem; margin-top: 0.25rem;">{{ $errorMessage }}</div>
    @endif

    {{-- Domicile "Other" input --}}
    @if($domicile === 'Other')
        <div class="mt-2">
            <input 
                type="text"
                name="{{ $fieldName }}_other"
                value="{{ $domicile_other }}"
                wire:model.blur="domicile_other"
                class="form-control form-control-flushed"
                placeholder="Specify Domicile"
                required
            />
        </div>
    @endif
</div>

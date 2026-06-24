<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Domicile;

class DomicileSelect extends Component
{
    public string $fieldName = 'domicile';
    public ?string $domicile = '';
    public ?string $domicile_other = '';
    public ?string $domicileSearch = '';
    public string $placeholder = 'Type to search Domicile...';
    public bool $hasError = false;
    public ?string $errorMessage = null;

    public function mount($fieldName = 'domicile', $oldValue = null, $oldOtherValue = null, $hasError = false, $errorMessage = null, $placeholder = 'Type to search Domicile...')
    {
        $this->fieldName = $fieldName;
        $this->hasError = $hasError;
        $this->errorMessage = $errorMessage;
        $this->placeholder = $placeholder;

        if ($oldValue) {
            $this->domicile = $oldValue;
            if ($oldValue === 'Other') {
                $this->domicile_other = $oldOtherValue ?? '';
                $this->domicileSearch = 'Other';
            } else {
                $this->domicileSearch = $oldValue;
            }
        }
    }

    public function selectDomicile(string $value, string $label): void
    {
        $this->domicile = $value;
        $this->domicileSearch = $label;
    }

    public function render()
    {
        $domicileOptions = [];
        if (strlen($this->domicileSearch) >= 2 && $this->domicileSearch !== 'Other') {
            $domicileOptions = Domicile::query()
                ->where('name', 'like', '%' . $this->domicileSearch . '%')
                ->limit(15)
                ->get()
                ->map(function ($item) {
                    if ($item->type === 'regency') {
                        $parent = Domicile::where('code', $item->parent_code)->first();
                        return [
                            'value' => $item->name,
                            'label' => $item->name . ($parent ? ', ' . $parent->name : ''),
                        ];
                    }
                    return [
                        'value' => $item->name,
                        'label' => $item->name . ' (Provinsi)',
                    ];
                })
                ->toArray();
        }

        return view('livewire.domicile-select', [
            'domicileOptions' => $domicileOptions,
        ]);
    }
}

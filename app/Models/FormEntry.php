<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FormEntry extends Model
{
    use HasFactory;

    protected $fillable = [
        'form_id',
        'data',
        'ip_address',
        'user_agent',
        'user_id',
    ];

    protected $casts = [
        'data' => 'array',
    ];

    /**
     * Get the form that owns the entry.
     */
    public function form()
    {
        return $this->belongsTo(Form::class);
    }

    /**
     * Get the user that submitted the entry.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get a specific field value from the entry data.
     */
    public function getFieldValue($fieldId)
    {
        return $this->data[$fieldId] ?? null;
    }

    /**
     * Get all field values with labels.
     */
    public function getFieldsWithLabels()
    {
        $fields = $this->form->fields;
        $result = [];

        foreach ($fields as $field) {
            $result[] = [
                'label' => $field->label,
                'value' => $this->getFieldValue($field->field_id),
                'type' => $field->type,
            ];
        }

        return $result;
    }

    /**
     * Export entry to array format.
     */
    public function toExportArray()
    {
        $export = [
            'ID' => $this->id,
            'Submitted At' => $this->created_at->format('Y-m-d H:i:s'),
        ];

        foreach ($this->form->fields as $field) {
            $value = $this->getFieldValue($field->field_id);
            
            // Format array values (for checkboxes)
            if (is_array($value)) {
                $value = implode(', ', $value);
            }
            
            $export[$field->label] = $value;
        }

        if ($this->user) {
            $export['Submitted By'] = $this->user->name;
        }

        $export['IP Address'] = $this->ip_address;

        return $export;
    }
}

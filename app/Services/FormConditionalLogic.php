<?php

namespace App\Services;

use App\Models\Form;
use App\Models\FormField;

class FormConditionalLogic
{
    /**
     * Supported operators for conditional rules.
     */
    public const OPERATORS = [
        'equals' => 'Equals',
        'not_equals' => 'Not Equals',
        'contains' => 'Contains',
        'not_contains' => 'Does Not Contain',
        'starts_with' => 'Starts With',
        'ends_with' => 'Ends With',
        'greater_than' => 'Greater Than',
        'less_than' => 'Less Than',
        'is_empty' => 'Is Empty',
        'is_not_empty' => 'Is Not Empty',
        'is_checked' => 'Is Checked',
        'is_not_checked' => 'Is Not Checked',
    ];
    
    /**
     * Actions that can be performed based on conditions.
     */
    public const ACTIONS = [
        'show' => 'Show this field',
        'hide' => 'Hide this field',
    ];

    /**
     * Evaluate if a field should be visible based on its conditional logic.
     */
    public function evaluateVisibility(FormField $field, array $formData): bool
    {
        $logic = $field->conditional_logic;
        
        // No conditional logic means always visible
        if (empty($logic) || empty($logic['conditions'])) {
            return true;
        }
        
        $action = $logic['action'] ?? 'show';
        $matchType = $logic['match_type'] ?? 'all';
        $conditions = $logic['conditions'] ?? [];
        
        if (empty($conditions)) {
            return true;
        }
        
        $results = [];
        foreach ($conditions as $condition) {
            $results[] = $this->evaluateCondition($condition, $formData);
        }
        
        // Determine if conditions are met based on match type
        $conditionsMet = $matchType === 'all' 
            ? !in_array(false, $results, true)
            : in_array(true, $results, true);
        
        // Apply action logic
        return $action === 'show' ? $conditionsMet : !$conditionsMet;
    }
    
    /**
     * Evaluate a single condition.
     */
    protected function evaluateCondition(array $condition, array $formData): bool
    {
        $fieldId = $condition['field_id'] ?? '';
        $operator = $condition['operator'] ?? 'equals';
        $targetValue = $condition['value'] ?? '';
        
        $actualValue = $formData[$fieldId] ?? '';
        
        // Handle array values (checkboxes, multi-select)
        if (is_array($actualValue)) {
            $actualValue = implode(',', $actualValue);
        }
        
        return match($operator) {
            'equals' => strtolower($actualValue) === strtolower($targetValue),
            'not_equals' => strtolower($actualValue) !== strtolower($targetValue),
            'contains' => str_contains(strtolower($actualValue), strtolower($targetValue)),
            'not_contains' => !str_contains(strtolower($actualValue), strtolower($targetValue)),
            'starts_with' => str_starts_with(strtolower($actualValue), strtolower($targetValue)),
            'ends_with' => str_ends_with(strtolower($actualValue), strtolower($targetValue)),
            'greater_than' => is_numeric($actualValue) && is_numeric($targetValue) && $actualValue > $targetValue,
            'less_than' => is_numeric($actualValue) && is_numeric($targetValue) && $actualValue < $targetValue,
            'is_empty' => empty($actualValue),
            'is_not_empty' => !empty($actualValue),
            'is_checked' => !empty($actualValue) && $actualValue !== '0' && $actualValue !== 'false',
            'is_not_checked' => empty($actualValue) || $actualValue === '0' || $actualValue === 'false',
            default => false,
        };
    }
    
    /**
     * Get all field dependencies for a form (which fields depend on which).
     */
    public function getFieldDependencies(Form $form): array
    {
        $dependencies = [];
        
        foreach ($form->fields as $field) {
            $logic = $field->conditional_logic;
            if (empty($logic['conditions'])) {
                continue;
            }
            
            foreach ($logic['conditions'] as $condition) {
                $sourceFieldId = $condition['field_id'] ?? '';
                if ($sourceFieldId) {
                    $dependencies[$sourceFieldId][] = $field->field_id;
                }
            }
        }
        
        return $dependencies;
    }
    
    /**
     * Generate JavaScript for frontend conditional logic.
     */
    public function renderJavaScript(Form $form): string
    {
        $fieldsWithLogic = $form->fields->filter(function ($field) {
            return !empty($field->conditional_logic['conditions']);
        });
        
        if ($fieldsWithLogic->isEmpty()) {
            return '';
        }
        
        $config = [];
        foreach ($fieldsWithLogic as $field) {
            $config[$field->field_id] = [
                'action' => $field->conditional_logic['action'] ?? 'show',
                'match_type' => $field->conditional_logic['match_type'] ?? 'all',
                'conditions' => $field->conditional_logic['conditions'] ?? [],
            ];
        }
        
        $dependencies = $this->getFieldDependencies($form);
        
        $js = '<script>
(function() {
    const conditionalConfig = ' . json_encode($config) . ';
    const dependencies = ' . json_encode($dependencies) . ';
    
    function getFieldValue(fieldId) {
        const field = document.querySelector(`[name="${fieldId}"], [name="${fieldId}[]"]`);
        if (!field) return "";
        
        if (field.type === "checkbox") {
            const checked = document.querySelectorAll(`[name="${fieldId}[]"]:checked, [name="${fieldId}"]:checked`);
            return Array.from(checked).map(c => c.value).join(",");
        }
        if (field.type === "radio") {
            const checked = document.querySelector(`[name="${fieldId}"]:checked`);
            return checked ? checked.value : "";
        }
        return field.value || "";
    }
    
    function evaluateCondition(condition) {
        const value = getFieldValue(condition.field_id).toLowerCase();
        const target = (condition.value || "").toLowerCase();
        
        switch(condition.operator) {
            case "equals": return value === target;
            case "not_equals": return value !== target;
            case "contains": return value.includes(target);
            case "not_contains": return !value.includes(target);
            case "starts_with": return value.startsWith(target);
            case "ends_with": return value.endsWith(target);
            case "greater_than": return parseFloat(value) > parseFloat(target);
            case "less_than": return parseFloat(value) < parseFloat(target);
            case "is_empty": return value === "";
            case "is_not_empty": return value !== "";
            case "is_checked": return value !== "" && value !== "0" && value !== "false";
            case "is_not_checked": return value === "" || value === "0" || value === "false";
            default: return false;
        }
    }
    
    function evaluateField(fieldId) {
        const config = conditionalConfig[fieldId];
        if (!config) return;
        
        const results = config.conditions.map(c => evaluateCondition(c));
        const conditionsMet = config.match_type === "all" 
            ? results.every(r => r) 
            : results.some(r => r);
        
        const shouldShow = config.action === "show" ? conditionsMet : !conditionsMet;
        const fieldGroup = document.querySelector(`[data-field-id="${fieldId}"]`) 
            || document.getElementById(fieldId)?.closest(".form-group");
        
        if (fieldGroup) {
            fieldGroup.style.display = shouldShow ? "" : "none";
            const inputs = fieldGroup.querySelectorAll("input, select, textarea");
            inputs.forEach(input => {
                if (!shouldShow) {
                    input.removeAttribute("required");
                    input.dataset.wasRequired = input.hasAttribute("required") ? "1" : "";
                } else if (input.dataset.wasRequired === "1") {
                    input.setAttribute("required", "");
                }
            });
        }
    }
    
    function evaluateAllFields() {
        Object.keys(conditionalConfig).forEach(evaluateField);
    }
    
    function attachListeners() {
        Object.keys(dependencies).forEach(sourceFieldId => {
            const fields = document.querySelectorAll(`[name="${sourceFieldId}"], [name="${sourceFieldId}[]"]`);
            fields.forEach(field => {
                field.addEventListener("change", () => {
                    dependencies[sourceFieldId].forEach(evaluateField);
                });
                if (field.tagName === "INPUT" && (field.type === "text" || field.type === "number")) {
                    field.addEventListener("input", () => {
                        dependencies[sourceFieldId].forEach(evaluateField);
                    });
                }
            });
        });
    }
    
    // Initialize
    document.addEventListener("DOMContentLoaded", function() {
        evaluateAllFields();
        attachListeners();
    });
})();
</script>';
        
        return $js;
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Form;
use App\Models\FormField;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class FormController extends Controller
{
    /**
     * Display a listing of forms.
     */
    public function index()
    {
        return view('admin.forms.index');
    }

    /**
     * Show the form for creating a new form.
     */
    public function create()
    {
        return view('admin.forms.create');
    }

    /**
     * Store a newly created form.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:forms,slug',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
            'settings' => 'nullable|array',
            'fields' => 'required|array|min:1',
            'fields.*.label' => 'required|string',
            'fields.*.field_id' => 'required|string',
            'fields.*.type' => 'required|string',
            'fields.*.is_required' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $form = Form::create([
                'name' => $request->name,
                'slug' => $request->slug ?: Str::slug($request->name),
                'description' => $request->description,
                'is_active' => $request->is_active ?? true,
                'settings' => $request->settings,
                'submit_button_text' => $request->submit_button_text ?? 'Submit',
                'notifications' => $request->notifications,
                'confirmations' => $request->confirmations,
                'spam_protection' => $request->spam_protection,
            ]);

            // Create form fields
            foreach ($request->fields as $index => $fieldData) {
                $form->fields()->create([
                    'label' => $fieldData['label'],
                    'field_id' => $fieldData['field_id'],
                    'type' => $fieldData['type'],
                    'options' => $fieldData['options'] ?? null,
                    'validation' => $fieldData['validation'] ?? null,
                    'order' => $index,
                    'is_required' => $fieldData['is_required'] ?? false,
                    'placeholder' => $fieldData['placeholder'] ?? null,
                    'help_text' => $fieldData['help_text'] ?? null,
                    'default_value' => $fieldData['default_value'] ?? null,
                    'column_width' => $fieldData['column_width'] ?? 'full',
                    'advanced_settings' => $fieldData['advanced_settings'] ?? null,
                    'conditional_logic' => $fieldData['conditional_logic'] ?? null,
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Form created successfully',
                'data' => $form->load('fields'),
            ]);
        } catch (\Exception $e) {
            \Log::error('Form creation failed: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Form creation failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified form.
     */
    public function show(Form $form)
    {
        $form->load(['fields', 'entries']);
        return view('admin.forms.show', compact('form'));
    }

    /**
     * Show the form for editing the specified form.
     */
    public function edit(Form $form)
    {
        $form->load('fields');
        return view('admin.forms.edit', compact('form'));
    }

    /**
     * Update the specified form.
     */
    public function update(Request $request, Form $form)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:forms,slug,' . $form->id,
            'description' => 'nullable|string',
            'is_active' => 'boolean',
            'settings' => 'nullable|array',
            'fields' => 'required|array|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $form->update([
                'name' => $request->name,
                'slug' => $request->slug ?: Str::slug($request->name),
                'description' => $request->description,
                'is_active' => $request->is_active ?? true,
                'settings' => $request->settings,
                'submit_button_text' => $request->submit_button_text ?? 'Submit',
                'notifications' => $request->notifications,
                'confirmations' => $request->confirmations,
                'spam_protection' => $request->spam_protection,
            ]);

            // Delete existing fields and recreate
            $form->fields()->delete();

            foreach ($request->fields as $index => $fieldData) {
                $form->fields()->create([
                    'label' => $fieldData['label'],
                    'field_id' => $fieldData['field_id'],
                    'type' => $fieldData['type'],
                    'options' => $fieldData['options'] ?? null,
                    'validation' => $fieldData['validation'] ?? null,
                    'order' => $index,
                    'is_required' => $fieldData['is_required'] ?? false,
                    'placeholder' => $fieldData['placeholder'] ?? null,
                    'help_text' => $fieldData['help_text'] ?? null,
                    'default_value' => $fieldData['default_value'] ?? null,
                    'column_width' => $fieldData['column_width'] ?? 'full',
                    'advanced_settings' => $fieldData['advanced_settings'] ?? null,
                    'conditional_logic' => $fieldData['conditional_logic'] ?? null,
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Form updated successfully',
                'data' => $form->load('fields'),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Form update failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified form.
     */
    public function destroy(Form $form)
    {
        try {
            $form->delete();

            return response()->json([
                'success' => true,
                'message' => 'Form deleted successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Form deletion failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display form entries.
     */
    public function entries(Form $form, Request $request)
    {
        $query = $form->entries()->with('user')->latest();
        
        // Search across all fields
        if ($search = $request->get('search')) {
            $query->where(function($q) use ($search) {
                $q->where('data', 'like', "%{$search}%")
                  ->orWhere('ip_address', 'like', "%{$search}%");
            });
        }
        
        // Date range filter
        if ($dateFrom = $request->get('date_from')) {
            $query->whereDate('created_at', '>=', $dateFrom);
        }
        if ($dateTo = $request->get('date_to')) {
            $query->whereDate('created_at', '<=', $dateTo);
        }
        
        // Get stats before pagination
        $stats = [
            'total' => $form->entries()->count(),
            'today' => $form->entries()->whereDate('created_at', today())->count(),
            'this_week' => $form->entries()->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
            'this_month' => $form->entries()->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->count(),
        ];
        
        $entries = $query->paginate(25)->withQueryString();
        
        return view('admin.forms.entries', compact('form', 'entries', 'stats'));
    }

    /**
     * Export form entries to multiple formats.
     */
    public function exportEntries(Form $form, Request $request)
    {
        $entries = $form->entries()->with('user')->get();
        $format = $request->get('format', 'csv');
        
        if ($entries->isEmpty()) {
            return back()->with('error', 'No entries to export');
        }
        
        $baseFilename = Str::slug($form->name) . '-entries-' . now()->format('Y-m-d');
        
        switch ($format) {
            case 'xlsx':
            case 'excel':
                // Export as tab-separated (can be opened in Excel)
                $filename = $baseFilename . '.xlsx';
                $callback = function() use ($entries, $form) {
                    $file = fopen('php://output', 'w');
                    
                    // Header row
                    $headers = ['ID', 'Submitted At', 'IP Address'];
                    foreach ($form->fields as $field) {
                        if (!in_array($field->type, ['section', 'divider', 'html'])) {
                            $headers[] = $field->label;
                        }
                    }
                    fputcsv($file, $headers, "\t");
                    
                    // Data rows
                    foreach ($entries as $entry) {
                        $row = [$entry->id, $entry->created_at->format('Y-m-d H:i:s'), $entry->ip_address];
                        foreach ($form->fields as $field) {
                            if (!in_array($field->type, ['section', 'divider', 'html'])) {
                                $value = $entry->getFieldValue($field->field_id);
                                $row[] = is_array($value) ? implode(', ', $value) : $value;
                            }
                        }
                        fputcsv($file, $row, "\t");
                    }
                    fclose($file);
                };
                return response()->stream($callback, 200, [
                    'Content-Type' => 'application/vnd.ms-excel',
                    'Content-Disposition' => "attachment; filename=\"{$filename}\"",
                ]);
                
            case 'pdf':
                // Generate simple HTML table for PDF
                $html = $this->generatePdfHtml($form, $entries);
                return response($html, 200, [
                    'Content-Type' => 'text/html',
                    'Content-Disposition' => "attachment; filename=\"{$baseFilename}.html\"",
                ]);
                
            default: // csv
                $filename = $baseFilename . '.csv';
                $callback = function() use ($entries) {
                    $file = fopen('php://output', 'w');
                    $firstEntry = $entries->first();
                    fputcsv($file, array_keys($firstEntry->toExportArray()));
                    foreach ($entries as $entry) {
                        fputcsv($file, $entry->toExportArray());
                    }
                    fclose($file);
                };
                return response()->stream($callback, 200, [
                    'Content-Type' => 'text/csv',
                    'Content-Disposition' => "attachment; filename=\"{$filename}\"",
                ]);
        }
    }
    
    /**
     * Generate PDF-ready HTML for entries.
     */
    protected function generatePdfHtml(Form $form, $entries)
    {
        $html = '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>' . e($form->name) . ' - Entries</title>';
        $html .= '<style>body{font-family:Arial,sans-serif;font-size:12px}table{width:100%;border-collapse:collapse;margin-top:20px}th,td{border:1px solid #ddd;padding:8px;text-align:left}th{background:#f5f5f5}h1{color:#333}</style></head><body>';
        $html .= '<h1>' . e($form->name) . ' - Form Entries</h1>';
        $html .= '<p>Exported: ' . now()->format('F j, Y g:i A') . ' | Total: ' . $entries->count() . ' entries</p>';
        $html .= '<table><thead><tr><th>ID</th><th>Submitted</th>';
        
        foreach ($form->fields as $field) {
            if (!in_array($field->type, ['section', 'divider', 'html'])) {
                $html .= '<th>' . e($field->label) . '</th>';
            }
        }
        $html .= '</tr></thead><tbody>';
        
        foreach ($entries as $entry) {
            $html .= '<tr><td>#' . $entry->id . '</td><td>' . $entry->created_at->format('M d, Y H:i') . '</td>';
            foreach ($form->fields as $field) {
                if (!in_array($field->type, ['section', 'divider', 'html'])) {
                    $value = $entry->getFieldValue($field->field_id);
                    $html .= '<td>' . e(is_array($value) ? implode(', ', $value) : $value) . '</td>';
                }
            }
            $html .= '</tr>';
        }
        
        $html .= '</tbody></table></body></html>';
        return $html;
    }

    /**
     * Toggle form active status.
     */
    public function toggleStatus(Form $form)
    {
        try {
            $form->update(['is_active' => !$form->is_active]);

            return response()->json([
                'success' => true,
                'message' => 'Form status updated successfully',
                'is_active' => $form->is_active,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Status update failed: ' . $e->getMessage(),
            ], 500);
        }
    }
    
    /**
     * Delete a form entry.
     */
    public function deleteEntry($entryId)
    {
        try {
            $entry = \App\Models\FormEntry::findOrFail($entryId);
            $entry->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Entry deleted successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete entry: ' . $e->getMessage(),
            ], 500);
        }
    }
}

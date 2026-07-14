<?php

namespace App\Http\Controllers;

use App\Models\Form;
use Illuminate\Http\Request;

class FormSubmissionController extends Controller
{
    /**
     * Display the form.
     */
    public function show($slug)
    {
        $form = Form::where('slug', $slug)
            ->where('is_active', true)
            ->with('fields')
            ->firstOrFail();

        return view('forms.show', compact('form'));
    }

    /**
     * Handle form submission.
     */
    public function submit(Request $request, $slug)
    {
        $form = Form::where('slug', $slug)
            ->where('is_active', true)
            ->with('fields')
            ->firstOrFail();

        // Process the submission (validation + entry creation + notifications)
        $result = $form->processSubmission($request->all(), $request);

        if (! $result['success']) {
            return back()
                ->withErrors($result['errors'])
                ->withInput();
        }

        // Notifications are dispatched inside processSubmission() via FormNotificationService.

        // Handle confirmation based on type
        $confirmations = $form->confirmations ?? [];
        $confirmationType = $confirmations['type'] ?? 'message';
        $successMessage = $confirmations['message'] ?? 'Form submitted successfully!';

        switch ($confirmationType) {
            case 'redirect':
                $redirectUrl = $confirmations['redirect_url'] ?? url('/');

                return redirect($redirectUrl)->with('success', $successMessage);

            case 'success_page':
                // Redirect to form's dedicated success page
                return redirect()->route('forms.success', $slug)
                    ->with('form_success_message', $successMessage);

            case 'message':
            default:
                return back()->with('success', $successMessage);
        }
    }

    /**
     * Display the form success page.
     */
    public function success($slug)
    {
        $form = Form::where('slug', $slug)->firstOrFail();

        $confirmations = $form->confirmations ?? [];
        $title = $confirmations['success_title'] ?? 'Thank You!';
        $message = $confirmations['success_description'] ?? 'Thank you for your submission!';

        return view('forms.success', compact('form', 'message', 'title'));
    }

    /**
     * AJAX submission handler.
     */
    public function submitAjax(Request $request, $slug)
    {
        $form = Form::where('slug', $slug)
            ->where('is_active', true)
            ->with('fields')
            ->firstOrFail();

        // Process the submission (validation + entry creation + notifications)
        $result = $form->processSubmission($request->all(), $request);

        if (! $result['success']) {
            return response()->json([
                'success' => false,
                'errors' => $result['errors'],
            ], 422);
        }

        $confirmations = $form->confirmations ?? [];
        $successMessage = $confirmations['message'] ?? 'Form submitted successfully!';

        return response()->json([
            'success' => true,
            'message' => $successMessage,
            'entry_id' => $result['entry']->id,
            'redirect_url' => ($confirmations['type'] ?? null) === 'redirect'
                ? ($confirmations['redirect_url'] ?? null)
                : null,
        ]);
    }
}

<?php

namespace Plugins\ArticleSubmission\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Plugins\ArticleSubmission\Models\ArticleSubmission;

class SubmissionController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => [
                'required',
                'string',
                function ($attribute, $value, $fail) {
                    $cleaned = preg_replace('/[^\d]/', '', $value);
                    if (strlen($cleaned) < 9 || strlen($cleaned) > 13) {
                        $fail('The phone number must be between 9 and 13 digits.');
                    }
                },
            ],
            'job_level' => 'required|string|max:100',
            'job_title' => 'required|string|max:255',
            'domicile' => 'required|string|max:255',
            'domicile_other' => 'required_if:domicile,Other|nullable|string|max:255',
            'linkedin' => ['nullable', 'string', 'max:255', 'regex:/^(https?:\/\/)?(www\.)?linkedin\.com\/.*$/i'],
            'institution' => 'required|string|max:255',
            'education_level' => 'nullable|string|max:100',
            'industry' => 'required|string|max:255',
            'article_file' => 'required|file|mimes:pdf|max:10240', // 10MB max
        ], [
            'linkedin.regex' => 'LinkedIn account must be a valid LinkedIn URL.',
        ]);

        if ($validated['domicile'] === 'Other' && ! empty($validated['domicile_other'])) {
            $validated['domicile'] = $validated['domicile_other'];
        }
        unset($validated['domicile_other']);

        // Handle file upload
        if ($request->hasFile('article_file')) {
            $file = $request->file('article_file');
            $filename = time().'_'.$file->getClientOriginalName();
            $path = $file->storeAs('article-submissions', $filename, 'public');
            $validated['article_file'] = $path;
        }

        $validated['status'] = ArticleSubmission::STATUS_PENDING;

        ArticleSubmission::create($validated);

        return redirect()->route('article-submission.success');
    }
}

<?php

namespace Plugins\ArticleSubmission\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Storage;
use Plugins\ArticleSubmission\Models\ArticleSubmission;

class SubmissionController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:50',
            'job_level' => 'nullable|string|max:100',
            'job_title' => 'nullable|string|max:255',
            'domicile' => 'nullable|string|max:255',
            'linkedin' => 'nullable|string|max:255',
            'institution' => 'nullable|string|max:255',
            'education_level' => 'nullable|string|max:100',
            'industry' => 'nullable|string|max:255',
            'article_file' => 'nullable|file|mimes:pdf|max:10240', // 10MB max
        ]);

        // Handle file upload
        if ($request->hasFile('article_file')) {
            $file = $request->file('article_file');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('article-submissions', $filename, 'public');
            $validated['article_file'] = $path;
        }

        $validated['status'] = ArticleSubmission::STATUS_PENDING;

        ArticleSubmission::create($validated);

        return redirect()->route('article-submission.success');
    }
}

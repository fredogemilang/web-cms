<?php

use Illuminate\Support\Facades\Route;

$adminPath = config('admin.path', 'ctrlpanel');

// Admin Routes
Route::prefix($adminPath . '/article-submissions')
    ->name('admin.article-submissions.')
    ->middleware(['web', 'auth'])
    ->group(function () {
        Route::get('/', function () {
            return view('article-submission::index');
        })->name('index')->middleware('permission:submissions.view');
        
        Route::get('/{id}', function ($id) {
            $submission = \Plugins\ArticleSubmission\Models\ArticleSubmission::withTrashed()->findOrFail($id);
            return view('article-submission::show', compact('submission'));
        })->name('show')->middleware('permission:submissions.view');
        
        Route::get('/{id}/download', function ($id) {
            $submission = \Plugins\ArticleSubmission\Models\ArticleSubmission::findOrFail($id);
            
            if ($submission->article_file && \Storage::disk('public')->exists($submission->article_file)) {
                return \Storage::disk('public')->download($submission->article_file);
            }
            
            return back()->with('error', 'File not found.');
        })->name('download')->middleware('permission:submissions.view');
        
        Route::get('/export/csv', function () {
            $submissions = \Plugins\ArticleSubmission\Models\ArticleSubmission::all();
            
            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="article-submissions-' . date('Y-m-d') . '.csv"',
            ];
            
            $callback = function() use ($submissions) {
                $file = fopen('php://output', 'w');
                fputcsv($file, ['Name', 'Email', 'Phone', 'Job Level', 'Job Title', 'Domicile', 'LinkedIn', 'Institution', 'Education', 'Industry', 'Status', 'Submitted At']);
                
                foreach ($submissions as $submission) {
                    fputcsv($file, [
                        $submission->name,
                        $submission->email,
                        $submission->phone,
                        $submission->job_level,
                        $submission->job_title,
                        $submission->domicile,
                        $submission->linkedin,
                        $submission->institution,
                        $submission->education_level,
                        $submission->industry,
                        $submission->status,
                        $submission->created_at->format('Y-m-d H:i:s'),
                    ]);
                }
                
                fclose($file);
            };
            
            return response()->stream($callback, 200, $headers);
        })->name('export')->middleware('permission:submissions.export');
    });

// Public Routes
Route::middleware(['web'])->group(function () {
    Route::get('/upload-article', function () {
        return view('article-submission::public.form');
    })->name('article-submission.form');
    
    Route::post('/upload-article', [\Plugins\ArticleSubmission\Http\Controllers\SubmissionController::class, 'store'])
        ->name('article-submission.submit');
    
    Route::get('/upload-article/success', function () {
        return view('article-submission::public.success');
    })->name('article-submission.success');
});

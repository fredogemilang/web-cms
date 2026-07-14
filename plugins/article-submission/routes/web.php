<?php

use Illuminate\Support\Facades\Route;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Plugins\ArticleSubmission\Http\Controllers\SubmissionController;
use Plugins\ArticleSubmission\Models\ArticleSubmission;

$adminPath = config('admin.path', 'ctrlpanel');

// Admin Routes
Route::prefix($adminPath.'/article-submissions')
    ->name('admin.article-submissions.')
    ->middleware(['web', 'auth'])
    ->group(function () {
        Route::get('/', function () {
            return view('article-submission::index');
        })->name('index')->middleware('permission:submissions.view');

        Route::get('/{id}', function ($id) {
            $submission = ArticleSubmission::withTrashed()->findOrFail($id);

            return view('article-submission::show', compact('submission'));
        })->name('show')->middleware('permission:submissions.view');

        Route::get('/{id}/download', function ($id) {
            $submission = ArticleSubmission::findOrFail($id);

            if ($submission->article_file && Storage::disk('public')->exists($submission->article_file)) {
                return Storage::disk('public')->download($submission->article_file);
            }

            return back()->with('error', 'File not found.');
        })->name('download')->middleware('permission:submissions.view');

        Route::get('/export/excel', function () {
            $submissions = ArticleSubmission::all();

            $filename = 'article-submissions-'.date('Y-m-d').'.xlsx';

            $spreadsheet = new Spreadsheet;
            $sheet = $spreadsheet->getActiveSheet();

            $sheet->fromArray(['Name', 'Email', 'Phone', 'Job Level', 'Job Title', 'Domicile', 'LinkedIn', 'Institution', 'Education', 'Industry', 'Status', 'Submitted At'], null, 'A1');

            $rowNumber = 2;
            foreach ($submissions as $submission) {
                $sheet->fromArray([
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
                ], null, 'A'.$rowNumber);
                $rowNumber++;
            }

            return response()->streamDownload(function () use ($spreadsheet) {
                $writer = new Xlsx($spreadsheet);
                $writer->save('php://output');
            }, $filename, [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            ]);
        })->name('export')->middleware('permission:submissions.export');
    });

// Public Routes
Route::middleware(['web'])->group(function () {
    Route::get('/upload-article', function () {
        return view('article-submission::public.form');
    })->name('article-submission.form');

    Route::post('/upload-article', [SubmissionController::class, 'store'])
        ->name('article-submission.submit');

    Route::get('/upload-article/success', function () {
        return view('article-submission::public.success');
    })->name('article-submission.success');
});

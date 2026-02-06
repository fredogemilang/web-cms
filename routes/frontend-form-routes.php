
// Frontend Form Routes
Route::prefix('forms')->name('forms.')->group(function () {
    Route::get('/{slug}', [\App\Http\Controllers\FormSubmissionController::class, 'show'])->name('show');
    Route::post('/{slug}', [\App\Http\Controllers\FormSubmissionController::class, 'submit'])->name('submit');
    Route::post('/{slug}/ajax', [\App\Http\Controllers\FormSubmissionController::class, 'submitAjax'])->name('submit.ajax');
});

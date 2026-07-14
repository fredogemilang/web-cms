<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\CptController;
use App\Http\Controllers\Api\V1\FormController;
use App\Http\Controllers\Api\V1\MediaController;
use App\Http\Controllers\Api\V1\PageController;
use Illuminate\Support\Facades\Route;

Route::middleware('api.cors')->prefix('v1')->group(function () {
    // Auth
    Route::post('/auth/login', [AuthController::class, 'login']);
    Route::post('/auth/logout', [AuthController::class, 'logout'])->middleware('api.auth');
    Route::get('/me', [AuthController::class, 'me'])->middleware('api.auth');

    // Public content (read-only)
    Route::get('/pages', [PageController::class, 'index']);
    Route::get('/pages/{slug}', [PageController::class, 'show']);
    Route::get('/cpt/{type}', [CptController::class, 'index']);
    Route::get('/cpt/{type}/{slug}', [CptController::class, 'show']);

    // Media — public read, authenticated write only
    Route::get('/media', [MediaController::class, 'index']);
    Route::get('/media/{id}', [MediaController::class, 'show']);

    // Form submission (public, with throttle)
    Route::post('/forms/{slug}/submit', [FormController::class, 'submit'])
        ->middleware('throttle:30,1');

    // OpenAPI spec stub
    Route::get('/openapi.json', function () {
        return response()->json([
            'openapi' => '3.1.0',
            'info' => [
                'title' => setting('site_name', config('app.name')).' API',
                'version' => '1.0.0',
                'description' => 'Public content + authenticated mutation surface.',
            ],
            'servers' => [['url' => url('/api/v1')]],
            'paths' => [
                '/pages' => ['get' => ['summary' => 'List published pages']],
                '/pages/{slug}' => ['get' => ['summary' => 'Get a single page']],
                '/cpt/{type}' => ['get' => ['summary' => 'List CPT entries']],
                '/cpt/{type}/{slug}' => ['get' => ['summary' => 'Get a CPT entry']],
                '/media' => ['get' => ['summary' => 'List media']],
                '/forms/{slug}/submit' => ['post' => ['summary' => 'Submit a form']],
                '/auth/login' => ['post' => ['summary' => 'Exchange credentials for a token']],
                '/auth/logout' => ['post' => ['summary' => 'Revoke current token']],
                '/me' => ['get' => ['summary' => 'Get current authenticated user']],
            ],
        ]);
    });
});

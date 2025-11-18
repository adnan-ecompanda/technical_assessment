<?php

use App\Http\Controllers\BulkEmailController;
use Illuminate\Support\Facades\Route;

// Route::get('/', function () {
//     return view('welcome');
// });

Route::get('/', function () {
    return redirect()->route('bulk-emails.create');
});

Route::get('/bulk-emails/create', [BulkEmailController::class, 'create'])
    ->name('bulk-emails.create');

Route::post('/bulk-emails', [BulkEmailController::class, 'store'])
    ->name('bulk-emails.store');

Route::get('/bulk-emails/{campaign}', [BulkEmailController::class, 'show'])
    ->name('bulk-emails.show');
Route::get('/bulk-emails/{campaign}/status', [BulkEmailController::class, 'status'])
    ->name('bulk-emails.status');
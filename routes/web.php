<?php

use App\Http\Controllers\CasesController;
use App\Http\Controllers\FrontController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\ArchivedController;
use App\Http\Controllers\InspectionsController;
use App\Http\Controllers\DocketingController;
use App\Http\Controllers\HearingProcessController;
use App\Http\Controllers\ReviewAndDraftingController;
use App\Http\Controllers\OrderAndDispositionController;
use App\Http\Controllers\ComplianceAndAwardController;
use App\Http\Controllers\AppealsAndResolutionController;
use Illuminate\Support\Facades\Route;

Route::get('/', [FrontController::class, 'index'])->name('home');

Route::get('/login', [FrontController::class, 'login'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->name('login.post');

// 2FA Routes
Route::get('/2fa/verify', [LoginController::class, 'show2FAForm'])->name('2fa.verify');
Route::post('/2fa/verify', [LoginController::class, 'verify2FA'])->name('2fa.verify.post');
Route::post('/2fa/resend', [LoginController::class, 'resend2FA'])->name('2fa.resend');

// Logout Route
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// User Management Routes (Admin)
Route::post('/user', [UserController::class, 'store'])->name('user.post');

// Password reset routes
Route::get('/password/reset/{token}', [App\Http\Controllers\Auth\ResetPasswordController::class, 'showResetForm'])->name('password.reset');
Route::post('/password/reset', [App\Http\Controllers\Auth\ResetPasswordController::class, 'reset'])->name('password.update');

// Protected Routes (require authentication)
Route::middleware('auth')->group(function () {
    
    // Dashboard/Home
    Route::get('/', [FrontController::class, 'index'])->name('home');
    
    // Users route  
    Route::get('/users', [FrontController::class, 'users'])->name('users');
    
    //cases
    Route::get('/case', [CasesController::class, 'case'])->name('case.index');
    Route::post('/case', [CasesController::class, 'store'])->name('case.store');
    Route::put('/case/{id}', [CasesController::class, 'update'])->name('case.update');
    Route::delete('/case/{id}', [CasesController::class, 'destroy'])->name('case.destroy');
    Route::get('/case/{id}', [CasesController::class, 'show'])->name('case.show');
    Route::get('/case/{id}/edit', [CasesController::class, 'edit'])->name('case.edit');

    // Inspections
    Route::resource('inspection', InspectionsController::class);

    //archived
    Route::get('/archive', [ArchivedController::class, 'index'])->name('archive');

    //docketing
    Route::resource('docketing', DocketingController::class);

    //hearing_process
    Route::post('hearing-process', [HearingProcessController::class, 'store'])->name('hearing-process.store');
    Route::get('hearing-process/{id}', [HearingProcessController::class, 'show'])->name('hearing-process.show');
    Route::get('hearing-process/{id}/edit', [HearingProcessController::class, 'edit'])->name('hearing-process.edit');
    Route::put('hearing-process/{id}', [HearingProcessController::class, 'update'])->name('hearing-process.update');
    Route::delete('hearing-process/{id}', [HearingProcessController::class, 'destroy'])->name('hearing-process.destroy');

    //review and drafting
    Route::resource('review-and-drafting', ReviewAndDraftingController::class);

    //order and disposition
    Route::resource('orders-and-disposition', OrderAndDispositionController::class);

    //compliance and awards
    Route::resource('compliance-and-awards', ComplianceAndAwardController::class);

    //appeals and resolution routes
    Route::resource('appeals-and-resolution', AppealsAndResolutionController::class);
});
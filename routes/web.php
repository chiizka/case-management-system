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
// Route::middleware('auth')->group(function () {
    
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

    //move to next stage
    Route::post('/case/{id}/next-stage', [CasesController::class, 'moveToNextStage'])->name('case.nextStage');

    //inline-update
    Route::put('/inspection/{inspection}/inline-update', [InspectionsController::class, 'inlineUpdate'])->name('inspection.inlineUpdate');
    Route::put('/case/{id}/inline-update', [CasesController::class, 'inlineUpdate'])->name('case.inlineUpdate');
    Route::put('/docketing/{id}/inline-update', [DocketingController::class, 'inlineUpdate'])->name('docketing.inlineUpdate');


Route::resource('hearing', HearingProcessController::class);
Route::put('/hearing/{id}/inline-update', [HearingProcessController::class, 'inlineUpdate'])->name('hearing.inlineUpdate');
Route::get('/hearing/{id}/get', [HearingProcessController::class, 'getHearingProcess'])->name('hearing.get');
Route::put('/hearing-process/{id}/inline-update', [HearingProcessController::class, 'inlineUpdate'])->name('hearing-process.inlineUpdate');
Route::resource('review-and-drafting', ReviewAndDraftingController::class);
Route::put('/review-and-drafting/{id}/inline-update', [ReviewAndDraftingController::class, 'inlineUpdate'])->name('review-and-drafting.inline-update');
Route::put('/orders-and-disposition/{id}/inline-update', [OrderAndDispositionController::class, 'inlineUpdate'])->name('orders-and-disposition.inline-update');

// });
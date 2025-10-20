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
use App\Http\Controllers\LogController;

Route::get('/', [FrontController::class, 'index'])->name('home');

Route::get('/login', [FrontController::class, 'login'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->name('login.post');

// 2FA Routes
Route::get('/2fa/verify', [LoginController::class, 'show2FAForm'])->name('2fa.verify');
Route::post('/2fa/verify', [LoginController::class, 'verify2FA'])->name('2fa.verify.post');
Route::post('/2fa/resend', [LoginController::class, 'resend2FA'])->name('2fa.resend');

// Logout Route
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// Password reset routes
Route::get('/password/reset/{token}', [App\Http\Controllers\Auth\ResetPasswordController::class, 'showResetForm'])->name('password.reset');
Route::post('/password/reset', [App\Http\Controllers\Auth\ResetPasswordController::class, 'reset'])->name('password.update');

Route::middleware('auth')->group(function () {
    
    // Dashboard/Home
    Route::get('/', [FrontController::class, 'index'])->name('home');
    
    // User Management - Admin Only
    Route::middleware('role:admin')->group(function () {
        Route::get('/users', [FrontController::class, 'users'])->name('users');
        Route::post('/user', [UserController::class, 'store'])->name('user.post');
        Route::post('/user/{id}/reset-password', [UserController::class, 'resetPassword'])->name('user.reset-password');
        Route::get('/logs', [LogController::class, 'index'])->name('logs.index');
        Route::put('/user/{id}', [UserController::class, 'update'])->name('user.update');
        Route::delete('/user/{id}', [UserController::class, 'destroy'])->name('user.destroy');
    });
    
    // Cases - Admin, Province, MALSU, Case Management
    Route::middleware('role:admin,province,malsu,case_management')->group(function () {
        Route::resource('case', CasesController::class);
        Route::get('/archive', [ArchivedController::class, 'index'])->name('archive.index');
        Route::post('/case/{id}/next-stage', [CasesController::class, 'moveToNextStage'])->name('case.nextStage');
        Route::put('/case/{id}/inline-update', [CasesController::class, 'inlineUpdate'])->name('case.inlineUpdate');
        Route::get('/case/load-tab/{tabNumber}', [CasesController::class, 'loadTabData'])->name('case.loadTab');
    });

    // Inspections - Admin, MALSU, Case Management, province
    Route::middleware('role:admin,malsu,case_management,province')->group(function () {
        Route::resource('inspection', InspectionsController::class);
        Route::put('/inspection/{inspection}/inline-update', [InspectionsController::class, 'inlineUpdate'])->name('inspection.inlineUpdate');
    });

    // Docketing - Admin, Case Management, province
    Route::middleware('role:admin,case_management,province')->group(function () {
        Route::resource('docketing', DocketingController::class);
        Route::put('/docketing/{id}/inline-update', [DocketingController::class, 'inlineUpdate'])->name('docketing.inlineUpdate');
    });

    // Hearing Process - Admin, Case Management, province
    Route::middleware('role:admin,case_management,province')->group(function () {
        Route::resource('hearing', HearingProcessController::class);
        Route::post('hearing-process', [HearingProcessController::class, 'store'])->name('hearing-process.store');
        Route::get('hearing-process/{id}', [HearingProcessController::class, 'show'])->name('hearing-process.show');
        Route::get('hearing-process/{id}/edit', [HearingProcessController::class, 'edit'])->name('hearing-process.edit');
        Route::put('hearing-process/{id}', [HearingProcessController::class, 'update'])->name('hearing-process.update');
        Route::delete('hearing-process/{id}', [HearingProcessController::class, 'destroy'])->name('hearing-process.destroy');
        Route::put('/hearing/{id}/inline-update', [HearingProcessController::class, 'inlineUpdate'])->name('hearing.inlineUpdate');
        Route::get('/hearing/{id}/get', [HearingProcessController::class, 'getHearingProcess'])->name('hearing.get');
        Route::put('/hearing-process/{id}/inline-update', [HearingProcessController::class, 'inlineUpdate'])->name('hearing-process.inlineUpdate');
    });

    // Review and Drafting - Admin, Case Management
    Route::middleware('role:admin,case_management')->group(function () {
        Route::resource('review-and-drafting', ReviewAndDraftingController::class);
        Route::put('/review-and-drafting/{id}/inline-update', [ReviewAndDraftingController::class, 'inlineUpdate'])->name('review-and-drafting.inline-update');
    });

    // Orders and Disposition - Admin, Case Management
    Route::middleware('role:admin,case_management')->group(function () {
        Route::resource('orders-and-disposition', OrderAndDispositionController::class);
        Route::put('/orders-and-disposition/{id}/inline-update', [OrderAndDispositionController::class, 'inlineUpdate'])->name('orders-and-disposition.inline-update');
    });

    // Compliance and Awards - Admin, Case Management
    Route::middleware('role:admin,case_management')->group(function () {
        Route::resource('compliance-and-awards', ComplianceAndAwardController::class);
        Route::put('/compliance-and-awards/{id}/inline-update', [ComplianceAndAwardController::class, 'inlineUpdate'])->name('compliance-and-awards.inline-update');
    });

    // Appeals and Resolution - Admin, Case Management, Malsu
    Route::middleware('role:admin,case_management,malsu')->group(function () {
        Route::resource('appeals-and-resolution', AppealsAndResolutionController::class);
        Route::put('/appeals-and-resolution/{id}/inline-update', [AppealsAndResolutionController::class, 'inlineUpdate'])->name('appeals-and-resolution.inline-update');
    });

});
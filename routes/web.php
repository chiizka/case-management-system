<?php

use App\Http\Controllers\CasesController;
use App\Http\Controllers\FrontController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ArchivedController;
use App\Http\Controllers\InspectionsController;
use Illuminate\Support\Facades\Route;

Route::get('/', [FrontController::class, 'index'])->name('home');

//cases
Route::get('/case', [CasesController::class, 'case'])->name('case.index');
Route::post('/case', [CasesController::class, 'store'])->name('case.store');
Route::put('/case/{id}', [CasesController::class, 'update'])->name('case.update');
Route::delete('/cases/{id}', [CasesController::class, 'destroy'])->name('cases.destroy');
Route::get('/case/{id}', [CasesController::class, 'show'])->name('case.show');
Route::get('/case/{id}/edit', [CasesController::class, 'edit'])->name('case.edit');

// Inspections
Route::resource('inspection', InspectionsController::class);

//archived
Route::get('/archive', [ArchivedController::class, 'index'])->name('archive');

//login and user routes
Route::get('/login', [FrontController::class, 'login'])->name('login');
Route::get('/users', [FrontController::class, 'users'])->name('users');
Route::post('/post', [UserController::class, 'store'])->name('user.post');
Route::post('/login', [UserController::class, 'login'])->name('login.post');


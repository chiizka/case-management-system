<?php

use App\Http\Controllers\FrontController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', [FrontController::class, 'index'])->name('home');
Route::get('/case', [FrontController::class, 'page'])->name('case');
Route::get('/login', [FrontController::class, 'login'])->name('login');
Route::get('/users', [FrontController::class, 'users'])->name('users');
Route::post('/post', [UserController::class, 'store'])->name('user.post');
Route::post('/login', [UserController::class, 'login'])->name('login.post');

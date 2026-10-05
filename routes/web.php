<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\FeedbackController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LinkEditController;
use App\Http\Controllers\StreamController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::post('/links', [HomeController::class, 'store'])->name('links.store');

Route::get('/feedback', [FeedbackController::class, 'form'])->name('feedback.form');
Route::post('/feedback', [FeedbackController::class, 'store'])
    ->name('feedback.store')
    ->middleware('throttle:10,1');

Route::get('/admin', [AdminController::class, 'dashboard'])->name('admin.dashboard');
Route::post('/admin/login', [AdminController::class, 'login'])
    ->name('admin.login')
    ->middleware('throttle:10,1');
Route::post('/admin/logout', [AdminController::class, 'logout'])->name('admin.logout');
Route::post('/admin/feedback/{feedback}/toggle', [AdminController::class, 'toggleResolved'])
    ->name('admin.feedback.toggle')
    ->middleware('throttle:60,1');

Route::get('/streaming/{token}/edit', [LinkEditController::class, 'show'])
    ->name('links.edit')
    ->whereNumber('token');
Route::post('/streaming/{token}/edit', [LinkEditController::class, 'update'])
    ->name('links.update')
    ->middleware('throttle:20,1');

Route::get('/radio', [StreamController::class, 'radio'])->name('radio');
Route::get('/streaming/{token}', [StreamController::class, 'show'])
    ->name('stream')
    ->whereNumber('token');

Route::get('/api/stream/now', [StreamController::class, 'now'])
    ->name('stream.now')
    ->middleware('throttle:240,1');

Route::get('/api/quran/page/{page}', [StreamController::class, 'page'])
    ->name('quran.page')
    ->whereNumber('page');

Route::post('/api/links/{token}/played', [StreamController::class, 'played'])
    ->name('links.played')
    ->whereNumber('token')
    ->middleware('throttle:120,1');

<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\StreamController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::post('/links', [HomeController::class, 'store'])->name('links.store');

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

<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\GameController;
use App\Http\Controllers\GameNotificationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\QuestionController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('welcome');

// Footer pages
Route::get('/about', function () {
    return view('footer.about');
})->name('about');

Route::get('/terms', function () {
    return view('footer.terms');
})->name('terms');

Route::get('/privacy', function () {
    return view('footer.privacy');
})->name('privacy');

Route::get('/donate', function () {
    return view('footer.donate');
})->name('donate');

Route::prefix('game')
    ->name('game.')
    ->group(function () {
        Route::get('', [GameController::class, 'lobby'])->name('lobby');
        Route::post('create', [GameController::class, 'create'])->name('create');
        Route::post('join', [GameController::class, 'join'])->name('join');
        Route::get('check-active', [GameNotificationController::class, 'checkActiveGame'])->middleware('auth')->name('checkActive');
        Route::get('{game}/wait', [GameController::class, 'wait'])->name('wait');
        Route::get('{game}/matchmaking', [GameController::class, 'matchmaking'])->name('matchmaking');
        Route::post('{game}/cancel-matchmaking', [GameController::class, 'cancelMatchmaking'])->name('cancelMatchmaking');
        Route::get('{game}/check-matchmaking', [GameController::class, 'checkMatchmaking'])->name('checkMatchmaking');
        Route::get('{game}/play', [GameController::class, 'play'])->name('play');
        Route::get('{game}/results', [GameController::class, 'results'])->name('results');
        Route::post('{game}/select-category', [GameController::class, 'selectCategory'])->name('selectCategory');
        Route::post('{game}/select-difficulty', [GameController::class, 'selectDifficulty'])->name('selectDifficulty');
        Route::post('{game}/submit-answer', [GameController::class, 'submitAnswer'])->name('submitAnswer');
        Route::post('{game}/forfeit', [GameController::class, 'forfeit'])->name('forfeit');
        Route::get('{game}/state', [GameController::class, 'getState'])->name('state');
    });

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth', 'editor'])
    ->prefix('questions')
    ->name('questions.')
    ->group(function () {
        Route::get('', [QuestionController::class, 'index'])->name('index');
        Route::get('create', [QuestionController::class, 'create'])->name('create');
        Route::post('', [QuestionController::class, 'store'])->name('store');
        Route::get('{question}/edit', [QuestionController::class, 'edit'])->name('edit');
        Route::put('{question}', [QuestionController::class, 'update'])->name('update');
        Route::delete('{question}', [QuestionController::class, 'destroy'])->name('destroy');

        // Admin only routes
        Route::get('pending', [QuestionController::class, 'pending'])->name('pending');
        Route::post('{question}/approve', [QuestionController::class, 'approve'])->name('approve');
        Route::post('{question}/reject', [QuestionController::class, 'reject'])->name('reject');
    });

Route::middleware(['auth', 'admin'])
    ->prefix('categories')
    ->name('categories.')
    ->group(function () {
        Route::get('', [CategoryController::class, 'index'])->name('index');
        Route::get('create', [CategoryController::class, 'create'])->name('create');
        Route::post('', [CategoryController::class, 'store'])->name('store');
        Route::get('{category}/edit', [CategoryController::class, 'edit'])->name('edit');
        Route::put('{category}', [CategoryController::class, 'update'])->name('update');
        Route::delete('{category}', [CategoryController::class, 'destroy'])->name('destroy');
});

require __DIR__.'/auth.php';

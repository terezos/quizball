<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\GameController;
use App\Http\Controllers\GameNotificationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\QuestionController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Game Routes
Route::get('/game', [GameController::class, 'lobby'])->name('game.lobby');
Route::post('/game/create', [GameController::class, 'create'])->name('game.create');
Route::post('/game/join', [GameController::class, 'join'])->name('game.join');
Route::get('/game/check-active', [GameNotificationController::class, 'checkActiveGame'])->middleware('auth')->name('game.checkActive');
Route::get('/game/{game}/wait', [GameController::class, 'wait'])->name('game.wait');
Route::get('/game/{game}/matchmaking', [GameController::class, 'matchmaking'])->name('game.matchmaking');
Route::post('/game/{game}/cancel-matchmaking', [GameController::class, 'cancelMatchmaking'])->name('game.cancelMatchmaking');
Route::get('/game/{game}/check-matchmaking', [GameController::class, 'checkMatchmaking'])->name('game.checkMatchmaking');
Route::get('/game/{game}/play', [GameController::class, 'play'])->name('game.play');
Route::get('/game/{game}/results', [GameController::class, 'results'])->name('game.results');

// Game API Routes
Route::post('/game/{game}/select-category', [GameController::class, 'selectCategory'])->name('game.selectCategory');
Route::post('/game/{game}/select-difficulty', [GameController::class, 'selectDifficulty'])->name('game.selectDifficulty');
Route::post('/game/{game}/submit-answer', [GameController::class, 'submitAnswer'])->name('game.submitAnswer');
Route::post('/game/{game}/forfeit', [GameController::class, 'forfeit'])->name('game.forfeit');
Route::get('/game/{game}/state', [GameController::class, 'getState'])->name('game.state');

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Editor Routes - Question Management
Route::middleware(['auth', 'editor'])->prefix('questions')->name('questions.')->group(function () {
    Route::get('/', [QuestionController::class, 'index'])->name('index');
    Route::get('/create', [QuestionController::class, 'create'])->name('create');
    Route::post('/', [QuestionController::class, 'store'])->name('store');
    Route::get('/{question}/edit', [QuestionController::class, 'edit'])->name('edit');
    Route::put('/{question}', [QuestionController::class, 'update'])->name('update');
    Route::delete('/{question}', [QuestionController::class, 'destroy'])->name('destroy');

    // Admin only routes
    Route::get('/pending', [QuestionController::class, 'pending'])->name('pending');
    Route::post('/{question}/approve', [QuestionController::class, 'approve'])->name('approve');
    Route::post('/{question}/reject', [QuestionController::class, 'reject'])->name('reject');
});

// Admin Routes - Category Management
Route::middleware(['auth', 'admin'])->prefix('categories')->name('categories.')->group(function () {
    Route::get('/', [CategoryController::class, 'index'])->name('index');
    Route::get('/create', [CategoryController::class, 'create'])->name('create');
    Route::post('/', [CategoryController::class, 'store'])->name('store');
    Route::get('/{category}/edit', [CategoryController::class, 'edit'])->name('edit');
    Route::put('/{category}', [CategoryController::class, 'update'])->name('update');
    Route::delete('/{category}', [CategoryController::class, 'destroy'])->name('destroy');
});

require __DIR__.'/auth.php';

<?php

use App\Http\Controllers\GameController;
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
Route::get('/game/{game}/wait', [GameController::class, 'wait'])->name('game.wait');
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
});

require __DIR__.'/auth.php';

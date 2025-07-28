<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\GameController;
use App\Http\Controllers\GameActionController;
use App\Http\Controllers\DashboardController;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/dashboard', DashboardController::class)
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth', 'verified'])->group(function () {
    // Game Lobby & Management
    Route::get('/games', [GameController::class, 'index'])->name('games.index');
    Route::post('/games', [GameController::class, 'store'])->name('games.store');
    Route::get('/games/{game}', [GameController::class, 'show'])->name('games.show');
    Route::post('/games/{game}/join', [GameController::class, 'join'])->name('games.join');
    Route::post('/games/{game}/start', [GameController::class, 'start'])->name('games.start');

    // In-Game Actions
    // All actions are POST because they change game state
    Route::post('/games/{game}/reinforce', [GameActionController::class, 'reinforce'])->name('game.action.reinforce');
    Route::post('/games/{game}/attack', [GameActionController::class, 'attack'])->name('game.action.attack');
    Route::post('/games/{game}/fortify', [GameActionController::class, 'fortify'])->name('game.action.fortify');
    Route::post('/games/{game}/end-attack-phase', [GameActionController::class, 'endAttackPhase'])->name('game.action.endAttackPhase');
});

require __DIR__.'/auth.php';

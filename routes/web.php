<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\CriteriaController;
use App\Http\Controllers\AlternativeController;
use App\Http\Controllers\DecisionController;
use App\Http\Controllers\CriteriaValueController;

// Welcome page for unauthenticated users
Route::get('/', function () {
    return view('welcome');
})->name('welcome');

// Protected routes (require authentication)
Route::middleware(['auth', 'verified'])->group(function () {
    // Dashboard/Home Route
    Route::get('/dashboard', [HomeController::class, 'index'])->name('home');
    
    // Criteria Routes - Admin can manage, User can view
    Route::get('/criteria', [CriteriaController::class, 'index'])->name('criteria.index');
    Route::get('/api/criteria', [CriteriaController::class, 'getCriteriaForAlternative'])->name('criteria.api');
    
    // Admin-only criteria management
    Route::middleware('role:admin')->group(function () {
        Route::get('/criteria/create', [CriteriaController::class, 'create'])->name('criteria.create');
        Route::post('/criteria', [CriteriaController::class, 'store'])->name('criteria.store');
        Route::get('/criteria/{criterion}/edit', [CriteriaController::class, 'edit'])->name('criteria.edit');
        Route::put('/criteria/{criterion}', [CriteriaController::class, 'update'])->name('criteria.update');
        Route::delete('/criteria/{criterion}', [CriteriaController::class, 'destroy'])->name('criteria.destroy');
        Route::get('/criteria/batch', [CriteriaController::class, 'batch'])->name('criteria.batch');
        Route::post('/criteria/batch', [CriteriaController::class, 'batchStore'])->name('criteria.batchStore');
    });

    // Alternatives Routes - Both admin and user can manage
    Route::get('/alternatives', [AlternativeController::class, 'index'])->name('alternatives.index');
    Route::get('/alternatives/create', [AlternativeController::class, 'create'])->name('alternatives.create');
    Route::post('/alternatives', [AlternativeController::class, 'store'])->name('alternatives.store');
    Route::get('/alternatives/{alternative}/edit', [AlternativeController::class, 'edit'])->name('alternatives.edit');
    Route::put('/alternatives/{alternative}', [AlternativeController::class, 'update'])->name('alternatives.update');
    Route::delete('/alternatives/{alternative}', [AlternativeController::class, 'destroy'])->name('alternatives.destroy');
    Route::get('/alternatives/batch', [AlternativeController::class, 'batch'])->name('alternatives.batch');
    Route::post('/alternatives/batch', [AlternativeController::class, 'batchStore'])->name('alternatives.batchStore');

    // Decision Routes - Both admin and user can manage
    Route::get('/decisions', [DecisionController::class, 'index'])->name('decisions.index');
    Route::get('/decisions/calculate', [DecisionController::class, 'calculate'])->name('decisions.calculate');
    Route::post('/decisions/process', [DecisionController::class, 'process'])->name('decisions.process');
    Route::get('/decisions/{decision}/result', [DecisionController::class, 'result'])->name('decisions.result');
    Route::delete('/decisions/{decision}', [DecisionController::class, 'destroy'])->name('decisions.destroy');


});

// Authentication routes
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
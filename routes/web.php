<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\StatusPageController;
use App\Http\Controllers\WebsiteController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [WebsiteController::class, 'index'])->name('dashboard');
    Route::post('/websites', [WebsiteController::class, 'store'])->name('websites.store');
    Route::delete('/websites/{website}', [WebsiteController::class, 'destroy'])->name('websites.destroy');
    Route::patch('/websites/{website}/toggle-monitoring', [WebsiteController::class, 'toggleMonitoring'])->name('websites.toggleMonitoring');
    Route::patch('/websites/{website}/toggle-public', [WebsiteController::class, 'togglePublic'])->name('websites.togglePublic');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::get('/websites/{website}/chart-data', [WebsiteController::class, 'chartData'])->name('websites.chart-data');

Route::get('/status/{slug}', [StatusPageController::class, 'show'])->name('status.show');

require __DIR__.'/auth.php';

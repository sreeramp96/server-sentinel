<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\WebsiteController;
use App\Models\Website;
use App\Notifications\SiteDownNotification;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [WebsiteController::class, 'index'])->name('dashboard');
    Route::post('/websites', [WebsiteController::class, 'store'])->name('websites.store');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::get('/websites/{website}/chart-data', [WebsiteController::class, 'chartData'])
    ->name('websites.chart-data');

Route::get('/test-notification', function () {
    $website = Website::first();
    $check = $website->uptimeChecks()->latest()->first();

    auth()->user()->notify(
        new SiteDownNotification($website, $check)
    );

    return 'Notification sent — check Mailtrap!';
})->middleware('auth');

require __DIR__.'/auth.php';

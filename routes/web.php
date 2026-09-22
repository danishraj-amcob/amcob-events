<?php

use App\Http\Controllers\Admin\AuthController as AdminAuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\RegistrationController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\EventFeedbackController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\SitemapController;
use Illuminate\Support\Facades\Route;

// Public — events
Route::get('/', [EventController::class, 'index'])->middleware('cache.html')->name('home');
Route::get('/filters', [EventController::class, 'filterOptions'])->name('filters');
Route::get('/events/cards', [EventController::class, 'cards'])->name('events.cards');
Route::get('/events/discovery', [EventController::class, 'discovery'])->name('events.discovery');
Route::post('/{slug}/preview-coupon', [EventController::class, 'previewCoupon'])->middleware('throttle:20,1')->name('events.preview-coupon');
Route::post('/{slug}/register', [EventController::class, 'register'])->middleware('throttle:5,1')->name('events.register');
Route::get('/get-the-app', [HomeController::class, 'index'])->middleware('cache.html')->name('grow-your-business');
Route::view('/privacy-policy', 'privacy')->middleware('cache.html')->name('privacy');
Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');

if (app()->environment('local') || config('app.debug')) {
    Route::get('/dev/feedback-preview/{scenario?}', [EventFeedbackController::class, 'preview'])
        ->where('scenario', 'form|already_submitted|invalid|not_available|missing_token')
        ->defaults('scenario', 'form')
        ->name('dev.feedback.preview');
    Route::post('/dev/feedback-preview', [EventFeedbackController::class, 'previewStore'])
        ->name('dev.feedback.preview.store');
}

Route::get('/{slug}/feedback', [EventFeedbackController::class, 'show'])->name('events.feedback');
Route::post('/{slug}/feedback', [EventFeedbackController::class, 'store'])->middleware('throttle:10,1')->name('events.feedback.store');
// Catch-all slug — must stay last among public GET routes
Route::get('/{slug}', [EventController::class, 'show'])->middleware('cache.html')->name('events.show');

// Public site accounts (guard: web) — optional
Route::prefix('account')->name('account.')->group(function () {
    // Route::get('login', ...)->name('login');
    // Route::post('login', ...);
    // Route::get('register', ...)->name('register');
    // Route::post('register', ...);
    // Route::post('logout', ...)->name('logout')->middleware('auth');
});

// Admin panel (guard: admin)
Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('login', [AdminAuthController::class, 'create'])->name('login');
    Route::post('login', [AdminAuthController::class, 'store'])->middleware('throttle:5,1');

    Route::middleware('auth:admin')->group(function () {
        Route::post('logout', [AdminAuthController::class, 'destroy'])->name('logout');
        Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('registrations', [RegistrationController::class, 'index'])->name('registrations.index');
        Route::post('cache/clear', [DashboardController::class, 'clearCache'])->name('cache.clear');
    });
});

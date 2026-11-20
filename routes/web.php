<?php

use App\Http\Controllers\Admin;
use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Route;

/*
| Public website
*/
Route::middleware('track.visit')->group(function () {
    Route::get('/', [HomeController::class, 'index'])->name('home');
});
Route::get('/rates/live', [HomeController::class, 'live'])->middleware('throttle:30,1')->name('rates.live');
Route::get('/history-data', [HomeController::class, 'history'])->name('history.data');
Route::post('/estimates', [HomeController::class, 'saveEstimate'])->middleware('throttle:20,1')->name('estimates.store');

/*
| Admin panel
*/
Route::prefix('admin')->name('admin.')->group(function () {
    Route::middleware('guest')->group(function () {
        Route::get('login', [Admin\AuthController::class, 'showLogin'])->name('login');
        Route::post('login', [Admin\AuthController::class, 'login'])->middleware('throttle:10,1')->name('login.attempt');
    });

    Route::middleware(['auth', 'admin'])->group(function () {
        Route::post('logout', [Admin\AuthController::class, 'logout'])->name('logout');

        Route::get('/', [Admin\DashboardController::class, 'index'])->name('dashboard');

        Route::get('rates', [Admin\RateController::class, 'index'])->name('rates.index');
        Route::post('rates', [Admin\RateController::class, 'store'])->name('rates.store');
        Route::post('rates/fetch', [Admin\RateController::class, 'fetch'])->name('rates.fetch');
        Route::put('rates/auto', [Admin\RateController::class, 'autoSettings'])->name('rates.auto');

        Route::resource('history', Admin\PriceHistoryController::class)->except('show')->parameters(['history' => 'history']);
        Route::resource('purities', Admin\PurityController::class)->except('show');
        Route::resource('countries', Admin\CountryPriceController::class)->except('show');
        Route::resource('faqs', Admin\FaqController::class)->except('show');
        Route::resource('users', Admin\UserController::class)->except('show');

        Route::get('visitors', [Admin\VisitorController::class, 'index'])->name('visitors.index');
        Route::get('visitors/{visitor}', [Admin\VisitorController::class, 'show'])->name('visitors.show');
        Route::delete('visitors/{visitor}', [Admin\VisitorController::class, 'destroy'])->name('visitors.destroy');

        Route::get('visits', [Admin\VisitController::class, 'index'])->name('visits.index');
        Route::delete('visits/purge', [Admin\VisitController::class, 'purge'])->name('visits.purge');

        Route::get('estimates', [Admin\EstimateController::class, 'index'])->name('estimates.index');
        Route::delete('estimates/{estimate}', [Admin\EstimateController::class, 'destroy'])->name('estimates.destroy');

        Route::get('settings', [Admin\SettingController::class, 'edit'])->name('settings.edit');
        Route::put('settings', [Admin\SettingController::class, 'update'])->name('settings.update');
        Route::put('settings/password', [Admin\SettingController::class, 'password'])->name('settings.password');
    });
});

// Laravel's auth middleware redirects guests to the route named "login"
Route::redirect('/login', '/admin/login')->name('login');

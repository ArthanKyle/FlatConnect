<?php

use App\Http\Controllers\PaymentController;
use App\Livewire\Auth\Login;
use App\Livewire\Auth\Register;
use App\Livewire\Client\Dashboard as ClientDashboard;
use App\Livewire\Client\Receipt;
use App\Livewire\Staff\Client as StaffClient;
use App\Livewire\Staff\Dashboard as StaffDashboard;
use App\Livewire\Staff\Logs as StaffLogs;
use App\Livewire\Staff\Payments as StaffPayments;
use App\Livewire\Staff\Settings as StaffSettings;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('login'));


// Public Routes
Route::get('/login', Login::class)->name('login');
Route::get('/register', Register::class)->name('register');

// Staff Routes
Route::middleware(['auth:staff'])->group(function () {
    Route::get('/staff/dashboard', StaffDashboard::class)->name('staff.dashboard');
    Route::get('/staff/client', StaffClient::class)->name('staff.client');
    Route::get('/staff/payments', StaffPayments::class)->name('staff.payments');
    Route::get('/staff/logs', StaffLogs::class)->name('staff.logs');
    Route::get('/staff/settings', StaffSettings::class)->name('staff.settings');
});

// Client Routes
Route::middleware(['auth:client'])->group(function () {
    Route::get('/client/dashboard', ClientDashboard::class)->name('client.dashboard');
    Route::get('/client/receipt/{payment}', Receipt::class)->name('receipt.print');
    Route::get('/client/renew/callback', [PaymentController::class, 'callback'])->name('client.renew.callback');
});

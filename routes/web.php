<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\Auth\Login;
use App\Livewire\Staff\Dashboard as StaffDashboard;
use App\Livewire\Client\Dashboard as ClientDashboard;
use App\Livewire\Staff\Client as StaffClient;
use App\Livewire\Staff\Payments as StaffPayments;
use App\Livewire\Staff\Logs as StaffLogs;
use App\Livewire\Staff\Settings as StaffSettings; 
use App\Livewire\Auth\Register;
use App\Livewire\Auth\VerifyEmail;
use App\Http\Middleware\EnsureClientEmailIsVerified;

Route::get('/', Login::class)->name('login');
Route::get('/register', Register::class)->name('register'); 
Route::get('/verify-email', VerifyEmail::class)->name('verification.notice');

Route::middleware(['auth:staff'])->group(function () {
    Route::get('/staff/dashboard', StaffDashboard::class)->name('staff.dashboard');
    Route::get('/staff/client', StaffClient::class)->name('staff.client');
    Route::get('/staff/payments', StaffPayments::class)->name('staff.payments');
    Route::get('/staff/logs', StaffLogs::class)->name('staff.logs');
    Route::get('/staff/settings', StaffSettings::class)->name('staff.settings');
});

Route::middleware(['auth:client'])->group(function () {
    Route::get('/client/dashboard', ClientDashboard::class)->name('client.dashboard');
});
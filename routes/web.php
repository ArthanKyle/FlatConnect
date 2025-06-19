<?php

use App\Livewire\Auth\Login;
use App\Livewire\Auth\Register;
use App\Livewire\Client\Dashboard as ClientDashboard;
use App\Livewire\Client\Receipt;
use App\Livewire\Staff\Client as StaffClient;
use App\Livewire\Staff\Dashboard as StaffDashboard;
use App\Livewire\Staff\Logs as StaffLogs;
use App\Livewire\Staff\Payments as StaffPayments;
use App\Livewire\Staff\Settings as StaffSettings;
use App\Models\Payment;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// Public Routes
Route::get('/', Login::class)->name('login');
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

    Route::get('/client/renew/callback', function () {
        $client = Auth::guard('client')->user();

        if ($client) {
            // Create payment record
            Payment::create([
                'client_id' => $client->id,
                'amount' => 1000,
                'method' => 'GCash',
                'reference' => 'gcash-' . now()->timestamp,
                'paid_at' => now(),
            ]);

            // Update client status
            $client->update([
                'payment_status' => 'Paid',
                'block_status' => 'Unblocked',
                'next_due_date' => now()->addDays(30),
            ]);

            return redirect()->route('client.dashboard')->with('success', 'Payment received!');
        }

        return redirect()->route('login')->with('error', 'Session expired.');
    })->name('client.renew.callback');
});

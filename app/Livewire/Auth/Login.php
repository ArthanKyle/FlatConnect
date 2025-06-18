<?php

namespace App\Livewire\Auth;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Component;

class Login extends Component
{
    public $email;

    public $password;

    public $remember = false;

    public $error;

    public function login()
    {
        // Validate input
        $this->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // Rate limiting per IP+email
        $key = 'login:'.strtolower($this->email).'|'.request()->ip();

        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);
            $this->error = "Too many attempts. Try again in {$seconds} seconds.";

            return;
        }

        RateLimiter::hit($key, 60); // Lockout time: 60 seconds

        // Attempt login as staff
        if (Auth::guard('staff')->attempt([
            'email' => $this->email,
            'password' => $this->password,
        ], $this->remember)) {
            RateLimiter::clear($key); // Clear rate limit on success
            Log::info('Staff logged in', ['email' => $this->email, 'ip' => request()->ip()]);

            return redirect()->route('staff.dashboard');
        }

        // Attempt login as client
        if (Auth::guard('client')->attempt([
            'email' => $this->email,
            'password' => $this->password,
        ], $this->remember)) {
            RateLimiter::clear($key);
            Log::info('Client logged in', ['email' => $this->email, 'ip' => request()->ip()]);

            return redirect()->route('client.dashboard');
        }

        // Log failed attempt
        Log::warning('Failed login attempt', [
            'email' => $this->email,
            'ip' => request()->ip(),
        ]);

        $this->error = 'Invalid credentials or account not found.';
    }

    public function render()
    {
        return view('livewire.auth.login')->layout('layouts.app', ['title' => 'Login - FlatConnect']);
    }
}

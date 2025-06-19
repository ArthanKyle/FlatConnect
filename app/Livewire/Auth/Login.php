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
        $this->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $ip = request()->ip();
        $key = 'login:' . strtolower($this->email) . '|' . $ip;

        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);

            Log::alert('Brute force lockout triggered', [
                'email' => $this->email,
                'ip' => $ip,
                'attempts' => RateLimiter::attempts($key),
                'cooldown' => $seconds,
            ]);

            // Optionally auto-block IP here
            // app(BlockIpService::class)->block($ip);

            $this->error = "Too many attempts. Try again in {$seconds} seconds.";
            return;
        }

        RateLimiter::hit($key, 60); // 60 seconds lockout time after 5 tries

        // Attempt login as staff
        if (Auth::guard('staff')->attempt([
            'email' => $this->email,
            'password' => $this->password,
        ], $this->remember)) {
            RateLimiter::clear($key);
            Log::info('Staff logged in', ['email' => $this->email, 'ip' => $ip]);

            return redirect()->route('staff.dashboard');
        }

        // Attempt login as client
        if (Auth::guard('client')->attempt([
            'email' => $this->email,
            'password' => $this->password,
        ], $this->remember)) {
            RateLimiter::clear($key);
            Log::info('Client logged in', ['email' => $this->email, 'ip' => $ip]);

            return redirect()->route('client.dashboard');
        }

        // Delay to slow brute force
        usleep(500_000); // 0.5 second delay

        // Log failed attempt
        Log::warning('Failed login attempt', [
            'email' => $this->email,
            'ip' => $ip,
            'attempts' => RateLimiter::attempts($key),
        ]);

        $this->password = ''; // Clear password field for safety
        $this->error = 'Invalid credentials or account not found.';
    }

    public function render()
    {
        return view('livewire.auth.login')->layout('layouts.app', ['title' => 'Login - FlatConnect']);
    }
}

<?php

namespace App\Livewire\Components;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Navbar extends Component
{
    public function logout()
    {
        if (Auth::guard('staff')->check()) {
            $staff = Auth::guard('staff')->user();
            $staff->setRememberToken(null); // Clear remember_token
            $staff->save();

            Auth::guard('staff')->logout();
            session()->invalidate();
            session()->regenerateToken();

            return redirect()->route('login'); // Adjust if you have a dedicated staff login route
        }

        return redirect()->route('login');
    }

    public function render()
    {
        return view('livewire.components.navbar');
    }
}

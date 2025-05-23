<?php


namespace App\Livewire\Auth;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Models\Client;

class VerifyEmail extends Component
{
    public $emailVerified = false;

   public function mount()
    {
        $client = Auth::guard('client')->user();

        if (!$client) {
            return redirect()->to('/'); 
        }

        if ($client->hasVerifiedEmail()) {
            return redirect()->route('client.dashboard');
        }

        $this->emailVerified = false;
    }


    public function resendVerification()
    {
        $client = Auth::guard('client')->user();

        if ($client instanceof Client && !$client->hasVerifiedEmail()) {
            $client->sendEmailVerificationNotification();
            session()->flash('message', 'Verification link sent!');
        }
    }

    public function render()
    {
        $client = Auth::guard('client')->user();

        if ($client && $client->hasVerifiedEmail()) {
            return redirect()->route('client.dashboard');
        }

        return view('livewire.auth.verify-email');
    }
}
<?php

namespace App\Livewire\Client;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class RenewSubscription extends Component
{
    public function redirectToGcash()
    {
        $client = Auth::guard('client')->user();
        $redirectUrl = route('client.renew.callback');

        $response = Http::withBasicAuth(env('PAYMONGO_SECRET_KEY'), '')
            ->post('https://api.paymongo.com/v1/sources', [
                'data' => [
                    'attributes' => [
                        'amount' => 1000 * 100, // centavos
                        'redirect' => [
                            'success' => $redirectUrl,
                            'failed' => $redirectUrl,
                        ],
                        'type' => 'gcash',
                        'currency' => 'PHP',
                    ],
                ],
            ]);

        $data = $response->json();

        if (isset($data['data']['attributes']['redirect']['checkout_url'])) {
            return redirect()->away($data['data']['attributes']['redirect']['checkout_url']);
        }

        session()->flash('error', 'Unable to connect to GCash. Try again later.');
    }

    public function render()
    {
        return view('livewire.client.renew-subscription');
    }
}

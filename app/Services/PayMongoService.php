<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class PayMongoService
{
    protected $baseUrl = 'https://api.paymongo.com/v1';

    public function createGCashSource($amount, $redirectUrl)
    {
        $response = Http::withBasicAuth(env('PAYMONGO_SECRET_KEY'), '')
            ->post("{$this->baseUrl}/sources", [
                'data' => [
                    'attributes' => [
                        'amount' => $amount * 100, // PayMongo uses centavos
                        'redirect' => [
                            'success' => $redirectUrl,
                            'failed' => $redirectUrl,
                        ],
                        'type' => 'gcash',
                        'currency' => 'PHP',
                    ],
                ],
            ]);

        return $response->json();
    }
}

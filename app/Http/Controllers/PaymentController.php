<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Services\PayMongoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    protected PayMongoService $payMongoService;

    public function __construct(PayMongoService $payMongoService)
    {
        $this->payMongoService = $payMongoService;
    }

    public function callback(Request $request)
    {
        $client = Auth::guard('client')->user();

        if (!$client) {
            Log::warning('Payment callback accessed without authenticated client', [
                'ip' => $request->ip(),
                'query' => $request->query()
            ]);
            return redirect()->route('login')->with('error', 'Session expired.');
        }

        // Verify payment source ID from PayMongo
        $sourceId = $request->query('source_id');
        
        if (!$sourceId) {
            Log::error('Payment callback missing source_id', [
                'client_id' => $client->id,
                'query' => $request->query()
            ]);
            return redirect()->route('client.dashboard')
                ->with('error', 'Invalid payment reference.');
        }

        // Verify the payment with PayMongo API
        try {
            $paymentSource = $this->payMongoService->verifySource($sourceId);
            
            if (!$paymentSource || $paymentSource['data']['attributes']['status'] !== 'chargeable') {
                Log::warning('Payment verification failed', [
                    'client_id' => $client->id,
                    'source_id' => $sourceId,
                    'status' => $paymentSource['data']['attributes']['status'] ?? 'unknown'
                ]);
                
                return redirect()->route('client.dashboard')
                    ->with('error', 'Payment verification failed. Contact support if amount was deducted.');
            }

            $amount = $paymentSource['data']['attributes']['amount'] / 100; // Convert from centavos

            // Create payment record
            Payment::create([
                'client_id' => $client->id,
                'amount' => $amount,
                'method' => 'GCash',
                'reference' => $sourceId,
                'paid_at' => now(),
            ]);

            // Update client status
            $client->update([
                'payment_status' => 'Paid',
                'block_status' => 'Unblocked',
                'next_due_date' => now()->addDays(30),
            ]);

            Log::info('Payment successfully processed', [
                'client_id' => $client->id,
                'amount' => $amount,
                'source_id' => $sourceId
            ]);

            return redirect()->route('client.dashboard')
                ->with('success', 'Payment received! Your subscription has been renewed.');

        } catch (\Exception $e) {
            Log::error('Payment callback exception', [
                'client_id' => $client->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->route('client.dashboard')
                ->with('error', 'Payment processing error. Please contact support.');
        }
    }
}

<?php

namespace App\Livewire\Client;

use Livewire\Component;
use App\Models\Payment;
use Barryvdh\DomPDF\Facade\Pdf;

class Receipt extends Component
{
    public $payment;
    public $clientIp;

    public function mount(Payment $payment)
    {
        $this->payment = $payment;
        $this->clientIp = request()->ip();
    }

    public function downloadPdf()
    {
        $pdf = Pdf::loadView('pdf.receipt', [
            'payment' => $this->payment,
            'clientIp' => $this->clientIp,
        ]);

        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->stream();
        }, 'receipt-flat-connect.pdf');
    }

    public function render()
    {
        return view('livewire.client.receipt')
            ->layout('layouts.app', ['title' => 'Payment Receipt']); 
    }
}

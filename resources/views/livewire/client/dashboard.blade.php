<!-- resources/views/livewire/client/dashboard.blade.php -->

<div class="min-h-screen flex items-center justify-center bg-[#0B0F1C]">
    <div class="bg-white rounded-xl shadow-lg w-full max-w-sm p-6">
        <h2 class="text-lg font-bold text-black mb-4">CLIENT PORTAL</h2>

        @if($clientIp === $connectedIp)
            <div class="bg-gray-200 p-3 rounded-lg mb-3 text-sm text-center">
                Your connected until <strong>{{ $nextDueDate->format('F d, Y') }}</strong>
            </div>

            <div class="mb-2 text-sm">
                <strong>IP Address:</strong> {{ $connectedIp }}
            </div>

            <div class="border border-gray-300 rounded overflow-hidden">
                <div class="bg-[#0B0F1C] text-white px-4 py-2 font-semibold text-sm">Payment History</div>
                <table class="w-full text-sm text-center">
                    <thead class="bg-gray-300">
                        <tr>
                            <th class="p-2">Date</th>
                            <th class="p-2">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($payments as $payment)
                            <tr class="bg-white border-t">
                                <td class="py-2">{{ $payment->date->format('F d, Y') }}</td>
                                <td class="py-2">PHP {{ number_format($payment->amount, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-4 text-center">
                <button class="bg-[#0B0F1C] text-white px-6 py-2 rounded-md hover:bg-gray-800">Renew</button>
            </div>
        @else
            <p class="text-red-500 text-center">Not connected to your registered device.</p>
        @endif
    </div>
</div>

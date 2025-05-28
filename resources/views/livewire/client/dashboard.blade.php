<div class="min-h-screen flex items-center justify-center bg-[#0B0F1C]">
    <div class="bg-white rounded-2xl shadow-lg w-full max-w-xs px-6 py-8">
        <h2 class="text-xl font-bold text-black mb-5 text-center">CLIENT PORTAL</h2>

        @if($clientIp === $connectedIp)
            @php
               $daysLeft = floor(\Carbon\Carbon::now()->diffInDays($nextDueDate, false));
            @endphp

            <div class="text-center text-sm rounded-xl py-2 px-4 mb-3 font-medium
                {{ $daysLeft <= 5 ? 'bg-red-200 text-red-800' : 'bg-gray-200 text-black' }}">
                Your connected until 
                <strong>{{ $nextDueDate->format('F d, Y') }}</strong>
                @if ($daysLeft <= 5 && $daysLeft >= 0)
                    <br><span class="text-xs">⚠ {{ $daysLeft }} day{{ $daysLeft === 1 ? '' : 's' }} left</span>
                @endif
            </div>

            <p class="text-sm mb-4 text-center">
                <strong>IP Address:</strong> {{ $connectedIp }}
            </p>

            <div class="border border-gray-300 rounded-xl overflow-hidden mb-4">
                <div class="bg-[#0B0F1C] text-white text-sm font-semibold px-4 py-2 text-left">
                    Payment History
                </div>
                <table class="w-full text-sm text-left">
                    <thead class="bg-[#415169] text-white">
                        <tr>
                            <th class="p-2">Date</th>
                            <th class="p-2">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($payments as $payment)
                            <tr class="bg-white even:bg-gray-100 border-t">
                                <td class="p-2">
                                    {{ $payment->paid_at ? $payment->paid_at->format('M d, Y') : '—' }}
                                </td>
                                <td class="p-2">PHP {{ number_format($payment->amount, 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="2" class="text-center p-2 text-gray-500">No payment history yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @livewire('client.renew-subscription')

        @else
            <p class="text-red-500 text-center text-sm">Not connected to your registered device.</p>
        @endif
    </div>
</div>

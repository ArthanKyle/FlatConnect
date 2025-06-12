<div class="min-h-screen flex items-center justify-center bg-[#0B0F1C] px-4">
    <div class="bg-white rounded-2xl shadow-lg w-full max-w-lg px-8 py-10">
        <h2 class="text-2xl font-bold text-black mb-5 text-center">CLIENT PORTAL</h2>

        @php
            $daysLeft = floor(\Carbon\Carbon::now()->diffInDays($nextDueDate, false));
        @endphp

        <div class="text-center text-sm rounded-xl py-2 px-4 mb-4 font-medium
            {{ $daysLeft <= 5 ? 'bg-red-200 text-red-800' : 'bg-gray-200 text-black' }}">
            You're connected until 
            <strong>{{ $nextDueDate->format('F d, Y') }}</strong>
            @if ($daysLeft <= 5 && $daysLeft >= 0)
                <br><span class="text-xs">⚠ {{ $daysLeft }} day{{ $daysLeft === 1 ? '' : 's' }} left</span>
            @endif
        </div>

        <p class="text-sm mb-4 text-center">
            <strong>IP Address:</strong> {{ $connectedIp }}
        </p>

        <div class="border border-gray-300 rounded-xl overflow-hidden mb-6">
            <div class="bg-[#0B0F1C] text-white text-sm font-semibold px-4 py-2 text-left">
                Payment History
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left table-auto">
                    <thead class="bg-[#415169] text-white">
                        <tr>
                            <th class="p-3">Date</th>
                            <th class="p-3">Amount</th>
                            <th class="p-3">Status</th>
                            <th class="p-3">Print Receipt</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($payments as $payment)
                            <tr class="bg-white even:bg-gray-100 border-t">
                                <td class="p-3">
                                    {{ $payment->paid_at ? $payment->paid_at->format('M d, Y') : '—' }}
                                </td>
                                <td class="p-3">PHP {{ number_format($payment->amount, 2) }}</td>
                                <td class="p-3">
                                    @if(!empty($payment->paid_at))
                                        <span class="text-green-500">Paid</span>
                                    @else
                                        <span class="text-red-500">Unpaid</span>
                                    @endif
                                </td>
                                <td class="p-3">
                                    @if(!empty($payment->paid_at))
                                        <div class="flex justify-center">
                                            <a href="{{ route('receipt.print', $payment->id) }}" target="_blank"
                                               class="text-xs sm:text-sm bg-[#0B0F1C] text-white px-3 py-1 rounded hover:bg-gray-800 whitespace-nowrap">
                                                Print Receipt
                                            </a>
                                        </div>
                                    @else
                                        —
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center p-3 text-gray-500">No payment history yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @livewire('client.renew-subscription')

        <div class="mt-4">
        <button wire:click="logout"
            class="bg-red-600 text-white px-6 py-2 rounded-md hover:bg-red-800">
            Logout
        </button>
        </div>
    </div>
</div>

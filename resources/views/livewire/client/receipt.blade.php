<div class="max-w-lg mx-auto p-8 bg-white shadow-2xl rounded-lg border" id="printable-receipt">
    {{-- Logo --}}
    <div class="flex justify-center mb-6">
        <img src="{{ asset('flat_connect_logo.png') }}" alt="FlatConnect Logo" class="h-16">
    </div>

    <h2 class="text-2xl font-semibold text-center text-gray-800 mb-4 tracking-wide">Official Payment Receipt</h2>
    <hr class="my-4 border-gray-300">

    {{-- Receipt Details --}}
    <div class="space-y-2 text-gray-700 text-sm leading-relaxed">
        <p><span class="font-semibold">Client Name:</span> {{ $payment->client->first_name }} {{ $payment->client->last_name }}</p>
        <p><span class="font-semibold">Reference No:</span> {{ $payment->reference }}</p>
        <p><span class="font-semibold">Amount Paid:</span> <span class="text-green-600 font-bold">PHP {{ number_format($payment->amount, 2) }}</span></p>
        <p><span class="font-semibold">Date Paid:</span> {{ $payment->paid_at ? $payment->paid_at->format('F d, Y') : 'N/A' }}</p>
        <p><span class="font-semibold">Client IP:</span> {{ $clientIp }}</p>
    </div>

    {{-- Footer --}}
    <p class="text-center mt-6 text-sm text-gray-500 italic">Thank you for your payment. Your connection is now active.</p>

    {{-- Action Buttons --}}
    <div class="mt-8 text-center space-x-2">
        <button onclick="printReceipt()" class="bg-blue-600 hover:bg-blue-700 text-white font-medium px-5 py-2 rounded shadow">
            🖨️ Print
        </button>
        <button wire:click="downloadPdf" class="bg-green-600 hover:bg-green-700 text-white font-medium px-5 py-2 rounded shadow">
            ⬇️ Download PDF
        </button>
    </div>
</div>

{{-- Print Script --}}
<script>
    function printReceipt() {
        const content = document.getElementById('printable-receipt').innerHTML;
        const win = window.open('', '', 'width=800,height=600');
        win.document.write('<html><head><title>Receipt</title><style>body{font-family:sans-serif; padding:20px;}</style></head><body>' + content + '</body></html>');
        win.document.close();
        win.print();
    }
</script>

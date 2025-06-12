<div>
    <div class="flex">
        <!-- Include Navbar -->
        <livewire:components.navbar />

        <!-- Main Content -->
        <div class="flex-1 p-6 bg-gray-50 min-h-screen">
            <!-- Page Title -->
            <h1 class="text-2xl font-bold mb-4">Payments Dashboard</h1>

            <!-- Filters -->
            <div class="mb-4 flex flex-col sm:flex-row items-start sm:items-center gap-4">
                <input type="text" wire:model.debounce.300ms="search" placeholder="Search by name or MAC"
                    class="border rounded px-3 py-2 w-full sm:w-64" />

                <select wire:model="statusFilter" class="border rounded px-3 py-2">
                    <option value="">All</option>
                    <option value="Paid">Paid</option>
                    <option value="Unpaid">Unpaid</option>
                </select>

                <div wire:loading class="text-sm text-gray-500">Filtering...</div>
            </div>

            <!-- Payments Table -->
            <div class="overflow-x-auto">
                <table class="min-w-full border-collapse border border-gray-300 text-sm">
                    <thead>
                        <tr class="bg-gray-800 text-white">
                            <th class="border px-4 py-2">Client Name</th>
                            <th class="border px-4 py-2">MAC Address</th>
                            <th class="border px-4 py-2">IP Address</th>
                            <th class="border px-4 py-2">Status</th>
                            <th class="border px-4 py-2">Next Due</th>
                            <th class="border px-4 py-2">Notice</th>
                            <th class="border px-4 py-2">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($clients as $client)
                            <tr class="{{ $client->payment_status === 'Unpaid' ? 'bg-red-100' : 'bg-green-50' }}">
                                <td class="border px-4 py-2">{{ $client->fullname }}</td>
                                <td class="border px-4 py-2">{{ $client->mac_address }}</td>
                                <td class="border px-4 py-2">{{ $client->ip_address }}</td>
                                <td class="border px-4 py-2 font-semibold text-sm {{ $client->payment_status === 'Unpaid' ? 'text-red-600' : 'text-green-600' }}">
                                    {{ $client->payment_status }}
                                </td>
                                <td class="border px-4 py-2">{{ $client->next_due_formatted ?? '-' }}</td>
                                <td class="border px-4 py-2 text-xs text-gray-600">{{ $client->due_notice }}</td>
                                <td class="border px-4 py-2 space-x-1">
                                    @if ($client->payment_status === 'Unpaid')
                                        <button wire:click="markAsPaid({{ $client->id }})"
                                            class="bg-green-500 hover:bg-green-600 text-white px-3 py-1 rounded shadow-sm">
                                            Mark Paid
                                        </button>
                                    @endif
                                    <button wire:click="markAsUnpaid({{ $client->id }})"
                                        class="bg-yellow-500 hover:bg-yellow-600 text-white px-3 py-1 rounded shadow-sm">
                                        Mark Unpaid
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4 text-gray-500">No clients found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

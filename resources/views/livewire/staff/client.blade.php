<div class="flex">
    <!-- Include Navbar -->
    <livewire:components.navbar />

    <!-- Main Content -->
    <div class="flex-1 p-6">
        <!-- Welcome Message -->
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-800">Welcome, {{ Auth::user()->name }}!</h1>
        </div>

       <div class="p-4">
    <div class="flex items-center mb-4 space-x-4">
        <input
            wire:model.debounce.300ms="search"
            type="search"
            placeholder="Search by repeater name or MAC"
            class="border rounded px-3 py-2 w-64"
        />

        <label class="flex items-center space-x-2">
            <input type="checkbox" wire:model="showOnlyBlocked" class="form-checkbox" />
            <span>Show Only Blocked</span>
        </label>
    </div>

    <table class="min-w-full border-collapse border border-gray-300">
        <thead>
            <tr class="bg-gray-800 text-white">
                <th class="border border-gray-300 px-4 py-2">Client's Name</th>
                <th class="border border-gray-300 px-4 py-2">Repeater Name</th>
                <th class="border border-gray-300 px-4 py-2">MAC Address</th>
                <th class="border border-gray-300 px-4 py-2">IP Address</th>
                <th class="border border-gray-300 px-4 py-2">Status</th>
                <th class="border border-gray-300 px-4 py-2">Next Due Date</th>
                <th class="border border-gray-300 px-4 py-2">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($clients as $client)
                <tr class="{{ $client['blocked'] ? 'bg-red-100' : '' }}">
                    <td class="border border-gray-300 px-4 py-2">{{ $client['fullname'] ?? '-' }}</td>

                    <td class="border border-gray-300 px-4 py-2">
                        @if ($editingClientId === $client['id'])
                            <input
                                type="text"
                                wire:model.defer="editRepeaterName"
                                class="border rounded px-2 py-1 w-full"
                            />
                        @else
                            {{ $client['repeater_name'] ?? '-' }}
                        @endif
                    </td>

                    <td class="border border-gray-300 px-4 py-2">{{ $client['mac_address'] }}</td>
                    <td class="border border-gray-300 px-4 py-2">{{ $client['ip_address'] }}</td>
                    <td class="border border-gray-300 px-4 py-2">
                        {{ $client['blocked'] ? 'Blocked' : 'Connected' }}
                    </td>

                    <td class="border border-gray-300 px-4 py-2">
                        @if ($editingClientId === $client['id'])
                            <input
                                type="date"
                                wire:model.defer="editNextDueDate"
                                class="border rounded px-2 py-1"
                            />
                        @else
                            {{ $client['next_due_formatted'] ?? '-' }}
                        @endif
                    </td>

                    <td class="border border-gray-300 px-4 py-2 space-x-2">
                        @if ($editingClientId === $client['id'])
                            <button
                                wire:click="saveEdit"
                                class="bg-green-500 hover:bg-green-600 text-white px-3 py-1 rounded"
                            >Save</button>
                            <button
                                wire:click="cancelEdit"
                                class="bg-gray-400 hover:bg-gray-500 text-white px-3 py-1 rounded"
                            >Cancel</button>
                        @else
                            <button
                                wire:click="editClient({{ $client['id'] }})"
                                class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded"
                            >Edit</button>

                            @if ($client['blocked'])
                               <button
                                    wire:click="unblock('{{ $client['mac_address'] }}')"
                                    wire:loading.attr="disabled"
                                    wire:target="unblock('{{ $client['mac_address'] }}')"
                                    class="bg-yellow-500 hover:bg-yellow-600 text-white px-3 py-1 rounded"
                                >Unblock</button>
                            @else
                                <button
                                    wire:click="block('{{ $client['mac_address'] }}')"
                                    wire:loading.attr="disabled"
                                    wire:target="block('{{ $client['mac_address'] }}')"
                                    class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded"
                                >Block</button>
                            @endif
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center p-4">No clients found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="mt-4 flex justify-between items-center">
        <div>
            Showing
            {{ ($currentPage - 1) * $perPage + 1 }}
            to
            {{ min($currentPage * $perPage, $total) }}
            of
            {{ $total }}
            clients
        </div>

        <div class="space-x-2">
            @php
                $totalPages = ceil($total / $perPage);
            @endphp

            @for ($page = 1; $page <= $totalPages; $page++)
                <button
                    wire:click="gotoPage({{ $page }})"
                    class="px-3 py-1 rounded {{ $page === $currentPage ? 'bg-blue-600 text-white' : 'bg-gray-200' }}"
                >
                    {{ $page }}
                </button>
            @endfor
        </div>
    </div>
</div>

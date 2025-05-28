<div class="flex flex-col md:flex-row min-h-screen">
    <!-- Include Navbar -->
    <livewire:components.navbar />

    <!-- Main Content -->
    <div class="flex-1 p-4 md:p-6 bg-gray-50 overflow-x-hidden">

        <!-- Welcome Message -->
        <div class="mb-4 md:mb-6">
            <h1 class="text-2xl font-bold text-gray-800">Welcome, {{ Auth::user()->name }}!</h1>
        </div>

        <div>
            <div class="mb-4 flex flex-col sm:flex-row sm:items-center sm:justify-between space-y-2 sm:space-y-0">
                <input
                    id="instantClientSearch"
                    type="text"
                    wire:model.debounce.500ms="search"
                    placeholder="Search repeater or MAC..."
                    class="border px-3 py-2 rounded w-full sm:w-72"
                />
                <label class="inline-flex items-center space-x-2 text-gray-700">
                    <input id="filterBlockedCheckbox" type="checkbox" wire:model="showOnlyBlocked" class="form-checkbox" />
                    <span>Show Only Blocked</span>
                </label>
            </div>

            @if (session()->has('success'))
                <div class="bg-green-100 text-green-800 p-2 mb-2 rounded">{{ session('success') }}</div>
            @endif
            @if (session()->has('error'))
                <div class="bg-red-100 text-red-800 p-2 mb-2 rounded">{{ session('error') }}</div>
            @endif

            <!-- Responsive table wrapper -->
            <div class="overflow-x-auto border border-gray-300 rounded">
                <table id="clientTable" class="min-w-[900px] w-full border-collapse border border-gray-300">
                    <thead>
                        <tr class="bg-gray-800 text-white whitespace-nowrap">
                            <th class="border border-gray-300 px-4 py-2 text-left">Client Name</th>
                            <th class="border border-gray-300 px-4 py-2 text-left">Building</th>
                            <th class="border border-gray-300 px-4 py-2 text-left">Apartment</th>
                            <th class="border border-gray-300 px-4 py-2 text-left">Repeater's Name</th>
                            <th class="border border-gray-300 px-4 py-2 text-left">MAC Address</th>
                            <th class="border border-gray-300 px-4 py-2 text-left">IP Address</th>
                            <th class="border border-gray-300 px-4 py-2 text-left">Block Status</th>
                            <th class="border border-gray-300 px-4 py-2 text-left">Enforcement Status</th>
                            <th class="border border-gray-300 px-4 py-2 text-left">Next Due Date</th>
                            <th class="border border-gray-300 px-4 py-2 text-left">Status</th>
                            <th class="border border-gray-300 px-4 py-2 text-left">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach ($clients as $client)
                        <tr class="bg-white border border-black whitespace-nowrap">
                            <td class="border border-black px-4 py-2">{{ $client['fullname'] }}</td>
                            <td class="border border-black px-4 py-2">{{ $client['building'] }}</td>
                            <td class="border border-black px-4 py-2">{{ $client['apartment_number'] }}</td>
                            <td class="border border-black px-4 py-2">{{ $client['repeater_name'] }}</td>
                            <td class="border border-black px-4 py-2">{{ $client['mac_address'] }}</td>
                            <td class="border border-black px-4 py-2">{{ $client['ip_address'] }}</td>
                            
                            <td class="border border-black px-4 py-2">
                                @if ($client['block_status'] === 'blocked')
                                    <span class="text-red-600 font-bold">Blocked</span>
                                @elseif ($client['block_status'] === 'pending_block')
                                    <span class="text-yellow-600">Pending Block</span>
                                @else
                                    <span class="text-green-600">Unblocked</span>
                                @endif
                            </td>
                            <td class="border border-black px-4 py-2">{{ ucfirst($client['enforcement_status']) }}</td>
                            <td class="border border-black px-4 py-2">
                                {{ !empty($client['next_due_date']) ? \Carbon\Carbon::parse($client['next_due_date'])->format('F j, Y') : '-' }}
                            </td>

                            <td class="border border-black px-4 py-2">
                                @if ($client['blocked'])
                                    <span class="inline-block px-3 py-1 rounded text-xs font-bold bg-red-200 text-red-800">Blocked</span>
                                @else
                                    @if ($client['repeater_status'] === 'offline')
                                        <span class="inline-block px-3 py-1 rounded text-xs font-bold bg-gray-200 text-gray-700">Offline</span>
                                    @else
                                        <span class="inline-block px-3 py-1 rounded text-xs font-bold bg-green-200 text-green-800">Connected</span>
                                    @endif
                                @endif
                            </td>
                            <td class="border border-black px-4 py-2 space-x-1 whitespace-normal">
                                @if ($client['block_status'] !== 'blocked')
                                    <button wire:click="block('{{ $client['mac_address'] }}')" class="bg-red-600 hover:bg-red-700 text-white px-2 py-1 rounded text-xs">Block</button>
                                @else
                                    <button wire:click="unblock('{{ $client['mac_address'] }}')" class="bg-green-600 hover:bg-green-700 text-white px-2 py-1 rounded text-xs">Unblock</button>
                                @endif
                                <button wire:click="editClient({{ $client['id'] }})" class="bg-blue-600 hover:bg-blue-700 text-white px-2 py-1 rounded text-xs">Edit</button>
                            </td>
                        </tr>
                    @endforeach

                    <!-- No clients found row -->
                    <tr id="noClientsRow" style="display:none;">
                        <td colspan="11" class="text-center text-gray-500 italic py-4">
                            No clients found matching your criteria.
                        </td>
                    </tr>
                </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            <div class="mt-4 flex flex-wrap justify-start gap-2">
                @for ($i = 1; $i <= $totalPages; $i++)
                    <button wire:click="gotoPage({{ $i }})"
                        class="px-3 py-1 rounded {{ $currentPage === $i ? 'bg-blue-600 text-white' : 'bg-gray-200' }}">
                        {{ $i }}
                    </button>
                @endfor
            </div>

            {{-- Edit Modal (simple inline version) --}}
            @if ($editingClientId)
                <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4">
                    <div class="bg-white p-6 rounded shadow max-w-lg w-full">
                        <h2 class="text-xl font-bold mb-4">Edit Client</h2>

                        <label class="block mb-2">
                            Repeater Name:
                            <input type="text" wire:model.defer="editRepeaterName" class="border p-1 w-full" />
                        </label>

                        <label class="block mb-2">
                            First Name:
                            <input type="text" wire:model.defer="editFirstName" class="border p-1 w-full" />
                        </label>

                        <label class="block mb-2">
                            Last Name:
                            <input type="text" wire:model.defer="editLastName" class="border p-1 w-full" />
                        </label>

                        <label class="block mb-2">
                            Apartment Number:
                            <input type="text" wire:model.defer="editApartmentNumber" class="border p-1 w-full" />
                        </label>

                        <label class="block mb-2">
                            Building:
                            <select wire:model.defer="editBuilding" class="border p-1 w-full">
                                <option value="">-- Select Building --</option>
                                <option value="Princeton">Princeton</option>
                                <option value="Wharton">Wharton</option>
                                <option value="Harvard">Harvard</option>
                            </select>
                        </label>

                        <label class="block mb-4">
                            Next Due Date:
                            <input type="date" wire:model.defer="editNextDueDate" class="border p-1 w-full" />
                        </label>

                        <div class="flex justify-end space-x-2">
                            <button wire:click="cancelEdit" class="px-4 py-2 bg-gray-300 rounded">Cancel</button>
                            <button wire:click="saveEdit" class="px-4 py-2 bg-blue-600 text-white rounded">Save</button>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>


<!-- JS: Search & Filter -->
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const searchInput = document.getElementById('instantClientSearch');
        const blockedCheckbox = document.getElementById('filterBlockedCheckbox');
        const table = document.getElementById('clientTable');

        function filterRows() {
            const query = searchInput.value.toLowerCase();
            const showOnlyBlocked = blockedCheckbox.checked;
            let visibleCount = 0;

            table.querySelectorAll('tbody tr').forEach(row => {
                // Skip the "noClientsRow" itself
                if (row.id === 'noClientsRow') return;

                const text = row.textContent.toLowerCase();
                // Check for blocked class added in row for filtering
                const isBlocked = row.classList.contains('blocked-row');

                const matchesSearch = text.includes(query);
                const matchesBlocked = showOnlyBlocked ? isBlocked : true;

                if (matchesSearch && matchesBlocked) {
                    row.style.display = '';
                    visibleCount++;
                } else {
                    row.style.display = 'none';
                }
            });

            // Show or hide the "No clients found" row
            const noClientsRow = document.getElementById('noClientsRow');
            noClientsRow.style.display = visibleCount === 0 ? '' : 'none';
        }

        searchInput.addEventListener('input', filterRows);
        blockedCheckbox.addEventListener('change', filterRows);

        // Also run filter every 10 seconds to sync with Livewire poll updates
        setInterval(filterRows, 10000);

        filterRows();
    });
</script>

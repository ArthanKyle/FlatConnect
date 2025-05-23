<div class="flex">
    <!-- Include Navbar -->
    <livewire:components.navbar />

    <!-- Main Content -->
    <div class="flex-1 p-6">
        <!-- Welcome Message -->
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-800">Welcome, {{ Auth::user()->name }}!</h1>
        </div>

        <!-- Search Bar -->
        <div class="mb-6">
            <input 
                type="text" 
                placeholder="Enter Client Name" 
                class="w-full max-w-sm px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
            />
        </div>

        <!-- Table -->
        <div class="overflow-x-auto">
            <table class="table-auto w-full border-collapse border border-gray-300">
                <thead>
                    <tr class="bg-gray-800 text-white">
                        <th class="border border-gray-300 px-4 py-2">Client Name</th>
                        <th class="border border-gray-300 px-4 py-2">MAC Address</th>
                        <th class="border border-gray-300 px-4 py-2">IP Address</th>
                        <th class="border border-gray-300 px-4 py-2">Status</th>
                        <th class="border border-gray-300 px-4 py-2">Next Due Date</th>
                    </tr>
                </thead>
                <tbody>
                   @foreach ($clients as $client)
                    <tr>
                        <td>{{ $client['hostname'] }}</td>
                        <td>{{ $client['mac'] }}</td>
                        <td>{{ $client['ip'] }}</td>
                        <td>{{ $client['ssid'] }}</td>
                        <td>
                            <button wire:click="block('{{ $client['mac'] }}')" class="btn btn-danger">Block</button>
                            <button wire:click="unblock('{{ $client['mac'] }}')" class="btn btn-success">Unblock</button>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
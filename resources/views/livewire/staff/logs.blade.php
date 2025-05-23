<div class="flex">
    <!-- Include Navbar -->
    <livewire:components.navbar />

    <!-- Main Content -->
    <div class="flex-1 p-6">

            <div class="p-6 bg-white shadow rounded-lg">
            <h2 class="text-xl font-semibold mb-4">Recent Admin Logs</h2>

            @if ($logs->isEmpty())
                <p class="text-gray-500">No logs found.</p>
            @else
                <table class="min-w-full text-sm border border-gray-300 rounded-md overflow-hidden">
                    <thead class="bg-gray-100 text-gray-700 uppercase text-xs font-semibold">
                        <tr>
                            <th class="px-4 py-2 text-left">Action</th>
                            <th class="px-4 py-2 text-left">Timestamp</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach ($logs as $log)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-2">{{ $log->action }}</td>
                                <td class="px-4 py-2 text-gray-500">{{ $log->created_at->format('Y-m-d H:i:s') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                <div class="mt-4">
                    {{ $logs->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
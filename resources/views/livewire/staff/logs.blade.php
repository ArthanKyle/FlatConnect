<div class="flex">
    <!-- Include Navbar -->
    <livewire:components.navbar />

    <!-- Main Content -->
    <div class="flex-1 p-6">
        <div class="p-4 bg-white shadow rounded-lg">
            <h2 class="text-2xl font-bold mb-6 text-gray-800">Recent Admin Logs</h2>

            @if ($logs->isEmpty())
                <p class="text-gray-500 italic">No logs found.</p>
            @else
                <table class="min-w-full border-collapse border border-gray-300">
                    <thead>
                        <tr class="bg-gray-800 text-white">
                            <th class="border border-gray-300 px-4 py-2 text-left">Action</th>
                            <th class="border border-gray-300 px-4 py-2 text-left">Details</th>
                            <th class="border border-gray-300 px-4 py-2 text-left">Timestamp</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($logs as $log)
                            <tr class="hover:bg-gray-100">
                                <td class="border border-gray-300 px-4 py-2">{{ $log->action }}</td>
                                <td class="border border-gray-300 px-4 py-2 text-gray-700">{{ $log->details }}</td>
                                <td class="border border-gray-300 px-4 py-2 text-gray-700 whitespace-nowrap">
                                    {{ $log->created_at->format('M d, Y h:i A') }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                <div class="mt-4 flex justify-end">
                    {{ $logs->links() }}
                </div>
            @endif
        </div>
    </div>
</div>

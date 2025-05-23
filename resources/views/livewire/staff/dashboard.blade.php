<div class="flex min-h-screen bg-gray-100">
    <!-- Include Navbar -->
    <livewire:components.navbar />

    <!-- Main Content -->
    <div class="flex-1 px-10 py-8">
        <!-- Welcome -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-800">Welcome, {{ Auth::user()->name }}!</h1>
            <p class="text-gray-600 mt-1">Here's a quick overview of your network status</p>
        </div>

        <!-- Summary Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6">
            <!-- Paid Clients -->
            <div class="bg-white rounded-xl shadow-md p-6 hover:shadow-xl transition-all duration-300">
                <div class="flex items-center justify-between mb-3">
                    <div class="text-pink-600 bg-pink-100 p-2 rounded-full">
                        <!-- Credit Card Icon -->
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2 7h20M2 11h20M2 15h8" />
                        </svg>
                    </div>
                    <a href="#" class="text-sm text-blue-600 hover:underline">View</a>
                </div>
                <h2 class="text-4xl font-bold text-gray-800">{{ $paidCount }}</h2>
                <p class="text-gray-500 mt-1">Paid Clients</p>
            </div>

            <!-- Unpaid Clients -->
            <div class="bg-white rounded-xl shadow-md p-6 hover:shadow-xl transition-all duration-300">
                <div class="flex items-center justify-between mb-3">
                    <div class="text-yellow-600 bg-yellow-100 p-2 rounded-full">
                        <!-- Clock Icon -->
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6l4 2" />
                            <circle cx="12" cy="12" r="10" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                    </div>
                    <a href="#" class="text-sm text-blue-600 hover:underline">View</a>
                </div>
                <h2 class="text-4xl font-bold text-gray-800">{{ $unpaidCount }}</h2>
                <p class="text-gray-500 mt-1">Unpaid Clients</p>
            </div>

            <!-- Repeaters -->
            <div class="bg-white rounded-xl shadow-md p-6 hover:shadow-xl transition-all duration-300">
                <div class="flex items-center justify-between mb-3">
                    <div class="text-green-600 bg-green-100 p-2 rounded-full">
                        <!-- WiFi Icon -->
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.5 16.5a3.5 3.5 0 016.9 0M2.05 8.81a14 14 0 0119.9 0M5.88 12.7a9 9 0 0112.24 0M12 20h.01" />
                        </svg>
                    </div>
                    <a href="#" class="text-sm text-blue-600 hover:underline">View</a>
                </div>
                <h2 class="text-4xl font-bold text-gray-800">{{ $repeaterCount }}</h2>
                <p class="text-gray-500 mt-1">Repeaters</p>
            </div>
        </div>
    </div>
</div>

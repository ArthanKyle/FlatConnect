<div class="min-h-screen bg-gray-900 text-white w-64 flex flex-col">
    <!-- Logo Section -->
    <div class="text-center">
        <img src="{{ asset('flat_connect_logo.png') }}" alt="FLATCONNECT Logo" class="mx-auto h-32 w-auto">
    </div>

    <!-- Navigation Links -->
    <nav class="flex-1">
        <ul class="space-y-4">
            <!-- Dashboard Link -->
            <li>
                <a href="{{ route('staff.dashboard') }}" 
                   class="flex items-center px-6 py-3 rounded-md {{ request()->routeIs('staff.dashboard') ? 'bg-blue-600' : 'hover:bg-gray-700' }}">
                    <span class="mr-3">
                        <img src="{{ asset('icons/dashboard.svg') }}" alt="Dashboard Icon" class="h-8 w-8">
                    </span>
                    Dashboard
                </a>
            </li>

            <!-- Client Link -->
            <li>
                <a href="{{ route('staff.client') }}" 
                   class="flex items-center px-6 py-3 rounded-md {{ request()->routeIs('staff.client') ? 'bg-blue-600' : 'hover:bg-gray-700' }}">
                    <span class="mr-3">
                        <img src="{{ asset('icons/client.svg') }}" alt="Client Icon" class="h-8 w-8">
                    </span>
                    Client
                </a>
            </li>

            <!-- Payments Link -->
            <li>
                <a href="{{ route('staff.payments') }}" 
                   class="flex items-center px-6 py-3 rounded-md {{ request()->routeIs('staff.payments') ? 'bg-blue-600' : 'hover:bg-gray-700' }}">
                    <span class="mr-3">
                        <img src="{{ asset('icons/payments.svg') }}" alt="Payments Icon" class="h-8 w-8">
                    </span>
                    Payments
                </a>
            </li>

            <!-- Logs Link -->
            <li>
                <a href="{{ route('staff.logs') }}" 
                   class="flex items-center px-6 py-3 rounded-md {{ request()->routeIs('staff.logs') ? 'bg-blue-600' : 'hover:bg-gray-700' }}">
                    <span class="mr-3">
                        <img src="{{ asset('icons/logs.svg') }}" alt="Logs Icon" class="h-8 w-8">
                    </span>
                    Logs
                </a>
            </li>        
            
             <!-- Logout Link -->
            <li>
                <button wire:click="logout" class="flex items-center px-6 py-3 rounded-md hover:bg-gray-700">
                    <span class="mr-3">
                        <img src="{{ asset('icons/logout.svg') }}" alt="Logout Icon" class="h-8 w-8">
                    </span>
                    Logout
                </button>
            </li>
        </ul>
    </nav>
</div>
<div class="flex flex-col md:flex-row min-h-screen items-center justify-center bg-gray-900 p-4">
    <div class="bg-white rounded-2xl shadow-2xl overflow-hidden w-full max-w-5xl flex flex-col md:flex-row min-h-[600px]">
        
        <!-- Left Panel -->
        <div class="w-full md:w-2/5 bg-[#0B0F1C] text-white flex items-center justify-center p-6 border-b md:border-b-0 md:border-r border-white">
            <div class="text-center">
                <img src="{{ asset('flat_connect_logo.png') }}" class="mx-auto h-24 md:h-32 w-auto mb-4" alt="Logo">
                <h1 class="text-3xl md:text-4xl font-semibold mb-2">FlatConnect</h1>
                <p class="text-sm leading-6 opacity-80">Fast, seamless Wi-Fi.<br>Get connected today.</p>
            </div>
        </div>

        <!-- Right Panel -->
        <div class="w-full md:w-3/5 p-6 md:p-12">
            <h2 class="text-2xl md:text-3xl font-bold text-gray-800 mb-6 md:mb-8 text-center">Create Your Account</h2>

            <form wire:submit.prevent="register" class="space-y-6">
                @csrf

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">First Name</label>
                    <input type="text" wire:model.defer="first_name" required
                        class="w-full px-4 py-3 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Last Name</label>
                    <input type="text" wire:model.defer="last_name" required
                        class="w-full px-4 py-3 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                    <input type="email" wire:model.defer="email" required
                        class="w-full px-4 py-3 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Phone Number</label>
                    <input type="text" wire:model.defer="phone_number" required
                        class="w-full px-4 py-3 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                 </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                    <input type="password" wire:model.defer="password" required
                        class="w-full px-4 py-3 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Confirm Password</label>
                    <input type="password" wire:model.defer="password_confirmation" required
                        class="w-full px-4 py-3 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>

                <input type="hidden" wire:model="mac_address">

                <div>
                    <button type="submit"
                        class="w-full bg-gray-800 hover:bg-gray-700 text-white font-semibold py-3 px-4 rounded-md shadow-sm transition duration-150">
                        Register
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

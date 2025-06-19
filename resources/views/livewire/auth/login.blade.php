<div class="flex flex-col md:flex-row min-h-screen items-center justify-center bg-gray-900 p-4">
    <div class="bg-white rounded-2xl shadow-2xl overflow-hidden w-full max-w-5xl flex flex-col md:flex-row min-h-[600px]">

        <!-- Left Panel -->
        <div class="w-full md:w-1/2 bg-[#0B0F1C] text-white flex items-center justify-center p-6 md:p-10 border-b md:border-b-0 md:border-r border-white">
            <div class="text-center">
                <img src="{{ asset('flat_connect_logo.png') }}" class="mx-auto h-24 md:h-32 w-auto mb-4" alt="Logo">
                <h1 class="text-3xl md:text-5xl font-semibold mb-2">FlatConnect</h1>
                <p class="text-sm leading-6 opacity-80">
                    Connecting Your<br />
                    Business, Seamlessly.<br />
                    Get fast, reliable Wi-Fi—<br />
                    switch today!
                </p>
            </div>
        </div>

        <!-- Right Panel -->
        <div class="w-full md:w-3/5 p-6 md:p-12">
            <h2 class="text-2xl md:text-3xl font-bold text-gray-800 mb-6 md:mb-10 text-center">Login to your account</h2>

            <form wire:submit.prevent="login">
                <div class="mb-4">
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                    <input type="email" id="email" wire:model="email" required
                        class="w-full px-4 py-3 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                </div>

                <div class="mb-4">
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                    <input type="password" id="password" wire:model="password" required
                        class="w-full px-4 py-3 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                </div>

                <div class="mb-4 flex items-center">
                    <input type="checkbox" id="remember" wire:model="remember"
                        class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                    <label for="remember" class="ml-2 block text-sm text-gray-700">Remember me</label>
                </div>

                <div class="mt-6">
                    <button type="submit"
                        class="w-full bg-gray-800 hover:bg-gray-700 text-white font-semibold py-3 px-4 rounded-md shadow-sm transition duration-150">
                        Login
                    </button>
                </div>

                <div class="mt-6 text-center">
                    <a href="{{ route('register') }}" class="text-sm text-blue-600 hover:underline">
                        Don’t have an account? Register here
                    </a>
                </div>

                @if ($error)
                    <div class="mt-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative text-sm text-center">
                        {{ $error }}
                    </div>
                @endif
            </form>
        </div>
    </div>
</div>

<div>
<!-- ======================================= -->
<!--              Desktop View              -->
<!-- ======================================= -->
    <div class="hidden md:flex min-h-screen items-center justify-center bg-gray-900">
        <!-- Main Container -->
        <div class="bg-white rounded-lg shadow-lg overflow-hidden w-full max-w-5xl min-h-[600px] flex flex-row">
            
            <!-- Left Section (Dark Blue) -->
            <div class="w-1/2 bg-[#0B0F1C] text-white flex items-center justify-center border-r border-white">
                <div class="text-center px-4">
                    <!-- Logo -->
                    <div class="mb-2">
                        <img src="{{ asset('flat_connect_logo.png') }}" alt="FlatConnect Logo" class="mx-auto h-32 w-auto">
                    </div>

                    <!-- Title -->
                    <h1 class="text-5xl font-semibold mb-4">FlatConnect</h1>

                    <!-- Tagline -->
                    <p class="text-sm leading-6">
                        Connecting Your<br />
                        Business, Seamlessly.<br />
                        Get fast, reliable Wi-Fi—<br />
                        switch today!
                    </p>
                </div>
            </div>

            <!-- Right Section (Form) -->
            <div class="w-3/5 p-12">
                <h2 class="text-3xl font-bold text-gray-800 mb-10 text-center">Login to your account</h2>

                <form wire:submit.prevent="login">
                    @csrf

                    <!-- Email -->
                    <div class="mb-4">
                        <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                        <input type="email" id="email" wire:model="email" required
                            class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    </div>

                    <!-- Password -->
                    <div class="mb-4">
                        <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                        <input type="password" id="password" wire:model="password" required
                            class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    </div>

                    <!-- Remember Me -->
                    <div class="mb-4 flex items-center">
                        <input type="checkbox" id="remember" wire:model="remember"
                            class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                        <label for="remember" class="ml-2 block text-sm text-gray-700">Remember me</label>
                    </div>

                    <!-- Submit -->
                    <div class="mt-6">
                        <button type="submit"
                            class="w-full bg-gray-800 text-white py-2 px-4 rounded-md hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            Login
                        </button>
                    </div>

                    <!-- Register Link -->
                    <div class="mt-6 text-center">
                        <a href="{{ route('register') }}" class="text-sm text-blue-600 hover:underline">
                            Don’t have an account? Register here
                        </a>
                    </div>

                    <!-- Error -->
                    @if ($error)
                        <div class="mt-4 text-red-500 text-sm">
                            {{ $error }}
                        </div>
                    @endif
                </form>
            </div>
        </div>
    </div>


    <!-- ======================================= -->
    <!--              Mobile View               -->
    <!-- ======================================= -->
    <div class="block md:hidden min-h-screen flex flex-col justify-center bg-gray-900 px-4 py-8">
        <!-- Top Section (Dark Blue) -->
        <div class="bg-[#0B0F1C] text-white p-6 rounded-t-lg text-center">
            <div class="mb-2">
                <img src="{{ asset('flat_connect_logo.png') }}" alt="FlatConnect Logo" class="mx-auto h-24 w-auto">
            </div>
            <h1 class="text-4xl font-semibold mb-3">FlatConnect</h1>
            <p class="text-sm leading-6">
                Connecting Your<br />
                Business, Seamlessly.<br />
                Get fast, reliable Wi-Fi—<br />
                switch today!
            </p>
        </div>

        <!-- Form Section -->
        <div class="bg-white rounded-b-lg p-6">
            <h2 class="text-2xl font-bold text-gray-800 mb-6 text-center">Login to your account</h2>

            <form wire:submit.prevent="login">
                @csrf

                <!-- Email -->
                <div class="mb-4">
                    <label for="email_mobile" class="block text-sm font-medium text-gray-700">Email</label>
                    <input type="email" id="email_mobile" wire:model="email" required
                        class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                </div>

                <!-- Password -->
                <div class="mb-4">
                    <label for="password_mobile" class="block text-sm font-medium text-gray-700">Password</label>
                    <input type="password" id="password_mobile" wire:model="password" required
                        class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                </div>

                <!-- Remember Me -->
                <div class="mb-4 flex items-center">
                    <input type="checkbox" id="remember_mobile" wire:model="remember"
                        class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                    <label for="remember_mobile" class="ml-2 block text-sm text-gray-700">Remember me</label>
                </div>

                <!-- Submit -->
                <div class="mt-4">
                    <button type="submit"
                        class="w-full bg-gray-800 text-white py-2 px-4 rounded-md hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Login
                    </button>
                </div>

                <!-- Register Link -->
                <div class="mt-4 text-center">
                    <a href="{{ route('register') }}" class="text-sm text-blue-600 hover:underline">
                        Don’t have an account? Register here
                    </a>
                </div>

                <!-- Error -->
                @if ($error)
                    <div class="mt-4 text-red-500 text-sm">
                        {{ $error }}
                    </div>
                @endif
            </form>
        </div>
        
    </div>


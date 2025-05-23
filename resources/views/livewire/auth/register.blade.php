<div class="hidden md:flex min-h-screen items-center justify-center bg-gray-900">
    <div class="bg-white rounded-lg shadow-lg overflow-hidden w-full max-w-5xl min-h-[600px] flex flex-row">
        <div class="w-1/2 bg-[#0B0F1C] text-white flex items-center justify-center border-r border-white">
            <div class="text-center px-4">
                <img src="{{ asset('flat_connect_logo.png') }}" class="mx-auto h-32 w-auto" alt="Logo">
                <h1 class="text-5xl font-semibold mb-4">FlatConnect</h1>
                <p class="text-sm leading-6">Fast, seamless Wi-Fi.<br />Get connected today.</p>
            </div>
        </div>
        <div class="w-3/5 p-12">
            <h2 class="text-3xl font-bold text-gray-800 mb-10 text-center">Create Your Account</h2>

            <form wire:submit.prevent="register">
                @csrf
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700">First Name</label>
                    <input type="text" wire:model.defer="first_name" required class="form-input">
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700">Last Name</label>
                    <input type="text" wire:model.defer="last_name" required class="form-input">
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700">Email</label>
                    <input type="email" wire:model.defer="email" required class="form-input">
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700">Password</label>
                    <input type="password" wire:model.defer="password" required class="form-input">
                </div>

                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700">Confirm Password</label>
                    <input type="password" wire:model.defer="password_confirmation" required class="form-input">
                </div>

                <button type="submit" class="w-full bg-gray-800 text-white py-2 px-4 rounded-md hover:bg-gray-700">
                    Register
                </button>
            </form>
        </div>
    </div>
</div>

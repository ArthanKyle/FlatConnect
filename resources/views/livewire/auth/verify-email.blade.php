<div class="min-h-screen flex flex-col items-center justify-center bg-gray-100">
    <div class="bg-white p-8 rounded shadow-md max-w-md w-full">
        <h2 class="text-xl font-bold mb-4">Verify Your Email Address</h2>

        @if ($emailVerified)
            <p class="text-green-600">Your email is already verified.</p>
        @else
            <p class="mb-4">Please check your email for a verification link.</p>

            @if (session()->has('message'))
                <div class="text-green-600 mb-4">{{ session('message') }}</div>
            @endif

            <button wire:click="resendVerification"
                class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-500">
                Resend Verification Email
            </button>
        @endif
    </div>
</div>

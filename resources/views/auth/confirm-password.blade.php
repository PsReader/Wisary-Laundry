<x-guest-layout>
    <div class="text-center mb-6">
        <h2 class="text-xl font-bold text-primary-900">Konfirmasi Kata Sandi</h2>
        <p class="mt-1 text-sm text-neutral-600">
            Ini adalah area aman aplikasi. Silakan konfirmasi kata sandi sebelum melanjutkan.
        </p>
    </div>

    <form method="POST" action="{{ route('password.confirm') }}" class="space-y-5">
        @csrf

        <x-input
            type="password"
            name="password"
            label="Kata sandi"
            required
            autocomplete="current-password"
            :error="$errors->first('password')"
        />

        <x-button type="submit" block>
            Konfirmasi
        </x-button>
    </form>
</x-guest-layout>
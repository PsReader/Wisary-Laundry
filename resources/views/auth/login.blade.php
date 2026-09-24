<x-guest-layout>
    <div class="text-center mb-6">
        <h2 class="text-xl font-bold text-primary-900">Masuk</h2>
        <p class="mt-1 text-sm text-neutral-600">Silakan masuk untuk mengelola layanan laundry.</p>
    </div>

    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="space-y-5">
        @csrf

        <x-input
            type="email"
            name="email"
            label="Email"
            :value="old('email')"
            required
            autofocus
            autocomplete="username"
            :error="$errors->first('email')"
        />

        <x-input
            type="password"
            name="password"
            label="Kata sandi"
            required
            autocomplete="current-password"
            :error="$errors->first('password')"
        />

        <div class="flex items-center justify-between">
            <label for="remember_me" class="inline-flex items-center gap-2 text-sm text-neutral-600">
                <input id="remember_me" type="checkbox" name="remember" class="rounded border-neutral-300 text-primary-700 focus:ring-primary-700" {{ old('remember') ? 'checked' : '' }}>
                Ingat saya
            </label>

            @if (Route::has('password.request'))
                <a class="text-sm font-medium text-primary-700 hover:text-primary-600 hover:underline rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-700" href="{{ route('password.request') }}">
                    Lupa kata sandi?
                </a>
            @endif
        </div>

        <x-button type="submit" block>
            Masuk
        </x-button>
    </form>
</x-guest-layout>
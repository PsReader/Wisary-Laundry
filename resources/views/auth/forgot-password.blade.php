<x-guest-layout>
    @if (session('reset_email'))
        <div class="text-center mb-6">
            <h2 class="text-xl font-bold text-primary-900">Masukkan Kode OTP</h2>
            <p class="mt-1 text-sm text-neutral-600">
                Kami telah mengirimkan kode 6 digit ke <span class="font-medium">{{ session('reset_email') }}</span>. Masukkan kode beserta kata sandi baru Anda.
            </p>
        </div>

        <x-auth-session-status class="mb-4" :status="session('status')" />

        <form method="POST" action="{{ route('password.store') }}" class="space-y-5">
            @csrf

            <x-input
                type="email"
                name="email"
                label="Email"
                :value="old('email', session('reset_email'))"
                required
                readonly
                class="bg-neutral-100"
            />

            <x-input
                type="text"
                name="code"
                label="Kode OTP (6 digit)"
                placeholder="000000"
                required
                inputmode="numeric"
                autocomplete="one-time-code"
                maxlength="6"
                :error="$errors->first('code')"
            />

            <x-input
                type="password"
                name="password"
                label="Kata sandi baru"
                required
                autocomplete="new-password"
                :error="$errors->first('password')"
            />

            <x-input
                type="password"
                name="password_confirmation"
                label="Ulangi kata sandi baru"
                required
                autocomplete="new-password"
            />

            <x-button type="submit" block>
                Atur Ulang Kata Sandi
            </x-button>
        </form>
    @else
        <div class="text-center mb-6">
            <h2 class="text-xl font-bold text-primary-900">Lupa Kata Sandi</h2>
            <p class="mt-1 text-sm text-neutral-600">
                Masukkan email Anda. Kami akan mengirimkan kode OTP untuk mengatur ulang kata sandi.
            </p>
        </div>

        <x-auth-session-status class="mb-4" :status="session('status')" />

        <form method="POST" action="{{ route('password.email') }}" class="space-y-5">
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

            <x-button type="submit" block>
                Kirim Kode OTP
            </x-button>
        </form>
    @endif
</x-guest-layout>
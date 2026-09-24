<section>
    <p class="mt-1 text-sm text-neutral-600">
        Gunakan kata sandi yang panjang dan acak agar akun Anda aman.
    </p>

    <form method="post" action="{{ route('password.update') }}" class="mt-6 space-y-6">
        @csrf
        @method('put')

        <x-input
            type="password"
            name="current_password"
            label="Kata sandi saat ini"
            required
            autocomplete="current-password"
            :error="$errors->updatePassword->first('current_password')"
        />

        <x-input
            type="password"
            name="password"
            label="Kata sandi baru"
            required
            autocomplete="new-password"
            :error="$errors->updatePassword->first('password')"
        />

        <x-input
            type="password"
            name="password_confirmation"
            label="Ulangi kata sandi baru"
            required
            autocomplete="new-password"
            :error="$errors->updatePassword->first('password_confirmation')"
        />

        <div class="flex items-center gap-4">
            <x-button type="submit">Simpan</x-button>

            @if (session('status') === 'password-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm text-success-700"
                >Tersimpan.</p>
            @endif
        </div>
    </form>
</section>
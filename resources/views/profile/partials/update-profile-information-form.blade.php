<section>
    <p class="mt-1 text-sm text-neutral-600">
        Perbarui informasi profil dan alamat email akun Anda.
    </p>

    <form method="post" action="{{ route('profile.update') }}" class="mt-6 space-y-6">
        @csrf
        @method('patch')

        <x-input
            type="text"
            name="name"
            label="Nama"
            :value="old('name', $user->name)"
            required
            autofocus
            autocomplete="name"
            :error="$errors->first('name')"
        />

        <x-input
            type="email"
            name="email"
            label="Email"
            :value="old('email', $user->email)"
            required
            autocomplete="username"
            :error="$errors->first('email')"
        />

        <div class="flex items-center gap-4">
            <x-button type="submit">Simpan</x-button>

            @if (session('status') === 'profile-updated')
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
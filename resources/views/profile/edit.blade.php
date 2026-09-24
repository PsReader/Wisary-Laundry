<x-app-layout
    title="Profil"
    :breadcrumbs="[['label' => 'Profil']]"
>
    <div class="space-y-6">
        <x-card title="Informasi Profil">
            <div class="max-w-xl">
                @include('profile.partials.update-profile-information-form')
            </div>
        </x-card>

        <x-card title="Ubah Kata Sandi">
            <div class="max-w-xl">
                @include('profile.partials.update-password-form')
            </div>
        </x-card>

        <x-card title="Hapus Akun">
            <div class="max-w-xl">
                @include('profile.partials.delete-user-form')
            </div>
        </x-card>
    </div>
</x-app-layout>
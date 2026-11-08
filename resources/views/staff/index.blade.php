<x-app-layout
    title="Manajemen Staf"
    :breadcrumbs="[['label' => 'Manajemen Staf']]"
>
    @php
        $users = \App\Models\User::query()->orderBy('role')->orderBy('name')->get();
    @endphp

    <x-slot name="headerActions">
        <x-button x-data @click="$dispatch('open-modal', 'create-staff')">Tambah Staf</x-button>
    </x-slot>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
        <x-stat-card label="Super Admin Aktif" :value="\App\Models\User::activeSuperAdminCount()" />
        <x-stat-card label="Staf Aktif" :value="\App\Models\User::activeStaffCount()" />
    </div>

    <x-card title="Akun Pengguna">
        <div class="overflow-x-auto">
            <table class="w-full text-sm min-w-[560px]">
                <thead>
                    <tr class="text-left text-xs uppercase tracking-wide text-neutral-500 border-b border-neutral-100">
                        <th class="py-3 pr-4 font-semibold">Nama</th>
                        <th class="py-3 pr-4 font-semibold">Email</th>
                        <th class="py-3 pr-4 font-semibold">Peran</th>
                        <th class="py-3 pr-4 font-semibold">Status</th>
                        <th class="py-3 font-semibold text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($users as $user)
                        <tr class="border-b border-neutral-100 last:border-0">
                            <td class="py-3 pr-4 font-medium">{{ $user->name }}</td>
                            <td class="py-3 pr-4 text-neutral-600">{{ $user->email }}</td>
                            <td class="py-3 pr-4">
                                <x-badge color="{{ $user->isSuperAdmin() ? 'bg-info-100 text-info-700' : 'bg-neutral-100 text-neutral-700' }}">{{ $user->roleLabel() }}</x-badge>
                            </td>
                            <td class="py-3 pr-4">
                                <x-badge color="{{ $user->is_active ? 'bg-success-100 text-success-700' : 'bg-danger-100 text-danger-700' }}">{{ $user->is_active ? 'Aktif' : 'Nonaktif' }}</x-badge>
                            </td>
                            <td class="py-3 text-right whitespace-nowrap">
                                <div class="inline-flex items-center gap-1">
                                    @if ($user->id !== auth()->id() && ! ($user->is_active && $user->isSuperAdmin() && \App\Models\User::activeSuperAdminCount() <= 1))
                                    <form method="POST" action="{{ route('staff.toggle-active') }}">
                                        @csrf
                                        <input type="hidden" name="user_id" value="{{ $user->id }}">
                                        <button
                                            type="submit"
                                            class="inline-flex items-center gap-1.5 px-3 py-2 min-h-12 text-sm font-medium {{ $user->is_active ? 'text-warning-700 hover:bg-warning-50' : 'text-success-700 hover:bg-success-50' }} rounded-lg transition-colors"
                                        >
                                            {{ $user->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                        </button>
                                    </form>
                                    @endif
                                    <button
                                        type="button"
                                        x-data
                                        @click="$dispatch('open-modal', { name: 'reset-password-{{ $user->id }}' })"
                                        class="inline-flex items-center gap-1.5 px-3 py-2 min-h-12 text-sm font-medium text-primary-700 hover:bg-primary-50 rounded-lg transition-colors"
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                            <rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                                        </svg>
                                        Reset password
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </x-card>

    <x-modal name="create-staff" :show="false" focusable>
        <form method="POST" action="{{ route('staff.store') }}" class="p-6">
            @csrf

            <h2 class="text-xl font-semibold text-primary-900">Tambah Akun Staf</h2>
            <p class="mt-1 text-sm text-neutral-600">
                Akun baru akan langsung aktif dan dapat login.
            </p>

            <div class="mt-5 space-y-4">
                <x-input
                    type="text"
                    name="name"
                    label="Nama lengkap"
                    value="{{ old('name') }}"
                    required
                    autocomplete="name"
                    :error="$errors->first('name')"
                />
                <x-input
                    type="email"
                    name="email"
                    label="Email"
                    value="{{ old('email') }}"
                    required
                    autocomplete="username"
                    :error="$errors->first('email')"
                />
                <x-input
                    type="password"
                    name="password"
                    label="Kata sandi"
                    required
                    autocomplete="new-password"
                    :error="$errors->first('password')"
                />
                <x-input
                    type="password"
                    name="password_confirmation"
                    label="Ulangi kata sandi"
                    required
                    autocomplete="new-password"
                />
            </div>

            <div class="mt-6 flex flex-col-reverse sm:flex-row sm:justify-end gap-2">
                <x-button type="button" variant="secondary" x-data @click="$dispatch('close-modal', {})">Batal</x-button>
                <x-button type="submit" class="sm:min-w-32">Simpan Akun</x-button>
            </div>
        </form>
    </x-modal>

    @foreach ($users as $user)
        <x-modal name="reset-password-{{ $user->id }}" :show="false" focusable>
            <form method="POST" action="{{ route('staff.password-reset') }}" class="p-6">
                @csrf
                <input type="hidden" name="user_id" value="{{ $user->id }}">

                <h2 class="text-xl font-semibold text-primary-900">Atur Ulang Kata Sandi</h2>
                <p class="mt-1 text-sm text-neutral-600">
                    Kata sandi baru untuk <strong>{{ $user->name }}</strong> ({{ $user->email }}).
                </p>

                <div class="mt-5">
                    <x-input
                        type="password"
                        name="password"
                        label="Kata sandi baru"
                        required
                        :error="$errors->first('password')"
                    />
                    <div class="mt-1.5"></div>
                    <x-input
                        type="password"
                        name="password_confirmation"
                        label="Ulangi kata sandi baru"
                        required
                    />
                </div>

                <div class="mt-6 flex flex-col-reverse sm:flex-row sm:justify-end gap-2">
                    <x-button type="button" variant="secondary" x-data @click="$dispatch('close-modal', {})">Batal</x-button>
                    <x-button type="submit" class="sm:min-w-32">Simpan Kata Sandi</x-button>
                </div>
            </form>
        </x-modal>
    @endforeach
</x-app-layout>
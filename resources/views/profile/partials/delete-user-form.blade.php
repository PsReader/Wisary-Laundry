<section class="space-y-6">
    <p class="mt-1 text-sm text-neutral-600">
        Setelah akun dihapus, seluruh data terkait akan dihapus permanen. Hapus akun hanya jika sudah yakin.
    </p>

    <x-button
        variant="destructive"
        x-data=""
        x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')"
    >Hapus Akun</x-button>

    <x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" focusable>
        <form method="post" action="{{ route('profile.destroy') }}" class="p-6">
            @csrf
            @method('delete')

            <h2 class="text-lg font-semibold text-primary-900">Yakin ingin menghapus akun?</h2>

            <p class="mt-1 text-sm text-neutral-600">
                Setelah akun dihapus, seluruh data akan dihapus permanen. Masukkan kata sandi untuk konfirmasi.
            </p>

            <div class="mt-6">
                <x-input
                    type="password"
                    name="password"
                    label="Kata sandi"
                    required
                    autocomplete="current-password"
                    :error="$errors->userDeletion->first('password')"
                />
            </div>

            <div class="mt-6 flex flex-col-reverse sm:flex-row sm:justify-end gap-2">
                <button type="button" x-data @click="$dispatch('close-modal', {})" class="flex items-center justify-center px-4 py-3 min-h-12 lg:min-h-10 text-sm font-medium text-neutral-700 bg-white border border-neutral-300 rounded-lg hover:bg-neutral-50 transition-colors">
                    Batal
                </button>
                <x-button type="submit" variant="destructive" class="sm:min-w-32">Hapus Akun</x-button>
            </div>
        </form>
    </x-modal>
</section>
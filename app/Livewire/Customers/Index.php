<?php

namespace App\Livewire\Customers;

use App\Models\Customer;
use App\Support\Audit;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $search = '';

    public string $formMode = 'create';

    public string $name = '';

    public string $whatsapp = '';

    public ?string $address = null;

    public ?string $notes = null;

    public ?int $editingId = null;

    public ?int $deleteId = null;

    /**
     * Aturan validasi dibuat dinamis karena whatsapp unik harus
     * mengabaikan pelanggan yang sedang diedit.
     *
     * @return array<string, array<int, string>>
     */
    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'whatsapp' => ['required', 'string', 'regex:/^08[0-9]{8,13}$/', 'unique:customers,whatsapp,'.$this->editingId],
            'address' => ['nullable', 'string', 'max:1000'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function render(): View
    {
        return view('livewire.customers.index');
    }

    #[Computed]
    public function customers(): LengthAwarePaginator
    {
        return Customer::query()
            ->withCount('orders')
            ->when(trim($this->search) !== '', function ($query) {
                $like = '%'.trim($this->search).'%';
                $query->where(function ($q) use ($like) {
                    $q->where('name', 'like', $like)
                        ->orWhere('whatsapp', 'like', $like);
                });
            })
            ->orderBy('name')
            ->paginate(10);
    }

    #[Computed]
    public function deleteCustomer(): ?Customer
    {
        return $this->deleteId ? Customer::find($this->deleteId) : null;
    }

    public function create(): void
    {
        $this->authorize('create', Customer::class);

        $this->resetForm();
        $this->formMode = 'create';

        $this->dispatch('open-modal', 'customer-form');
    }

    public function edit(int $customerId): void
    {
        $customer = Customer::findOrFail($customerId);

        $this->authorize('update', $customer);

        $this->resetForm();
        $this->formMode = 'edit';
        $this->editingId = $customer->id;
        $this->name = $customer->name;
        $this->whatsapp = $customer->whatsapp;
        $this->address = $customer->address;
        $this->notes = $customer->notes;

        $this->dispatch('open-modal', 'customer-form');
    }

    public function save(): RedirectResponse
    {
        $data = $this->validate();

        if ($this->formMode === 'edit' && $this->editingId) {
            $customer = Customer::findOrFail($this->editingId);

            $this->authorize('update', $customer);

            $before = $this->customerSnapshot($customer);
            $customer->update($data);

            Audit::record(auth()->user(), 'customer.updated', $customer, $before, $this->customerSnapshot($customer));

            $message = 'Data pelanggan berhasil diperbarui.';
        } else {
            $this->authorize('create', Customer::class);

            $customer = Customer::query()->create([
                'name' => $data['name'],
                'whatsapp' => $data['whatsapp'],
                'address' => $data['address'] ?? null,
                'notes' => $data['notes'] ?? null,
            ]);

            Audit::record(auth()->user(), 'customer.created', $customer, null, $this->customerSnapshot($customer));

            $message = 'Pelanggan berhasil ditambahkan.';
        }

        return redirect()->route('customers')->with('success', $message);
    }

    public function confirmDelete(int $customerId): void
    {
        $this->deleteId = $customerId;

        $this->dispatch('open-modal', 'confirm-delete');
    }

    public function delete(): RedirectResponse
    {
        $customer = Customer::findOrFail($this->deleteId);

        $this->authorize('delete', $customer);

        if ($customer->orders()->exists()) {
            return redirect()->route('customers')->with('error', 'Pelanggan memiliki riwayat pesanan dan tidak dapat dihapus.');
        }

        Audit::record(auth()->user(), 'customer.deleted', $customer, $this->customerSnapshot($customer), null);

        $customer->delete();

        return redirect()->route('customers')->with('success', "Pelanggan {$customer->name} berhasil dihapus.");
    }

    /**
     * Cuplikan atribut pelanggan untuk keperluan audit.
     *
     * @return array<string, mixed>
     */
    private function customerSnapshot(Customer $customer): array
    {
        return [
            'name' => $customer->name,
            'whatsapp' => $customer->whatsapp,
            'address' => $customer->address,
            'notes' => $customer->notes,
        ];
    }

    private function resetForm(): void
    {
        $this->name = '';
        $this->whatsapp = '';
        $this->address = null;
        $this->notes = null;
        $this->editingId = null;
        $this->resetValidation();
    }
}

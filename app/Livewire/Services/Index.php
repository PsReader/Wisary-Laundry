<?php

namespace App\Livewire\Services;

use App\Models\Service;
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

    public string $category = '';

    public string $pricing_type = Service::PRICING_TYPE_KG;

    public string $price = '';

    public string $estimated_days = '3';

    public ?int $editingId = null;

    /**
     * Aturan validasi dinamis agar aturan unik nama layanan
     * mengabaikan layanan yang sedang diedit.
     *
     * @return array<string, array<int, string>>
     */
    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', 'unique:services,name,'.$this->editingId],
            'category' => ['required', 'string', 'max:100'],
            'pricing_type' => ['required', 'in:'.Service::PRICING_TYPE_KG.','.Service::PRICING_TYPE_UNIT],
            'price' => ['required', 'integer', 'min:1', 'max:100000000'],
            'estimated_days' => ['required', 'integer', 'min:0', 'max:30'],
        ];
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function render(): View
    {
        return view('livewire.services.index');
    }

    #[Computed]
    public function services(): LengthAwarePaginator
    {
        return Service::query()
            ->when(trim($this->search) !== '', function ($query) {
                $like = '%'.trim($this->search).'%';

                $query->where(function ($q) use ($like) {
                    $q->where('name', 'like', $like)
                        ->orWhere('category', 'like', $like);
                });
            })
            ->orderBy('category')
            ->orderBy('name')
            ->paginate(10);
    }

    #[Computed]
    public function existingCategories(): array
    {
        return Service::query()
            ->distinct()
            ->orderBy('category')
            ->pluck('category')
            ->all();
    }

    public function create(): void
    {
        $this->authorize('create', Service::class);

        $this->resetForm();
        $this->formMode = 'create';

        $this->dispatch('open-modal', 'service-form');
    }

    public function edit(int $serviceId): void
    {
        $service = Service::findOrFail($serviceId);

        $this->authorize('update', $service);

        $this->resetForm();
        $this->formMode = 'edit';
        $this->editingId = $service->id;
        $this->name = $service->name;
        $this->category = $service->category;
        $this->pricing_type = $service->pricing_type;
        $this->price = $service->price;
        $this->estimated_days = (string) $service->estimated_days;

        $this->dispatch('open-modal', 'service-form');
    }

    public function save(): RedirectResponse
    {
        if ($this->formMode === 'edit' && $this->editingId) {
            $service = Service::findOrFail($this->editingId);
            $this->authorize('update', $service);
        } else {
            $this->authorize('create', Service::class);
        }

        $data = $this->validate();
        $data['unit'] = $this->pricing_type === Service::PRICING_TYPE_KG ? Service::UNIT_KG : Service::UNIT_PCS;
        $data['is_same_day'] = (int) $data['estimated_days'] === 0;

        if ($this->formMode === 'edit' && $this->editingId) {
            $service = Service::findOrFail($this->editingId);
            $before = $this->serviceSnapshot($service);
            $service->update($data);

            Audit::record(auth()->user(), 'service.updated', $service, $before, $this->serviceSnapshot($service));

            $message = 'Layanan berhasil diperbarui.';
        } else {
            $service = Service::query()->create($data);

            Audit::record(auth()->user(), 'service.created', $service, null, $this->serviceSnapshot($service));

            $message = 'Layanan berhasil ditambahkan.';
        }

        return redirect()->route('services')->with('success', $message);
    }

    public function toggle(int $serviceId): RedirectResponse
    {
        $service = Service::findOrFail($serviceId);

        $this->authorize('toggle', $service);

        $before = $service->is_active;
        $service->update(['is_active' => ! $before]);

        Audit::record(auth()->user(), 'service.toggled', $service, [
            'is_active' => $before,
        ], [
            'is_active' => $service->is_active,
        ]);

        $status = $service->is_active ? 'diaktifkan' : 'dinonaktifkan';

        return redirect()->route('services')->with('success', "Layanan {$service->name} berhasil {$status}.");
    }

    /**
     * Cuplikan atribut layanan untuk keperluan audit.
     *
     * @return array<string, mixed>
     */
    private function serviceSnapshot(Service $service): array
    {
        return [
            'name' => $service->name,
            'category' => $service->category,
            'pricing_type' => $service->pricing_type,
            'price' => $service->price,
            'estimated_days' => $service->estimated_days,
            'is_same_day' => $service->is_same_day,
            'is_active' => $service->is_active,
        ];
    }

    private function resetForm(): void
    {
        $this->name = '';
        $this->category = '';
        $this->pricing_type = Service::PRICING_TYPE_KG;
        $this->price = '';
        $this->estimated_days = '3';
        $this->editingId = null;
        $this->resetValidation();
    }
}

<?php

namespace App\Livewire\Orders;

use App\Models\Customer;
use App\Models\Order;
use App\Models\Service;
use App\Support\EstimateCalculator;
use App\Support\PricingCalculator;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Buat Pesanan')]
class Create extends Component
{
    public ?int $customer_id = null;

    public string $customer_search = '';

    public string $method = Order::METHOD_DIAMBIL;

    public ?string $delivery_address = null;

    public ?string $notes = null;

    /** @var array<int, array<string, mixed>> */
    public array $items = [];

    public string $customer_name = '';

    public string $customer_whatsapp = '';

    public ?string $customer_address = null;

    public ?string $customer_notes = null;

    public function mount(): void
    {
        $this->items = [];
    }

    public function render(): View
    {
        return view('livewire.orders.create');
    }

    #[Computed]
    public function services(): Collection
    {
        return Service::active()
            ->orderBy('category')
            ->orderBy('name')
            ->get();
    }

    /**
     * Layanan dikelompokkan per kategori untuk pemilih cepat.
     *
     * @return array<string, array<int, array<string, mixed>>>
     */
    #[Computed]
    public function groupedServices(): array
    {
        return $this->services
            ->groupBy('category')
            ->mapWithKeys(fn (Collection $group, string $category) => [
                $category => $group->map(fn (Service $service) => [
                    'id' => $service->id,
                    'name' => $service->name,
                    'price_label' => $service->price_label,
                    'unit' => $service->pricing_type,
                ])->values()->all(),
            ])
            ->sortBy(fn (array $services) => $services[0]['unit'] === Service::PRICING_TYPE_UNIT)
            ->all();
    }

    #[Computed]
    public function selectedServices(): Collection
    {
        $ids = array_filter(array_map(
            fn (array $item) => (int) ($item['service_id'] ?? 0),
            $this->items,
        ));

        return $this->services->whereIn('id', $ids)->values();
    }

    #[Computed]
    public function estimatedCompletion(): ?Carbon
    {
        return $this->estimateCalculator()->orderCompletionDate($this->selectedServices, now());
    }

    #[Computed]
    public function totalPreview(): int
    {
        return $this->calculator()->totalFromItems(
            array_map(
                fn (array $item) => [
                    'service' => $this->services->firstWhere('id', (int) ($item['service_id'] ?? 0)),
                    'actual_weight' => ($item['actual_weight'] ?? '') !== '' ? (float) $item['actual_weight'] : 0,
                    'quantity' => (int) ($item['quantity'] ?? 0),
                ],
                $this->items,
            )
        );
    }

    #[Computed]
    public function selectedCustomer(): ?Customer
    {
        return $this->customer_id ? Customer::find($this->customer_id) : null;
    }

    #[Computed]
    public function customerResults(): Collection
    {
        if (trim($this->customer_search) === '') {
            return collect();
        }

        $like = '%'.trim($this->customer_search).'%';

        return Customer::query()
            ->where(function ($query) use ($like) {
                $query->where('name', 'like', $like)
                    ->orWhere('whatsapp', 'like', $like);
            })
            ->orderBy('name')
            ->limit(8)
            ->get();
    }

    public function selectCustomer(int $customerId): void
    {
        $this->customer_id = $customerId;
        $this->customer_search = '';
        $this->resetValidation(['customer_id']);
    }

    public function resetCustomer(): void
    {
        $this->customer_id = null;
        $this->customer_search = '';
    }

    public function openNewCustomer(): void
    {
        $this->customer_name = '';
        $this->customer_whatsapp = '';
        $this->customer_address = null;
        $this->customer_notes = null;
        $this->resetValidation([
            'customer_name', 'customer_whatsapp', 'customer_address', 'customer_notes',
        ]);

        $this->dispatch('open-modal', 'new-customer');
    }

    public function saveNewCustomer(): void
    {
        $data = $this->validate([
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_whatsapp' => ['required', 'string', 'regex:/^08[0-9]{8,13}$/', 'unique:customers,whatsapp'],
            'customer_address' => ['nullable', 'string', 'max:1000'],
            'customer_notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $customer = Customer::create([
            'name' => $data['customer_name'],
            'whatsapp' => $data['customer_whatsapp'],
            'address' => trim((string) ($data['customer_address'] ?? '')) ?: null,
            'notes' => trim((string) ($data['customer_notes'] ?? '')) ?: null,
        ]);

        $this->customer_id = $customer->id;
        $this->customer_search = '';
        $this->resetValidation(['customer_id']);

        $this->dispatch('close-modal', 'new-customer');
    }

    public function addService(int $serviceId): void
    {
        $this->items[] = $this->newItem();
        $index = array_key_last($this->items);
        $this->items[$index]['service_id'] = $serviceId;

        $this->resetValidation(
            'items.'.$index.'.service_id',
            'items.'.$index.'.actual_weight',
            'items.'.$index.'.quantity',
        );
    }

    public function removeItem(int $index): void
    {
        unset($this->items[$index]);
        $this->items = array_values($this->items);
    }

    public function changeService(int $index): void
    {
        $this->items[$index]['service_id'] = '';
        $this->items[$index]['actual_weight'] = '';
        $this->items[$index]['quantity'] = 1;

        $this->resetValidation(
            'items.'.$index.'.service_id',
            'items.'.$index.'.actual_weight',
            'items.'.$index.'.quantity',
        );
    }

    public function serviceSelected(int $serviceId): bool
    {
        foreach ($this->items as $item) {
            if ((int) ($item['service_id'] ?? 0) === $serviceId) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param  array<string, mixed>  $item
     */
    public function itemSubtotal(array $item): int
    {
        $service = $this->services->firstWhere('id', (int) ($item['service_id'] ?? 0));

        if (! $service instanceof Service) {
            return 0;
        }

        return $this->calculator()->lineSubtotal(
            $service,
            ($item['actual_weight'] ?? '') !== '' ? (float) $item['actual_weight'] : 0,
            (int) ($item['quantity'] ?? 0),
        );
    }

    /**
     * Berat yang ditagihkan untuk baris kilogram; null jika belum diisi
     * atau layanan bukan berbasis kilogram.
     *
     * @param  array<string, mixed>  $item
     */
    public function itemBilledWeight(array $item): ?int
    {
        $service = $this->services->firstWhere('id', (int) ($item['service_id'] ?? 0));

        if (! $service instanceof Service || $service->pricing_type !== Service::PRICING_TYPE_KG) {
            return null;
        }

        $weight = (float) ($item['actual_weight'] ?? 0);

        return $weight > 0 ? $this->calculator()->billedWeight($weight) : null;
    }

    public function save(): RedirectResponse
    {
        $this->validate();

        $now = now();
        $estimatedCompletion = $this->estimateCalculator()->orderCompletionDate($this->selectedServices, $now);
        $preparedItems = [];
        $total = 0;

        foreach ($this->items as $item) {
            $service = Service::active()->findOrFail($item['service_id']);

            $actualWeight = null;
            $billedWeight = null;
            $quantity = 0;
            $subtotal = 0;

            if ($service->pricing_type === Service::PRICING_TYPE_KG) {
                $actualWeight = round((float) $item['actual_weight'], 2);
                $billedWeight = $this->calculator()->billedWeight($actualWeight);
                $quantity = 1;
                $subtotal = $this->calculator()->lineSubtotal($service, $actualWeight);
            } else {
                $quantity = (int) $item['quantity'];
                $subtotal = $this->calculator()->lineSubtotal($service, quantity: $quantity);
            }

            $preparedItems[] = [
                'service_id' => $service->id,
                'service_name' => $service->name,
                'pricing_type' => $service->pricing_type,
                'unit' => $service->unit,
                'price' => $service->price,
                'actual_weight' => $actualWeight,
                'billed_weight' => $billedWeight,
                'quantity' => $quantity,
                'subtotal' => $subtotal,
                'note' => trim((string) ($item['note'] ?? '')) ?: null,
            ];

            $total += $subtotal;
        }

        $order = DB::transaction(function () use ($preparedItems, $total, $now, $estimatedCompletion) {
            $order = Order::create([
                'order_number' => Order::nextOrderNumber($now),
                'customer_id' => $this->customer_id,
                'received_at' => $now,
                'estimated_completion_at' => $estimatedCompletion,
                'status' => Order::STATUS_DITERIMA,
                'handover_status' => Order::HANDOVER_BELUM_DISERAHKAN,
                'payment_status' => Order::PAYMENT_BELUM_LUNAS,
                'method' => $this->method,
                'delivery_address' => $this->method === Order::METHOD_DIANTAR ? trim((string) $this->delivery_address) : null,
                'notes' => trim((string) $this->notes) ?: null,
                'total_price' => $total,
                'created_by' => auth()->id(),
            ]);

            $order->statusHistories()->create([
                'from_status' => null,
                'to_status' => Order::STATUS_DITERIMA,
                'user_id' => auth()->id(),
                'note' => 'Pesanan diterima',
            ]);

            foreach ($preparedItems as $item) {
                $order->items()->create($item);
            }

            return $order;
        });

        return redirect()->route('orders')
            ->with('success', "Pesanan {$order->order_number} berhasil dibuat.");
    }

    /**
     * @return array<string, array<int, string>>
     */
    protected function rules(): array
    {
        $rules = [
            'customer_id' => ['required', 'integer', 'exists:customers,id'],
            'method' => ['required', 'in:'.Order::METHOD_DIAMBIL.','.Order::METHOD_DIANTAR],
            'delivery_address' => ['nullable', 'required_if:method,'.Order::METHOD_DIANTAR, 'string', 'max:1000'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'items' => ['required', 'array', 'min:1'],
        ];

        foreach ($this->items as $index => $item) {
            $key = "items.$index";
            $service = $this->services->firstWhere('id', (int) ($item['service_id'] ?? 0));

            $rules["$key.service_id"] = ['required', 'integer'];
            $rules["$key.note"] = ['nullable', 'string', 'max:255'];

            if ($service !== null) {
                $rules["$key.service_id"][] = function ($attribute, $value, $fail) {
                    if (! Service::active()->whereKey($value)->exists()) {
                        $fail('Layanan tidak valid atau sudah dinonaktifkan.');
                    }
                };

                if ($service->pricing_type === Service::PRICING_TYPE_KG) {
                    $rules["$key.actual_weight"] = ['required', 'numeric', 'gt:0', 'max:999999'];
                } else {
                    $rules["$key.actual_weight"] = ['nullable'];
                    $rules["$key.quantity"] = ['required', 'integer', 'min:1', 'max:1000000'];
                }
            }
        }

        return $rules;
    }

    private function calculator(): PricingCalculator
    {
        return new PricingCalculator;
    }

    private function estimateCalculator(): EstimateCalculator
    {
        return new EstimateCalculator;
    }

    /**
     * @return array<string, mixed>
     */
    private function newItem(): array
    {
        return [
            'service_id' => '',
            'actual_weight' => '',
            'quantity' => 1,
            'note' => '',
        ];
    }
}

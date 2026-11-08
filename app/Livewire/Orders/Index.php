<?php

namespace App\Livewire\Orders;

use App\Models\Order;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Pesanan')]
class Index extends Component
{
    use WithPagination;

    public string $tab = 'active';

    public string $search = '';

    public string $statusFilter = '';

    public string $paymentFilter = '';

    public string $handoverFilter = '';

    public string $methodFilter = '';

    public string $receivedDateFilter = '';

    public string $recent7Filter = '';

    public string $sortBy = 'received_at';

    public string $sortDir = 'desc';

    protected function queryString(): array
    {
        return [
            'tab' => ['except' => 'active'],
            'search' => ['except' => '', 'as' => 'q'],
            'statusFilter' => ['except' => ''],
            'paymentFilter' => ['except' => ''],
            'handoverFilter' => ['except' => ''],
            'methodFilter' => ['except' => ''],
            'receivedDateFilter' => ['except' => ''],
            'recent7Filter' => ['except' => ''],
            'sortBy' => ['except' => 'received_at'],
            'sortDir' => ['except' => 'desc'],
        ];
    }

    public function setTab(string $tab): void
    {
        $this->tab = $tab === 'cancelled' ? 'cancelled' : 'active';
        $this->statusFilter = '';
        $this->resetPage();
    }

    public function render(): View
    {
        $query = $this->baseQuery();

        if ($this->tab === 'cancelled') {
            $query = $query->where('status', Order::STATUS_DIBATALKAN);
        } else {
            $query = $query->where('status', '!=', Order::STATUS_DIBATALKAN)
                ->when($this->statusFilter, fn (Builder $q, string $s) => $q->where('status', $s));
        }

        $orders = $query
            ->orderBy($this->tab === 'cancelled' ? 'cancelled_at' : $this->sortBy, $this->tab === 'cancelled' ? 'desc' : ($this->sortDir === 'desc' ? 'desc' : 'asc'))
            ->paginate(10);

        return view('livewire.orders.index', ['orders' => $orders]);
    }

    /**
     * @return array<string, int>
     */
    #[Computed]
    public function statusCounts(): array
    {
        if ($this->tab === 'cancelled') {
            return [];
        }

        return $this->baseQuery()
            ->where('status', '!=', Order::STATUS_DIBATALKAN)
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status')
            ->map(fn ($total) => (int) $total)
            ->all();
    }

    public function hasFilters(): bool
    {
        return (bool) ($this->search || $this->statusFilter || $this->paymentFilter
            || $this->handoverFilter || $this->methodFilter
            || $this->receivedDateFilter || $this->recent7Filter);
    }

    public function resetFilters(): void
    {
        $this->search = '';
        $this->statusFilter = '';
        $this->paymentFilter = '';
        $this->handoverFilter = '';
        $this->methodFilter = '';
        $this->receivedDateFilter = '';
        $this->recent7Filter = '';
    }

    private function baseQuery(): Builder
    {
        return Order::query()
            ->with(['customer', 'createdBy'])
            ->when($this->search, function (Builder $q, string $search) {
                $term = trim($search);
                $q->where(function (Builder $nested) use ($term) {
                    $nested->where('order_number', 'like', '%'.$term.'%')
                        ->orWhereHas('customer', function (Builder $cq) use ($term) {
                            $cq->where('name', 'like', '%'.$term.'%')
                                ->orWhere('whatsapp', 'like', '%'.$term.'%');
                        });
                });
            })
            ->when($this->paymentFilter, fn (Builder $q, string $s) => $q->where('payment_status', $s))
            ->when($this->handoverFilter, fn (Builder $q, string $s) => $q->where('handover_status', $s))
            ->when($this->methodFilter, fn (Builder $q, string $s) => $q->where('method', $s))
            ->when($this->receivedDateFilter, fn (Builder $q, string $d) => $q->whereDate('received_at', $d))
            ->when($this->recent7Filter, fn (Builder $q) => $q->where('received_at', '>=', now('Asia/Jakarta')->subDays(6)->startOfDay()));
    }

    public function toggleSort(string $field): void
    {
        if ($this->sortBy === $field) {
            $this->sortDir = $this->sortDir === 'desc' ? 'asc' : 'desc';
        } else {
            $this->sortBy = $field;
            $this->sortDir = 'desc';
        }
    }

    public function setStatusFilter(string $status): void
    {
        $this->statusFilter = $this->statusFilter === $status ? '' : $status;
        $this->resetPage();
    }

    public function setDateFilter(string $range): void
    {
        $this->recent7Filter = '';
        $this->receivedDateFilter = '';

        if ($range === 'today') {
            $this->receivedDateFilter = now('Asia/Jakarta')->toDateString();
        } elseif ($range === 'yesterday') {
            $this->receivedDateFilter = now('Asia/Jakarta')->subDay()->toDateString();
        } elseif ($range === '7d') {
            $this->recent7Filter = '7';
        }

        $this->resetPage();
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedStatusFilter(): void
    {
        $this->resetPage();
    }

    public function updatedPaymentFilter(): void
    {
        $this->resetPage();
    }

    public function updatedHandoverFilter(): void
    {
        $this->resetPage();
    }

    public function updatedMethodFilter(): void
    {
        $this->resetPage();
    }

    public function updatedReceivedDateFilter(): void
    {
        $this->recent7Filter = '';
        $this->resetPage();
    }

    public function updatedRecent7Filter(): void
    {
        $this->receivedDateFilter = '';
        $this->resetPage();
    }
}

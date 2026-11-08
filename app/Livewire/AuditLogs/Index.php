<?php

namespace App\Livewire\AuditLogs;

use App\Models\User;
use App\Support\AuditFeed;
use Illuminate\Contracts\View\View;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Log Aktivitas')]
class Index extends Component
{
    use WithPagination;

    #[Url]
    public string $from = '';

    #[Url]
    public string $to = '';

    #[Url]
    public ?int $user = null;

    #[Url]
    public string $group = '';

    public function mount(): void
    {
        $this->applyDefaults();
    }

    public function applyFilters(): void
    {
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->from = '';
        $this->to = '';
        $this->user = null;
        $this->group = '';

        $this->applyDefaults();
        $this->resetPage();
    }

    public function updatedFrom(): void
    {
        $this->resetPage();
    }

    public function updatedTo(): void
    {
        $this->resetPage();
    }

    public function updatedUser(): void
    {
        $this->resetPage();
    }

    public function updatedGroup(): void
    {
        $this->resetPage();
    }

    public function render(): View
    {
        $from = Carbon::parse($this->from !== '' ? $this->from : now()->startOfMonth()->toDateString())->startOfDay();
        $to = Carbon::parse($this->to !== '' ? $this->to : now()->toDateString())->endOfDay();

        if ($from->gt($to)) {
            [$from, $to] = [$to, $from];
        }

        $feed = AuditFeed::feed(
            $from,
            $to,
            $this->user ?: null,
            $this->group !== '' ? $this->group : null,
            500,
        );

        $page = $this->paginators['page'] ?? 1;
        $perPage = 10;
        $items = $feed->forPage($page, $perPage)->values();
        $paginator = new LengthAwarePaginator(
            $items,
            $feed->count(),
            $perPage,
            $page,
            [
                'path' => request()->url(),
                'query' => request()->query(),
            ]
        );

        return view('livewire.audit-logs.index', [
            'feed' => $paginator,
            'groups' => AuditFeed::groupOptions(),
            'users' => User::query()->orderBy('name')->get(['id', 'name']),
            'fromValue' => $this->from,
            'toValue' => $this->to,
        ]);
    }

    private function applyDefaults(): void
    {
        if ($this->from === '') {
            $this->from = now()->startOfMonth()->toDateString();
        }

        if ($this->to === '') {
            $this->to = now()->toDateString();
        }
    }
}

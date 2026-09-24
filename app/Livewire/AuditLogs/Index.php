<?php

namespace App\Livewire\AuditLogs;

use App\Models\User;
use App\Support\AuditFeed;
use Illuminate\Support\Carbon;
use Livewire\Attributes\Url;
use Livewire\Component;

class Index extends Component
{
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
        // Status URL diperbarui otomatis oleh atribut #[Url].
    }

    public function resetFilters(): void
    {
        $this->from = '';
        $this->to = '';
        $this->user = null;
        $this->group = '';

        $this->applyDefaults();
    }

    public function render()
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
        );

        return view('livewire.audit-logs.index', [
            'feed' => $feed,
            'groups' => AuditFeed::groups(),
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

<?php

namespace App\Livewire\Admin;

use App\Models\AuthAuditLog;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class AuditLogs extends Component
{
    use WithPagination;

    #[Url]
    public string $search = '';

    #[Url]
    public string $eventType = '';

    #[Url]
    public string $dateFrom = '';

    #[Url]
    public string $dateTo = '';

    #[Url]
    public string $roleFilter = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingEventType(): void
    {
        $this->resetPage();
    }

    public function updatingDateFrom(): void
    {
        $this->resetPage();
    }

    public function updatingDateTo(): void
    {
        $this->resetPage();
    }

    public function updatingRoleFilter(): void
    {
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->reset(['search', 'eventType', 'dateFrom', 'dateTo', 'roleFilter']);
        $this->resetPage();
    }

    public function render()
    {
        $query = AuthAuditLog::query()
            ->with('user')
            ->when($this->search, function ($q) {
                $q->where(function ($query) {
                    $query->where('email', 'like', '%'.$this->search.'%')
                        ->orWhere('ip_address', 'like', '%'.$this->search.'%');
                });
            })
            ->when($this->eventType, function ($q) {
                $q->where('event_type', $this->eventType);
            })
            ->when($this->dateFrom, function ($q) {
                $q->whereDate('created_at', '>=', $this->dateFrom);
            })
            ->when($this->dateTo, function ($q) {
                $q->whereDate('created_at', '<=', $this->dateTo);
            })
            ->when($this->roleFilter, function ($q) {
                $q->whereHas('user', fn ($userQuery) => $userQuery->role($this->roleFilter));
            })
            ->orderBy('created_at', 'desc');

        // Get unique event types for filter dropdown
        $eventTypes = AuthAuditLog::select('event_type')
            ->distinct()
            ->pluck('event_type')
            ->sort()
            ->values();

        return view('livewire.admin.audit-logs', [
            'logs' => $query->paginate(20),
            'eventTypes' => $eventTypes,
        ])->layout('layouts.admin', ['title' => 'Audit Trails & Logs']);
    }
}

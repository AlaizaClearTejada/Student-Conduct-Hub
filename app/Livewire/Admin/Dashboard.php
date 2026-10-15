<?php

namespace App\Livewire\Admin;

use App\Models\AuthAuditLog;
use App\Models\IncidentReport;
use App\Models\User;
use App\Models\ViolationRecord;
use Livewire\Component;

class Dashboard extends Component
{
    public int $activeStaffCount = 0;

    public int $suspendedAccountsCount = 0;

    public int $totalIncidentsCount = 0;

    public int $pendingIncidentsCount = 0;

    public int $pendingCasesCount = 0;

    public int $resolvedCasesCount = 0;

    public array $pendingCases = [];

    public array $recentActivity = [];

    public function mount(): void
    {
        $this->loadStats();
        $this->loadPendingCases();
        $this->loadRecentActivity();
    }

    public function loadStats(): void
    {
        $this->activeStaffCount = User::role('staff')
            ->whereNull('suspended_at')
            ->count();

        $this->suspendedAccountsCount = User::whereNotNull('suspended_at')->count();

        $this->totalIncidentsCount = IncidentReport::count();

        $this->pendingIncidentsCount = IncidentReport::where('status', 'pending')->count();

        $this->resolvedCasesCount = ViolationRecord::where('status', 'Resolved')->count();
    }

    public function loadPendingCases(): void
    {
        $pendingCases = ViolationRecord::query()
            ->where('status', 'Pending Review')
            ->where(function ($query): void {
                $query->whereNull('investigation_type')
                    ->orWhere('investigation_type', '!=', 'Tribunal')
                    ->orWhere(function ($tribunalQuery): void {
                        $tribunalQuery->where('investigation_type', 'Tribunal')
                            ->where(function ($eligibleQuery): void {
                                $eligibleQuery
                                    ->whereHas('offenseRule', fn ($offenseQuery) => $offenseQuery
                                        ->whereRaw('LOWER(severity_level) = ?', ['major']))
                                    ->orWhereHas('tribunalCase', fn ($tribunalCaseQuery) => $tribunalCaseQuery
                                        ->forTribunal());
                            });
                    });
            });

        $this->pendingCasesCount = (clone $pendingCases)->count();

        $this->pendingCases = $pendingCases->with(['student', 'offenseRule', 'reporter'])
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get()
            ->map(fn ($case) => [
                'id' => $case->id,
                'case_tracking_number' => $case->case_tracking_number,
                'student_name' => $case->student?->name ?? 'Unknown Student',
                'offense' => $case->offenseRule?->title ?? 'Unknown Offense',
                'reporter_name' => $case->reporter?->name ?? 'Unknown Staff',
                'filed_at' => $case->created_at->diffForHumans(),
                'filed_timestamp' => $case->created_at->format('M d, Y g:i A'),
                'investigation_type' => $case->investigation_type,
            ])
            ->toArray();
    }

    public function loadRecentActivity(): void
    {
        $this->recentActivity = AuthAuditLog::with('user')
            ->latest()
            ->take(10)
            ->get()
            ->map(fn ($log) => [
                'id' => $log->id,
                'event' => $log->event_type,
                'email' => $log->email,
                'user_name' => $log->user?->name ?? 'Unknown User',
                'ip_address' => $log->ip_address,
                'created_at' => $log->created_at->diffForHumans(),
                'timestamp' => $log->created_at->format('M d, Y g:i A'),
            ])
            ->toArray();
    }

    public function render()
    {
        return view('livewire.admin.dashboard')
            ->layout('layouts.admin', ['title' => 'Dashboard Overview']);
    }
}

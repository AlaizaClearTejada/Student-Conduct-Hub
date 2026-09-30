<?php

namespace App\Livewire\Admin;

use App\Models\AuthAuditLog;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Permission\Models\Role;

class UserManagement extends Component
{
    use WithPagination;

    #[Url]
    public string $search = '';

    #[Url]
    public string $roleFilter = '';

    #[Url]
    public string $statusFilter = '';

    public bool $showCreateModal = false;

    public bool $showEditModal = false;

    public bool $showSuspendModal = false;

    public ?int $editingUserId = null;

    public ?int $suspendingUserId = null;

    public string $suspensionReason = '';

    // Create/Edit form fields
    public string $name = '';

    public string $firstName = '';

    public string $lastName = '';

    public string $email = '';

    public string $password = '';

    public string $role = 'staff';

    public function mount(): void
    {
        // Check if we should open create modal from query param
        if (request()->has('action') && request()->get('action') === 'create') {
            $this->showCreateModal = true;
        }
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingRoleFilter(): void
    {
        $this->resetPage();
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function openCreateModal(): void
    {
        $this->reset(['name', 'firstName', 'lastName', 'email', 'password', 'role', 'editingUserId']);
        $this->showEditModal = false;
        $this->showCreateModal = true;
    }

    public function closeCreateModal(): void
    {
        $this->showCreateModal = false;
        $this->showEditModal = false;
        $this->editingUserId = null;
        $this->resetValidation();
    }

    public function openEditModal(int $userId): void
    {
        $user = User::findOrFail($userId);

        $this->editingUserId = $user->id;
        $this->firstName = $user->first_name ?: str($user->name)->beforeLast(' ')->toString();
        $this->lastName = $user->last_name ?: str($user->name)->afterLast(' ')->toString();
        $this->email = $user->email;
        $this->password = '';
        $this->role = $user->getRoleNames()->first() ?? 'staff';
        $this->showCreateModal = false;
        $this->showEditModal = true;
        $this->resetValidation();
    }

    public function updateUser(): void
    {
        $user = User::findOrFail($this->editingUserId);

        $validated = $this->validate([
            'firstName' => ['required', 'string', 'max:255'],
            'lastName' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'password' => ['nullable', 'string', 'min:8'],
            'role' => ['required', Rule::in(['administrator', 'staff'])],
        ]);

        if ($user->id === auth()->id() && $validated['role'] !== 'administrator') {
            session()->flash('error', 'You cannot remove administrator access from your own account.');

            return;
        }

        $user->update([
            'name' => trim($validated['firstName'].' '.$validated['lastName']),
            'first_name' => $validated['firstName'],
            'last_name' => $validated['lastName'],
            'email' => $validated['email'],
            ...($validated['password'] ? ['password' => Hash::make($validated['password'])] : []),
        ]);

        $user->syncRoles([$validated['role']]);

        AuthAuditLog::create([
            'user_id' => auth()->id(),
            'email' => auth()->user()->email,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'event_type' => 'user_updated',
            'additional_data' => json_encode([
                'updated_user_id' => $user->id,
                'updated_user_email' => $user->email,
                'role' => $validated['role'],
                'password_changed' => (bool) $validated['password'],
            ]),
        ]);

        $this->closeCreateModal();
        session()->flash('message', 'User updated successfully.');
    }

    public function createUser(): void
    {
        $validated = $this->validate([
            'firstName' => ['required', 'string', 'max:255'],
            'lastName' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')],
            'password' => ['required', 'string', 'min:8'],
            'role' => ['required', Rule::in(['administrator', 'staff'])],
        ]);

        $user = User::create([
            'name' => trim($validated['firstName'].' '.$validated['lastName']),
            'first_name' => $validated['firstName'],
            'last_name' => $validated['lastName'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        $user->assignRole($validated['role']);

        // Log the action
        AuthAuditLog::create([
            'user_id' => auth()->id(),
            'email' => auth()->user()->email,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'event_type' => 'user_created',
            'additional_data' => json_encode([
                'created_user_id' => $user->id,
                'created_user_email' => $user->email,
                'role' => $validated['role'],
            ]),
        ]);

        $this->closeCreateModal();
        session()->flash('message', 'User created successfully.');
    }

    public function openSuspendModal(int $userId): void
    {
        $this->suspendingUserId = $userId;
        $this->suspensionReason = '';
        $this->showSuspendModal = true;
    }

    public function closeSuspendModal(): void
    {
        $this->showSuspendModal = false;
        $this->suspendingUserId = null;
        $this->suspensionReason = '';
    }

    public function suspendUser(): void
    {
        $this->validate([
            'suspensionReason' => ['required', 'string', 'max:500'],
        ]);

        $user = User::findOrFail($this->suspendingUserId);

        // Prevent self-suspension
        if ($user->id === auth()->id()) {
            session()->flash('error', 'You cannot suspend your own account.');
            $this->closeSuspendModal();

            return;
        }

        $user->update([
            'suspended_at' => now(),
            'suspension_reason' => $this->suspensionReason,
        ]);

        // Log the action
        AuthAuditLog::create([
            'user_id' => auth()->id(),
            'email' => auth()->user()->email,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'event_type' => 'user_suspended',
            'additional_data' => json_encode([
                'suspended_user_id' => $user->id,
                'suspended_user_email' => $user->email,
                'reason' => $this->suspensionReason,
            ]),
        ]);

        $this->closeSuspendModal();
        session()->flash('message', 'User account suspended.');
    }

    public function activateUser(int $userId): void
    {
        $user = User::findOrFail($userId);

        $user->update([
            'suspended_at' => null,
            'suspension_reason' => null,
        ]);

        // Log the action
        AuthAuditLog::create([
            'user_id' => auth()->id(),
            'email' => auth()->user()->email,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'event_type' => 'user_activated',
            'additional_data' => json_encode([
                'activated_user_id' => $user->id,
                'activated_user_email' => $user->email,
            ]),
        ]);

        session()->flash('message', 'User account activated.');
    }

    public function deleteUser(int $userId): void
    {
        $user = User::findOrFail($userId);

        // Prevent self-deletion
        if ($user->id === auth()->id()) {
            session()->flash('error', 'You cannot delete your own account.');

            return;
        }

        // Prevent deletion if user has linked incident reports
        if (
            \App\Models\IncidentReport::where('student_id', $userId)->exists() ||
            \App\Models\IncidentReport::where('reporter_id', $userId)->exists()
        ) {
            session()->flash('error', 'Cannot delete this user — they have associated incident reports on record.');

            return;
        }

        $userEmail = $user->email;
        $user->delete();

        // Log the action
        AuthAuditLog::create([
            'user_id' => auth()->id(),
            'email' => auth()->user()->email,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'event_type' => 'user_deleted',
            'additional_data' => json_encode([
                'deleted_user_email' => $userEmail,
            ]),
        ]);

        session()->flash('message', 'User deleted successfully.');
    }

    public function render()
    {
        $query = User::query()
            ->with('roles')
            ->when($this->search, function ($q) {
                $q->where(function ($query) {
                    $query->where('name', 'like', '%'.$this->search.'%')
                        ->orWhere('email', 'like', '%'.$this->search.'%');
                });
            })
            ->when($this->roleFilter, function ($q) {
                $q->role($this->roleFilter);
            })
            ->when($this->statusFilter === 'active', function ($q) {
                $q->whereNull('suspended_at');
            })
            ->when($this->statusFilter === 'suspended', function ($q) {
                $q->whereNotNull('suspended_at');
            })
            ->orderBy('created_at', 'desc');

        return view('livewire.admin.user-management', [
            'users' => $query->paginate(10),
            'roles' => Role::whereIn('name', ['administrator', 'staff'])->get(),
        ])->layout('layouts.admin', ['title' => 'Staff & User Management']);
    }
}

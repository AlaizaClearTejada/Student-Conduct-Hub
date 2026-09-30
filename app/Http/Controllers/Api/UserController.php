<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{
    /**
     * Display a listing of all system accounts.
     * Required Role: Administrator
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $users = User::query()
            ->when($request->search, fn ($q, $search) => $q->where('name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%"))
            ->when($request->role, fn ($q, $role) => $q->role($role))
            ->latest()
            ->paginate($request->per_page ?? 15);

        return UserResource::collection($users);
    }

    /**
     * Store a newly created user account.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'first_name' => ['sometimes', 'string', 'max:255'],
            'last_name' => ['sometimes', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', Password::defaults()],
            'role' => ['required', 'string', 'in:administrator,staff,student'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'first_name' => $validated['first_name'] ?? null,
            'last_name' => $validated['last_name'] ?? null,
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        $user->assignRole($validated['role']);

        return response()->json([
            'message' => 'User created successfully',
            'user' => new UserResource($user),
        ], 201);
    }

    /**
     * Display the specified user.
     */
    public function show(User $user): JsonResponse
    {
        return response()->json([
            'user' => new UserResource($user),
        ]);
    }

    /**
     * Update the specified user.
     */
    public function update(Request $request, User $user): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'first_name' => ['sometimes', 'string', 'max:255'],
            'last_name' => ['sometimes', 'string', 'max:255'],
            'email' => ['sometimes', 'email', 'unique:users,email,'.$user->id],
            'password' => ['sometimes', Password::defaults()],
            'role' => ['sometimes', 'string', 'in:administrator,staff,student'],
        ]);

        if (isset($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        }

        $user->update($validated);

        if (isset($validated['role'])) {
            $user->syncRoles([$validated['role']]);
        }

        return response()->json([
            'message' => 'User updated successfully',
            'user' => new UserResource($user->fresh()),
        ]);
    }

    /**
     * Remove the specified user (Soft delete).
     */
    public function destroy(User $user): JsonResponse
    {
        $user->delete();

        // Log audit
        \App\Models\AuditLog::create([
            'entity_type' => 'users',
            'entity_id' => (string) $user->id,
            'action' => 'DELETE',
            'actor_id' => request()->user()?->id,
        ]);

        return response()->json([
            'message' => 'User deleted successfully',
        ]);
    }

    /**
     * Restore a soft-deleted user.
     */
    public function restore(Request $request, $id): JsonResponse
    {
        $user = User::withTrashed()->findOrFail($id);
        $user->restore();

        // Log audit
        \App\Models\AuditLog::create([
            'entity_type' => 'users',
            'entity_id' => (string) $user->id,
            'action' => 'RESTORE',
            'actor_id' => $request->user()?->id,
        ]);

        return response()->json([
            'message' => 'User restored successfully',
            'user' => new UserResource($user),
        ]);
    }

    /**
     * Suspend an active user.
     */
    public function suspend(Request $request, User $user): JsonResponse
    {
        $validated = $request->validate([
            'reason' => ['required', 'string', 'max:1000'],
        ]);

        $user->update([
            'suspended_at' => now(),
            'suspension_reason' => $validated['reason'],
        ]);

        // Log audit
        \App\Models\AuditLog::create([
            'entity_type' => 'users',
            'entity_id' => (string) $user->id,
            'action' => 'SUSPEND',
            'actor_id' => $request->user()?->id,
            'changes' => [
                'reason' => $validated['reason'],
            ],
        ]);

        return response()->json([
            'message' => 'User suspended successfully',
            'user' => new UserResource($user),
        ]);
    }
}

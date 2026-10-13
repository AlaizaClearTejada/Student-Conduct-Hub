<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::transaction(function (): void {
            $now = now();

            DB::table('roles')->insertOrIgnore([
                'name' => 'administrator',
                'guard_name' => 'web',
                'created_at' => $now,
                'updated_at' => $now,
            ]);
            DB::table('permissions')->insertOrIgnore([
                'name' => 'access tribunal module',
                'guard_name' => 'web',
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            $administratorRoleId = DB::table('roles')
                ->where('name', 'administrator')
                ->where('guard_name', 'web')
                ->value('id');
            $tribunalPermissionId = DB::table('permissions')
                ->where('name', 'access tribunal module')
                ->where('guard_name', 'web')
                ->value('id');

            DB::table('role_has_permissions')->insertOrIgnore([
                'permission_id' => $tribunalPermissionId,
                'role_id' => $administratorRoleId,
            ]);

            $legacyRoleIds = DB::table('roles')
                ->where('guard_name', 'web')
                ->whereIn('name', ['tribunal', 'tribunal_panel', 'tribunal panel', 'tribunal chair'])
                ->pluck('id');
            $legacyUserIds = DB::table('users')
                ->where('role_type', 'tribunal_panel')
                ->pluck('id');

            if ($legacyRoleIds->isNotEmpty()) {
                $legacyUserIds = $legacyUserIds->merge(
                    DB::table('model_has_roles')
                        ->whereIn('role_id', $legacyRoleIds)
                        ->where('model_type', User::class)
                        ->pluck('model_id')
                )->unique();

                DB::table('model_has_roles')->whereIn('role_id', $legacyRoleIds)->delete();
                DB::table('role_has_permissions')->whereIn('role_id', $legacyRoleIds)->delete();
            }

            foreach ($legacyUserIds as $userId) {
                DB::table('model_has_roles')->insertOrIgnore([
                    'role_id' => $administratorRoleId,
                    'model_type' => User::class,
                    'model_id' => $userId,
                ]);
            }

            if ($legacyUserIds->isNotEmpty()) {
                DB::table('users')->whereIn('id', $legacyUserIds)->update(['role_type' => 'admin']);
            }

            if ($legacyRoleIds->isNotEmpty()) {
                DB::table('roles')->whereIn('id', $legacyRoleIds)->delete();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        throw new LogicException('The Tribunal role consolidation cannot be safely reversed.');
    }
};

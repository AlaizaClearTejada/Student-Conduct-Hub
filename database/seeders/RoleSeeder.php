<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions
        $permissions = [
            'view incidents',
            'create incidents',
            'edit incidents',
            'delete incidents',
            'view reports',
            'generate reports',
            'manage users',
            'assign discipline',
            'view own incidents',
            'appeal incidents',
            'access tribunal module',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(
                ['name' => $permission, 'guard_name' => 'web']
            );
        }

        // Create roles and assign permissions

        // Student role - limited permissions
        $studentRole = Role::firstOrCreate(['name' => 'student', 'guard_name' => 'web']);
        $studentRole->syncPermissions([
            'view own incidents',
            'appeal incidents',
        ]);

        // Staff role - moderate permissions
        $staffRole = Role::firstOrCreate(['name' => 'staff', 'guard_name' => 'web']);
        $staffRole->syncPermissions([
            'view incidents',
            'create incidents',
            'edit incidents',
            'view reports',
            'assign discipline',
        ]);

        // Administrator role (OSDW Admin) - all permissions, including Tribunal access.
        $adminRole = Role::firstOrCreate(['name' => 'administrator', 'guard_name' => 'web']);
        $adminRole->syncPermissions(Permission::all());
    }
}

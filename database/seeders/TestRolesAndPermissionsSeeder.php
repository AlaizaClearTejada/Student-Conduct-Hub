<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class TestRolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app()['cache']->forget('spatie.permission.cache');

        $roles = ['student', 'staff', 'administrator', 'auditor'];

        foreach ($roles as $roleName) {
            Role::firstOrCreate([
                'name' => $roleName,
                'guard_name' => 'web',
            ]);
        }

        $permission = Permission::firstOrCreate([
            'name' => 'access tribunal module',
            'guard_name' => 'web',
        ]);

        Role::where('name', 'administrator')->firstOrFail()->givePermissionTo($permission);
    }
}

<?php

namespace Database\Seeders;

use App\Support\AdminAccess;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $guardName = config('auth.defaults.guard', 'web');

        $permissions = collect(AdminAccess::permissions())->mapWithKeys(function (string $permission) use ($guardName) {
            return [
                $permission => Permission::findOrCreate($permission, $guardName),
            ];
        });

        $adminRole = Role::findOrCreate('admin', $guardName);
        $adminRole->syncPermissions($permissions->values()->all());

        $staffRole = Role::findOrCreate('staff', $guardName);
        $staffRole->syncPermissions(
            $permissions->only(AdminAccess::defaultStaffPermissions())->values()->all()
        );
    }
}

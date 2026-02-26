<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create Permissions
        Permission::firstOrCreate(['name' => 'manage users']);
        Permission::firstOrCreate(['name' => 'manage projects']);
        Permission::firstOrCreate(['name' => 'delete builds']);
        Permission::firstOrCreate(['name' => 'approve builds']);
        Permission::firstOrCreate(['name' => 'submit feedback']);
        Permission::firstOrCreate(['name' => 'manage tasks']);

        // Admin: All permissions
        $admin = Role::firstOrCreate(['name' => 'Admin']);
        $admin->givePermissionTo(Permission::all());

        // Developer: Manage projects, builds, and tasks
        $dev = Role::firstOrCreate(['name' => 'Developer']);
        $dev->givePermissionTo(['manage projects', 'approve builds', 'submit feedback', 'manage tasks']);

        // Tester: Only feedback and downloading
        $tester = Role::firstOrCreate(['name' => 'Tester']);
        $tester->givePermissionTo(['submit feedback']);
    }
}

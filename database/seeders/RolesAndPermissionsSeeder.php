<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Admin -> manages the team, projects, and users
        // Developer -> uploads builds, resolves tasks and feedback
        // QC Engineer -> tests builds, submits bugs, approves or rejects
        // Viewer -> can see and download builds, read feedback

        $roles = [
            'Admin',
            'Developer',
            'QC Engineer',
            'Viewer',
        ];

        foreach ($roles as $role) {
            \Spatie\Permission\Models\Role::create(['name' => $role]);
        }

        // Create a default admin user
        $admin = \App\Models\User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
        ]);

        $admin->assignRole('Admin');
    }
}

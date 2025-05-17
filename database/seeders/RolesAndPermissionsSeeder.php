<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesAndPermissionsSeeder extends Seeder
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
            // User management
            'view-users',
            'create-users',
            'edit-users',
            'delete-users',
            'assign-user-roles',

            // Role management
            'view-roles',
            'create-roles',
            'edit-roles',
            'delete-roles',
            'manage-roles',

            // Permission management
            'view-permissions',
            'create-permissions',
            'edit-permissions',
            'delete-permissions',

            // Add other permissions as needed
            'manage-settings',

            // Reporting Permissions

            'generate-manual-reports'
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // Create roles and assign permissions

        // Super Admin - gets all permissions
        $superAdminRole = Role::create(['name' => 'super-admin']);
        $superAdminRole->givePermissionTo(Permission::all());

        // Admin - gets most permissions but not permission management
        $adminRole = Role::create(['name' => 'admin']);
        $adminRole->givePermissionTo([
            'view-users', 'create-users', 'edit-users', 'delete-users', 'assign-user-roles',
            'view-roles', 'create-roles', 'edit-roles', 'delete-roles', 'manage-roles',
            'manage-settings',
        ]);

        // Manager - gets some user management permissions
        $managerRole = Role::create(['name' => 'manager']);
        $managerRole->givePermissionTo([
            'view-users', 'edit-users',
            'view-roles',
        ]);

        // User - regular user role with limited permissions
        $userRole = Role::create(['name' => 'user']);

        // Create super admin users
        $superAdmin1 = User::create([
            'name' => 'Super Admin',
            'email' => 'superadmin@example.com',
            'password' => Hash::make('password'),
            'is_active' => true,
        ]);
        $superAdmin1->assignRole($superAdminRole);

        $superAdmin2 = User::create([
            'name' => 'Super Admin 2',
            'email' => 'superadmin2@example.com',
            'password' => Hash::make('password'),
            'is_active' => true,
        ]);
        $superAdmin2->assignRole($superAdminRole);

        // Create a regular admin user
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'is_active' => true,
        ]);
        $admin->assignRole($adminRole);

        // Create a manager user
        $manager = User::create([
            'name' => 'Manager User',
            'email' => 'manager@example.com',
            'password' => Hash::make('password'),
            'is_active' => true,
        ]);
        $manager->assignRole($managerRole);

        // Create a regular user
        $user = User::create([
            'name' => 'Regular User',
            'email' => 'user@example.com',
            'password' => Hash::make('password'),
            'is_active' => true,
        ]);
        $user->assignRole($userRole);
    }
}
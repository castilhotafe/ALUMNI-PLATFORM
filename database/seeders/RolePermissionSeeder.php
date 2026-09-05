<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permissions = [
            'access admin dashboard',
        ];

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission);
        }

        $roles = [
            'Current Student',
            'Alumni',
            'Lecturer',
            'Partner',
            'General User',
            'Admin',
        ];

        foreach ($roles as $role) {
            Role::findOrCreate($role);
        }

        Role::findByName('Lecturer')->givePermissionTo([]);

        Role::findByName('Partner')->givePermissionTo([]);

        Role::findByName('Admin')->givePermissionTo([
            'access admin dashboard',
        ]);
    }
}

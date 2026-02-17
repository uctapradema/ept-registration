<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Throwable;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        try {
            // Create roles
            $roles = [
                'admin',
                'finance',
                'mahasiswa',
            ];

            foreach ($roles as $roleName) {
                Role::firstOrCreate(
                    ['name' => $roleName, 'guard_name' => 'web'],
                    ['name' => $roleName, 'guard_name' => 'web']
                );
            }

            // Create permissions
            $permissions = [
                // Exam Schedule permissions
                'exam_schedule:view',
                'exam_schedule:create',
                'exam_schedule:edit',
                'exam_schedule:delete',

                // Registration permissions
                'registration:view_all',
                'registration:view',
                'registration:edit',
                'registration:delete',
                'registration:verify',
                'registration:reject',
                'registration:view_pending',
                'registration:view_verified',

                // User permissions
                'user:view',
                'user:create',
                'user:edit',
                'user:delete',
            ];

            foreach ($permissions as $permissionName) {
                Permission::firstOrCreate(
                    ['name' => $permissionName, 'guard_name' => 'web'],
                    ['name' => $permissionName, 'guard_name' => 'web']
                );
            }

            // Assign permissions to roles
            $adminRole = Role::findByName('admin');
            $adminRole->syncPermissions(Permission::all());

            $financeRole = Role::findByName('finance');
            $financeRole->syncPermissions([
                'registration:view_pending',
                'registration:view_verified',
                'registration:verify',
                'registration:reject',
            ]);

            // Mahasiswa has no permissions (controlled by middleware)
            $mahasiswaRole = Role::findByName('mahasiswa');
            $mahasiswaRole->syncPermissions([]);

        } catch (Throwable $e) {
            report($e);
            throw $e;
        }
    }
}

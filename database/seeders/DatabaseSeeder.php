<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create default roles
        $adminRole = Role::create(['name' => 'admin']);
        $editorRole = Role::create(['name' => 'editor']);
        $userRole = Role::create(['name' => 'user']);

        // Create default permissions
        Permission::create(['name' => 'view panel']);
        Permission::create(['name' => 'manage users']);
        Permission::create(['name' => 'manage roles']);
        Permission::create(['name' => 'manage permissions']);

        // Assign permissions to roles
        $adminRole->givePermissionTo(Permission::all());
        $editorRole->givePermissionTo('view panel');

        // Create default users
        $admin = User::create([
            'name' => 'Admin',
            'username' => 'admin',
            'email' => 'admin@mail.com',
            'password' => bcrypt('admin'),
        ]);
        $admin->assignRole('admin');

        // Create editor user
        $editor = User::create([
            'name' => 'Editor',
            'username' => 'editor',
            'email' => 'editor@mail.com',
            'password' => bcrypt('editor'),
        ]);
        $editor->assignRole('editor');

        // Create regular user
        $user = User::create([
            'name' => 'Regular User',
            'username' => 'user',
            'email' => 'user@mail.com',
            'password' => bcrypt('user'),
        ]);
        $user->assignRole('user');
    }
}

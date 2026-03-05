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
        // Create default roles (admin, vendedor, cliente; keep editor/user for compatibility)
        $roles = ['admin', 'editor', 'user', 'vendedor', 'cliente'];
        foreach ($roles as $name) {
            Role::firstOrCreate(['name' => $name]);
        }
        $adminRole = Role::where('name', 'admin')->first();
        $editorRole = Role::where('name', 'editor')->first();

        // Create default permissions
        $permissions = ['view panel', 'manage users', 'manage roles', 'manage permissions'];
        foreach ($permissions as $name) {
            Permission::firstOrCreate(['name' => $name]);
        }
        $adminRole->givePermissionTo(Permission::all());
        $editorRole->syncPermissions(['view panel']);

        // Create default users
        $admin = User::firstOrCreate(
            ['username' => 'admin'],
            [
                'name' => 'Admin',
                'email' => 'admin@mail.com',
                'password' => bcrypt('admin'),
            ]
        );
        if (! $admin->hasRole('admin')) {
            $admin->assignRole('admin');
        }

        $editor = User::firstOrCreate(
            ['username' => 'editor'],
            [
                'name' => 'Editor',
                'email' => 'editor@mail.com',
                'password' => bcrypt('editor'),
            ]
        );
        if (! $editor->hasRole('editor')) {
            $editor->assignRole('editor');
        }

        $user = User::firstOrCreate(
            ['username' => 'user'],
            [
                'name' => 'Regular User',
                'email' => 'user@mail.com',
                'password' => bcrypt('user'),
            ]
        );
        if (! $user->hasRole('user')) {
            $user->assignRole('user');
        }

        $this->call(DemoFrigorificaSeeder::class);
    }
}

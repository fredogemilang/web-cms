<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Seed roles first
        $this->call([
            RoleSeeder::class,
            PermissionSeeder::class,
            MediaPermissionsSeeder::class,
            ThemePermissionsSeeder::class,
            PagesPermissionsSeeder::class,
            MenuItemSeeder::class,
        ]);

        // Create admin user
        $admin = User::create([
            'name' => 'Administrator',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
        ]);

        // Assign Administrator role to admin user
        $adminRole = \App\Models\Role::where('slug', 'administrator')->first();
        $admin->roles()->attach($adminRole->id);

        // Create test users for other roles
        $editor = User::create([
            'name' => 'Editor User',
            'email' => 'editor@example.com',
            'password' => bcrypt('password'),
        ]);
        $editorRole = \App\Models\Role::where('slug', 'editor')->first();
        $editor->roles()->attach($editorRole->id);

        $author = User::create([
            'name' => 'Author User',
            'email' => 'author@example.com',
            'password' => bcrypt('password'),
        ]);
        $authorRole = \App\Models\Role::where('slug', 'author')->first();
        $author->roles()->attach($authorRole->id);
    }
}

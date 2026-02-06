<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            [
                'name' => 'Administrator',
                'slug' => 'administrator',
                'description' => 'Super admin dengan akses penuh ke semua fitur',
                'is_super_admin' => true,
            ],
            [
                'name' => 'Editor',
                'slug' => 'editor',
                'description' => 'Dapat mengelola konten dan user',
                'is_super_admin' => false,
            ],
            [
                'name' => 'Author',
                'slug' => 'author',
                'description' => 'Dapat membuat dan mengelola konten sendiri',
                'is_super_admin' => false,
            ],
            [
                'name' => 'Subscriber',
                'slug' => 'subscriber',
                'description' => 'Akses read-only ke dashboard',
                'is_super_admin' => false,
            ],
        ];

        foreach ($roles as $role) {
            Role::create($role);
        }
    }
}

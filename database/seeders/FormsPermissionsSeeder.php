<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Seeder;

class FormsPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            [
                'name' => 'forms.view',
                'module' => 'forms',
                'action' => 'view',
                'description' => 'View forms',
                'source' => 'core',
            ],
            [
                'name' => 'forms.create',
                'module' => 'forms',
                'action' => 'create',
                'description' => 'Create new forms',
                'source' => 'core',
            ],
            [
                'name' => 'forms.edit',
                'module' => 'forms',
                'action' => 'edit',
                'description' => 'Edit forms',
                'source' => 'core',
            ],
            [
                'name' => 'forms.delete',
                'module' => 'forms',
                'action' => 'delete',
                'description' => 'Delete forms',
                'source' => 'core',
            ],
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(
                ['name' => $permission['name']],
                $permission
            );
        }

        $this->command->info('Forms permissions created successfully!');
    }
}

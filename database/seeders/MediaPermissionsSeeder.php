<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Seeder;

class MediaPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            [
                'name' => 'media.view',
                'module' => 'media',
                'action' => 'view',
                'description' => 'View media library',
                'source' => 'core',
            ],
            [
                'name' => 'media.upload',
                'module' => 'media',
                'action' => 'upload',
                'description' => 'Upload new media files',
                'source' => 'core',
            ],
            [
                'name' => 'media.edit',
                'module' => 'media',
                'action' => 'edit',
                'description' => 'Edit media metadata',
                'source' => 'core',
            ],
            [
                'name' => 'media.delete',
                'module' => 'media',
                'action' => 'delete',
                'description' => 'Delete media files',
                'source' => 'core',
            ],
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(
                ['name' => $permission['name']],
                $permission
            );
        }

        $this->command->info('Media permissions created successfully!');
    }
}

<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EventCategoriesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'iC-Talk',
                'slug' => 'ic-talk',
                'description' => 'Inspiring talks and presentations from industry experts',
                'color' => '#3B82F6', // Blue
                'icon' => 'campaign',
                'order' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'iC-Connect',
                'slug' => 'ic-connect',
                'description' => 'Networking events and community gatherings',
                'color' => '#10B981', // Green
                'icon' => 'people',
                'order' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'iC-Class',
                'slug' => 'ic-class',
                'description' => 'Educational workshops and training sessions',
                'color' => '#F59E0B', // Orange
                'icon' => 'school',
                'order' => 3,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'iC-MeetHub',
                'slug' => 'ic-meethub',
                'description' => 'Collaborative meetups and discussion forums',
                'color' => '#8B5CF6', // Purple
                'icon' => 'forum',
                'order' => 4,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($categories as $category) {
            DB::table('event_categories')->updateOrInsert(
                ['slug' => $category['slug']],
                $category
            );
        }

        $this->command->info('✅ Event categories seeded successfully!');
        $this->command->info('   - iC-Talk (Blue)');
        $this->command->info('   - iC-Connect (Green)');
        $this->command->info('   - iC-Class (Orange)');
        $this->command->info('   - iC-MeetHub (Purple)');
    }
}

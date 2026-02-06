<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Plugins\Events\Models\EventCategory;

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
            ],
            [
                'name' => 'iC-Connect',
                'slug' => 'ic-connect',
                'description' => 'Networking events and community gatherings',
                'color' => '#10B981', // Green
                'icon' => 'people',
                'order' => 2,
            ],
            [
                'name' => 'iC-Class',
                'slug' => 'ic-class',
                'description' => 'Educational workshops and training sessions',
                'color' => '#F59E0B', // Orange
                'icon' => 'school',
                'order' => 3,
            ],
            [
                'name' => 'iC-MeetHub',
                'slug' => 'ic-meethub',
                'description' => 'Collaborative meetups and discussion forums',
                'color' => '#8B5CF6', // Purple
                'icon' => 'forum',
                'order' => 4,
            ],
        ];

        foreach ($categories as $category) {
            EventCategory::updateOrCreate(
                ['slug' => $category['slug']],
                $category
            );
        }

        $this->command->info('Event categories seeded successfully!');
    }
}

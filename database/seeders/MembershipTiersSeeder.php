<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MembershipTiersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tiers = [
            [
                'name' => 'Student Member',
                'slug' => 'student-member',
                'description' => 'Special rate for students with valid student ID',
                'price' => 50000,
                'duration_months' => 12,
                'benefits' => [
                    'Access to all iC-Talk events',
                    'Access to iC-Connect networking sessions',
                    'Member-only resources',
                    'Newsletter subscription',
                ],
                'is_active' => true,
                'order' => 1,
                'color' => '#3B82F6',
                'icon' => 'school',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Regular Member',
                'slug' => 'regular-member',
                'description' => 'Standard membership for professionals',
                'price' => 100000,
                'duration_months' => 12,
                'benefits' => [
                    'Access to all events',
                    'Priority registration for workshops',
                    'Networking opportunities',
                    'Member directory listing',
                    'Exclusive content access',
                ],
                'is_active' => true,
                'order' => 2,
                'color' => '#10B981',
                'icon' => 'person',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Corporate Member',
                'slug' => 'corporate-member',
                'description' => 'Membership for companies and organizations',
                'price' => 500000,
                'duration_months' => 12,
                'benefits' => [
                    'Up to 5 employee registrations',
                    'Company logo on website',
                    'Priority event sponsorship',
                    'Exclusive corporate networking',
                    'Quarterly business insights',
                ],
                'is_active' => true,
                'order' => 3,
                'color' => '#F59E0B',
                'icon' => 'business',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Lifetime Member',
                'slug' => 'lifetime-member',
                'description' => 'One-time payment for lifetime access',
                'price' => 2000000,
                'duration_months' => null, // Lifetime
                'benefits' => [
                    'Lifetime access to all events',
                    'VIP status at all gatherings',
                    'Exclusive lifetime member badge',
                    'Priority support',
                    'Legacy member recognition',
                ],
                'is_active' => true,
                'order' => 4,
                'color' => '#8B5CF6',
                'icon' => 'workspace_premium',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($tiers as $tier) {
            DB::table('membership_tiers')->updateOrInsert(
                ['slug' => $tier['slug']],
                $tier
            );
        }

        $this->command->info('✅ Membership tiers seeded successfully!');
        $this->command->info('   - Student Member (Rp 50,000/year)');
        $this->command->info('   - Regular Member (Rp 100,000/year)');
        $this->command->info('   - Corporate Member (Rp 500,000/year)');
        $this->command->info('   - Lifetime Member (Rp 2,000,000)');
    }
}

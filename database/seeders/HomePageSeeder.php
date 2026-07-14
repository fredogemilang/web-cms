<?php

namespace Database\Seeders;

use App\Models\Page;
use App\Models\PageBlock;
use Illuminate\Database\Seeder;

class HomePageSeeder extends Seeder
{
    public function run(): void
    {
        $page = Page::firstOrCreate(
            ['slug' => 'home'],
            [
                'title' => 'Home',
                'status' => 'published',
                'template' => 'default',
                'author_id' => 1,
                'menu_order' => 0,
            ]
        );

        // Clear existing blocks for clean slate
        $page->allBlocks()->delete();

        $blocks = [
            // ── HERO SECTION ──────────────────────────────────────────
            [
                'name' => 'hero_title',
                'type' => 'text',
                'label' => 'Hero Title',
                'value' => 'iCCom',
                'order' => 1,
            ],
            [
                'name' => 'hero_subtitle',
                'type' => 'text',
                'label' => 'Hero Subtitle',
                'value' => 'Indonesia Cloud Community',
                'order' => 2,
            ],
            [
                'name' => 'hero_description',
                'type' => 'textarea',
                'label' => 'Hero Description',
                'value' => 'Join us to level up your skills in becoming cloud engineer, and take a step forward to improve the development of cloud in Indonesia.',
                'order' => 3,
            ],

            // ── WHO ARE WE SECTION ────────────────────────────────────
            [
                'name' => 'who_are_we_title',
                'type' => 'text',
                'label' => 'Who Are We Title',
                'value' => 'Who Are We?',
                'order' => 4,
            ],
            [
                'name' => 'who_are_we_description',
                'type' => 'wysiwyg',
                'label' => 'Who Are We Description',
                'value' => '<p><strong>iCCom</strong>, or <strong>Indonesia Cloud Community</strong>, is a community for people who are enthusiastic about the development of cloud technology. We are a non-profit organization that seeks to contribute to the growth of Indonesia\'s cloud expert resources. We welcome all cloud enthusiasts, from newbies to professionals.</p>',
                'order' => 5,
            ],

            // ── OUR MISSIONS SECTION ──────────────────────────────────
            [
                'name' => 'iccom_core_value_title',
                'type' => 'text',
                'label' => 'Missions Title',
                'value' => 'Our Missions',
                'order' => 6,
            ],
            [
                'name' => 'iccom_core_value_loop',
                'type' => 'repeater',
                'label' => 'Missions List',
                'value' => json_encode([
                    ['core_title' => 'Develop Skills & Knowledge',      'core_description' => "Develop members' skills and knowledge to become cloud engineer."],
                    ['core_title' => 'Empower Knowledge Sharing',       'core_description' => 'Empower members to share their knowledge and best practices with one another.'],
                    ['core_title' => 'Conduct Networking Sessions',     'core_description' => 'Conduct networking session for members to engage with other cloud enthusiasts and technologists.'],
                    ['core_title' => 'Advance Members\' Careers',       'core_description' => "Advance members' careers by offering a platform for members to show their skills/experience through community events."],
                ]),
                'options' => ['min_items' => 0, 'max_items' => 20, 'button_label' => 'Add Mission'],
                'children' => [
                    ['name' => 'core_title',       'type' => 'text',     'label' => 'Title',       'order' => 0],
                    ['name' => 'core_description', 'type' => 'textarea', 'label' => 'Description', 'order' => 1],
                ],
                'order' => 7,
            ],

            // ── STATS SECTION ─────────────────────────────────────────
            [
                'name' => 'counter_member',
                'type' => 'number',
                'label' => 'Counter Number',
                'value' => 50000,
                'order' => 8,
            ],
            [
                'name' => 'counter_title',
                'type' => 'text',
                'label' => 'Counter Title',
                'value' => 'Have Joined to Be a Part of iCCom',
                'order' => 9,
            ],

            // ── OUR PARTNERS SECTION ──────────────────────────────────
            [
                'name' => 'our_partners_title',
                'type' => 'text',
                'label' => 'Partners Title',
                'value' => 'Our Partners',
                'order' => 10,
            ],

            // ── CTA / GROW TOGETHER SECTION ───────────────────────────
            [
                'name' => 'talent_referral_title',
                'type' => 'text',
                'label' => 'CTA Title',
                'value' => 'Join us for FREE!',
                'order' => 11,
            ],
            [
                'name' => 'talent_referral_description',
                'type' => 'textarea',
                'label' => 'CTA Description',
                'value' => "Enjoy all of our exclusive community activities!\n\n#UNITED@CLOUD",
                'order' => 12,
            ],

            // ── TESTIMONIALS SECTION TITLE ────────────────────────────
            [
                'name' => 'testimonal_title',
                'type' => 'text',
                'label' => 'Testimonials Title',
                'value' => 'What Our Members Say',
                'order' => 13,
            ],
        ];

        $created = 0;
        foreach ($blocks as $block) {
            $parent = PageBlock::create([
                'page_id' => $page->id,
                'name' => $block['name'],
                'type' => $block['type'],
                'label' => $block['label'],
                'value' => $block['value'],
                'options' => $block['options'] ?? [],
                'order' => $block['order'],
                'is_active' => true,
            ]);
            $created++;

            // Create child schema blocks for repeater types
            if (! empty($block['children'])) {
                foreach ($block['children'] as $childIndex => $child) {
                    PageBlock::create([
                        'page_id' => $page->id,
                        'parent_block_id' => $parent->id,
                        'name' => $child['name'],
                        'type' => $child['type'],
                        'label' => $child['label'],
                        'value' => '',
                        'order' => $child['order'] ?? $childIndex,
                        'is_active' => true,
                    ]);
                    $created++;
                }
            }
        }

        echo "✅ Home page blocks seeded: {$created} blocks created.\n";
    }
}

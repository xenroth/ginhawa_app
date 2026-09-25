<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // 1. Ensure sectors table has baseline active sectors
        if (Schema::hasTable('sectors')) {
            $defaultSectors = [
                ['name' => 'MANIFESTO', 'slug' => 'manifesto', 'description' => 'Charter, covenants, and foundational declarations.', 'is_active' => true, 'sort_order' => 1],
                ['name' => 'OPERATIVES', 'slug' => 'operatives', 'description' => 'Tactical coordination, field dispatches, and operative logistics.', 'is_active' => true, 'sort_order' => 2],
                ['name' => 'PRESERVATION', 'slug' => 'preservation', 'description' => 'Sanctuary network, food security, and human protection directives.', 'is_active' => true, 'sort_order' => 3],
                ['name' => 'ARCHIVES', 'slug' => 'archives', 'description' => 'Decentralized records, investigative intel, and declassified files.', 'is_active' => true, 'sort_order' => 4],
                ['name' => 'LOUNGE', 'slug' => 'lounge', 'description' => 'Open citizen communications and communal dialogue.', 'is_active' => true, 'sort_order' => 5],
            ];

            foreach ($defaultSectors as $sector) {
                DB::table('sectors')->updateOrInsert(
                    ['name' => $sector['name']],
                    array_merge($sector, [
                        'created_at' => now(),
                        'updated_at' => now(),
                    ])
                );
            }
        }

        // 2. Ensure directives table has baseline active directives
        if (Schema::hasTable('directives')) {
            $defaultDirectives = [
                ['pillar' => 'PAGTIPIG', 'title' => 'Preserve human life', 'body' => 'Life is non-negotiable. Sanctuary, medical security, and mutual preservation above all systems.', 'is_active' => true, 'sort_order' => 1],
                ['pillar' => 'KAUGALINGON', 'title' => 'Retain humanity', 'body' => 'Defend privacy, autonomy, and moral dignity without compromise against predatory surveillance.', 'is_active' => true, 'sort_order' => 2],
                ['pillar' => 'PAG-USWAG', 'title' => 'Develop humanity', 'body' => 'Advance decentralized intelligence, open science, sovereign education, and collective ascension.', 'is_active' => true, 'sort_order' => 3],
                ['pillar' => 'PAG-ATBANG', 'title' => 'Eradicate corruption', 'body' => 'Expose predatory hierarchies and build resilient community alternatives.', 'is_active' => true, 'sort_order' => 4],
            ];

            foreach ($defaultDirectives as $directive) {
                DB::table('directives')->updateOrInsert(
                    ['title' => $directive['title']],
                    array_merge($directive, [
                        'created_at' => now(),
                        'updated_at' => now(),
                    ])
                );
            }
        }

        // 3. Populate clearance rules and custom widget settings in site_settings
        if (Schema::hasTable('site_settings')) {
            $defaultSettings = [
                'clearance_rules' => implode("\n", [
                    'CLEARANCE 01 // PUBLIC CITIZEN: Read-only access to unclassified transmissions and general directives.',
                    'CLEARANCE 02 // VERIFIED MEMBER: Outbound dispatch capabilities, encrypted comments, and peer connections.',
                    'CLEARANCE 03 // MODERATOR & ENFORCER: Intel signal verification, citizen review, and sector supervision.',
                    'CLEARANCE 04 // COUNCIL ADMINISTRATOR: System architecture, cryptographic controls, and directive promulgation.',
                ]),
                'custom_widget_enabled' => '1',
                'custom_widget_badge' => '// ALLIED NETWORK',
                'custom_widget_title' => 'HUMANITARIAN RELIEF & OPERATIVES ALLIANCE',
                'custom_widget_content' => 'Mutual aid coordinates, field sanctuaries, and encrypted supply chains are deployed through our decentralized nodes. Connect with regional coordinators to support the mission.',
            ];

            foreach ($defaultSettings as $key => $value) {
                DB::table('site_settings')->updateOrInsert(
                    ['key' => $key],
                    [
                        'value' => $value,
                        'type' => 'text',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            }
        }
    }

    public function down(): void
    {
        // Non-destructive rollback
    }
};

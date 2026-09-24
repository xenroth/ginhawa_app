<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $roles = collect([
            ['name' => 'citizen', 'label' => 'Public Citizen', 'clearance' => 1],
            ['name' => 'member', 'label' => 'Member / Operative', 'clearance' => 2],
            ['name' => 'moderator', 'label' => 'Moderator / Enforcer', 'clearance' => 4],
            ['name' => 'administrator', 'label' => 'Administrator', 'clearance' => 5],
            ['name' => 'benefactor', 'label' => 'Benefactor', 'clearance' => 3],
        ])->mapWithKeys(fn ($role) => [$role['name'] => Role::create($role)]);
        $admin = User::create(['name' => 'Akhzaroth Khan', 'email' => 'admin@ginhawa.local', 'password' => Hash::make('change-me-now'), 'status' => 'active', 'approved_at' => now()]);
        $admin->roles()->attach($roles['administrator']);
        foreach ([['name' => 'MANIFESTO', 'slug' => 'manifesto'], ['name' => 'OPERATIVES', 'slug' => 'operatives'], ['name' => 'PRESERVATION', 'slug' => 'preservation'], ['name' => 'ARCHIVES', 'slug' => 'archives'], ['name' => 'LOUNGE', 'slug' => 'lounge']] as $sector) \App\Models\Sector::create($sector);
        foreach ([['title' => 'Preserve human life', 'body' => 'Life is non-negotiable.', 'pillar' => 'PAGTIPIG'], ['title' => 'Retain humanity', 'body' => 'Protect autonomy, privacy, and dignity.', 'pillar' => 'KAUGALINGON'], ['title' => 'Develop humanity', 'body' => 'Advance open science and education.', 'pillar' => 'PAG-USWAG'], ['title' => 'Eradicate corruption', 'body' => 'Expose predatory systems and protect communities.', 'pillar' => 'PAG-ATBANG']] as $directive) \App\Models\Directive::create($directive);
        foreach ([['key' => 'seo_title', 'value' => 'GINHAWA // Covenant Network'], ['key' => 'seo_description', 'value' => 'A member-led network for human preservation and community intelligence.'], ['key' => 'seo_keywords', 'value' => 'Ginhawa, community, preservation, covenant'], ['key' => 'site_name', 'value' => 'GINHAWA'], ['key' => 'site_tagline', 'value' => '// DECENTRALIZED INTEL NETWORK'], ['key' => 'default_jurisdiction', 'value' => 'Local Community / Own Country'], ['key' => 'default_designation', 'value' => 'Ginhawa Citizen']] as $setting) \App\Models\SiteSetting::create($setting);
    }
}
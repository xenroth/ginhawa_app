<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        foreach ([['name' => 'MANIFESTO', 'slug' => 'manifesto'], ['name' => 'OPERATIVES', 'slug' => 'operatives'], ['name' => 'PRESERVATION', 'slug' => 'preservation'], ['name' => 'ARCHIVES', 'slug' => 'archives'], ['name' => 'LOUNGE', 'slug' => 'lounge']] as $sector) DB::table('sectors')->updateOrInsert(['slug' => $sector['slug']], array_merge($sector, ['updated_at' => now(), 'created_at' => now()]));
        foreach ([['key' => 'seo_title', 'value' => 'GINHAWA // Covenant Network'], ['key' => 'seo_description', 'value' => 'A member-led network for human preservation and community intelligence.'], ['key' => 'seo_keywords', 'value' => 'Ginhawa, community, preservation, covenant'], ['key' => 'site_name', 'value' => 'GINHAWA']] as $setting) DB::table('site_settings')->updateOrInsert(['key' => $setting['key']], array_merge($setting, ['type' => 'text', 'updated_at' => now(), 'created_at' => now()]));
    }
    public function down(): void { }
};
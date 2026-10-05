<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('users') && !Schema::hasColumn('users', 'community_last_seen_at')) {
            Schema::table('users', function (Blueprint $table) {
                $table->timestamp('community_last_seen_at')->nullable()->after('approved_at');
            });
        }
    }

    public function down(): void
    {
        // Intentionally non-destructive for production data.
    }
};

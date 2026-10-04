<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('comments')) {
            Schema::table('comments', function (Blueprint $table) {
                if (!Schema::hasColumn('comments', 'parent_id')) {
                    $table->foreignId('parent_id')->nullable()->after('post_id');
                    $table->index('parent_id');
                }
            });
        }
        if (!Schema::hasTable('comment_reactions')) {
            Schema::create('comment_reactions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('comment_id')->constrained('comments')->cascadeOnDelete();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->string('value');
                $table->timestamps();
                $table->unique(['comment_id', 'user_id']);
            });
        }
    }

    public function down(): void
    {
        // Intentionally non-destructive for production data.
    }
};

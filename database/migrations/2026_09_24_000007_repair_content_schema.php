<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('posts')) {
            Schema::table('posts', function (Blueprint $table) {
                if (!Schema::hasColumn('posts', 'status')) $table->string('status')->default('pending');
                if (!Schema::hasColumn('posts', 'is_pinned')) $table->boolean('is_pinned')->default(false);
                if (!Schema::hasColumn('posts', 'clearance')) $table->string('clearance')->default('public');
                if (!Schema::hasColumn('posts', 'tags')) $table->json('tags')->nullable();
            });
        }
        if (!Schema::hasTable('sectors')) Schema::create('sectors', function (Blueprint $table) { $table->id(); $table->string('name')->unique(); $table->string('slug')->unique(); $table->text('description')->nullable(); $table->boolean('is_active')->default(true); $table->unsignedInteger('sort_order')->default(0); $table->timestamps(); });
        if (!Schema::hasTable('directives')) Schema::create('directives', function (Blueprint $table) { $table->id(); $table->string('title'); $table->text('body'); $table->string('pillar')->nullable(); $table->boolean('is_active')->default(true); $table->unsignedInteger('sort_order')->default(0); $table->timestamps(); });
    }

    public function down(): void
    {
        // Content repair is intentionally non-destructive for production data.
    }
};
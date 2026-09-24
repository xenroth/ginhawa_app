<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'citizen_number')) $table->string('citizen_number')->nullable()->unique();
            if (!Schema::hasColumn('users', 'phone')) $table->string('phone')->nullable();
            if (!Schema::hasColumn('users', 'social_handle')) $table->string('social_handle')->nullable();
            if (!Schema::hasColumn('users', 'avatar_path')) $table->string('avatar_path')->nullable();
            if (!Schema::hasColumn('users', 'cover_path')) $table->string('cover_path')->nullable();
            if (!Schema::hasColumn('users', 'profile_visibility')) $table->string('profile_visibility')->default('members');
            if (!Schema::hasColumn('users', 'hide_contact')) $table->boolean('hide_contact')->default(true);
        });
        Schema::table('roles', function (Blueprint $table) {
            if (!Schema::hasColumn('roles', 'permissions')) $table->json('permissions')->nullable();
        });

        Schema::create('verification_documents', function (Blueprint $table) {
            $table->id(); $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('valid_id_path')->nullable(); $table->string('social_handle')->nullable(); $table->string('mobile_number')->nullable();
            $table->string('status')->default('pending'); $table->text('admin_notes')->nullable(); $table->timestamps();
        });
        Schema::create('comments', function (Blueprint $table) {
            $table->id(); $table->foreignId('post_id')->constrained()->cascadeOnDelete(); $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->text('body'); $table->string('status')->default('approved'); $table->timestamps();
        });
        Schema::create('connections', function (Blueprint $table) {
            $table->id(); $table->foreignId('requester_id')->constrained('users')->cascadeOnDelete(); $table->foreignId('recipient_id')->constrained('users')->cascadeOnDelete();
            $table->string('status')->default('pending'); $table->timestamps(); $table->unique(['requester_id', 'recipient_id']);
        });
        Schema::create('messages', function (Blueprint $table) {
            $table->id(); $table->foreignId('sender_id')->constrained('users')->cascadeOnDelete(); $table->foreignId('recipient_id')->constrained('users')->cascadeOnDelete();
            $table->text('body'); $table->timestamp('read_at')->nullable(); $table->timestamps();
        });
        Schema::create('sectors', function (Blueprint $table) { $table->id(); $table->string('name')->unique(); $table->string('slug')->unique(); $table->text('description')->nullable(); $table->boolean('is_active')->default(true); $table->unsignedInteger('sort_order')->default(0); $table->timestamps(); });
        Schema::create('directives', function (Blueprint $table) { $table->id(); $table->string('title'); $table->text('body'); $table->string('pillar')->nullable(); $table->boolean('is_active')->default(true); $table->unsignedInteger('sort_order')->default(0); $table->timestamps(); });
        Schema::create('site_settings', function (Blueprint $table) { $table->id(); $table->string('key')->unique(); $table->text('value')->nullable(); $table->string('type')->default('text'); $table->timestamps(); });
    }

    public function down(): void
    {
        Schema::dropIfExists('site_settings'); Schema::dropIfExists('directives'); Schema::dropIfExists('sectors'); Schema::dropIfExists('messages'); Schema::dropIfExists('connections'); Schema::dropIfExists('comments'); Schema::dropIfExists('verification_documents');
        Schema::table('roles', function (Blueprint $table) { if (Schema::hasColumn('roles', 'permissions')) $table->dropColumn('permissions'); });
        Schema::table('users', function (Blueprint $table) { foreach (['citizen_number', 'phone', 'social_handle', 'avatar_path', 'cover_path', 'profile_visibility', 'hide_contact'] as $column) if (Schema::hasColumn('users', $column)) $table->dropColumn($column); });
    }
};
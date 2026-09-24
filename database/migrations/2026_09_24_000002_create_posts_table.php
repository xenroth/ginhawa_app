<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void { Schema::create('posts', function (Blueprint $table) { $table->id(); $table->foreignId('user_id')->constrained()->cascadeOnDelete(); $table->string('title'); $table->text('body'); $table->string('sector'); $table->string('clearance')->default('public'); $table->json('tags')->nullable(); $table->string('status')->default('pending'); $table->boolean('is_pinned')->default(false); $table->timestamps(); }); }
    public function down(): void { Schema::dropIfExists('posts'); }
};
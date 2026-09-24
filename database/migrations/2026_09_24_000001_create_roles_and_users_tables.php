<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('roles', function (Blueprint $table) { $table->id(); $table->string('name')->unique(); $table->string('label'); $table->unsignedTinyInteger('clearance')->default(1); $table->timestamps(); });
        Schema::create('users', function (Blueprint $table) { $table->id(); $table->string('name'); $table->string('email')->unique(); $table->timestamp('email_verified_at')->nullable(); $table->string('password'); $table->string('status')->default('pending'); $table->timestamp('approved_at')->nullable(); $table->rememberToken(); $table->timestamps(); });
        Schema::create('role_user', function (Blueprint $table) { $table->foreignId('role_id')->constrained()->cascadeOnDelete(); $table->foreignId('user_id')->constrained()->cascadeOnDelete(); $table->primary(['role_id', 'user_id']); });
    }
    public function down(): void { Schema::dropIfExists('role_user'); Schema::dropIfExists('users'); Schema::dropIfExists('roles'); }
};
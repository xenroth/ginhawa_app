<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('password_reset_requests')) {
            Schema::create('password_reset_requests', function (Blueprint $table) {
                $table->id();
                $table->string('email');
                $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('status')->default('pending');
                $table->text('note')->nullable();
                $table->timestamp('handled_at')->nullable();
                $table->timestamps();
                $table->index(['status', 'email']);
            });
        }
    }

    public function down(): void
    {
        // Intentionally non-destructive for production data.
    }
};

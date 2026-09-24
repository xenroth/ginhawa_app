<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'jurisdiction')) $table->string('jurisdiction')->nullable();
            if (!Schema::hasColumn('users', 'designation')) $table->string('designation')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'jurisdiction')) $table->dropColumn('jurisdiction');
            if (Schema::hasColumn('users', 'designation')) $table->dropColumn('designation');
        });
    }
};
<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

abstract class Controller
{
    private static $schemaReady = false;

    /**
     * Runtime schema ensure (shared by community + interaction controllers).
     * Adds media column and post_reactions table when missing, so the feature
     * works on hosts without SSH/artisan access. Safe to call on every request.
     */
    protected function ensureSchema(): void
    {
        if (self::$schemaReady) {
            return;
        }
        try {
            if (Schema::hasTable('posts') && !Schema::hasColumn('posts', 'media')) {
                Schema::table('posts', function (Blueprint $table) {
                    $table->json('media')->nullable();
                });
            }
            if (Schema::hasTable('sectors') && !Schema::hasColumn('sectors', 'requires_approval')) {
                Schema::table('sectors', function (Blueprint $table) {
                    $table->boolean('requires_approval')->default(true);
                });
            }
            if (!Schema::hasTable('post_reactions')) {
                Schema::create('post_reactions', function (Blueprint $table) {
                    $table->id();
                    $table->foreignId('post_id')->constrained()->cascadeOnDelete();
                    $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                    $table->string('value');
                    $table->timestamps();
                    $table->unique(['post_id', 'user_id']);
                });
            }
            if (!is_dir(storage_path('app/public/posts'))) {
                @mkdir(storage_path('app/public/posts'), 0775, true);
            }
            self::$schemaReady = true;
        } catch (\Throwable $exception) {
            Log::error('Schema ensure failed.', ['exception' => $exception]);
        }
    }
}

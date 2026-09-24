<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        DB::table('users')->whereNull('citizen_number')->orderBy('id')->eachById(function ($user) {
            do { $number = 'GHW-'.strtoupper(bin2hex(random_bytes(2))).'-'.strtoupper(bin2hex(random_bytes(2))).'-'.date('Y'); } while (DB::table('users')->where('citizen_number', $number)->exists());
            DB::table('users')->where('id', $user->id)->update(['citizen_number' => $number]);
        });
    }
    public function down(): void { }
};
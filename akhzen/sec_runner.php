<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
if (Schema::hasTable('sectors') && !Schema::hasColumn('sectors', 'requires_approval')) {
    Schema::table('sectors', function (Blueprint $table) {
        $table->boolean('requires_approval')->default(true);
    });
    echo "column added\n";
} else { echo "column exists or no table\n"; }
echo "sectors: ".json_encode(Illuminate\Support\Facades\DB::table('sectors')->select('id','name','requires_approval')->get()->all())."\n";

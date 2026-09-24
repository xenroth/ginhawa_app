<?php

use App\Models\Role;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    public function up(): void { Role::firstOrCreate(['name' => 'benefactor'], ['label' => 'Benefactor', 'clearance' => 3, 'permissions' => ['card.generate']]); }
    public function down(): void { Role::where('name', 'benefactor')->delete(); }
};
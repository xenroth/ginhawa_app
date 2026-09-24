<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $roles = collect([
            ['name' => 'citizen', 'label' => 'Public Citizen', 'clearance' => 1],
            ['name' => 'member', 'label' => 'Member / Operative', 'clearance' => 2],
            ['name' => 'moderator', 'label' => 'Moderator / Enforcer', 'clearance' => 4],
            ['name' => 'administrator', 'label' => 'Administrator', 'clearance' => 5],
        ])->mapWithKeys(fn ($role) => [$role['name'] => Role::create($role)]);
        $admin = User::create(['name' => 'Akhzaroth Khan', 'email' => 'admin@ginhawa.local', 'password' => Hash::make('change-me-now'), 'status' => 'active', 'approved_at' => now()]);
        $admin->roles()->attach($roles['administrator']);
    }
}
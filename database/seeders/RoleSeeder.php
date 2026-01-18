<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            ['name' => 'admin', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'doctor', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'patient', 'created_at' => now(), 'updated_at' => now()],
        ];

        foreach ($roles as $role) {
            DB::table('roles')->updateOrInsert(
                ['name' => $role['name']], // Check if the role exists by name
                ['created_at' => $role['created_at'], 'updated_at' => $role['updated_at']] // Update timestamps if it exists
            );
        }
    }
}
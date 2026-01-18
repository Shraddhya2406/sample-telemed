<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = Role::whereIn('name', ['admin', 'doctor', 'patient'])->get()->keyBy('name');

        $users = [
            [
                'name' => 'Admin User',
                'email' => 'admin@example.com',
                'password' => Hash::make('password'),
                'role_id' => $roles['admin']->id ?? null,
            ],
            [
                'name' => 'Doctor User',
                'email' => 'doctor@example.com',
                'password' => Hash::make('password'),
                'role_id' => $roles['doctor']->id ?? null,
            ],
            [
                'name' => 'Patient User',
                'email' => 'patient@example.com',
                'password' => Hash::make('password'),
                'role_id' => $roles['patient']->id ?? null,
            ],
        ];

        foreach ($users as $user) {
            User::updateOrInsert(
                ['email' => $user['email']], // Check if the user exists by email
                [
                    'name' => $user['name'],
                    'password' => $user['password'],
                    'role_id' => $user['role_id'],
                ]
            );
        }
    }
}
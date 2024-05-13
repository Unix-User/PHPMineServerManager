<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Team;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AdminUserSeeder extends Seeder
{
    public function run()
    {
        // Ensure environment variables are set
        if (!env('ADMIN_NAME') || !env('ADMIN_EMAIL') || !env('ADMIN_PASSWORD') || !env('ADMIN_ROLE')) {
            throw new \Exception('Admin environment variables are not properly set in .env file.');
        }

        $users = [
            [
                'name' => env('ADMIN_NAME', 'Admin'),
                'email' => env('ADMIN_EMAIL', 'admin@admin.com'),
                'password' => env('ADMIN_PASSWORD', 'SecurePassword'),
                'role' => env('ADMIN_ROLE', 'admin')
            ]
        ];

        foreach ($users as $user) {
            // Validate user data
            $validator = Validator::make($user, [
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'password' => 'required|string|min:8',
                'role' => 'required|string'
            ]);

            if ($validator->fails()) {
                throw new \Exception('Validation failed for user data: ' . implode(", ", $validator->errors()->all()));
            }

            $adminUser = User::create([
                'name' => $user['name'],
                'email' => $user['email'],
                'password' => Hash::make($user['password']),
            ]);

            // Check if assignRole method exists and is callable
            if (method_exists($adminUser, 'assignRole')) {
                $adminUser->assignRole($user['role']);
            } else {
                throw new \Exception('Method assignRole does not exist on User model');
            }

            // Create a personal team for the admin user
            $adminUser->ownedTeams()->save(Team::forceCreate([
                'user_id' => $adminUser->id,
                'name' => explode(' ', $adminUser->name, 2)[0] . "'s Team",
                'personal_team' => true,
            ]));
        }
    }
}
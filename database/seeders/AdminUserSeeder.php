<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Team;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run()
    {
        $users = [
            [
                'name' => 'Admin',
                'email' => 'admin@admin.com',
                'password' => 'SecurePassword',
                'role' => 'admin'
            ],
            [
                'name' => 'Weverton',
                'email' => 'wevertonslima@gmail.com',
                'password' => 'Dracar2s',
                'role' => 'admin'
            ]
        ];
        foreach ($users as $user) {
            $adminUser = User::create([
                'name' => $user['name'],
                'email' => $user['email'],
                'password' => Hash::make($user['password']),
            ]);
            $adminUser->assignRole($user['role']);

            // Create a personal team for the admin user
            $adminUser->ownedTeams()->save(Team::forceCreate([
                'user_id' => $adminUser->id,
                'name' => explode(' ', $adminUser->name, 2)[0] . "'s Team",
                'personal_team' => true,
            ]));
        }
    }
}
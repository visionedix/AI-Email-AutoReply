<?php

namespace Database\Seeders;

use App\Models\User;
use App\Support\AdminAccess;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Seed five users.
     */
    public function run(): void
    {
        $users = [
            ['name' => 'admin', 'email' => 'admin@gmail.com'],
            ['name' => 'abhishek', 'email' => 'abhishek@gmail.com'],
            ['name' => 'Rohan', 'email' => 'rohan@gmail.com'],
           
        ];

        foreach ($users as $user) {
            $model = User::updateOrCreate(
                ['email' => $user['email']],
                [
                    'name' => $user['name'],
                    'email_verified_at' => now(),
                    'password' => Hash::make('password'),
                ],
            );

            if ($model->email === 'admin@gmail.com') {
                $model->syncRoles('admin');
            } else {
                $model->syncRoles('staff');
            }
        }
    }
}

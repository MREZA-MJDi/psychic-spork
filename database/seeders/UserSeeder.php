<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            [
                'name' => 'مدیر جانان',
                'email' => 'admin@janan.test',
                'password' => 'password123',
                'is_admin' => true,
            ],
            [
                'name' => 'مشتری نمونه',
                'email' => 'customer@janan.test',
                'password' => 'password123',
                'is_admin' => false,
            ],
            [
                'name' => 'سارا احمدی',
                'email' => 'sara@janan.test',
                'password' => 'password123',
                'is_admin' => false,
            ],
            [
                'name' => 'نگار محمدی',
                'email' => 'negar@janan.test',
                'password' => 'password123',
                'is_admin' => false,
            ],
        ];

        foreach ($users as $data) {
            $user = User::firstOrNew([
                'email' => $data['email'],
            ]);

            $user->name = $data['name'];
            $user->is_admin = $data['is_admin'];

            if (! $user->exists) {
                $user->password = Hash::make($data['password']);
                $user->email_verified_at = now();
            }

            $user->save();
        }
    }
}

<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use RuntimeException;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $email = trim((string) config('app.admin.email'));
        $password = (string) config('app.admin.password');
        $name = trim((string) config('app.admin.name', 'Janan Admin'));

        if ($email === '' || $password === '') {
            throw new RuntimeException(
                'JANAN_ADMIN_EMAIL and JANAN_ADMIN_PASSWORD must be set in your .env before seeding the admin user.'
            );
        }

        $user = User::query()->firstOrNew([
            'email' => $email,
        ]);

        $isNew = ! $user->exists;

        if ($isNew) {
            $user->name = $name;
            $user->password = $password;
        }

        // This seeder never downgrades an existing account and never
        // overwrites an existing admin password on repeated db:seed runs.
        $user->is_admin = true;
        $user->save();

        $this->command?->info(
            $isNew
                ? "Admin user created: {$email}"
                : "Admin access confirmed for: {$email}"
        );
    }
}

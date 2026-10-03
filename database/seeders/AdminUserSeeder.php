<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use RuntimeException;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $phone = trim((string) config('app.admin.phone'));
        $email = trim((string) config('app.admin.email'));
        $password = (string) config('app.admin.password');
        $name = trim((string) config('app.admin.name', 'Janan Admin'));

        if ($phone === '' || $password === '') {
            throw new RuntimeException(
                'Set JANAN_ADMIN_PHONE and JANAN_ADMIN_PASSWORD before creating an admin account.'
            );
        }

        $user = User::query()->where('phone', $phone)->first();

        if (! $user && $email !== '') {
            $user = User::query()->where('email', $email)->first();
        }

        $user ??= new User();

        $isNew = ! $user->exists;

        // .env is the source of truth for the local admin account.
        // Re-running this seeder intentionally synchronizes the name and password.
        $user->name = $name;
        $user->phone = $phone;
        $user->email = $email !== '' ? $email : null;
        $user->password = $password;
        $user->is_admin = true;
        $user->save();

        $this->command?->info(
            $isNew
                ? "Admin user created: {$phone}"
                : "Admin credentials synchronized: {$phone}"
        );
    }
}

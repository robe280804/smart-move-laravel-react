<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

use function Symfony\Component\Clock\now;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        $email = config('app.admin_email');
        $password = config('app.admin_password');

        if (! $email || ! $password) {
            $this->command->warn('AdminSeeder skipped: ADMIN_EMAIL or ADMIN_PASSWORD not set in .env');

            return;
        }

        $admin = User::firstOrCreate(
            ['email' => $email],
            [
                'name' => 'Admin',
                'surname' => 'Admin',
                'password' => $password,
                'email_verified_at' => now(),
            ]
        );

        if (! $admin->hasRole(Role::Admin->value)) {
            $admin->assignRole(Role::Admin->value);
        }
    }
}

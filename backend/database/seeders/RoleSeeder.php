<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\Role;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role as SpatieRole;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        foreach (Role::cases() as $role) {
            SpatieRole::firstOrCreate(['name' => $role->value, 'guard_name' => 'web']);
        }
    }
}

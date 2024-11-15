<?php

namespace Database\Seeders;

use App\Enums\RoleEnum;
use App\Models\Role;
use App\Models\RoleUser;
use App\Models\User;
use Illuminate\Database\Seeder;

class RolesSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $admin_role = Role::create([
            'name' => RoleEnum::ADMIN->name,
            'permissions' => '*'
        ]);

        $manager_role = Role::create([
            'name' => RoleEnum::MANAGER->name,
            'permissions' => '*'
        ]);

        $client_role = Role::create([
            'name' => RoleEnum::USER->name,
            'permissions' => json_encode([
                'gpt',
                'gpt_settings',
            ])
        ]);

        User::find(1)->update([
            'role_id' => $admin_role->id,
        ]);
        User::find(2)->update([
            'role_id' => $manager_role->id,
        ]);
        User::find(3)->update([
            'role_id' => $client_role->id,
        ]);
    }
}

<?php

namespace Database\Seeders;

use App\Enums\RoleEnum;
use App\Models\GptPrompt;
use App\Models\Role;
use App\Models\RoleUser;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class RaOwner extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $user = User::create([
            'name' => 'ra-oleg@rb.ru',
            'email' => 'pismenov@bk.ru',
            'email_verified_at' => now()->format('Y-m-d H:i:s'),
            'password' => Hash::make('12345$'),
            'role_id' => 9
        ]);
        GptPrompt::create([
            'user_id' => $user->id,
            'prompt' => 'Тест',
            'owner' => '153',
        ]);
    }
}

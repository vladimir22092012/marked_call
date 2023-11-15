<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\Owners;
use Inertia\Inertia;

class AdminController extends Controller
{
    public function users() {
        return Inertia::render('Users/Index', [
            'users' => User::query()->get()->toArray(),
        ]);
    }

    public function form() {
        return Inertia::render('Users/Form', [

        ]);
    }

    public function save() {
        echo 'createUser';
    }

    public function delete(User $user) {
        try {
            $user->delete();
            return $this->success(['status' => 'ok', 'users' => User::query()->get()->toArray()], 'Пользователь удалён', 200);
        } catch (\Exception $exception) {
            return $this->error(500, 'Пользователь не удалён', $exception);
        }
    }
}

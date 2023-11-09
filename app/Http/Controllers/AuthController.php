<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use function Termwind\render;

class AuthController extends Controller
{
    public function auth(Request $request) {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

            return redirect()->to('/');
        }
        return back()->withErrors([
            'email' => 'Не верный логин или пароль.',
        ])->onlyInput('email');
    }

    public function logout() {
        Auth::logout();
        return redirect()->to('/');
    }
}

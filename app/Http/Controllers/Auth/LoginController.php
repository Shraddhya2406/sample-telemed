<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    /**
     * Show the login form.
     */
    public function showLoginForm()
    {
        return view('auth.login');
    }

    /**
     * Handle the login request.
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

            $user = Auth::user()->load('role');
            $roleName = $user->role?->name;

            // Redirect based on user role
            return match($roleName) {
                'admin' => redirect()->intended(route('dashboard.admin')),
                'doctor' => redirect()->intended(route('dashboard.doctor')),
                'patient' => redirect()->intended(route('dashboard.patient')),
                default => redirect()->intended('/'),
            };
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ]);
    }
}
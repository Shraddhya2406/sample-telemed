<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Role;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class RegisterController extends Controller
{
    /**
     * Show the registration form.
     */
    public function showRegistrationForm()
    {
        return view('auth.register');
    }

    /**
     * Handle the registration request.
     */
    public function register(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role_id' => 'required|exists:roles,id',
        ]);
        $role_id = Role::where('name', $data['role_id'])->value('id');

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role_id' => $data['role_id'],
        ]);

        Auth::login($user);

        // Refresh user to load role relationship
        $user->refresh();
        $roleName = $user->role?->name;

        // Redirect based on user role (default to patient dashboard if no role)
        return match($roleName) {
            'admin' => redirect()->intended(route('dashboard.admin')),
            'doctor' => redirect()->intended(route('dashboard.doctor')),
            'patient' => redirect()->intended(route('dashboard.patient')),
            default => redirect()->intended(route('dashboard.patient')), // Default to patient
        };
    }
}
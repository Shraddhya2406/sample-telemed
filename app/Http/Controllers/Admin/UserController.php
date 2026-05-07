<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateUserRoleRequest;
use App\Models\Role;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class UserController extends Controller
{
    public function index(): View
    {
        $users = User::query()
            ->with('role:id,name')
            ->withCount('orders')
            ->latest()
            ->get();

        return view('admin.users.index', compact('users'));
    }

    public function show(User $user): View
    {
        $user->load(['role:id,name', 'orders.items.medicine']);

        return view('admin.users.show', compact('user'));
    }

    public function edit(User $user): View
    {
        $roles = Role::query()
            ->whereIn('name', ['admin', 'patient'])
            ->orderBy('name')
            ->get();

        $user->load('role:id,name');

        return view('admin.users.edit', compact('user', 'roles'));
    }

    public function update(UpdateUserRoleRequest $request, User $user): RedirectResponse
    {
        $user->update($request->validated());

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'User role updated successfully.');
    }
}

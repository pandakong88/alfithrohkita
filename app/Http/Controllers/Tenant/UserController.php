<?php

namespace App\Http\Controllers\Tenant;

use App\Domains\User\Actions\CreateUserAction;
use App\Domains\User\Actions\DeleteUserAction;
use App\Domains\User\Actions\RestoreUserAction;
use App\Domains\User\Actions\ToggleUserStatusAction;
use App\Domains\User\Actions\UpdateUserAction;
use App\Domains\User\DTO\CreateUserData;
use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index()
    {
        $users = User::with('roles')
            ->whereDoesntHave('roles', function ($query) {
                $query->where('name', 'super_admin');
            })
            ->latest()
            ->get();
    
        return view('tenant.user.index', compact('users'));
    }
    

    public function create()
    {
        $roles = Role::where('pondok_id', auth()->user()->pondok_id)
            ->whereNotIn('name', ['super_admin'])
            ->get();

        return view('tenant.user.create', compact('roles'));
    }

    public function store(Request $request, CreateUserAction $action)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6',
            'role_id' => [
                'required',
                function ($attribute, $value, $fail) {
                    $role = Role::where('id', $value)
                        ->where('pondok_id', auth()->user()->pondok_id)
                        ->first();

                    if (!$role) {
                        $fail('Role tidak valid.');
                    }
                }
            ],
        ]);

        $dto = CreateUserData::fromArray($validated);

        $action->execute($dto);

        return redirect()
            ->route('tenant.user.index')
            ->with('success', 'User berhasil dibuat.');
    }

    public function edit(User $user)
    {
        $this->ensureTenantAccess($user);

        $roles = Role::where('pondok_id', auth()->user()->pondok_id)
            ->whereNotIn('name', ['super_admin'])
            ->get();

        return view('tenant.user.edit', compact('user', 'roles'));
    }

    public function update(Request $request, User $user, UpdateUserAction $action)
    {
        $this->ensureTenantAccess($user);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'role_id' => [
                'required',
                function ($attribute, $value, $fail) {
                    $role = Role::where('id', $value)
                        ->where('pondok_id', auth()->user()->pondok_id)
                        ->first();

                    if (!$role) {
                        $fail('Role tidak valid.');
                    }
                }
            ],
            'password' => 'nullable|min:6',
        ]);

        $action->execute($user, $validated);

        return redirect()
            ->route('tenant.user.index')
            ->with('success', 'User berhasil diperbarui.');
    }

    public function toggle(User $user, ToggleUserStatusAction $action)
    {
        $this->ensureTenantAccess($user);

        $action->execute($user);

        return back()->with('success', 'Status user diperbarui.');
    }

    public function destroy(User $user, DeleteUserAction $action)
    {
        $this->ensureTenantAccess($user);

        $action->execute($user);

        return back()->with('success', 'User dihapus.');
    }

    /**
     * Pastikan user yang diakses milik pondok ini dan bukan super admin
     */
    private function ensureTenantAccess(User $user): void
    {
        if (
            $user->pondok_id !== auth()->user()->pondok_id ||
            $user->hasRole('super_admin')
        ) {
            abort(403);
        }
    }

    public function trash()
    {
        $users = User::onlyTrashed()
            ->where('pondok_id', auth()->user()->pondok_id)
            ->get();

        return view('tenant.user.trash', compact('users'));
    }

    public function restore($id, RestoreUserAction $action)
{
    $action->execute($id);

    return back()->with('success', 'User berhasil direstore.');
}


}

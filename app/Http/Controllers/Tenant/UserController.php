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
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index()
    {
        $pondokId = auth()->user()->pondok_id;

        $users = User::with('roles')
            ->where('pondok_id', $pondokId)
            ->whereDoesntHave('roles', function ($query) {
                $query->where('name', 'super_admin');
            })
            ->latest()
            ->get();

        return view('tenant.user.index', compact('users'));
    }

    public function create()
    {
        $pondokId = auth()->user()->pondok_id;

        $roles = Role::where('pondok_id', $pondokId)
            ->whereNotIn('name', ['super_admin'])
            ->get();

        return view('tenant.user.create', compact('roles'));
    }

    public function store(Request $request, CreateUserAction $action)
    {
        $pondokId = auth()->user()->pondok_id;

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6',
            'role_id' => [
                'required',
                Rule::exists('roles', 'id')
                    ->where(fn ($q) => $q->where('pondok_id', $pondokId))
            ],
        ]);

        $dto = CreateUserData::fromArray([
            ...$validated,
            'pondok_id' => $pondokId
        ]);

        $action->execute($dto);

        return redirect()
            ->route('tenant.user.index')
            ->with('success', 'User berhasil dibuat.');
    }

    public function edit(User $user)
    {
        $this->ensureTenantAccess($user);

        $pondokId = auth()->user()->pondok_id;

        $roles = Role::where('pondok_id', $pondokId)
            ->whereNotIn('name', ['super_admin'])
            ->get();

        return view('tenant.user.edit', compact('user', 'roles'));
    }

    public function update(Request $request, User $user, UpdateUserAction $action)
    {
        $this->ensureTenantAccess($user);

        $pondokId = auth()->user()->pondok_id;

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                Rule::unique('users', 'email')->ignore($user->id),
            ],
            'role_id' => [
                'required',
                Rule::exists('roles', 'id')
                    ->where(fn ($q) => $q->where('pondok_id', $pondokId))
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

    public function trash()
    {
        $pondokId = auth()->user()->pondok_id;

        $users = User::onlyTrashed()
            ->where('pondok_id', $pondokId)
            ->get();

        return view('tenant.user.trash', compact('users'));
    }

    public function restore($id, RestoreUserAction $action)
    {
        $user = User::onlyTrashed()->findOrFail($id);
    
        $this->ensureTenantAccess($user);
    
        $action->execute($user);
    
        return back()->with('success', 'User berhasil direstore.');
    }
    /**
     * Pastikan user milik pondok ini dan bukan super_admin
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
}
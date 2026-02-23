<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Role;
use App\Models\Permission;
use App\Domains\Role\Actions\CreateRoleAction;
use App\Domains\Role\Actions\UpdateRoleAction;
use App\Domains\Role\Actions\DeleteRoleAction;
use App\Domains\Role\DTO\CreateRoleData;

class RoleController extends Controller
{
    public function index()
    {
        $roles = Role::where('pondok_id', auth()->user()->pondok_id)->get();

        return view('tenant.role.index', compact('roles'));
    }

    public function create()
    {
        $permissions = Permission::all();

        return view('tenant.role.create', compact('permissions'));
    }

    public function store(Request $request, CreateRoleAction $action)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'permissions' => 'array'
        ]);

        $dto = CreateRoleData::fromArray($validated);

        $action->execute($dto);

        return redirect()->route('tenant.role.index')
            ->with('success', 'Role berhasil dibuat.');
    }

    public function edit(Role $role)
    {
        $permissions = Permission::all();

        return view('tenant.role.edit', compact('role','permissions'));
    }

    public function update(Request $request, Role $role, UpdateRoleAction $action)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'permissions' => 'array'
        ]);

        $action->execute($role, $validated);

        return redirect()->route('tenant.role.index')
            ->with('success', 'Role berhasil diperbarui.');
    }

    public function destroy(Role $role, DeleteRoleAction $action)
    {
        $action->execute($role);

        return back()->with('success','Role dihapus.');
    }
}


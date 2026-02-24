<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Domains\Tenant\Actions\CreateTenantAction;
use App\Domains\Tenant\DTO\CreateTenantData;
use App\Domains\Tenant\Actions\UpdateTenantAction;
use App\Domains\Tenant\Actions\ToggleTenantStatusAction;
use App\Domains\Tenant\Actions\DeleteTenantAction;


use App\Models\Pondok;

class PondokController extends Controller
{
    public function index()
    {
        $pondoks = Pondok::latest()->get();
        return view('superadmin.pondok.index', compact('pondoks'));
    }

    public function create()
    {
        return view('superadmin.pondok.create');
    }

    public function store(Request $request, CreateTenantAction $action)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'address' => 'nullable|string',
            'phone' => 'nullable|string',
            'logo' => 'nullable|image|max:2048',
            'admin_name' => 'required|string',
            'admin_email' => 'required|email|unique:users,email',
            'admin_password' => 'required|min:6',
        ]);

        // Tambahkan file logo ke array jika ada
        if ($request->hasFile('logo')) {
            $validated['logo'] = $request->file('logo');
        } else {
            $validated['logo'] = null;
        }

        $dto = CreateTenantData::fromArray($validated);
        $pondok = $action->execute($dto);

        return redirect()
            ->route('superadmin.pondok.index')
            ->with('success', 'Pondok berhasil dibuat.');
    }

    public function edit(Pondok $pondok)
    {
        return view('superadmin.pondok.edit', compact('pondok'));
    }

    public function update(Request $request, Pondok $pondok, UpdateTenantAction $action)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'address' => 'nullable|string',
            'phone' => 'nullable|string',
            'logo' => 'nullable|image|max:2048',
        ]);

        // Tambahkan file logo ke array jika ada
        if ($request->hasFile('logo')) {
            $validated['logo'] = $request->file('logo');
        } else {
            $validated['logo'] = null;
        }

        $old = $pondok->toArray();
        $action->execute($pondok, $validated);
        $pondok->refresh();

        return redirect()->route('superadmin.pondok.index')
            ->with('success', 'Pondok berhasil diperbarui.');
    }

    public function toggle(Pondok $pondok, ToggleTenantStatusAction $action)
    {
        $old = $pondok->toArray();
        $action->execute($pondok);
        $pondok->refresh();

        return back()->with('success', 'Status pondok diperbarui.');
    }
    
    public function destroy(Pondok $pondok, DeleteTenantAction $action)
    {
        $old = $pondok->toArray();
        $action->execute($pondok);

        return redirect()->route('superadmin.pondok.index')
            ->with('success', 'Pondok dihapus (soft delete).');
    }
}

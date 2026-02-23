<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Domains\Auth\Actions\ResolveDashboardRedirect;

class LoginController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect((new ResolveDashboardRedirect())->execute(Auth::user()));
        }

        return view('auth.login');
    }

    public function login(Request $request, ResolveDashboardRedirect $redirectAction)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (!Auth::attempt($credentials)) {
            return back()->withErrors([
                'email' => 'Email atau password salah.'
            ]);
        }

        $request->session()->regenerate();

        $user = Auth::user();

        // 🔒 User nonaktif
        if (!$user->is_active) {
            Auth::logout();
            return back()->withErrors([
                'email' => 'Akun Anda dinonaktifkan.'
            ]);
        }

        // 🔒 Jika bukan super admin, cek tenant
        if (!$user->hasRole('super_admin')) {

            if (
                !$user->pondok ||
                !$user->pondok->is_active ||
                $user->pondok->deleted_at
            ) {
                Auth::logout();
                return back()->withErrors([
                    'email' => 'Pondok Anda tidak aktif.'
                ]);
            }
        }

        return redirect($redirectAction->execute($user));
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}

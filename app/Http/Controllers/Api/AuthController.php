<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required','email'],
            'password' => ['required','string'],
        ]);

        // Login attempt
        if (!Auth::attempt($credentials)) {
            return response()->json([
                'success' => false,
                'message' => 'Email atau password salah'
            ], 401);
        }

        $user = Auth::user();

        // 🔒 User nonaktif
        if (!$user->is_active) {
            Auth::logout();

            return response()->json([
                'success' => false,
                'message' => 'Akun Anda dinonaktifkan'
            ], 403);
        }

        // 🔒 Jika bukan super admin, cek tenant / pondok
        if (!$user->hasRole('super_admin')) {

            if (
                !$user->pondok ||
                !$user->pondok->is_active ||
                $user->pondok->deleted_at
            ) {
                Auth::logout();

                return response()->json([
                    'success' => false,
                    'message' => 'Pondok Anda tidak aktif'
                ], 403);
            }
        }

        // Hapus token lama
        $user->tokens()->delete();

        // Buat token baru
        $token = $user->createToken('mobile-token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login berhasil',
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'roles' => $user->getRoleNames(),
                ],
                'token' => $token
            ]
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logout berhasil'
        ]);
    }
}
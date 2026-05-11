<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TemplateVariable; // Pastikan modelnya sudah ada
// use Illuminate\Http\Request;

class TemplateVariableController extends Controller
{
    public function index()
    {
        // Kita ambil semua variabel yang aktif
        $variables = TemplateVariable::where('is_active', 1)->get();

        return response()->json([
            'success' => true,
            'message' => 'Daftar Master Variabel Berhasil Diambil',
            'data' => $variables
        ]);
    }
}
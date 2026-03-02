<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\SantriHandbook;

class SantriHandbookController extends Controller
{
    public function index()
    {
        $latest = SantriHandbook::where('status', 'published')
            ->latest('release_date')
            ->first();

        $history = SantriHandbook::orderByDesc('release_date')
            ->get();

        return view('public.handbook.index', compact('latest', 'history'));
    }

    public function download(SantriHandbook $handbook)
    {
        $filePath = public_path($handbook->file_path);

        if (!file_exists($filePath)) {
            abort(404, 'File tidak ditemukan.');
        }

        $downloadName = $handbook->version . ' Buku Pedoman Santri.pdf';

        return response()->download($filePath, $downloadName);
    }

    public function preview($id)
    {
        $handbook = SantriHandbook::findOrFail($id);

        $filePath = public_path($handbook->file_path);

        if (!file_exists($filePath)) {
            abort(404, 'File preview tidak ditemukan.');
        }

        return response()->file($filePath);
    }
}
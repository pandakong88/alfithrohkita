<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Pondok;
use App\Models\SantriHandbook;

class SantriHandbookController extends Controller
{
    public function index($pondok_slug)
    {
        $pondok = Pondok::where('slug', $pondok_slug)
            ->where('is_active', true)
            ->firstOrFail();

        $latest = SantriHandbook::where('pondok_id', $pondok->id)
            ->where('status', 'published')
            ->latest('release_date')
            ->first();

        $history = SantriHandbook::where('pondok_id', $pondok->id)
            ->orderByDesc('release_date')
            ->get();

        return view('public.handbook.index', compact('latest', 'history', 'pondok'));
    }

    public function download($pondok_slug, SantriHandbook $handbook)
    {
        $pondok = Pondok::where('slug', $pondok_slug)
            ->where('is_active', true)
            ->firstOrFail();

        if ($handbook->pondok_id !== $pondok->id) {
            abort(404, 'Buku pedoman tidak ditemukan untuk pondok ini.');
        }

        $filePath = public_path($handbook->file_path);

        if (!file_exists($filePath)) {
            abort(404, 'File tidak ditemukan.');
        }

        $downloadName = $handbook->version . ' Buku Pedoman Santri.pdf';

        return response()->download($filePath, $downloadName);
    }

    public function preview($pondok_slug, $id)
    {
        $pondok = Pondok::where('slug', $pondok_slug)
            ->where('is_active', true)
            ->firstOrFail();

        $handbook = SantriHandbook::where('pondok_id', $pondok->id)->findOrFail($id);

        $filePath = public_path($handbook->file_path);

        if (!file_exists($filePath)) {
            abort(404, 'File preview tidak ditemukan.');
        }

        return response()->file($filePath);
    }
}
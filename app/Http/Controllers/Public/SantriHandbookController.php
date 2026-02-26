<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\SantriHandbook;
use Illuminate\Support\Facades\Storage;


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
        if (!Storage::exists($handbook->file_path)) {
            abort(404, 'File tidak ditemukan.');
        }

        return Storage::download($handbook->file_path);
    }
}
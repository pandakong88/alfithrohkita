<?php

namespace App\Http\Controllers\Tenant\Perizinan;

use App\Http\Controllers\Controller;
use App\Models\TemplatePerizinan;
use App\Models\TemplateVariable;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

use App\Domains\Perizinan\Actions\CreateTemplatePerizinanAction;
use App\Domains\Perizinan\Actions\UpdateTemplatePerizinanAction;

use App\Domains\Perizinan\DTO\CreateTemplatePerizinanData;
use App\Domains\Perizinan\DTO\UpdateTemplatePerizinanData;

class TemplatePerizinanController extends Controller
{

    /*
    |--------------------------------------------------------------------------
    | LIST TEMPLATE
    |--------------------------------------------------------------------------
    */
    public function index(Request $request)
    {
        $query = TemplatePerizinan::byPondok(auth()->user()->pondok_id);

        if ($request->status === 'trash') {
            $query->onlyTrashed();
        }

        $templates = $query->latest()->get();

        // return json_encode($templates);
        return view('tenant.perizinan.template.index', compact('templates'));
    }

    /*
    |--------------------------------------------------------------------------
    | FORM UPLOAD (STEP 2)
    |--------------------------------------------------------------------------
    */
    public function create(Request $request)
    {
        $file = $request->file ?? null;

        $variables = TemplateVariable::where('is_active', true)->get();

        return view('tenant.perizinan.template.upload', compact('file', 'variables'));
    }

    /*
    |--------------------------------------------------------------------------
    | UPLOAD FILE (STEP 1)
    |--------------------------------------------------------------------------
    */
    public function storeFile(Request $request)
    {
        $request->validate([
            'file_pdf' => 'required|mimes:pdf|max:2048',
        ]);

        $path = $request->file('file_pdf')
            ->store('temp/template-pdf', 'public');

        return redirect()->route('tenant.template-perizinan.upload', [
            'file' => $path
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | STORE FINAL TEMPLATE (1x SAVE)
    |--------------------------------------------------------------------------
    */
    public function store(Request $request, CreateTemplatePerizinanAction $action)
    {
        // return $request->all();
        $request->validate([
            'nama' => 'required|string|max:255',
            'variables' => 'required|array',
            'variables.*' => 'string|exists:template_variables,key',
            'file_pdf' => 'required|string'
        ]);

        $tempPath = $request->file_pdf;

        // 🔥 pindahkan dari temp → final
        $finalPath = str_replace('temp/', '', $tempPath);

        Storage::disk('public')->move($tempPath, $finalPath);

        $data = CreateTemplatePerizinanData::fromArray([
            'nama' => $request->nama,
            'deskripsi' => $request->deskripsi,
            'layout_print' => $request->layout_print ?? 1, // ✅ FIX DI SINI
            'required_variables' => $request->variables,
            'file_pdf' => $finalPath,
            'is_active' => true,
            'is_default' => $request->has('is_default'),
        ]);

        $action->execute($data);

        return redirect()->route('tenant.template-perizinan.index')
            ->with('success', 'Template berhasil dibuat');
    }

    /*
    |--------------------------------------------------------------------------
    | EDIT
    |--------------------------------------------------------------------------
    */
    public function edit($id)
    {
        $template = TemplatePerizinan::byPondok(auth()->user()->pondok_id)
            ->findOrFail($id);

        $variables = TemplateVariable::where('is_active', true)->get();

        return view('tenant.perizinan.template.edit_pdf', compact('template', 'variables'));
    }

    /*
    |--------------------------------------------------------------------------
    | UPDATE
    |--------------------------------------------------------------------------
    */
    public function update(Request $request, $id, UpdateTemplatePerizinanAction $action)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',

            'variables' => 'required|array',
            'variables.*' => 'string|exists:template_variables,key',

            'file_pdf' => 'nullable|mimes:pdf|max:2048',
        ]);

        $template = TemplatePerizinan::byPondok(auth()->user()->pondok_id)
            ->findOrFail($id);

        $filePath = $template->file_pdf;

        if ($request->hasFile('file_pdf')) {
            $filePath = $request->file('file_pdf')
                ->store('template-pdf', 'public');
        }

        $data = UpdateTemplatePerizinanData::fromArray([
            'nama' => $request->nama,
            'deskripsi' => $request->deskripsi,
            'format_surat' => null,
            'layout_print' => $request->layout_print ?? 1,
            'required_variables' => $request->variables,
            'file_pdf' => $filePath,
            'is_active' => true,
            'is_default' => $request->has('is_default'),
        ]);

        $action->execute($template, $data);

        return redirect()
            ->route('tenant.template-perizinan.index')
            ->with('success', 'Template berhasil diupdate');
    }

    public function updateStatus(Request $request, UpdateTemplatePerizinanAction $action)
    {
        // 1. Cari modelnya
        $template = TemplatePerizinan::findOrFail($request->id);

        // 2. Buat DTO dengan data yang sudah ada (existing), tapi ubah is_active nya
        // Asumsi: Anda menggunakan Spatie Data atau class DTO biasa
        $data = new UpdateTemplatePerizinanData(
            nama: $template->nama,
            deskripsi: $template->deskripsi,
            format_surat: $template->format_surat,
            layout_print: $template->layout_print,
            required_variables: $template->required_variables ?? [],
            file_pdf: $template->file_pdf,
            is_active: (bool) $request->is_active, // Nilai baru dari toggle
            is_default: $template->is_default
        );

        try {
            // 3. Jalankan Action (Log activity otomatis tercatat di sini)
            $action->execute($template, $data);

            return response()->json([
                'success' => true,
                'message' => 'Status berhasil diperbarui'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
    /*
    |--------------------------------------------------------------------------
    | DELETE
    |--------------------------------------------------------------------------
    */
    public function destroy($id)
    {
        $template = TemplatePerizinan::byPondok(auth()->user()->pondok_id)
            ->findOrFail($id);

        $template->delete();

        return back()->with('success', 'Template berhasil dihapus');
    }

    /*
    |--------------------------------------------------------------------------
    | RESTORE
    |--------------------------------------------------------------------------
    */
    public function restore($id)
    {
        $template = TemplatePerizinan::withTrashed()
            ->byPondok(auth()->user()->pondok_id)
            ->findOrFail($id);

        $template->restore();

        return back()->with('success', 'Template berhasil direstore');
    }

    /*
    |--------------------------------------------------------------------------
    | FORCE DELETE
    |--------------------------------------------------------------------------
    */
    public function forceDelete($id)
    {
        $template = TemplatePerizinan::withTrashed()
            ->byPondok(auth()->user()->pondok_id)
            ->findOrFail($id);

        if ($template->file_pdf) {
            Storage::disk('public')->delete($template->file_pdf);
        }

        $template->forceDelete();

        return back()->with('success', 'Template dihapus permanen');
    }
}
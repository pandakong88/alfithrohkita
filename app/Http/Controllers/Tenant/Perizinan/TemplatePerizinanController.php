<?php

namespace App\Http\Controllers\Tenant\Perizinan;

use App\Domains\Perizinan\Actions\CreateTemplatePerizinanAction;
use App\Domains\Perizinan\DTO\CreateTemplatePerizinanData;
use App\Http\Controllers\Controller;
use App\Models\TemplatePerizinan;
use App\Models\TemplateVariable;
use Illuminate\Http\Request;
class TemplatePerizinanController extends Controller
{

    /*
    |--------------------------------------------------------------------------
    | LIST TEMPLATE
    |--------------------------------------------------------------------------
    */

    public function index()
    {
        $templates = TemplatePerizinan::byPondok(auth()->user()->pondok_id)
            ->latest()
            ->get();

        return view('tenant.perizinan.template.index', compact('templates'));
    }

    /*
    |--------------------------------------------------------------------------
    | FORM CREATE
    |--------------------------------------------------------------------------
    */
    public function create(Request $request)
    {
        $template = null;

        if ($request->template_id) {
            $template = TemplatePerizinan::find($request->template_id);
        }

        $variables = TemplateVariable::where('is_active', true)->get();

        return view('tenant.perizinan.template.upload', compact('template', 'variables'));
    }

    // 🔥 upload file dulu
    public function storeFile(Request $request)
    {
        $request->validate([
            'file_pdf' => 'required|mimes:pdf|max:2048',
        ]);

        $path = $request->file('file_pdf')->store('template-pdf', 'public');

        $template = TemplatePerizinan::create([
            'pondok_id' => auth()->user()->pondok_id,
            'nama' => 'Draft',
            'slug' => 'draft-' . time(),
            'file_pdf' => $path,
            'created_by' => auth()->id(),
        ]);

        return redirect()->route('tenant.template-perizinan.upload', [
            'template_id' => $template->id
        ]);
    }

    // 🔥 simpan final (pakai DTO + Action)
    public function store(Request $request, CreateTemplatePerizinanAction $action)
    {
        $request->validate([
            'template_id' => 'required|exists:template_perizinans,id',
            'nama' => 'required|string|max:255',
            'variables' => 'required|array'
        ]);

        $template = TemplatePerizinan::findOrFail($request->template_id);

        $data = CreateTemplatePerizinanData::fromArray([
            'nama' => $request->nama,
            'deskripsi' => $request->deskripsi,
            'layout_print' => 4,
            'required_variables' => $request->variables,
            'file_pdf' => $template->file_pdf, // 🔥 ambil dari draft
            'is_active' => true,
            'is_default' => false,
        ]);

        // 🔥 delete draft lama biar ga numpuk
        $template->delete();

        $action->execute($data);

        return redirect()->route('tenant.template-perizinan.index')
            ->with('success', 'Template berhasil dibuat');
    }
}
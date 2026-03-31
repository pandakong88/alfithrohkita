<?php

namespace App\Http\Controllers\Tenant;

use App\Domains\Import\Actions\DynamicImportAction;
use App\Exports\DynamicTemplateExport;
use App\Http\Controllers\Controller;
use App\Models\ImportField;
use App\Models\ImportTemplate;
use App\Models\ImportTemplateField;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ImportTemplateController extends Controller
{
    public function index()
    {
        $templates = ImportTemplate::where('pondok_id', auth()->user()->pondok_id)
            ->latest()
            ->get();

        return view('tenant.import-templates.index', compact('templates'));
    }


    public function create()
    {
        $fields = ImportField::orderBy('label')->get();

        return view('tenant.import-templates.create', compact('fields'));
    }


    public function store(Request $request)
    {
        $request->validate([
            'nama_template' => 'required|string|max:255',
            'fields' => 'required|array'
        ]);

        $template = ImportTemplate::create([
            'pondok_id' => auth()->user()->pondok_id,
            'nama_template' => $request->nama_template
        ]);


        foreach ($request->fields as $order => $fieldId) {

            ImportTemplateField::create([
                'template_id' => $template->id,
                'field_id' => $fieldId,
                'order' => $order
            ]);

        }


        return redirect()
            ->route('tenant.import-templates.index')
            ->with('success','Template berhasil dibuat');
    }


    public function show($id)
    {
        $template = ImportTemplate::with('fields')
            ->where('pondok_id', auth()->user()->pondok_id)
            ->findOrFail($id);
    
        $fields = $template->fields
            ->sortBy('pivot.order');
    
        return view(
            'tenant.import-templates.show',
            compact('template','fields')
        );
    }


    public function download($id)
    {
        $template = ImportTemplate::with('fields')
            ->where('pondok_id', auth()->user()->pondok_id)
            ->findOrFail($id);

        return Excel::download(
            new DynamicTemplateExport($template),
            $template->nama_template . '.xlsx'
        );
    }

    public function import(Request $request)
    {

        $request->validate([
            'file' => 'required|mimes:xlsx,csv'
        ]);

        app(DynamicImportAction::class)
            ->execute($request->file('file'));

        return back()->with('success','Import berhasil');
    }
    
}
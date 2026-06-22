<?php

namespace App\Http\Controllers\Tenant\Perizinan;

use App\Http\Controllers\Controller;
use App\Models\Pondok;
use App\Models\Santri;
use App\Models\TemplateAsset;
use App\Models\TemplatePerizinan;
use App\Models\TemplateVariable;
use Spatie\Permission\Models\Role;

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

        return view('tenant.perizinan.template.index', compact('templates'));
    }

    /*
    |--------------------------------------------------------------------------
    | FORM CREATE / UPLOAD STEP
    |--------------------------------------------------------------------------
    */
    public function create(Request $request)
    {
        $pondokId = auth()->user()->pondok_id;
        $roles = Role::where('guard_name', 'web')->get();

        if ($request->is('*upload*') || $request->type === 'pdf') {
            $file = $request->file ?? null;
            $variables = TemplateVariable::where('is_active', true)->get();
            return view('tenant.perizinan.template.upload', compact('file', 'variables', 'roles'));
        }

        // HTML Canvas Editor
        $assets = TemplateAsset::where('pondok_id', $pondokId)
                    ->latest()
                    ->get();

        $pondok = Pondok::find($pondokId);
        $sampleSantris = Santri::where('pondok_id', $pondokId)->limit(10)->get();
        $sampleSantri = $sampleSantris->first();

        $variables = [
            'Lembaga' => [
                '{nama_pondok}'    => $pondok->name ?? 'Nama Pondok',
                '{alamat_pondok}'  => $pondok->address ?? 'Alamat belum diatur',
            ],
            'Santri' => [
                '{nama_santri}'    => $sampleSantri->nama_lengkap ?? 'Contoh Nama Santri',
                '{nis}'            => $sampleSantri->nis ?? '12345',
                '{status_santri}'  => $sampleSantri->status_keberadaan ?? 'aktif',
            ],
            'Perizinan' => [
                '{keperluan}'      => 'Izin Pulang (Contoh)',
                '{tgl_keluar}'     => now()->format('d/m/Y H:i'),
                '{batas_kembali}'  => now()->addDays(3)->format('d/m/Y H:i'),
            ]
        ];

        return view('tenant.perizinan.template.create', compact('variables', 'assets', 'roles', 'sampleSantris'));
    }

    /*
    |--------------------------------------------------------------------------
    | STEP 1: UPLOAD PDF FILE
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
    | STORE NEW TEMPLATE (HTML / PDF)
    |--------------------------------------------------------------------------
    */
    public function store(Request $request, CreateTemplatePerizinanAction $action)
    {
        $rules = [
            'nama' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'layout_print' => 'required|in:1,2,4',
            'is_default' => 'nullable|boolean',
            'rules' => 'nullable|array',
            'rules.late_penalty.enabled' => 'nullable',
            'rules.late_penalty.interval_minutes' => 'nullable|integer|min:1',
            'rules.late_penalty.points_per_interval' => 'nullable|integer|min:0',
            'rules.approval_workflow' => 'nullable|array',
        ];

        if ($request->has('file_pdf') && !empty($request->file_pdf)) {
            // PDF Flow
            $rules['variables'] = 'required|array';
            $rules['file_pdf'] = 'required|string';
            $request->validate($rules);

            $tempPath = $request->file_pdf;
            $finalPath = str_replace('temp/', '', $tempPath);
            Storage::disk('public')->move($tempPath, $finalPath);

            $data = CreateTemplatePerizinanData::fromArray([
                'nama' => $request->nama,
                'deskripsi' => $request->deskripsi,
                'layout_print' => (int) $request->layout_print,
                'required_variables' => $request->variables,
                'rules' => $request->rules ?? [],
                'file_pdf' => $finalPath,
                'is_active' => true,
                'is_default' => $request->has('is_default'),
            ]);
        } else {
            // HTML Flow
            $rules['format_surat'] = 'required|string';
            $request->validate($rules);

            // Extract variables from format_surat
            $requiredVariables = [];
            preg_match_all('/\{([a-zA-Z0-9_]+)\}/', $request->format_surat, $matches);
            if (!empty($matches[1])) {
                $placeholderMap = [
                    'nama_pondok' => 'pondok.nama',
                    'alamat_pondok' => 'pondok.alamat',
                    'nama_santri' => 'santri.nama_lengkap',
                    'nis' => 'santri.nis',
                    'status_santri' => 'santri.status',
                    'keperluan' => 'keperluan',
                    'tgl_keluar' => 'tanggal_keluar',
                    'batas_kembali' => 'batas_kembali',
                    'tanggal_sekarang' => 'tanggal_sekarang',
                    'nama_kamar' => 'kamar.nama',
                ];
                foreach ($matches[1] as $match) {
                    if (isset($placeholderMap[$match])) {
                        $requiredVariables[] = $placeholderMap[$match];
                    } elseif ($match === 'qr_code') {
                        $requiredVariables[] = 'qr_code';
                    } elseif ($match === 'kode_surat') {
                        $requiredVariables[] = 'kode_surat';
                    } else {
                        $requiredVariables[] = 'custom_variables.' . $match;
                    }
                }
            }
            $requiredVariables = array_unique($requiredVariables);

            $data = CreateTemplatePerizinanData::fromArray([
                'nama' => $request->nama,
                'deskripsi' => $request->deskripsi,
                'format_surat' => $request->format_surat,
                'layout_print' => (int) $request->layout_print,
                'required_variables' => $requiredVariables,
                'rules' => $request->rules ?? [],
                'is_active' => true,
                'is_default' => $request->has('is_default'),
            ]);
        }

        $action->execute($data);

        return redirect()->route('tenant.template-perizinan.index')
            ->with('success', 'Template berhasil dibuat');
    }

    /*
    |--------------------------------------------------------------------------
    | FORM EDIT (HTML / PDF)
    |--------------------------------------------------------------------------
    */
    public function edit($id)
    {
        $template = TemplatePerizinan::byPondok(auth()->user()->pondok_id)
            ->findOrFail($id);

        $roles = Role::where('guard_name', 'web')->get();

        if ($template->source_type === 'upload_pdf') {
            $variables = TemplateVariable::where('is_active', true)->get();
            return view('tenant.perizinan.template.edit_pdf', compact('template', 'variables', 'roles'));
        }

        // HTML Edit Flow
        $pondokId = auth()->user()->pondok_id;
        $assets = TemplateAsset::where('pondok_id', $pondokId)->latest()->get();
        $pondok = Pondok::find($pondokId);
        $sampleSantris = Santri::where('pondok_id', $pondokId)->limit(10)->get();
        $sampleSantri = $sampleSantris->first();

        $variables = [
            'Lembaga' => [
                '{nama_pondok}'    => $pondok->name ?? 'Nama Pondok',
                '{alamat_pondok}'  => $pondok->address ?? 'Alamat belum diatur',
            ],
            'Santri' => [
                '{nama_santri}'    => $sampleSantri->nama_lengkap ?? 'Contoh Nama Santri',
                '{nis}'            => $sampleSantri->nis ?? '12345',
                '{status_santri}'  => $sampleSantri->status_keberadaan ?? 'aktif',
            ],
            'Perizinan' => [
                '{keperluan}'      => 'Izin Pulang (Contoh)',
                '{tgl_keluar}'     => now()->format('d/m/Y H:i'),
                '{batas_kembali}'  => now()->addDays(3)->format('d/m/Y H:i'),
            ]
        ];

        return view('tenant.perizinan.template.edit', compact('template', 'variables', 'assets', 'roles', 'sampleSantris'));
    }

    /*
    |--------------------------------------------------------------------------
    | UPDATE TEMPLATE (HTML / PDF)
    |--------------------------------------------------------------------------
    */
    public function update(Request $request, $id, UpdateTemplatePerizinanAction $action)
    {
        $template = TemplatePerizinan::byPondok(auth()->user()->pondok_id)
            ->findOrFail($id);

        $rules = [
            'nama' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'layout_print' => 'required|in:1,2,4',
            'is_default' => 'nullable|boolean',
            'rules' => 'nullable|array',
            'rules.late_penalty.enabled' => 'nullable',
            'rules.late_penalty.interval_minutes' => 'nullable|integer|min:1',
            'rules.late_penalty.points_per_interval' => 'nullable|integer|min:0',
            'rules.approval_workflow' => 'nullable|array',
        ];

        if ($template->source_type === 'upload_pdf') {
            $rules['variables'] = 'required|array';
            $rules['file_pdf'] = 'nullable|mimes:pdf|max:2048';
            $request->validate($rules);

            $filePath = $template->file_pdf;
            if ($request->hasFile('file_pdf')) {
                $filePath = $request->file('file_pdf')
                    ->store('template-pdf', 'public');
            }

            $data = UpdateTemplatePerizinanData::fromArray([
                'nama' => $request->nama,
                'deskripsi' => $request->deskripsi,
                'layout_print' => (int) $request->layout_print,
                'required_variables' => $request->variables,
                'rules' => $request->rules ?? [],
                'file_pdf' => $filePath,
                'is_active' => true,
                'is_default' => $request->has('is_default'),
            ]);
        } else {
            // HTML Update Flow
            $rules['format_surat'] = 'required|string';
            $request->validate($rules);

            $requiredVariables = [];
            preg_match_all('/\{([a-zA-Z0-9_]+)\}/', $request->format_surat, $matches);
            if (!empty($matches[1])) {
                $placeholderMap = [
                    'nama_pondok' => 'pondok.nama',
                    'alamat_pondok' => 'pondok.alamat',
                    'nama_santri' => 'santri.nama_lengkap',
                    'nis' => 'santri.nis',
                    'status_santri' => 'santri.status',
                    'keperluan' => 'keperluan',
                    'tgl_keluar' => 'tanggal_keluar',
                    'batas_kembali' => 'batas_kembali',
                    'tanggal_sekarang' => 'tanggal_sekarang',
                    'nama_kamar' => 'kamar.nama',
                ];
                foreach ($matches[1] as $match) {
                    if (isset($placeholderMap[$match])) {
                        $requiredVariables[] = $placeholderMap[$match];
                    } elseif ($match === 'qr_code') {
                        $requiredVariables[] = 'qr_code';
                    } elseif ($match === 'kode_surat') {
                        $requiredVariables[] = 'kode_surat';
                    } else {
                        $requiredVariables[] = 'custom_variables.' . $match;
                    }
                }
            }
            $requiredVariables = array_unique($requiredVariables);

            $data = UpdateTemplatePerizinanData::fromArray([
                'nama' => $request->nama,
                'deskripsi' => $request->deskripsi,
                'format_surat' => $request->format_surat,
                'layout_print' => (int) $request->layout_print,
                'required_variables' => $requiredVariables,
                'rules' => $request->rules ?? [],
                'file_pdf' => null,
                'is_active' => true,
                'is_default' => $request->has('is_default'),
            ]);
        }

        $action->execute($template, $data);

        return redirect()->route('tenant.template-perizinan.index')
            ->with('success', 'Template berhasil diupdate');
    }

    /*
    |--------------------------------------------------------------------------
    | TOGGLE ACTIVE STATUS (AJAX)
    |--------------------------------------------------------------------------
    */
    public function updateStatus(Request $request, UpdateTemplatePerizinanAction $action)
    {
        $template = TemplatePerizinan::findOrFail($request->id);

        $data = new UpdateTemplatePerizinanData(
            nama: $template->nama,
            deskripsi: $template->deskripsi,
            format_surat: $template->format_surat,
            layout_print: $template->layout_print,
            required_variables: $template->required_variables ?? [],
            file_pdf: $template->file_pdf,
            is_active: (bool) $request->is_active,
            is_default: $template->is_default
        );

        try {
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
    | SUMMERNOTE IMAGE UPLOAD (AJAX)
    |--------------------------------------------------------------------------
    */
    public function uploadImage(Request $request) 
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);
    
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $pondokId = auth()->user()->pondok_id;
            $path = $file->store("pondok/{$pondokId}/assets", 'public');
    
            $asset = TemplateAsset::create([
                'pondok_id' => $pondokId,
                'file_path' => $path,
                'file_name' => $file->getClientOriginalName(),
            ]);
    
            return response()->json([
                'success' => true,
                'url' => asset('storage/' . $path),
                'id'  => $asset->id
            ]);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | SUMMERNOTE DELETE IMAGE ASSET (AJAX)
    |--------------------------------------------------------------------------
    */
    public function deleteAsset($id)
    {
        $pondokId = auth()->user()->pondok_id;
        $asset = TemplateAsset::where('id', $id)->where('pondok_id', $pondokId)->firstOrFail();
        Storage::disk('public')->delete($asset->file_path);
        $asset->delete();
        return response()->json(['success' => true]);
    }
    public function printBlank($id)
    {
        $template = TemplatePerizinan::byPondok(auth()->user()->pondok_id)
            ->findOrFail($id);

        $pondok = Pondok::find(auth()->user()->pondok_id);

        // Define mock values for all variables to replace them with blank underlines
        $variables = [
            '{nama_pondok}'    => $pondok->name ?? '___________________________',
            '{alamat_pondok}'  => $pondok->address ?? '___________________________',
            '{nama_santri}'    => '___________________________',
            '{nis}'            => '___________________________',
            '{status_santri}'  => '___________________________',
            '{keperluan}'      => '___________________________',
            '{tgl_keluar}'     => '___________________________',
            '{batas_kembali}'  => '___________________________',
            '{tanggal_sekarang}' => '___________________________',
            '{nama_kamar}'     => '___________________________',
        ];

        $content = $template->format_surat;
        if ($content) {
            foreach ($variables as $key => $value) {
                $content = str_replace($key, $value, $content);
            }
        }

        return view('tenant.perizinan.template.print_blank', compact('template', 'content'));
    }

    /*
    |--------------------------------------------------------------------------
    | DELETE (SOFT DELETE)
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
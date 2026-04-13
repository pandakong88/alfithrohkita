<?php

namespace App\Http\Controllers\Tenant\Perizinan;

use App\Domains\Perizinan\Actions\CreateTemplatePerizinanAction;
use App\Domains\Perizinan\Actions\DeleteTemplatePerizinanAction;
use App\Domains\Perizinan\Actions\ToggleTemplatePerizinanStatusAction;
use App\Domains\Perizinan\Actions\UpdateTemplatePerizinanAction;
use App\Domains\Perizinan\DTO\CreateTemplatePerizinanData;
use App\Domains\Perizinan\DTO\UpdateTemplatePerizinanData;
use App\Http\Controllers\Controller;
use App\Models\Pondok;
use App\Models\Santri;
use App\Models\TemplateAsset;
use App\Models\TemplatePerizinan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

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
    public function create()
    {
        $pondokId = auth()->user()->pondok_id;
    
        // 1. Ambil Assets Gambar khusus Pondok ini untuk sidebar Canva
        $assets = TemplateAsset::where('pondok_id', $pondokId)
                    ->latest()
                    ->get();
    
        // 2. Ambil data real pondok ini
        $pondok = Pondok::find($pondokId);
    
        // 3. Ambil SATU contoh santri real dari pondok ini untuk preview
        $sampleSantri = Santri::where('pondok_id', $pondokId)->first();
    
        // 4. Definisikan variabel yang tersedia
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
    
        // Jangan lupa kirim 'assets' ke view
        return view('tenant.perizinan.template.create', compact('variables', 'assets'));
    }
    
    /**
     * Handle AJAX Upload Image dari Sidebar Canva
     */
    public function uploadImage(Request $request) 
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);
    
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $pondokId = auth()->user()->pondok_id;
    
            // Path folder: storage/app/public/pondok/1/assets
            $path = $file->store("pondok/{$pondokId}/assets", 'public');
    
            $asset = TemplateAsset::create([
                'pondok_id' => $pondokId,
                'file_path' => $path,
                'file_name' => $file->getClientOriginalName(),
            ]);
    
            return response()->json([
                'url' => asset('storage/' . $path),
                'id'  => $asset->id
            ]);
        }
    }
    
    /**
     * Handle Delete Asset dari Sidebar
     */
    public function deleteAsset($id)
    {
        $pondokId = auth()->user()->pondok_id;
        $asset = TemplateAsset::where('id', $id)->where('pondok_id', $pondokId)->firstOrFail();
    
        // Hapus file fisik dari storage
        Storage::disk('public')->delete($asset->file_path);
        
        // Hapus data dari database
        $asset->delete();
    
        return response()->json(['success' => true]);
    }


    /*
    |--------------------------------------------------------------------------
    | STORE
    |--------------------------------------------------------------------------
    */

    public function store(
        Request $request,
        CreateTemplatePerizinanAction $action
    ) {
    
        $validated = $request->validate([
    
            'nama' => 'required|string|max:100',
    
            'slug' => 'nullable|string|max:120',
    
            'deskripsi' => 'nullable|string',
    
            'format_surat' => 'nullable|string',
    
            'layout_print' => 'required|in:1,2,4',
    
            'is_active' => 'nullable|boolean',
    
            'is_default' => 'nullable|boolean',
    
        ]);
    
        $dto = CreateTemplatePerizinanData::fromArray($validated);
    
        $action->execute($dto);
    
        return redirect()
            ->route('tenant.template-perizinan.index')
            ->with('success', 'Template perizinan berhasil dibuat.');
    }

    /*
    |--------------------------------------------------------------------------
    | FORM EDIT
    |--------------------------------------------------------------------------
    */

    public function edit(TemplatePerizinan $template)
    {
        abort_if(
            $template->pondok_id !== auth()->user()->pondok_id,
            403
        );

        return view('tenant.perizinan.template.edit', compact('template'));
    }


    /*
    |--------------------------------------------------------------------------
    | UPDATE
    |--------------------------------------------------------------------------
    */

    public function update(
        Request $request,
        TemplatePerizinan $template,
        UpdateTemplatePerizinanAction $action
    ) {

        abort_if(
            $template->pondok_id !== auth()->user()->pondok_id,
            403
        );

        $validated = $request->validate([

            'nama' => 'required|string|max:100',

            'deskripsi' => 'nullable|string',

            'format_surat' => 'nullable|string',

            'layout_print' => 'required|in:1,2,4',

            'is_active' => 'nullable|boolean',

        ]);

        // Jika DTO update membutuhkan pondok_id, tambahkan juga di sini
        $validated['pondok_id'] = $template->pondok_id;
        $validated['updated_by'] = auth()->id();

        $dto = UpdateTemplatePerizinanData::fromArray($validated);

        $action->execute($template, $dto);

        return redirect()
            ->route('tenant.template-perizinan.index')
            ->with('success', 'Template perizinan berhasil diperbarui.');
    }


    /*
    |--------------------------------------------------------------------------
    | DELETE
    |--------------------------------------------------------------------------
    */

    public function destroy(
        TemplatePerizinan $template,
        DeleteTemplatePerizinanAction $action
    ) {

        abort_if(
            $template->pondok_id !== auth()->user()->pondok_id,
            403
        );

        $action->execute($template);

        return back()->with('success', 'Template berhasil dihapus.');
    }


    /*
    |--------------------------------------------------------------------------
    | TOGGLE STATUS
    |--------------------------------------------------------------------------
    */

    public function toggleStatus(
        TemplatePerizinan $template,
        ToggleTemplatePerizinanStatusAction $action
    ) {

        abort_if(
            $template->pondok_id !== auth()->user()->pondok_id,
            403
        );

        $action->execute($template);

        return back()->with('success', 'Status template berhasil diubah.');
    }
}
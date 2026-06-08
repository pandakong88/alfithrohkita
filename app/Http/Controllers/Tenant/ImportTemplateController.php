<?php

namespace App\Http\Controllers\Tenant;

use App\Domains\Import\Actions\DynamicImportAction;
use App\Exports\DynamicTemplateExport;
use App\Http\Controllers\Controller;
use App\Models\ImportField;
use App\Models\ImportTemplate;
use App\Models\ImportTemplateField;
use App\Models\Komplek;
use App\Models\Kamar;
use App\Models\Kelas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;


class ImportTemplateController extends Controller
{
    public function index()
    {
        // Gunakan Eager Loading dengan closure untuk mengurutkan field sesuai pivot 'order'
        $templates = ImportTemplate::with(['fields' => function($query) {
                $query->orderBy('import_template_fields.order', 'asc');
            }])
            ->where('pondok_id', auth()->user()->pondok_id)
            ->latest()
            ->get();

        // Ambil data lookup untuk filter download
        $kompleks = Komplek::where('pondok_id', auth()->user()->pondok_id)->orderBy('nama')->get();
        $kelas = Kelas::where('pondok_id', auth()->user()->pondok_id)->orderBy('nama')->get();
        $kamars = Kamar::where('pondok_id', auth()->user()->pondok_id)->orderBy('nama')->get();

        // Hapus atau beri komentar pada return json ini jika sudah mau dilempar ke view
        // return $templates; 

        return view('tenant.import-templates.index', compact('templates', 'kompleks', 'kelas', 'kamars'));
    }


    public function storeCustomField(Request $request)
    {
        $request->validate([
            'label' => 'required|string|max:255', 
        ]);
    
        // Bikin key otomatis dari label: "Ukuran Sarung" -> "ukuran_sarung"
        $fieldKey = Str::slug($request->label, '_'); 
    
        // Tampung hasil create ke dalam variabel $newField
        $newField = ImportField::create([
            'pondok_id'   => auth()->user()->pondok_id, 
            'field_key'   => $fieldKey,
            'label'       => $request->label,
            'entity'      => 'custom', 
            'column_name' => "custom_fields->{$fieldKey}", 
            'is_required' => 0,
        ]);
    
        // Kunci Perubahan: Kembalikan json berisi data field yang baru dibuat
        return response()->json([
            'success' => true,
            'message' => 'Kolom kustom berhasil ditambahkan!',
            'data'    => $newField // Data ini yang akan dibaca oleh JavaScript kamu
        ]);
    }

    public function destroyCustomField($id)
    {
        // 1. Validasi kepemilikan: Hanya cari field yang dibuat oleh pondok yang sedang login
        // Ini otomatis mengamankan field core (karena pondok_id-nya NULL) agar tidak bisa dihapus oleh tenant
        $field = ImportField::where('pondok_id', auth()->user()->pondok_id)
                            ->where('entity', 'custom')
                            ->findOrFail($id);

        // 2. Jalankan Database Transaction untuk membersihkan relasi pivot
        DB::beginTransaction();

        try {
            // 3. Hapus keterkaitan field kustom ini di seluruh template import milik pondok ini (tabel pivot)
            // Kita berasumsi nama tabel pivot kamu adalah 'import_template_fields'
            DB::table('import_template_fields')->where('field_id', $field->id)->delete();

            // 4. Hapus data utama kolom kustom dari tabel import_fields
            $field->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Kolom kustom berhasil dihapus dari sistem.'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus kolom kustom: ' . $e->getMessage()
            ], 500);
        }
    }
    
    public function create()
    {
        // Ambil field global (NULL) ATAU field milik pondok ini sendiri
        $groupedFields = ImportField::where(function($query) {
                $query->whereNull('pondok_id')
                    ->orWhere('pondok_id', auth()->user()->pondok_id);
            })
            ->orderBy('label')
            ->get()
            ->groupBy('entity'); // Otomatis masuk ke folder masing-masing di UI kiri

        return view('tenant.import-templates.create', compact('groupedFields'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_template' => 'required|string|max:255',
            'fields' => 'required|array|min:1'
        ]);

        // 1. Bungkus dengan Transaction agar data tidak corrupt kalau ada error di tengah jalan
        DB::beginTransaction();

        try {
            // 2. Buat Master Template
            $template = ImportTemplate::create([
                'pondok_id' => auth()->user()->pondok_id,
                'nama_template' => trim($request->nama_template)
            ]);

            // 3. Tampung semua data insert ke dalam satu array (Bulk Insert)
            $insertData = [];
            foreach ($request->fields as $order => $fieldId) {
                $insertData[] = [
                    'template_id' => $template->id,
                    'field_id'    => $fieldId,
                    'order'       => $order,
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ];
            }

            // 4. Eksekusi INSERT sekaligus dalam 1 baris query saja (Sangat Cepat!)
            ImportTemplateField::insert($insertData);

            // Commit perubahan jika semua sukses
            DB::commit();

            return redirect()
                ->route('tenant.import-templates.index')
                ->with('success', 'Template berhasil dibuat');

        } catch (\Exception $e) {
            // Batalkan segalanya jika ada crash/error
            DB::rollBack();

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Gagal membuat template: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        $template = ImportTemplate::with('fields')
            ->where('pondok_id', auth()->user()->pondok_id)
            ->findOrFail($id);
    
        $fields = $template->fields
            ->sortBy('pivot.order');

        // Ambil data lookup untuk filter download
        $kompleks = Komplek::where('pondok_id', auth()->user()->pondok_id)->orderBy('nama')->get();
        $kelas = Kelas::where('pondok_id', auth()->user()->pondok_id)->orderBy('nama')->get();
        $kamars = Kamar::where('pondok_id', auth()->user()->pondok_id)->orderBy('nama')->get();
    
        return view(
            'tenant.import-templates.show',
            compact('template', 'fields', 'kompleks', 'kelas', 'kamars')
        );
    }

 /**
     * Tampilkan Halaman Form Edit Template
     */
    public function edit($id)
    {
        // 1. Amankan query: Pastikan template yang dicari benar-benar milik pondok ini
        $template = ImportTemplate::with(['fields' => function($query) {
                $query->orderBy('import_template_fields.order'); // Menggunakan nama tabel pivot untuk order
            }])
            ->where('pondok_id', auth()->user()->pondok_id)
            ->findOrFail($id);
        
        // 2. Gunakan logic yang sama dengan fungsi create: Ambil global (NULL) ATAU milik pondok sendiri
        $groupedFields = ImportField::where(function($query) {
                $query->whereNull('pondok_id')
                    ->orWhere('pondok_id', auth()->user()->pondok_id);
            })
            ->orderBy('label', 'asc')
            ->get()
            ->groupBy('entity'); // Tetap ter-grouping berdasarkan entity agar UI kiri tidak pecah

        // 3. Ambil ID field yang aktif beserta urutannya (jika dibutuhkan di UI drag-drop/sorting)
        $activeFieldIds = $template->fields->pluck('id')->toArray();

        return view('tenant.import-templates.edit', compact('template', 'groupedFields', 'activeFieldIds'));
    }

    /**
     * Proses Simpan Perubahan Struktur Template (Update Data)
     */
    public function update(Request $request, $id)
    {
        // 1. Validasi input (Sesuaikan nama field request-nya dengan form create agar konsisten: 'nama_template' & 'fields')
        $request->validate([
            'nama_template' => 'required|string|max:255',
            'fields'        => 'required|array|min:1', 
            'fields.*'      => 'integer|exists:import_fields,id'
        ]);

        // 2. Pastikan template milik pondok yang request
        $template = ImportTemplate::where('pondok_id', auth()->user()->pondok_id)->findOrFail($id);

        DB::beginTransaction();

        try {
            // 3. Update basic data template
            $template->update([
                'nama_template' => trim($request->nama_template),
            ]);

            // 4. Susun data pivot untuk sync() agar urutan (order) terjaga seperti saat store
            $pivotData = [];
            foreach ($request->fields as $order => $fieldId) {
                $pivotData[$fieldId] = [
                    'order'      => $order,
                    'created_at' => now(), // Opsional, jaga-jaga kalau tabel pivot mencatat timestamp
                    'updated_at' => now(),
                ];
            }

            // 5. Sinkronisasi tabel pivot (Otomatis hapus yang lama, tambah/update yang baru)
            $template->fields()->sync($pivotData);

            DB::commit();

            return redirect()
                ->route('tenant.import-templates.index')
                ->with('success', 'Struktur susunan template berhasil diperbarui, Cok!');

        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Gagal memperbarui template: ' . $e->getMessage());
        }
    }
    public function download($id, Request $request)
    {
        $template = ImportTemplate::with('fields')
            ->where('pondok_id', auth()->user()->pondok_id)
            ->findOrFail($id);

        // Ambil flag request, default false (kosongan) jika tidak dikirim
        $withData = $request->get('with_data') === 'true' ? true : false;
        
        $filters = [
            'komplek_id'    => $request->get('komplek_id'),
            'kamar_id'      => $request->get('kamar_id'),
            'kelas_id'      => $request->get('kelas_id'),
            'jenis_kelamin' => $request->get('jenis_kelamin'),
            'status'        => $request->get('status'),
        ];

        $filename = $request->get('filename');
        if (empty($filename)) {
            $suffix = $withData ? '_dengan_data' : '_template_kosong';
            $filename = $template->nama_template . $suffix;
        }

        // Sanitasi nama file untuk menghindari karakter sistem file ilegal
        $filename = preg_replace('/[^a-zA-Z0-9\s\-_.]/', '', $filename);
        $filename = trim($filename);
        if (empty($filename)) {
            $filename = 'template_export';
        }

        if (!str_ends_with(strtolower($filename), '.xlsx')) {
            $filename .= '.xlsx';
        }

        return Excel::download(
            new DynamicTemplateExport($template, $withData, $filters),
            $filename
        );
    }

    public function duplicate($id)
    {
        $template = ImportTemplate::with('fields')
            ->where('pondok_id', auth()->user()->pondok_id)
            ->findOrFail($id);

        DB::beginTransaction();

        try {
            $newTemplate = ImportTemplate::create([
                'pondok_id' => auth()->user()->pondok_id,
                'nama_template' => $template->nama_template . ' (Salinan)'
            ]);

            $pivotData = [];
            foreach ($template->fields as $field) {
                $pivotData[$field->id] = [
                    'order' => $field->pivot->order,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            $newTemplate->fields()->sync($pivotData);

            DB::commit();

            return redirect()
                ->route('tenant.import-templates.index')
                ->with('success', 'Template berhasil diduplikasi');

        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()
                ->back()
                ->with('error', 'Gagal menduplikat template: ' . $e->getMessage());
        }
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

    public function destroy($id)
    {
        $template = ImportTemplate::where('pondok_id', auth()->user()->pondok_id)
            ->findOrFail($id);

        $template->delete();

        return redirect()
            ->route('tenant.import-templates.index')
            ->with('success', 'Template berhasil dihapus');
    }
    
}
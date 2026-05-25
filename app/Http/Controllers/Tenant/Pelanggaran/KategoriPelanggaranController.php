<?php 
namespace App\Http\Controllers\Tenant\Pelanggaran;

use App\Domains\Pelanggaran\Actions\CreateKategoriPelanggaranAction;
use App\Domains\Pelanggaran\Actions\UpdateKategoriPelanggaranAction;
use App\Domains\Pelanggaran\Actions\DeleteKategoriPelanggaranAction;
use App\Domains\Pelanggaran\DTO\KategoriPelanggaranDTO;
use App\Http\Controllers\Controller;
use App\Models\KategoriPelanggaran;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class KategoriPelanggaranController extends Controller
{
    /**
     * Tampilkan Daftar Kategori Pelanggaran
     */
    public function index()
    {
        // Trait BelongsToTenant atau global scope otomatis memfilter per pondok_id
        $categories = KategoriPelanggaran::orderBy('tingkat', 'asc')
            ->orderBy('nama_pelanggaran', 'asc')
            ->get();

        return view('tenant.kategori-pelanggaran.index', compact('categories'));
    }

    /**
     * Store & Update (Single Method Action)
     */
    public function store(
        Request $request, 
        CreateKategoriPelanggaranAction $createAction,
        UpdateKategoriPelanggaranAction $updateAction
    ) {
        // dd($request->all());
        $request->validate([
            'nama_pelanggaran' => 'required|string|max:255',
            'poin'             => 'required|integer|min:1',
            'tingkat'          => ['required', 'string', Rule::in(['ringan', 'sedang', 'berat'])],
            'id'               => 'nullable|exists:kategori_pelanggarans,id' // Ganti sesuai nama tabelmu jika berbeda
        ], [
            'nama_pelanggaran.required' => 'Nama kategori pelanggaran wajib diisi.',
            'poin.min'                  => 'Poin minimal bernilai 1.',
            'tingkat.in'                => 'Pilihan tingkat harus berupa: ringan, sedang, atau berat.',
        ]);

        try {
            if ($request->id) {
                // 1. Transform request ke DTO (bawa $id untuk parameter update)
                $dto = KategoriPelanggaranDTO::fromRequest($request, $request->id);

                // 2. Eksekusi Action Update
                $updateAction->execute($dto);
                $message = 'Kategori pelanggaran berhasil diperbarui';
            } else {
                // 1. Transform request ke DTO untuk create baru
                $dto = KategoriPelanggaranDTO::fromRequest($request);

                // 2. Eksekusi Action Create
                $createAction->execute($dto);
                $message = 'Kategori pelanggaran berhasil ditambahkan';
            }

            return redirect()
                ->route('tenant.kategori-pelanggaran.index')
                ->with('success', $message);

        } catch (\Throwable $e) {
            return back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Hapus Kategori Pelanggaran
     */
    public function destroy($id, DeleteKategoriPelanggaranAction $action)
    {
        try {
            // Memanfaatkan action delete bawaanmu yang mencatat log sebelum hapus
            $action->execute($id);

            return redirect()
                ->route('tenant.kategori-pelanggaran.index')
                ->with('success', 'Kategori pelanggaran berhasil dihapus');
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal menghapus: ' . $e->getMessage());
        }
    }
}
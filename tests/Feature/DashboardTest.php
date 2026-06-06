<?php

namespace Tests\Feature;

use App\Models\Pondok;
use App\Models\User;
use App\Models\Santri;
use App\Models\Wali;
use App\Models\Perizinan;
use App\Models\PelanggaranSantri;
use App\Models\TemplatePerizinan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    protected Pondok $pondok;
    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        // Create Pondok and User
        $this->pondok = Pondok::create([
            'name' => 'Alfitroh Kita',
            'slug' => 'alfitroh-kita',
            'is_active' => true,
        ]);

        $this->user = User::factory()->create([
            'pondok_id' => $this->pondok->id,
        ]);

        $this->actingAs($this->user);
    }

    public function test_tenant_dashboard_loads_correct_data(): void
    {
        // 1. Create a Santri and a Wali
        $santri = Santri::create([
            'pondok_id' => $this->pondok->id,
            'nis' => 'NIS100',
            'nama_lengkap' => 'Santri Test',
            'jenis_kelamin' => 'L',
            'alamat' => 'Alamat Test',
        ]);

        Wali::create([
            'pondok_id' => $this->pondok->id,
            'nama' => 'Wali Test',
            'no_hp' => '081234567890',
        ]);

        // 2. Create a Leave Permit (Perizinan)
        $template = TemplatePerizinan::create([
            'pondok_id' => $this->pondok->id,
            'nama' => 'Izin Mingguan',
            'slug' => 'izin-mingguan',
        ]);

        Perizinan::create([
            'pondok_id' => $this->pondok->id,
            'santri_id' => $santri->id,
            'template_perizinan_id' => $template->id,
            'kode_surat' => 'IZN-001',
            'tanggal_keluar' => now(),
            'batas_kembali' => now()->addDays(3),
            'status' => 'aktif',
            'keperluan' => 'Pulang kampung',
        ]);

        // 3. Create a Disciplinary Violation (PelanggaranSantri)
        PelanggaranSantri::create([
            'pondok_id' => $this->pondok->id,
            'santri_id' => $santri->id,
            'judul_pelanggaran' => 'Terlambat Berjamaah',
            'poin' => 5,
            'tanggal' => now()->format('Y-m-d'),
        ]);

        // 4. Request the Dashboard page
        $response = $this->get(route('tenant.dashboard'));

        $response->assertStatus(200);

        // Assert stats are present in the response
        $response->assertViewHasAll([
            'totalSantri' => 1,
            'totalWali' => 1,
            'aktifIzin' => 1,
            'totalPelanggaran' => 1,
            'santriLaki' => 1,
            'santriPerempuan' => 0,
        ]);

        // Assert data rendering check
        $response->assertSee('Santri Test');
        $response->assertSee('Terlambat Berjamaah');
        $response->assertSee('Pulang kampung');
    }
}

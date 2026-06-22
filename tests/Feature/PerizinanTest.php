<?php

namespace Tests\Feature;

use App\Models\Pondok;
use App\Models\User;
use App\Models\Santri;
use App\Models\TemplatePerizinan;
use App\Models\Perizinan;
use App\Models\Absensi;
use App\Models\AbsensiSesi;
use App\Models\PelanggaranSantri;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PerizinanTest extends TestCase
{
    use RefreshDatabase;

    protected Pondok $pondok;
    protected User $user;
    protected Santri $santri;

    protected function setUp(): void
    {
        parent::setUp();

        $this->pondok = Pondok::create([
            'name' => 'Pondok Alfitroh',
            'slug' => 'pondok-alfitroh',
            'is_active' => true,
        ]);

        $this->user = User::factory()->create([
            'pondok_id' => $this->pondok->id,
        ]);

        $this->santri = Santri::create([
            'pondok_id' => $this->pondok->id,
            'nis' => 'NIS200',
            'nama_lengkap' => 'Santri Perizinan Test',
            'jenis_kelamin' => 'L',
            'alamat' => 'Alamat Test',
            'status' => 'active',
        ]);

        // Setup Spatie permission
        \Spatie\Permission\Models\Permission::findOrCreate('manage_perizinan');
        $this->user->givePermissionTo('manage_perizinan');

        $this->actingAs($this->user);
    }

    public function test_create_template_with_rules_stores_correctly(): void
    {
        // Setup variable key
        \App\Models\TemplateVariable::create([
            'key' => 'wali.nama',
            'label' => 'Nama Wali',
            'type' => 'manual',
            'is_active' => true,
        ]);

        $response = $this->post(route('tenant.template-perizinan.store'), [
            'nama' => 'Izin Darurat',
            'deskripsi' => 'Template untuk keadaan darurat',
            'variables' => ['wali.nama'],
            'layout_print' => 1,
            'file_pdf' => 'temp/template-pdf/dummy.pdf',
            'rules' => [
                'late_penalty' => [
                    'enabled' => '1',
                    'interval_minutes' => '30',
                    'points_per_interval' => '10',
                ]
            ],
            'is_default' => '1'
        ]);

        $template = TemplatePerizinan::where('nama', 'Izin Darurat')->first();
        $this->assertNotNull($template);
        $this->assertEquals($this->pondok->id, $template->pondok_id);
        $this->assertTrue(isset($template->rules['late_penalty']['enabled']));
        $this->assertEquals('30', $template->rules['late_penalty']['interval_minutes']);
        $this->assertEquals('10', $template->rules['late_penalty']['points_per_interval']);
    }

    public function test_early_return_cleans_up_future_attendance_logs(): void
    {
        // 1. Setup template with rules
        $template = TemplatePerizinan::create([
            'pondok_id' => $this->pondok->id,
            'nama' => 'Izin Mingguan',
            'slug' => 'izin-mingguan',
            'required_variables' => [],
            'rules' => [
                'late_penalty' => [
                    'enabled' => false,
                ]
            ]
        ]);

        // 2. Setup Sesi Absensi
        $sesi = AbsensiSesi::create([
            'pondok_id' => $this->pondok->id,
            'nama_sesi' => 'Sesi Subuh',
            'jam_mulai' => '04:30:00',
            'jam_selesai' => '05:30:00',
        ]);

        // 3. Create leave permit starting today and returning in 3 days
        $perizinan = Perizinan::create([
            'pondok_id' => $this->pondok->id,
            'santri_id' => $this->santri->id,
            'template_perizinan_id' => $template->id,
            'kode_surat' => 'IZN-999',
            'tanggal_keluar' => now(),
            'batas_kembali' => now()->addDays(3),
            'status' => 'aktif',
        ]);

        // Pre-populate attendance logs
        Absensi::create([
            'pondok_id' => $this->pondok->id,
            'santri_id' => $this->santri->id,
            'sesi_id' => $sesi->id,
            'tanggal' => now()->toDateString(),
            'status' => 'izin',
            'keterangan' => "Otomatis: Izin via Surat {$perizinan->kode_surat}",
        ]);

        Absensi::create([
            'pondok_id' => $this->pondok->id,
            'santri_id' => $this->santri->id,
            'sesi_id' => $sesi->id,
            'tanggal' => now()->addDays(1)->toDateString(),
            'status' => 'izin',
            'keterangan' => "Otomatis: Izin via Surat {$perizinan->kode_surat}",
        ]);

        $this->assertEquals(2, Absensi::count());

        // 4. Return early (today)
        $response = $this->post(route('tenant.perizinan.kembali', $perizinan->id));
        $response->assertRedirect();

        // 5. Verify that tomorrow's attendance log is deleted
        $tomorrowLogs = Absensi::where('tanggal', now()->addDays(1)->toDateString())->count();
        $this->assertEquals(0, $tomorrowLogs);
    }

    public function test_late_return_automatically_creates_violation_record(): void
    {
        // 1. Setup template with active rules (10 points per 30 minutes)
        $template = TemplatePerizinan::create([
            'pondok_id' => $this->pondok->id,
            'nama' => 'Izin Bulanan',
            'slug' => 'izin-bulanan',
            'required_variables' => [],
            'rules' => [
                'late_penalty' => [
                    'enabled' => true,
                    'interval_minutes' => 30,
                    'points_per_interval' => 10,
                ]
            ]
        ]);

        // 2. Create leave permit that has expired 65 minutes ago
        $perizinan = Perizinan::create([
            'pondok_id' => $this->pondok->id,
            'santri_id' => $this->santri->id,
            'template_perizinan_id' => $template->id,
            'kode_surat' => 'IZN-888',
            'tanggal_keluar' => now()->subDays(2),
            'batas_kembali' => now()->subMinutes(65),
            'status' => 'aktif',
        ]);

        // 3. Confirm return (now late by 65 minutes = 2 intervals of 30 minutes = 20 points)
        $response = $this->post(route('tenant.perizinan.kembali', $perizinan->id));
        $response->assertRedirect();

        // 4. Check that violation record was created automatically
        $violation = PelanggaranSantri::where('santri_id', $this->santri->id)->first();
        $this->assertNotNull($violation);
        $this->assertEquals(20, $violation->poin);
        $this->assertEquals('otomatis', $violation->kategori_sumber);
        $this->assertStringContainsString('Keterlambatan kembali izin', $violation->judul_pelanggaran);
    }

    public function test_template_pages_render_successfully(): void
    {
        // Setup template variable
        \App\Models\TemplateVariable::create([
            'key' => 'santri.nama_lengkap',
            'label' => 'Nama Santri',
            'type' => 'auto',
            'is_active' => true,
        ]);

        // 1. Index
        $response = $this->get(route('tenant.template-perizinan.index'));
        $response->assertStatus(200);

        // 2. Create HTML Template Page
        $response = $this->get(route('tenant.template-perizinan.create'));
        $response->assertStatus(200);

        // 3. Upload PDF Template Page
        $response = $this->get(route('tenant.template-perizinan.upload'));
        $response->assertStatus(200);

        // Create a template first for edit tests
        $template = TemplatePerizinan::create([
            'pondok_id' => $this->pondok->id,
            'nama' => 'Izin Harian',
            'slug' => 'izin-harian',
            'source_type' => 'html',
            'format_surat' => '<p>Surat Izin</p>',
            'required_variables' => [],
        ]);

        // 4. Edit HTML Template Page
        $response = $this->get(route('tenant.template-perizinan.edit', $template->id));
        $response->assertStatus(200);

        // 5. Print Blank Template Page
        $response = $this->get(route('tenant.template-perizinan.print-blank', $template->id));
        $response->assertStatus(200);
    }

    public function test_approval_workflow_full_flow(): void
    {
        // Setup variable key
        \App\Models\TemplateVariable::create([
            'key' => 'santri.nama_lengkap',
            'label' => 'Nama Santri',
            'type' => 'auto',
            'is_active' => true,
        ]);

        // 1. Create Spatie roles
        $roleKeamanan = \Spatie\Permission\Models\Role::findOrCreate('keamanan', 'web');
        $rolePengasuh = \Spatie\Permission\Models\Role::findOrCreate('pengasuh', 'web');
        $roleBendahara = \Spatie\Permission\Models\Role::findOrCreate('bendahara', 'web');

        // Create secondary users with roles
        $userKeamanan = User::factory()->create([
            'pondok_id' => $this->pondok->id,
        ]);
        $userKeamanan->assignRole($roleKeamanan);
        $userKeamanan->givePermissionTo('manage_perizinan');

        $userBendahara = User::factory()->create([
            'pondok_id' => $this->pondok->id,
        ]);
        $userBendahara->assignRole($roleBendahara);
        $userBendahara->givePermissionTo('manage_perizinan');

        // 2. Verify creating workflow template with roles inside store
        $response = $this->post(route('tenant.template-perizinan.store'), [
            'nama' => 'Izin Keluar Pondok Dengan Approval',
            'deskripsi' => 'Harus disetujui Keamanan lalu Pengasuh',
            'format_surat' => '<p>Surat Izin untuk {nama_santri}</p>',
            'layout_print' => 1,
            'rules' => [
                'approval_workflow' => [
                    [
                        'step' => 1,
                        'name' => 'Persetujuan Keamanan',
                        'required_role' => 'keamanan',
                    ],
                    [
                        'step' => 2,
                        'name' => 'Persetujuan Pengasuh',
                        'required_role' => 'pengasuh',
                    ],
                ]
            ],
        ]);

        $response->assertRedirect(route('tenant.template-perizinan.index'));
        
        $template = TemplatePerizinan::where('nama', 'Izin Keluar Pondok Dengan Approval')->first();
        $this->assertNotNull($template);
        $this->assertEquals(2, count($template->rules['approval_workflow']));
        $this->assertEquals('keamanan', $template->rules['approval_workflow'][0]['required_role']);
        $this->assertEquals('pengasuh', $template->rules['approval_workflow'][1]['required_role']);

        // 3. Verify student registration with workflow template creates a pending perizinan
        $this->actingAs($this->user);
        $responseCreate = $this->post(route('tenant.perizinan.store'), [
            'santri_id' => $this->santri->id,
            'template_perizinan_id' => $template->id,
            'tanggal_keluar' => now()->addHour()->toDateTimeString(),
            'batas_kembali' => now()->addDays(2)->toDateTimeString(),
            'keperluan' => 'Keperluan mendesak',
        ]);

        $responseCreate->assertRedirect(route('tenant.perizinan.index'));

        $perizinan = Perizinan::where('santri_id', $this->santri->id)
            ->where('template_perizinan_id', $template->id)
            ->first();

        $this->assertNotNull($perizinan);
        $this->assertEquals('pending', $perizinan->status);
        $this->assertEquals(1, $perizinan->current_step);

        // Verify that santri status is still 'active' (not changed to 'izin' yet)
        $this->santri->refresh();
        $this->assertEquals('active', $this->santri->status);

        // 4. Verify user with non-matching role is blocked from approving step 1
        $this->actingAs($userBendahara);
        $responseApproveFail = $this->post(route('tenant.perizinan.approve', $perizinan->id));
        $responseApproveFail->assertSessionHas('error');
        $this->assertStringContainsString('LANGKAH INI MEMERLUKAN WEWENANG PERAN KEAMANAN', strtoupper(session('error')));

        // perizinan current_step should still be 1, status should still be pending
        $perizinan->refresh();
        $this->assertEquals(1, $perizinan->current_step);
        $this->assertEquals('pending', $perizinan->status);

        // 5. Verify user with matching role (Keamanan) approves step 1
        $this->actingAs($userKeamanan);
        $responseApproveSuccess1 = $this->post(route('tenant.perizinan.approve', $perizinan->id));
        $responseApproveSuccess1->assertSessionHas('success');

        $perizinan->refresh();
        $this->assertEquals(2, $perizinan->current_step);
        $this->assertEquals('pending', $perizinan->status);
        $this->assertCount(1, $perizinan->approvals);
        $this->assertEquals($userKeamanan->id, $perizinan->approvals->first()->approved_by);

        // Verify that santri status is still 'active' (not changed to 'izin' yet)
        $this->santri->refresh();
        $this->assertEquals('active', $this->santri->status);

        // 6. Verify final approval (Pengasuh) triggers 'aktif' status, updates santri status, and maps attendance
        // Let's assign Pengasuh role to our first user ($this->user) to let them approve step 2
        $this->user->assignRole($rolePengasuh);
        $this->actingAs($this->user);

        // Setup Sesi Absensi for attendance syncing verification
        $sesi = AbsensiSesi::create([
            'pondok_id' => $this->pondok->id,
            'nama_sesi' => 'Sesi Asar',
            'jam_mulai' => '15:00:00',
            'jam_selesai' => '16:00:00',
            'target_tipe' => 'global',
            'is_active' => true,
        ]);

        $responseApproveSuccess2 = $this->post(route('tenant.perizinan.approve', $perizinan->id));
        $responseApproveSuccess2->assertSessionHas('success');

        $perizinan->refresh();
        // current_step should now be 3 (out of 2 steps)
        $this->assertEquals(3, $perizinan->current_step);
        $this->assertEquals('aktif', $perizinan->status);
        $this->assertCount(2, $perizinan->approvals);

        // Verify student status changed to 'izin'
        $this->santri->refresh();
        $this->assertEquals('izin', $this->santri->status);

        // Verify attendance logs synced automatically
        $absensiCount = Absensi::where('santri_id', $this->santri->id)->where('status', 'izin')->count();
        $this->assertGreaterThan(0, $absensiCount);
    }

    public function test_custom_variables_and_printing_flow(): void
    {
        // Setup standard variable key
        \App\Models\TemplateVariable::create([
            'key' => 'santri.nama_lengkap',
            'label' => 'Nama Santri',
            'type' => 'auto',
            'is_active' => true,
        ]);

        // 1. Create template with custom placeholders and qr_code element
        $response = $this->post(route('tenant.template-perizinan.store'), [
            'nama' => 'Izin Belanja Kompleks',
            'deskripsi' => 'Template untuk belanja mingguan',
            'format_surat' => '<p>Surat Izin untuk {nama_santri}. Kendaraan: {nopol_motor}. QR: {qr_code}</p>',
            'layout_print' => 1,
        ]);

        $response->assertRedirect(route('tenant.template-perizinan.index'));
        
        $template = TemplatePerizinan::where('nama', 'Izin Belanja Kompleks')->first();
        $this->assertNotNull($template);
        
        // nopol_motor should map to custom_variables.nopol_motor, and qr_code is extracted
        $this->assertContains('custom_variables.nopol_motor', $template->required_variables);
        $this->assertContains('qr_code', $template->required_variables);

        // 2. Create permit using the template and providing custom variable value
        $responseCreate = $this->post(route('tenant.perizinan.store'), [
            'santri_id' => $this->santri->id,
            'template_perizinan_id' => $template->id,
            'tanggal_keluar' => now()->addHour()->toDateTimeString(),
            'batas_kembali' => now()->addDays(1)->toDateTimeString(),
            'keperluan' => 'Belanja bulanan',
            'variables' => [
                'custom_variables.nopol_motor' => 'B 7777 XYZ',
            ],
        ]);

        $responseCreate->assertRedirect(route('tenant.perizinan.index'));

        $perizinan = Perizinan::where('santri_id', $this->santri->id)
            ->where('template_perizinan_id', $template->id)
            ->first();

        $this->assertNotNull($perizinan);
        $this->assertEquals('B 7777 XYZ', $perizinan->variables['custom_variables.nopol_motor']);

        // 3. Print the permit and verify it replaces placeholders, including custom ones and QR code
        $responsePrint = $this->get(route('tenant.perizinan.print', $perizinan->id));
        $responsePrint->assertStatus(200);
        $responsePrint->assertSee('B 7777 XYZ'); // custom variable replacement check
        $responsePrint->assertSee('<svg', false); // QR Code rendering check
    }

    public function test_html_template_printing_custom_styles(): void
    {
        // Create template with styling rules
        $template = TemplatePerizinan::create([
            'pondok_id' => $this->pondok->id,
            'nama' => 'Izin Custom Style',
            'slug' => 'izin-custom-style',
            'deskripsi' => 'Template with custom styling',
            'source_type' => 'html',
            'format_surat' => '<p>Surat Izin Resmi untuk {nama_santri} dan QR: {qr_code}</p>',
            'layout_print' => 1,
            'required_variables' => ['santri.nama_lengkap', 'qr_code'],
            'rules' => [
                'styling' => [
                    'font_family' => "'Inter', sans-serif",
                    'line_height' => '1.6',
                    'border_frame' => '1',
                ],
            ],
            'is_default' => false,
            'is_active' => true,
            'created_by' => $this->user->id,
        ]);

        // Create permit using the template
        $responseCreate = $this->post(route('tenant.perizinan.store'), [
            'santri_id' => $this->santri->id,
            'template_perizinan_id' => $template->id,
            'tanggal_keluar' => now()->addHour()->toDateTimeString(),
            'batas_kembali' => now()->addDays(1)->toDateTimeString(),
            'keperluan' => 'Piknik',
        ]);

        $responseCreate->assertRedirect(route('tenant.perizinan.index'));

        $perizinan = Perizinan::where('santri_id', $this->santri->id)
            ->where('template_perizinan_id', $template->id)
            ->first();

        $this->assertNotNull($perizinan);

        // Print and verify styles are present in the response
        $responsePrint = $this->get(route('tenant.perizinan.print', $perizinan->id));
        $responsePrint->assertStatus(200);
        $responsePrint->assertSee("font-family: 'Inter', sans-serif", false);
        $responsePrint->assertSee("line-height: 1.6", false);
        $responsePrint->assertSee("border: 10px double #000", false);
    }

    public function test_html_template_saves_wizard_state(): void
    {
        // 1. Create a template with wizard state
        $response = $this->post(route('tenant.template-perizinan.store'), [
            'nama' => 'Izin Wizard Template',
            'deskripsi' => 'Template built with wizard',
            'format_surat' => '<p>Template Content</p>',
            'layout_print' => 1,
            'rules' => [
                'design_mode' => 'wizard',
                'wizard_state' => [
                    'use_kop' => '1',
                    'kop_yayasan' => 'Yayasan Test',
                    'kop_pondok' => 'Pondok Test',
                    'judul' => 'SURAT IZIN WIZARD',
                ]
            ]
        ]);

        $response->assertRedirect(route('tenant.template-perizinan.index'));

        $template = TemplatePerizinan::where('nama', 'Izin Wizard Template')->first();
        $this->assertNotNull($template);
        $this->assertEquals('wizard', $template->rules['design_mode']);
        $this->assertEquals('Yayasan Test', $template->rules['wizard_state']['kop_yayasan']);
        $this->assertEquals('SURAT IZIN WIZARD', $template->rules['wizard_state']['judul']);
    }

    public function test_pdf_overlay_printing_flow(): void
    {
        // 1. Create a PDF template in DB
        $template = TemplatePerizinan::create([
            'pondok_id' => $this->pondok->id,
            'nama' => 'Izin Resmi PDF',
            'slug' => 'izin-resmi-pdf',
            'deskripsi' => 'PDF Template description',
            'source_type' => 'upload_pdf',
            'file_pdf' => 'templates/dummy.pdf',
            'layout_print' => 2,
            'required_variables' => [
                'santri.nama_lengkap' => ['x' => '10.50', 'y' => '20.25'],
                'custom_variables.nama_penjemput' => ['x' => '15.00', 'y' => '30.00'],
                'qr_code' => ['x' => '50.00', 'y' => '50.00'],
            ],
            'is_default' => false,
            'is_active' => true,
            'created_by' => $this->user->id,
        ]);

        // 2. Create permit using this PDF template
        $responseCreate = $this->post(route('tenant.perizinan.store'), [
            'santri_id' => $this->santri->id,
            'template_perizinan_id' => $template->id,
            'tanggal_keluar' => now()->addHour()->toDateTimeString(),
            'batas_kembali' => now()->addDays(1)->toDateTimeString(),
            'keperluan' => 'Piknik asrama',
            'variables' => [
                'custom_variables.nama_penjemput' => 'Bapak Budi',
            ],
        ]);

        $responseCreate->assertRedirect(route('tenant.perizinan.index'));

        $perizinan = Perizinan::where('santri_id', $this->santri->id)
            ->where('template_perizinan_id', $template->id)
            ->first();

        $this->assertNotNull($perizinan);
        $this->assertEquals('Bapak Budi', $perizinan->variables['custom_variables.nama_penjemput']);

        // 3. Print the PDF overlay permit and check view variables
        $responsePrint = $this->get(route('tenant.perizinan.print', $perizinan->id));
        $responsePrint->assertStatus(200);
        $responsePrint->assertViewIs('tenant.perizinan.print_pdf_overlay');
        $responsePrint->assertSee('Bapak Budi'); // overlaid value
        $responsePrint->assertSee('10.50%'); // coordinate style percentage check
        $responsePrint->assertSee('20.25%');
        $responsePrint->assertSee('<svg', false); // inline QR code SVG check
    }
}


<?php

namespace Tests\Feature;

use App\Domains\Import\Actions\CommitImportAction;
use App\Domains\Import\Actions\PreviewImportAction;
use App\Models\ImportBatch;
use App\Models\ImportField;
use App\Models\ImportRow;
use App\Models\ImportTemplate;
use App\Models\ImportTemplateField;
use App\Models\Kamar;
use App\Models\Komplek;
use App\Models\Lemari;
use App\Models\LemariSlot;
use App\Models\Pondok;
use App\Models\Santri;
use App\Models\User;
use App\Models\Wali;
use Database\Seeders\ImportFieldSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class ImportTest extends TestCase
{
    use RefreshDatabase;

    protected Pondok $pondok;
    protected User $user;
    protected ImportTemplate $template;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed import fields
        $this->seed(ImportFieldSeeder::class);

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

        // Create Import Template
        $this->template = ImportTemplate::create([
            'pondok_id' => $this->pondok->id,
            'nama_template' => 'Template Lengkap',
        ]);
    }

    /**
     * Helper to associate fields with template in order
     */
    protected function setupTemplateFields(array $fieldKeys): void
    {
        $fields = ImportField::whereIn('field_key', $fieldKeys)->get()->keyBy('field_key');

        $insertData = [];
        foreach ($fieldKeys as $order => $key) {
            $insertData[] = [
                'template_id' => $this->template->id,
                'field_id' => $fields[$key]->id,
                'order' => $order,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        ImportTemplateField::insert($insertData);
    }

    /**
     * Helper to create uploaded file from CSV string
     */
    protected function createCsvFile(array $headers, array $rows): UploadedFile
    {
        $csvContent = implode(',', $headers) . "\n";
        foreach ($rows as $row) {
            $csvContent .= implode(',', $row) . "\n";
        }

        $filePath = tempnam(sys_get_temp_dir(), 'import_test_');
        file_put_contents($filePath, $csvContent);

        return new UploadedFile(
            $filePath,
            'import.csv',
            'text/csv',
            null,
            true // test mode
        );
    }

    public function test_row_level_validation_detects_missing_parent_relations(): void
    {
        // Setup template fields: komplek, kamar, lemari, slot, nis, nama_lengkap
        $this->setupTemplateFields(['komplek', 'kamar', 'lemari', 'slot', 'nis', 'nama_lengkap']);

        // Row 1: Kamar filled, but Komplek empty
        // Row 2: Lemari filled, but Kamar empty
        // Row 3: Slot filled, but Lemari empty
        // Row 4: Nama Santri filled, but NIS empty
        $file = $this->createCsvFile(
            ['Komplek', 'Kamar', 'Nama Lemari', 'Nomor Slot', 'NIS', 'Nama Santri'],
            [
                ['', 'Kamar A', '', '', '12345', 'Ahmad'], // Kamar A but no Komplek
                ['Komplek A', '', 'Lemari A', '', '12346', 'Budi'], // Lemari A but no Kamar
                ['Komplek A', 'Kamar A', '', '1', '12347', 'Coki'], // Slot 1 but no Lemari
                ['Komplek A', 'Kamar A', 'Lemari A', '1', '', 'Dono'], // Santri Dono but no NIS
            ]
        );

        $action = new PreviewImportAction();
        $batch = $action->execute(
            $this->pondok->id,
            $this->user->id,
            $this->template->id,
            $file,
            'create',
            'update'
        );

        $this->assertNotNull($batch);
        $this->assertEquals(4, $batch->total_rows);
        $this->assertEquals(0, $batch->valid_rows);
        $this->assertEquals(4, $batch->invalid_rows);

        $rows = $batch->rows()->orderBy('row_number')->get();

        // Row 1 error check
        $this->assertContains('Kolom Komplek harus diisi di Excel jika kolom Kamar diisi.', $rows[0]->errors);

        // Row 2 error check
        $this->assertContains('Kolom Kamar harus diisi di Excel jika kolom Lemari diisi.', $rows[1]->errors);

        // Row 3 error check
        $this->assertContains('Kolom Lemari harus diisi di Excel jika kolom Slot diisi.', $rows[2]->errors);

        // Row 4 error check
        $this->assertContains('NIS (Nomor Induk Santri) wajib diisi untuk memasukkan data Santri.', $rows[3]->errors);
    }

    public function test_import_and_commit_successfully_saves_room_relations_with_correct_lemari_slot(): void
    {
        $this->setupTemplateFields(['komplek', 'kamar', 'lemari', 'slot', 'slot_status', 'slot_keterangan']);

        $file = $this->createCsvFile(
            ['Komplek', 'Kamar', 'Nama Lemari', 'Nomor Slot', 'Status Slot', 'Keterangan Slot'],
            [
                ['Komplek Mawar', 'Kamar 101', 'Lemari Kayu', '2', 'dipakai', 'Milik Santri Baru'],
            ]
        );

        $previewAction = new PreviewImportAction();
        $batch = $previewAction->execute(
            $this->pondok->id,
            $this->user->id,
            $this->template->id,
            $file,
            'create',
            'update'
        );

        $this->assertNotNull($batch);
        $this->assertEquals(1, $batch->valid_rows);
        $this->assertEquals(0, $batch->invalid_rows);

        // Commit the import
        $commitAction = new CommitImportAction();
        $commitAction->execute($batch->id);

        // Assert Komplek created
        $komplek = Komplek::where('pondok_id', $this->pondok->id)->where('nama', 'Komplek Mawar')->first();
        $this->assertNotNull($komplek);

        // Assert Kamar created and references Komplek
        $kamar = Kamar::where('pondok_id', $this->pondok->id)->where('nama', 'Kamar 101')->first();
        $this->assertNotNull($kamar);
        $this->assertEquals($komplek->id, $kamar->komplek_id);

        // Assert Lemari created and references Kamar
        $lemari = Lemari::where('pondok_id', $this->pondok->id)->where('nama', 'Lemari Kayu')->first();
        $this->assertNotNull($lemari);
        $this->assertEquals($kamar->id, $lemari->kamar_id);

        // Assert LemariSlot created and references Lemari (not pondok_id)
        $slot = LemariSlot::where('lemari_id', $lemari->id)->where('nomor_slot', 2)->first();
        $this->assertNotNull($slot);
        $this->assertEquals('dipakai', $slot->status);
        $this->assertEquals('Milik Santri Baru', $slot->keterangan);
    }

    public function test_import_and_commit_successfully_saves_and_updates_santri_and_wali(): void
    {
        $this->setupTemplateFields(['nis', 'nama_lengkap', 'alamat', 'wali_nama', 'wali_nik']);

        // 1. First import: Create new Santri and Wali
        $file1 = $this->createCsvFile(
            ['NIS', 'Nama Santri', 'Alamat Santri', 'Nama Wali', 'NIK Wali'],
            [
                ['NIS001', 'Santri Pertama', 'Alamat A', 'Wali Pertama', 'NIK12345'],
            ]
        );

        $previewAction = new PreviewImportAction();
        $batch1 = $previewAction->execute(
            $this->pondok->id,
            $this->user->id,
            $this->template->id,
            $file1,
            'create',
            'update'
        );

        $commitAction = new CommitImportAction();
        $commitAction->execute($batch1->id);

        // Verify Santri saved in database
        $santri = Santri::where('pondok_id', $this->pondok->id)->where('nis', 'NIS001')->first();
        $this->assertNotNull($santri);
        $this->assertEquals('Santri Pertama', $santri->nama_lengkap);
        $this->assertEquals('Alamat A', $santri->alamat);

        // Verify Wali saved
        $wali = Wali::where('pondok_id', $this->pondok->id)->where('nik', 'NIK12345')->first();
        $this->assertNotNull($wali);
        $this->assertEquals('Wali Pertama', $wali->nama);
        $this->assertEquals($wali->id, $santri->wali_id);

        // 2. Second import: Update Santri's name/address and keep Wali
        $file2 = $this->createCsvFile(
            ['NIS', 'Nama Santri', 'Alamat Santri', 'Nama Wali', 'NIK Wali'],
            [
                ['NIS001', 'Santri Pertama Updated', 'Alamat A Updated', 'Wali Pertama', 'NIK12345'],
            ]
        );

        $batch2 = $previewAction->execute(
            $this->pondok->id,
            $this->user->id,
            $this->template->id,
            $file2,
            'create',
            'update'
        );

        $commitAction->execute($batch2->id);

        // Verify Santri updated in database
        $santriUpdated = Santri::where('pondok_id', $this->pondok->id)->where('nis', 'NIS001')->first();
        $this->assertNotNull($santriUpdated);
        $this->assertEquals('Santri Pertama Updated', $santriUpdated->nama_lengkap);
        $this->assertEquals('Alamat A Updated', $santriUpdated->alamat);
        $this->assertEquals($wali->id, $santriUpdated->wali_id);
    }

    public function test_import_strips_leading_single_quotes_from_strings(): void
    {
        $this->setupTemplateFields(['nis', 'nama_lengkap', 'no_hp']);

        $file = $this->createCsvFile(
            ['NIS', 'Nama Santri', 'No HP Santri'],
            [
                ["'12345", 'Ahmad', "'081234567890"],
            ]
        );

        $previewAction = new PreviewImportAction();
        $batch = $previewAction->execute(
            $this->pondok->id,
            $this->user->id,
            $this->template->id,
            $file,
            'create',
            'update'
        );

        $this->assertNotNull($batch);
        $row = $batch->rows()->first();
        $this->assertEquals('12345', $row->payload['nis']);
        $this->assertEquals('081234567890', $row->payload['no_hp']);
    }

    public function test_rollback_correctly_deletes_new_records(): void
    {
        $this->setupTemplateFields(['nis', 'nama_lengkap']);

        $file = $this->createCsvFile(
            ['NIS', 'Nama Santri'],
            [
                ['NIS999', 'Santri Rollback Test'],
            ]
        );

        $previewAction = new PreviewImportAction();
        $batch = $previewAction->execute(
            $this->pondok->id,
            $this->user->id,
            $this->template->id,
            $file,
            'create',
            'update'
        );

        $commitAction = new CommitImportAction();
        $commitAction->execute($batch->id);

        // Assert Santri exists in database
        $this->assertDatabaseHas('santris', [
            'pondok_id' => $this->pondok->id,
            'nis' => 'NIS999',
            'nama_lengkap' => 'Santri Rollback Test',
        ]);

        // Run Rollback
        $rollbackAction = new \App\Domains\Import\Actions\RollbackImportAction();
        $rollbackAction->execute($batch->id);

        // Assert Santri was soft deleted from the database
        $santri = Santri::withTrashed()->where('nis', 'NIS999')->first();
        $this->assertNotNull($santri);
        $this->assertNotNull($santri->deleted_at);
    }

    public function test_rollback_correctly_restores_existing_records_with_date_and_json_fields(): void
    {
        $this->setupTemplateFields(['nis', 'nama_lengkap', 'tanggal_lahir']);

        // Create initial Santri
        $santri = Santri::create([
            'pondok_id' => $this->pondok->id,
            'nis' => 'NIS-ROLLBACK-TEST',
            'nama_lengkap' => 'Original Name',
            'tanggal_lahir' => '2020-01-01',
            'custom_fields' => ['riwayat_penyakit' => 'Alergi'],
        ]);

        // We simulate an import batch and changes manually to test legacy support as well
        $batch = ImportBatch::create([
            'pondok_id' => $this->pondok->id,
            'template_id' => $this->template->id,
            'uploaded_by' => $this->user->id,
            'filename' => 'test_rollback.csv',
            'total_rows' => 1,
            'valid_rows' => 1,
            'invalid_rows' => 0,
            'status' => 'committed',
        ]);

        $row = ImportRow::create([
            'batch_id' => $batch->id,
            'row_number' => 1,
            'payload' => [
                'nis' => 'NIS-ROLLBACK-TEST',
                'nama_lengkap' => 'Updated Name',
                'tanggal_lahir' => '2021-02-02',
            ],
            'is_valid' => true,
        ]);

        // Manually update the Santri to the new state
        $santri->update([
            'nama_lengkap' => 'Updated Name',
            'tanggal_lahir' => '2021-02-02',
            'custom_fields' => ['riwayat_penyakit' => 'Asma'],
        ]);

        // Log the changes simulating the legacy json format for date and json (wrapped in outer quotes / json encoded)
        // 1. name change (normal string)
        \App\Models\ImportChange::create([
            'batch_id' => $batch->id,
            'row_id' => $row->id,
            'entity' => 'santri',
            'entity_id' => $santri->id,
            'column_name' => 'nama_lengkap',
            'old_value' => 'Original Name',
            'new_value' => 'Updated Name',
        ]);

        // 2. date change (simulated legacy format: serialized JSON string with double quotes)
        \App\Models\ImportChange::create([
            'batch_id' => $batch->id,
            'row_id' => $row->id,
            'entity' => 'santri',
            'entity_id' => $santri->id,
            'column_name' => 'tanggal_lahir',
            'old_value' => '"2020-01-01T00:00:00.000000Z"', // simulated legacy date
            'new_value' => '2021-02-02',
        ]);

        // 3. custom_fields change (simulated legacy format: json array/object serialized)
        \App\Models\ImportChange::create([
            'batch_id' => $batch->id,
            'row_id' => $row->id,
            'entity' => 'santri',
            'entity_id' => $santri->id,
            'column_name' => 'custom_fields',
            'old_value' => '{"riwayat_penyakit":"Alergi"}',
            'new_value' => '{"riwayat_penyakit":"Asma"}',
        ]);

        // Run Rollback
        $rollbackAction = new \App\Domains\Import\Actions\RollbackImportAction();
        $rollbackAction->execute($batch->id);

        // Refresh Santri
        $santri->refresh();

        // Assert that values were restored correctly
        $this->assertEquals('Original Name', $santri->nama_lengkap);
        $this->assertEquals('2020-01-01', $santri->tanggal_lahir->format('Y-m-d'));
        $this->assertEquals(['riwayat_penyakit' => 'Alergi'], $santri->custom_fields);
    }

    public function test_large_import_commits_asynchronously_via_queue(): void

    {
        Queue::fake();

        $batch = ImportBatch::create([
            'pondok_id' => $this->pondok->id,
            'template_id' => $this->template->id,
            'uploaded_by' => $this->user->id,
            'filename' => 'large.csv',
            'total_rows' => 105, // > 100
            'status' => 'preview',
        ]);

        $response = $this->post(route('tenant.import.commit', $batch->id));

        $response->assertRedirect(route('tenant.import.history'));
        $response->assertSessionHas('success');

        Queue::assertPushed(\App\Jobs\ProcessImportJob::class);
    }

    public function test_import_saves_custom_fields_to_json_column(): void
    {
        $customField = ImportField::create([
            'pondok_id' => $this->pondok->id,
            'field_key' => 'riwayat_penyakit',
            'label' => 'Riwayat Penyakit',
            'entity' => 'custom',
            'column_name' => 'custom_fields->riwayat_penyakit',
            'is_required' => false,
        ]);

        $this->setupTemplateFields(['nis', 'nama_lengkap', 'riwayat_penyakit']);

        $file = $this->createCsvFile(
            ['NIS', 'Nama Santri', 'Riwayat Penyakit'],
            [
                ['NIS777', 'Santri Custom Test', 'Alergi Dingin'],
            ]
        );

        $previewAction = new PreviewImportAction();
        $batch = $previewAction->execute(
            $this->pondok->id,
            $this->user->id,
            $this->template->id,
            $file,
            'create',
            'update'
        );

        $commitAction = new CommitImportAction();
        $commitAction->execute($batch->id);

        $santri = Santri::where('pondok_id', $this->pondok->id)->where('nis', 'NIS777')->first();
        $this->assertNotNull($santri);
        $this->assertArrayHasKey('riwayat_penyakit', $santri->custom_fields);
        $this->assertEquals('Alergi Dingin', $santri->custom_fields['riwayat_penyakit']);
    }

    public function test_import_with_two_header_rows_and_shuffled_columns(): void
    {
        $this->setupTemplateFields(['nis', 'nama_lengkap', 'jenis_kelamin']);

        // Shuffled order: nama_lengkap (col 0), jenis_kelamin (col 1), nis (col 2)
        // Row 1: database keys (exact lowercase)
        // Row 2: visual labels
        // Row 3: actual data
        $rows = [
            ['nama_lengkap', 'jenis_kelamin', 'nis'],
            ['Nama Lengkap *', 'Jenis Kelamin *', 'NIS *'],
            ['Ahmad Syafiq', 'L', 'NIS991'],
            ['Halimah', 'P', 'NIS992']
        ];

        // Let's build a CSV representing this 2-header layout
        $csvContent = '';
        foreach ($rows as $row) {
            $csvContent .= implode(',', $row) . "\n";
        }

        $filePath = tempnam(sys_get_temp_dir(), 'import_test_');
        file_put_contents($filePath, $csvContent);

        $file = new UploadedFile($filePath, 'import.csv', 'text/csv', null, true);

        $previewAction = new PreviewImportAction();
        $batch = $previewAction->execute(
            $this->pondok->id,
            $this->user->id,
            $this->template->id,
            $file,
            'create',
            'update'
        );

        $this->assertNotNull($batch);
        $this->assertEquals(2, $batch->total_rows);
        $this->assertEquals(2, $batch->valid_rows);

        $rowModels = $batch->rows()->orderBy('row_number')->get();
        
        // Assert Row 1 (Excel Row 3) is Ahmad Syafiq
        $this->assertEquals('NIS991', $rowModels[0]->payload['nis']);
        $this->assertEquals('Ahmad Syafiq', $rowModels[0]->payload['nama_lengkap']);
        $this->assertEquals('L', $rowModels[0]->payload['jenis_kelamin']);
        $this->assertEquals(3, $rowModels[0]->row_number);

        // Assert Row 2 (Excel Row 4) is Halimah
        $this->assertEquals('NIS992', $rowModels[1]->payload['nis']);
        $this->assertEquals('Halimah', $rowModels[1]->payload['nama_lengkap']);
        $this->assertEquals('P', $rowModels[1]->payload['jenis_kelamin']);
        $this->assertEquals(4, $rowModels[1]->row_number);
    }

    public function test_import_with_mismatched_template_throws_exception(): void
    {
        // Setup template fields: nis, nama_lengkap
        $this->setupTemplateFields(['nis', 'nama_lengkap']);

        // Excel has columns matching other system fields: komplek, kamar, kelas
        $file = $this->createCsvFile(
            ['Komplek', 'Kamar', 'Kelas'],
            [
                ['Mawar', '101', 'Awaliyah 1']
            ]
        );

        $previewAction = new PreviewImportAction();

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("Struktur kolom berkas Excel tidak sesuai dengan template 'Template Lengkap' yang Anda pilih.");

        $previewAction->execute(
            $this->pondok->id,
            $this->user->id,
            $this->template->id,
            $file,
            'create',
            'update'
        );
    }

    public function test_import_selects_correct_data_sheet_when_multiple_sheets_exist(): void
    {
        $this->setupTemplateFields(['nis', 'nama_lengkap']);

        // Create spreadsheet with two sheets: 'Lookups' (first) and 'Data' (second)
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        
        // Sheet 1: Lookups (Active/First sheet)
        $sheetLookups = $spreadsheet->getActiveSheet();
        $sheetLookups->setTitle('Lookups');
        $sheetLookups->setCellValue('A1', 'Kelas');
        $sheetLookups->setCellValue('B1', 'Komplek');
        $sheetLookups->setCellValue('A2', 'Awaliyah 1');
        $sheetLookups->setCellValue('B2', 'Mawar');

        // Sheet 2: Data
        $sheetData = $spreadsheet->createSheet();
        $sheetData->setTitle('Data');
        $sheetData->setCellValue('A1', 'nis');
        $sheetData->setCellValue('B1', 'nama_lengkap');
        $sheetData->setCellValue('A2', 'NIS *');
        $sheetData->setCellValue('B2', 'Nama Santri *');
        $sheetData->setCellValue('A3', 'NIS881');
        $sheetData->setCellValue('B3', 'Akhmad');

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $filePath = tempnam(sys_get_temp_dir(), 'import_test_sheet_');
        $writer->save($filePath);

        $file = new UploadedFile($filePath, 'import.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', null, true);

        $previewAction = new PreviewImportAction();
        $batch = $previewAction->execute(
            $this->pondok->id,
            $this->user->id,
            $this->template->id,
            $file,
            'create',
            'update'
        );

        $this->assertNotNull($batch);
        $this->assertEquals(1, $batch->total_rows);
        $this->assertEquals(1, $batch->valid_rows);

        $rowModels = $batch->rows()->first();
        $this->assertEquals('NIS881', $rowModels->payload['nis']);
        $this->assertEquals('Akhmad', $rowModels->payload['nama_lengkap']);
    }

    public function test_download_template_generates_file_successfully(): void
    {
        $this->setupTemplateFields(['nis', 'nama_lengkap', 'kelas', 'komplek', 'kamar']);

        $response = $this->get(route('tenant.import-templates.download', $this->template->id));

        $response->assertStatus(200);
        $this->assertNotNull($response->getFile());
    }
}

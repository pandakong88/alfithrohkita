<?php

namespace Tests\Feature;

use App\Models\Pondok;
use App\Models\User;
use App\Models\Wali;
use App\Models\WaliImportBatch;
use App\Domains\Wali\Actions\ImportWaliPreviewAction;
use App\Domains\Wali\Actions\CommitWaliImportAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class WaliImportTest extends TestCase
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

        // Setup Spatie permissions
        \Spatie\Permission\Models\Permission::findOrCreate('manage_wali');
        $this->user->givePermissionTo('manage_wali');

        $this->actingAs($this->user);
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

        $filePath = tempnam(sys_get_temp_dir(), 'wali_import_test_');
        file_put_contents($filePath, $csvContent);

        return new UploadedFile(
            $filePath,
            'import.csv',
            'text/csv',
            null,
            true // test mode
        );
    }

    public function test_download_template_route(): void
    {
        $response = $this->get(route('tenant.wali.template.download'));
        $response->assertStatus(200);
        $this->assertNotNull($response->getFile());
    }

    public function test_wali_import_form_route(): void
    {
        $response = $this->get(route('tenant.wali.import'));
        $response->assertStatus(200);
    }

    public function test_preview_and_commit_wali_import(): void
    {
        $file = $this->createCsvFile(
            ['nama', 'nik', 'no_hp', 'alamat', 'pekerjaan'],
            [
                ['Wali Sukses', '1234567890123456', '081234567890', 'Alamat Jalan A', 'Pedagang'],
                ['Wali Kedua', '1234567890123457', '081234567891', 'Alamat Jalan B', 'Swasta'],
            ]
        );

        $previewAction = new ImportWaliPreviewAction();
        $batch = $previewAction->execute($file);

        $this->assertNotNull($batch);
        $this->assertEquals(2, $batch->total_rows);
        $this->assertEquals(2, $batch->valid_rows);
        $this->assertEquals(0, $batch->invalid_rows);

        // Commit the import
        $commitAction = new CommitWaliImportAction();
        $commitAction->execute($batch);

        // Assert Walis are created in DB
        $this->assertDatabaseHas('walis', [
            'pondok_id' => $this->pondok->id,
            'nama' => 'Wali Sukses',
            'nik' => '1234567890123456',
            'no_hp' => '081234567890',
            'alamat' => 'Alamat Jalan A',
            'pekerjaan' => 'Pedagang',
        ]);

        $this->assertDatabaseHas('walis', [
            'pondok_id' => $this->pondok->id,
            'nama' => 'Wali Kedua',
            'nik' => '1234567890123457',
            'no_hp' => '081234567891',
            'alamat' => 'Alamat Jalan B',
            'pekerjaan' => 'Swasta',
        ]);
    }

    public function test_validation_missing_name_and_duplicates(): void
    {
        // Wali Pertama exists in database
        Wali::create([
            'pondok_id' => $this->pondok->id,
            'nama' => 'Wali Pertama',
            'nik' => '3201010101010001',
            'no_hp' => '081111111111',
        ]);

        // Excel file contains:
        // Row 1: Missing nama
        // Row 2: Duplicate NIK within file
        // Row 3: Same NIK as row 2
        // Row 4: Duplicate NIK with existing DB record
        $file = $this->createCsvFile(
            ['nama', 'nik', 'no_hp', 'alamat', 'pekerjaan'],
            [
                ['', '3201010101010002', '082222222222', 'Alamat C', 'PNS'], // No name
                ['Row Two', '3201010101010003', '083333333333', 'Alamat D', 'Guru'],
                ['Row Three', '3201010101010003', '084444444444', 'Alamat E', 'Guru'], // Duplicate NIK inside Excel
                ['Row Four', '3201010101010001', '085555555555', 'Alamat F', 'Tani'], // Duplicate NIK with DB (marks as update mode, which is valid)
            ]
        );

        $previewAction = new ImportWaliPreviewAction();
        $batch = $previewAction->execute($file);

        $this->assertNotNull($batch);
        $this->assertEquals(4, $batch->total_rows);
        $this->assertEquals(2, $batch->valid_rows); // Row 2 and Row 4 are valid (Row 4 will trigger an update on Wali Pertama)
        $this->assertEquals(2, $batch->invalid_rows); // Row 1 and Row 3 are invalid

        $rows = $batch->rows()->orderBy('row_number')->get();

        // Row 1 check (Missing name)
        $this->assertFalse($rows[0]->is_valid);
        $this->assertArrayHasKey('nama', $rows[0]->errors);

        // Row 2 check (Valid new insert)
        $this->assertTrue($rows[1]->is_valid);
        $this->assertEquals('insert', $rows[1]->mode);

        // Row 3 check (Duplicate NIK in file)
        $this->assertFalse($rows[2]->is_valid);
        $this->assertArrayHasKey('nik', $rows[2]->errors);
        $this->assertEquals('NIK ganda ditemukan dalam file Excel', $rows[2]->errors['nik']);

        // Row 4 check (Duplicate NIK with database -> update mode)
        $this->assertTrue($rows[3]->is_valid);
        $this->assertEquals('update', $rows[3]->mode);
    }
}

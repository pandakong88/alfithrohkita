<?php

namespace Tests\Feature;

use App\Models\Pondok;
use App\Models\User;
use App\Models\SantriHandbook;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class HandbookTest extends TestCase
{
    use RefreshDatabase;

    protected Pondok $pondokA;
    protected Pondok $pondokB;
    protected User $adminA;
    protected User $adminB;

    protected function setUp(): void
    {
        parent::setUp();

        // Create Pondoks
        $this->pondokA = Pondok::create([
            'name' => 'Pondok A',
            'slug' => 'pondok-a',
            'is_active' => true,
        ]);

        $this->pondokB = Pondok::create([
            'name' => 'Pondok B',
            'slug' => 'pondok-b',
            'is_active' => true,
        ]);

        // Create Admin Users
        $this->adminA = User::factory()->create([
            'pondok_id' => $this->pondokA->id,
        ]);

        $this->adminB = User::factory()->create([
            'pondok_id' => $this->pondokB->id,
        ]);

        // Setup Spatie permissions
        \Spatie\Permission\Models\Permission::findOrCreate('manage_users');
        $this->adminA->givePermissionTo('manage_users');
        $this->adminB->givePermissionTo('manage_users');
    }

    public function test_tenant_admin_can_crud_handbooks_with_automatic_isolation(): void
    {
        $this->actingAs($this->adminA);

        // 1. Get index page
        $response = $this->get(route('tenant.santri.handbook.index'));
        $response->assertStatus(200);

        // Fake the public disk upload directory
        $file = UploadedFile::fake()->create('pedoman_v1.pdf', 500, 'application/pdf');

        // 2. Store a new handbook version
        $response = $this->post(route('tenant.santri.handbook.store'), [
            'version' => '1.0.0',
            'release_date' => '2026-06-01',
            'status' => 'published',
            'description' => 'Versi awal pedoman Pondok A',
            'file' => $file,
        ]);

        $response->assertRedirect(route('tenant.santri.handbook.index'));
        $response->assertSessionHas('success');

        // Assert handbook exists and belongs to Pondok A
        $handbook = SantriHandbook::where('version', '1.0.0')->first();
        $this->assertNotNull($handbook);
        $this->assertEquals($this->pondokA->id, $handbook->pondok_id);
        $this->assertEquals('published', $handbook->status);

        // Verify the file was stored in public/handbooks
        $filePath = public_path($handbook->file_path);
        $this->assertTrue(file_exists($filePath));

        // 3. Edit handbook version
        $response = $this->get(route('tenant.santri.handbook.edit', $handbook->id));
        $response->assertStatus(200);

        // 4. Update handbook
        $response = $this->put(route('tenant.santri.handbook.update', $handbook->id), [
            'version' => '1.0.1', // change version
            'release_date' => '2026-06-02',
            'status' => 'published',
            'description' => 'Pembaruan deskripsi Pondok A',
        ]);

        $response->assertRedirect(route('tenant.santri.handbook.index'));
        $handbook->refresh();
        $this->assertEquals('1.0.1', $handbook->version);
        $this->assertEquals('Pembaruan deskripsi Pondok A', $handbook->description);

        // Clean up file
        if (file_exists($filePath)) {
            unlink($filePath);
        }
    }

    public function test_automatic_archiving_of_previous_versions_per_tenant(): void
    {
        // Pondok A uploads published v1
        $h1A = SantriHandbook::create([
            'pondok_id' => $this->pondokA->id,
            'version' => '1.0.0',
            'release_date' => '2026-06-01',
            'status' => 'published',
            'description' => 'Pondok A v1',
            'file_path' => 'handbooks/temp1.pdf',
        ]);

        // Pondok B uploads published v1
        $h1B = SantriHandbook::create([
            'pondok_id' => $this->pondokB->id,
            'version' => '1.0.0',
            'release_date' => '2026-06-01',
            'status' => 'published',
            'description' => 'Pondok B v1',
            'file_path' => 'handbooks/temp2.pdf',
        ]);

        // Acting as Admin A, we publish v2
        $this->actingAs($this->adminA);
        $file = UploadedFile::fake()->create('pedoman_v2.pdf', 500, 'application/pdf');

        $response = $this->post(route('tenant.santri.handbook.store'), [
            'version' => '2.0.0',
            'release_date' => '2026-06-02',
            'status' => 'published',
            'description' => 'Pondok A v2',
            'file' => $file,
        ]);

        // Assert Pondok A's v1 is archived, and v2 is published
        $h1A->refresh();
        $this->assertEquals('archived', $h1A->status);

        $h2A = SantriHandbook::where('pondok_id', $this->pondokA->id)->where('version', '2.0.0')->first();
        $this->assertNotNull($h2A);
        $this->assertEquals('published', $h2A->status);

        // Assert Pondok B's v1 is UNTOUCHED (still published) -> Enforces Tenant Isolation
        $h1B->refresh();
        $this->assertEquals('published', $h1B->status);

        // Cleanup
        $filePath = public_path($h2A->file_path);
        if (file_exists($filePath)) {
            unlink($filePath);
        }
    }

    public function test_public_access_is_tenant_isolated_by_slug(): void
    {
        // Pondok A Handbook
        $hA = SantriHandbook::create([
            'pondok_id' => $this->pondokA->id,
            'version' => '1.0.0-A',
            'release_date' => '2026-06-01',
            'status' => 'published',
            'description' => 'Pedoman Pondok A',
            'file_path' => 'handbooks/pondok_a.pdf',
        ]);

        // Pondok B Handbook
        $hB = SantriHandbook::create([
            'pondok_id' => $this->pondokB->id,
            'version' => '1.0.0-B',
            'release_date' => '2026-06-01',
            'status' => 'published',
            'description' => 'Pedoman Pondok B',
            'file_path' => 'handbooks/pondok_b.pdf',
        ]);

        // Create temporary files to prevent 404 on physical file checks
        @mkdir(public_path('handbooks'), 0755, true);
        file_put_contents(public_path($hA->file_path), 'fake pdf content A');
        file_put_contents(public_path($hB->file_path), 'fake pdf content B');

        // 1. Visit Pondok A public handbook page
        $response = $this->get(route('public.handbook.index', ['pondok_slug' => $this->pondokA->slug]));
        $response->assertStatus(200);
        $response->assertSee('v1.0.0-A');
        $response->assertDontSee('v1.0.0-B');

        // 2. Visit Pondok B public handbook page
        $response = $this->get(route('public.handbook.index', ['pondok_slug' => $this->pondokB->slug]));
        $response->assertStatus(200);
        $response->assertSee('v1.0.0-B');
        $response->assertDontSee('v1.0.0-A');

        // 3. Download Pondok A's handbook using Pondok A's slug -> success
        $response = $this->get(route('public.handbook.download', [
            'pondok_slug' => $this->pondokA->slug,
            'handbook' => $hA->id
        ]));
        $response->assertStatus(200);

        // 4. Download Pondok A's handbook using Pondok B's slug -> failure (404/403)
        $response = $this->get(route('public.handbook.download', [
            'pondok_slug' => $this->pondokB->slug,
            'handbook' => $hA->id
        ]));
        $response->assertStatus(404);

        // 5. Preview Pondok A's handbook using Pondok A's slug -> success
        $response = $this->get(route('handbook.preview', [
            'pondok_slug' => $this->pondokA->slug,
            'handbook' => $hA->id
        ]));
        $response->assertStatus(200);

        // 6. Preview Pondok A's handbook using Pondok B's slug -> failure
        $response = $this->get(route('handbook.preview', [
            'pondok_slug' => $this->pondokB->slug,
            'handbook' => $hA->id
        ]));
        $response->assertStatus(404);

        // Cleanup
        @unlink(public_path($hA->file_path));
        @unlink(public_path($hB->file_path));
    }
}

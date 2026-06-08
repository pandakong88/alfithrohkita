<?php

namespace Tests\Feature;

use App\Models\Pondok;
use App\Models\User;
use App\Models\Santri;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SantriTest extends TestCase
{
    use RefreshDatabase;

    protected Pondok $pondok;
    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->pondok = Pondok::create([
            'name' => 'Alfitroh Kita',
            'slug' => 'alfitroh-kita',
            'is_active' => true,
            'nis_auto_generate' => false,
            'nis_pattern' => '[YEAR][SEQ:4]',
        ]);

        $this->user = User::factory()->create([
            'pondok_id' => $this->pondok->id,
        ]);

        \Spatie\Permission\Models\Permission::findOrCreate('manage_santri');
        $this->user->givePermissionTo('manage_santri');

        $this->actingAs($this->user);
    }

    public function test_create_santri_form_loads_successfully(): void
    {
        $response = $this->get(route('tenant.santri.create'));
        $response->assertStatus(200);
    }

    public function test_create_santri_fails_when_nis_empty_and_auto_generate_disabled(): void
    {
        $response = $this->post(route('tenant.santri.store'), [
            'nis' => '',
            'nama_lengkap' => 'Santri Baru',
            'jenis_kelamin' => 'L',
            'tanggal_masuk' => '2026-06-07',
        ]);

        $response->assertSessionHasErrors(['nis']);
    }

    public function test_create_santri_succeeds_when_nis_provided_and_auto_generate_disabled(): void
    {
        $response = $this->post(route('tenant.santri.store'), [
            'nis' => '12345',
            'nama_lengkap' => 'Santri Baru',
            'jenis_kelamin' => 'L',
            'tanggal_masuk' => '2026-06-07',
        ]);

        $response->assertRedirect(route('tenant.santri.index'));
        $this->assertDatabaseHas('santris', [
            'pondok_id' => $this->pondok->id,
            'nis' => '12345',
            'nama_lengkap' => 'Santri Baru',
        ]);
    }

    public function test_create_santri_succeeds_and_generates_nis_when_empty_and_auto_generate_enabled(): void
    {
        $this->pondok->update(['nis_auto_generate' => true]);

        $response = $this->post(route('tenant.santri.store'), [
            'nis' => '',
            'nama_lengkap' => 'Santri Baru Auto',
            'jenis_kelamin' => 'P',
            'tanggal_masuk' => '2026-06-07',
        ]);

        $response->assertRedirect(route('tenant.santri.index'));
        $this->assertDatabaseHas('santris', [
            'pondok_id' => $this->pondok->id,
            'nama_lengkap' => 'Santri Baru Auto',
            'nis' => '20260001',
        ]);
    }

    public function test_santri_room_history_logged_correctly(): void
    {
        // 1. Create a Komplek and Kamar A & B
        $komplek = \App\Models\Komplek::create([
            'pondok_id' => $this->pondok->id,
            'nama' => 'Komplek A',
        ]);
        
        $kamarA = \App\Models\Kamar::create([
            'pondok_id' => $this->pondok->id,
            'komplek_id' => $komplek->id,
            'nama' => 'Kamar A',
        ]);

        $kamarB = \App\Models\Kamar::create([
            'pondok_id' => $this->pondok->id,
            'komplek_id' => $komplek->id,
            'nama' => 'Kamar B',
        ]);

        // 2. Create Santri with Kamar A
        $response = $this->post(route('tenant.santri.store'), [
            'nis' => '123456',
            'nama_lengkap' => 'Elsa',
            'jenis_kelamin' => 'P',
            'tanggal_masuk' => '2026-06-07',
            'kamar_id' => $kamarA->id,
        ]);

        $response->assertRedirect(route('tenant.santri.index'));
        $santri = Santri::where('nis', '123456')->first();
        $this->assertNotNull($santri);

        // Verify room history log created
        $this->assertDatabaseHas('santri_kamar_histories', [
            'pondok_id' => $this->pondok->id,
            'santri_id' => $santri->id,
            'kamar_id' => $kamarA->id,
            'end_date' => null,
        ]);

        // 3. Move Santri to Kamar B
        $response2 = $this->put(route('tenant.santri.update', $santri->id), [
            'nis' => '123456',
            'nama_lengkap' => 'Elsa',
            'jenis_kelamin' => 'P',
            'status' => 'active',
            'kamar_id' => $kamarB->id,
        ]);

        $response2->assertRedirect(route('tenant.santri.index'));

        // Verify Kamar A assignment is closed (end_date set to today)
        $this->assertDatabaseHas('santri_kamar_histories', [
            'pondok_id' => $this->pondok->id,
            'santri_id' => $santri->id,
            'kamar_id' => $kamarA->id,
            'end_date' => now()->format('Y-m-d'),
        ]);

        // Verify Kamar B assignment is created
        $this->assertDatabaseHas('santri_kamar_histories', [
            'pondok_id' => $this->pondok->id,
            'santri_id' => $santri->id,
            'kamar_id' => $kamarB->id,
            'end_date' => null,
        ]);

        // 4. Delete Santri
        $santri->delete();

        // Verify Kamar B assignment is closed
        $this->assertDatabaseHas('santri_kamar_histories', [
            'pondok_id' => $this->pondok->id,
            'santri_id' => $santri->id,
            'kamar_id' => $kamarB->id,
            'end_date' => now()->format('Y-m-d'),
        ]);
    }
}

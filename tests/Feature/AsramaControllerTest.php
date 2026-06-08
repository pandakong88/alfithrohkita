<?php

namespace Tests\Feature;

use App\Models\Kamar;
use App\Models\Komplek;
use App\Models\Lemari;
use App\Models\LemariSlot;
use App\Models\Pondok;
use App\Models\Santri;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class AsramaControllerTest extends TestCase
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
        ]);

        $this->user = User::factory()->create([
            'pondok_id' => $this->pondok->id,
        ]);

        Permission::findOrCreate('manage_asrama');
        $this->user->givePermissionTo('manage_asrama');

        $this->actingAs($this->user);
    }

    public function test_asrama_module_restricted_by_manage_asrama_permission(): void
    {
        // Sign out user and create a user without permission
        $userWithoutPermission = User::factory()->create([
            'pondok_id' => $this->pondok->id,
        ]);
        $this->actingAs($userWithoutPermission);

        // Access index page
        $response = $this->get(route('tenant.kamar.index'));
        $response->assertStatus(403);

        // Sign back in with the user who has permission
        $this->actingAs($this->user);
        $response = $this->get(route('tenant.kamar.index'));
        $response->assertStatus(200);
    }

    public function test_crud_komplek(): void
    {
        // Store Komplek
        $response = $this->post(route('tenant.komplek.store'), [
            'nama' => 'Komplek A',
        ]);
        $response->assertRedirect(route('tenant.kamar.index'));
        $this->assertDatabaseHas('kompleks', [
            'pondok_id' => $this->pondok->id,
            'nama' => 'Komplek A',
        ]);

        $komplek = Komplek::where('nama', 'Komplek A')->first();

        // Update Komplek
        $response = $this->put(route('tenant.komplek.update', $komplek), [
            'nama' => 'Komplek A-Modified',
        ]);
        $response->assertRedirect(route('tenant.kamar.index'));
        $this->assertDatabaseHas('kompleks', [
            'id' => $komplek->id,
            'nama' => 'Komplek A-Modified',
        ]);

        // Destroy Komplek
        $response = $this->delete(route('tenant.komplek.destroy', $komplek));
        $response->assertRedirect(route('tenant.kamar.index'));
        $this->assertDatabaseMissing('kompleks', [
            'id' => $komplek->id,
        ]);
    }

    public function test_crud_kamar(): void
    {
        $komplek = Komplek::create([
            'pondok_id' => $this->pondok->id,
            'nama' => 'Komplek A',
        ]);

        // Store Kamar
        $response = $this->post(route('tenant.kamar.store'), [
            'komplek_id' => $komplek->id,
            'nama' => 'Kamar 101',
            'kapasitas' => 4,
        ]);
        $response->assertRedirect(route('tenant.kamar.index'));
        $this->assertDatabaseHas('kamars', [
            'pondok_id' => $this->pondok->id,
            'komplek_id' => $komplek->id,
            'nama' => 'Kamar 101',
            'kapasitas' => 4,
        ]);

        $kamar = Kamar::where('nama', 'Kamar 101')->first();

        // Show Kamar details
        $response = $this->get(route('tenant.kamar.show', $kamar));
        $response->assertStatus(200);
        $response->assertSee('Kamar 101');

        // Update Kamar
        $response = $this->put(route('tenant.kamar.update', $kamar), [
            'komplek_id' => $komplek->id,
            'nama' => 'Kamar 101-Modified',
            'kapasitas' => 6,
        ]);
        $response->assertRedirect(route('tenant.kamar.index'));
        $this->assertDatabaseHas('kamars', [
            'id' => $kamar->id,
            'nama' => 'Kamar 101-Modified',
            'kapasitas' => 6,
        ]);

        // Destroy Kamar
        $response = $this->delete(route('tenant.kamar.destroy', $kamar));
        $response->assertRedirect(route('tenant.kamar.index'));
        $this->assertDatabaseMissing('kamars', [
            'id' => $kamar->id,
        ]);
    }

    public function test_assign_and_remove_occupant_from_kamar(): void
    {
        $komplek = Komplek::create([
            'pondok_id' => $this->pondok->id,
            'nama' => 'Komplek A',
        ]);

        $kamar = Kamar::create([
            'pondok_id' => $this->pondok->id,
            'komplek_id' => $komplek->id,
            'nama' => 'Kamar 101',
            'kapasitas' => 2,
        ]);

        $santri1 = Santri::create([
            'pondok_id' => $this->pondok->id,
            'nis' => '10001',
            'nama_lengkap' => 'Santri Satu',
            'jenis_kelamin' => 'L',
            'status' => 'active',
        ]);

        $santri2 = Santri::create([
            'pondok_id' => $this->pondok->id,
            'nis' => '10002',
            'nama_lengkap' => 'Santri Dua',
            'jenis_kelamin' => 'L',
            'status' => 'active',
        ]);

        $santri3 = Santri::create([
            'pondok_id' => $this->pondok->id,
            'nis' => '10003',
            'nama_lengkap' => 'Santri Tiga',
            'jenis_kelamin' => 'L',
            'status' => 'active',
        ]);

        // Assign Santri 1 (Should succeed)
        $response = $this->post(route('tenant.kamar.occupant.add', $kamar), [
            'santri_id' => $santri1->id,
        ]);
        $response->assertSessionHasNoErrors();
        $this->assertEquals($kamar->id, $santri1->refresh()->kamar_id);

        // Assign Santri 2 (Should succeed)
        $response = $this->post(route('tenant.kamar.occupant.add', $kamar), [
            'santri_id' => $santri2->id,
        ]);
        $response->assertSessionHasNoErrors();
        $this->assertEquals($kamar->id, $santri2->refresh()->kamar_id);

        // Assign Santri 3 (Should fail because capacity is 2)
        $response = $this->post(route('tenant.kamar.occupant.add', $kamar), [
            'santri_id' => $santri3->id,
        ]);
        $response->assertSessionHas('error');
        $this->assertNull($santri3->refresh()->kamar_id);

        // Remove Santri 1
        $response = $this->delete(route('tenant.kamar.occupant.remove', [$kamar, $santri1]));
        $response->assertSessionHasNoErrors();
        $this->assertNull($santri1->refresh()->kamar_id);
    }

    public function test_lemari_crud_and_auto_generates_slots(): void
    {
        $komplek = Komplek::create([
            'pondok_id' => $this->pondok->id,
            'nama' => 'Komplek A',
        ]);

        $kamar = Kamar::create([
            'pondok_id' => $this->pondok->id,
            'komplek_id' => $komplek->id,
            'nama' => 'Kamar 101',
            'kapasitas' => 4,
        ]);

        // Store Lemari (Should auto generate slots)
        $response = $this->post(route('tenant.lemari.store'), [
            'kamar_id' => $kamar->id,
            'nama' => 'Lemari A',
            'tipe' => 'lemari',
            'jumlah_slot' => 4,
        ]);
        $response->assertSessionHasNoErrors();

        $lemari = Lemari::where('nama', 'Lemari A')->first();
        $this->assertNotNull($lemari);
        $this->assertEquals(4, $lemari->slots()->count());
        $this->assertDatabaseHas('lemari_slots', [
            'lemari_id' => $lemari->id,
            'nomor_slot' => 1,
            'status' => 'kosong',
        ]);
        $this->assertDatabaseHas('lemari_slots', [
            'lemari_id' => $lemari->id,
            'nomor_slot' => 4,
            'status' => 'kosong',
        ]);
    }

    public function test_lemari_update_capacity(): void
    {
        $komplek = Komplek::create([
            'pondok_id' => $this->pondok->id,
            'nama' => 'Komplek A',
        ]);

        $kamar = Kamar::create([
            'pondok_id' => $this->pondok->id,
            'komplek_id' => $komplek->id,
            'nama' => 'Kamar 101',
            'kapasitas' => 4,
        ]);

        $lemari = Lemari::create([
            'pondok_id' => $this->pondok->id,
            'kamar_id' => $kamar->id,
            'nama' => 'Lemari A',
            'tipe' => 'lemari',
            'jumlah_slot' => 3,
        ]);

        // Auto generate initial slots
        for ($i = 1; $i <= 3; $i++) {
            $lemari->slots()->create(['nomor_slot' => $i, 'status' => 'kosong']);
        }

        // Increase slots capacity (from 3 to 5)
        $response = $this->put(route('tenant.lemari.update', $lemari), [
            'nama' => 'Lemari A',
            'tipe' => 'lemari',
            'jumlah_slot' => 5,
        ]);
        $response->assertSessionHasNoErrors();
        $this->assertEquals(5, $lemari->refresh()->slots()->count());
        $this->assertDatabaseHas('lemari_slots', [
            'lemari_id' => $lemari->id,
            'nomor_slot' => 5,
            'status' => 'kosong',
        ]);

        // Downsize capacity (from 5 to 4) when extra slots are EMPTY
        $response = $this->put(route('tenant.lemari.update', $lemari), [
            'nama' => 'Lemari A',
            'tipe' => 'lemari',
            'jumlah_slot' => 4,
        ]);
        $response->assertSessionHasNoErrors();
        $this->assertEquals(4, $lemari->refresh()->slots()->count());

        // Occupy slot #4
        $santri = Santri::create([
            'pondok_id' => $this->pondok->id,
            'nis' => '10001',
            'nama_lengkap' => 'Santri Satu',
            'jenis_kelamin' => 'L',
            'status' => 'active',
        ]);
        $lemari->slots()->where('nomor_slot', 4)->update([
            'santri_id' => $santri->id,
            'status' => 'dipakai',
        ]);

        // Downsize capacity (from 4 to 3) when slot #4 is OCCUPIED (Should fail)
        $response = $this->put(route('tenant.lemari.update', $lemari), [
            'nama' => 'Lemari A',
            'tipe' => 'lemari',
            'jumlah_slot' => 3,
        ]);
        $response->assertSessionHas('error');
        $this->assertEquals(4, $lemari->refresh()->slots()->count()); // Should not change
    }

    public function test_lemari_slot_update(): void
    {
        $komplek = Komplek::create([
            'pondok_id' => $this->pondok->id,
            'nama' => 'Komplek A',
        ]);

        $kamar = Kamar::create([
            'pondok_id' => $this->pondok->id,
            'komplek_id' => $komplek->id,
            'nama' => 'Kamar 101',
            'kapasitas' => 4,
        ]);

        $lemari = Lemari::create([
            'pondok_id' => $this->pondok->id,
            'kamar_id' => $kamar->id,
            'nama' => 'Lemari A',
            'tipe' => 'lemari',
            'jumlah_slot' => 2,
        ]);

        $slot = $lemari->slots()->create(['nomor_slot' => 1, 'status' => 'kosong']);

        $santri = Santri::create([
            'pondok_id' => $this->pondok->id,
            'nis' => '10001',
            'nama_lengkap' => 'Santri Satu',
            'jenis_kelamin' => 'L',
            'status' => 'active',
        ]);

        // Update slot to occupied by Santri
        $response = $this->put(route('tenant.lemari-slot.update', $slot), [
            'status' => 'dipakai',
            'santri_id' => $santri->id,
            'keterangan' => 'Dipakai laci pakaian',
        ]);
        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('lemari_slots', [
            'id' => $slot->id,
            'status' => 'dipakai',
            'santri_id' => $santri->id,
            'keterangan' => 'Dipakai laci pakaian',
        ]);

        // Update slot to broken (should clear santri_id since it's not 'dipakai')
        $response = $this->put(route('tenant.lemari-slot.update', $slot), [
            'status' => 'rusak',
            'santri_id' => null,
            'keterangan' => 'Laci rusak',
        ]);
        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('lemari_slots', [
            'id' => $slot->id,
            'status' => 'rusak',
            'santri_id' => null,
            'keterangan' => 'Laci rusak',
        ]);
    }
}

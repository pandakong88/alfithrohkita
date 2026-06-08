<?php

namespace Tests\Feature;

use App\Models\Pondok;
use App\Models\User;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    protected Pondok $pondokA;
    protected Pondok $pondokB;
    protected User $adminA;
    protected User $adminB;
    protected User $staffA;
    protected Role $roleAdminPondokA;
    protected Role $roleAdminPondokB;
    protected Role $roleStaffA;

    protected function setUp(): void
    {
        parent::setUp();

        // Run the PermissionSeeder to create permissions and global roles
        $this->seed(\Database\Seeders\PermissionSeeder::class);

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

        // Create Tenant-specific Admin Roles (matching CreateTenantAction behavior)
        $this->roleAdminPondokA = Role::create([
            'name' => 'admin_pondok_' . $this->pondokA->id,
            'guard_name' => 'web',
            'pondok_id' => $this->pondokA->id,
        ]);
        $this->roleAdminPondokA->syncPermissions(\Spatie\Permission\Models\Permission::all());

        $this->roleAdminPondokB = Role::create([
            'name' => 'admin_pondok_' . $this->pondokB->id,
            'guard_name' => 'web',
            'pondok_id' => $this->pondokB->id,
        ]);
        $this->roleAdminPondokB->syncPermissions(\Spatie\Permission\Models\Permission::all());

        // Create Tenant-specific Staff Role
        $this->roleStaffA = Role::create([
            'name' => 'staff_' . $this->pondokA->id,
            'guard_name' => 'web',
            'pondok_id' => $this->pondokA->id,
        ]);
        $this->roleStaffA->givePermissionTo('manage_users');

        // Create Users
        $this->adminA = User::factory()->create([
            'pondok_id' => $this->pondokA->id,
        ]);
        $this->adminA->assignRole($this->roleAdminPondokA);

        $this->adminB = User::factory()->create([
            'pondok_id' => $this->pondokB->id,
        ]);
        $this->adminB->assignRole($this->roleAdminPondokB);

        $this->staffA = User::factory()->create([
            'pondok_id' => $this->pondokA->id,
        ]);
        $this->staffA->assignRole($this->roleStaffA);
    }

    /**
     * Test Tenant isolation (IDOR protection)
     */
    public function test_tenant_user_cannot_access_other_tenant_user(): void
    {
        $this->actingAs($this->adminA);

        // Edit user of Pondok B
        $response = $this->get(route('tenant.user.edit', $this->adminB->id));
        $response->assertStatus(403);

        // Update user of Pondok B
        $response = $this->put(route('tenant.user.update', $this->adminB->id), [
            'name' => 'New Name',
            'email' => 'adminB_new@alfitroh.com',
            'role_id' => $this->roleAdminPondokB->id,
        ]);
        $response->assertStatus(403);

        // Toggle user of Pondok B
        $response = $this->patch(route('tenant.user.toggle', $this->adminB->id));
        $response->assertStatus(403);

        // Delete user of Pondok B
        $response = $this->delete(route('tenant.user.destroy', $this->adminB->id));
        $response->assertStatus(403);
    }

    /**
     * Test self-actions restriction
     */
    public function test_user_cannot_deactivate_or_delete_self(): void
    {
        $this->actingAs($this->adminA);

        // Self deactivate
        $response = $this->patch(route('tenant.user.toggle', $this->adminA->id));
        $response->assertStatus(403);

        // Self delete
        $response = $this->delete(route('tenant.user.destroy', $this->adminA->id));
        $response->assertStatus(403);
    }

    /**
     * Test privilege escalation: staff cannot modify or delete admin_pondok
     */
    public function test_staff_cannot_modify_or_delete_admin_pondok(): void
    {
        $this->actingAs($this->staffA);

        // Edit admin_pondok
        $response = $this->get(route('tenant.user.edit', $this->adminA->id));
        $response->assertStatus(403);

        // Update admin_pondok
        $response = $this->put(route('tenant.user.update', $this->adminA->id), [
            'name' => 'Updated Admin',
            'email' => 'adminA_updated@alfitroh.com',
            'role_id' => $this->roleStaffA->id,
        ]);
        $response->assertStatus(403);

        // Toggle admin_pondok status
        $response = $this->patch(route('tenant.user.toggle', $this->adminA->id));
        $response->assertStatus(403);

        // Delete admin_pondok
        $response = $this->delete(route('tenant.user.destroy', $this->adminA->id));
        $response->assertStatus(403);
    }

    /**
     * Test privilege escalation: staff cannot assign admin_pondok role
     */
    public function test_staff_cannot_assign_admin_pondok_role_to_new_user(): void
    {
        $this->actingAs($this->staffA);

        // Attempt to create user with admin_pondok role
        $response = $this->post(route('tenant.user.store'), [
            'name' => 'New Admin',
            'email' => 'new_admin@alfitroh.com',
            'password' => 'secret123',
            'role_id' => $this->roleAdminPondokA->id,
        ]);

        $response->assertStatus(403);
    }

    /**
     * Test privilege escalation: staff cannot upgrade user to admin_pondok
     */
    public function test_staff_cannot_upgrade_user_to_admin_pondok(): void
    {
        $this->actingAs($this->staffA);

        // Create standard user
        $standardUser = User::factory()->create([
            'pondok_id' => $this->pondokA->id,
        ]);
        $standardUser->assignRole($this->roleStaffA);

        // Attempt to update standard user's role to admin_pondok
        $response = $this->put(route('tenant.user.update', $standardUser->id), [
            'name' => $standardUser->name,
            'email' => $standardUser->email,
            'role_id' => $this->roleAdminPondokA->id,
        ]);

        $response->assertStatus(403);
    }

    /**
     * Test admin_pondok can perform actions on other users
     */
    public function test_admin_pondok_can_manage_other_users(): void
    {
        $this->actingAs($this->adminA);

        // Create standard user
        $response = $this->post(route('tenant.user.store'), [
            'name' => 'New Staff',
            'email' => 'new_staff@alfitroh.com',
            'password' => 'secret123',
            'role_id' => $this->roleStaffA->id,
        ]);
        $response->assertRedirect(route('tenant.user.index'));

        $newStaff = User::where('email', 'new_staff@alfitroh.com')->first();
        $this->assertNotNull($newStaff);
        $this->assertTrue($newStaff->hasRole('staff_' . $this->pondokA->id));

        // Toggle
        $response = $this->patch(route('tenant.user.toggle', $newStaff->id));
        $response->assertRedirect();
        $newStaff->refresh();
        $this->assertFalse($newStaff->is_active);

        // Delete
        $response = $this->delete(route('tenant.user.destroy', $newStaff->id));
        $response->assertRedirect();
        $this->assertSoftDeleted($newStaff);
    }
}

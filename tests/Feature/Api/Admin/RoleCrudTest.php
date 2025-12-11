<?php

namespace Tests\Feature\Api\Admin;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleCrudTest extends TestCase
{
    use RefreshDatabase;

    protected User $superadmin;
    protected User $admin;
    protected User $regularUser;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test roles
        Role::factory()->create(['name' => 'superadmin']);
        Role::factory()->create(['name' => 'admin']);
        Role::factory()->create(['name' => 'user']);

        // Create test users
        $this->superadmin = User::factory()->create();
        $this->superadmin->roles()->attach(Role::where('name', 'superadmin')->first());

        $this->admin = User::factory()->create();
        $this->admin->roles()->attach(Role::where('name', 'admin')->first());

        $this->regularUser = User::factory()->create();
        $this->regularUser->roles()->attach(Role::where('name', 'user')->first());
    }

    /**
     * Test guest cannot access roles
     */
    public function test_guest_cannot_access_roles()
    {
        $this->getJson('/api/admin/roles')
            ->assertUnauthorized();
    }

    /**
     * Test superadmin can list roles
     */
    public function test_superadmin_can_list_roles()
    {
        $response = $this->actingAs($this->superadmin)
            ->getJson('/api/admin/roles');

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id', 'name', 'description', 'is_active', 'created_at', 'updated_at'
                    ]
                ],
                'meta' => ['current_page', 'per_page', 'total', 'last_page']
            ]);
    }

    /**
     * Test create role requires authentication
     */
    public function test_create_role_requires_authentication()
    {
        $this->postJson('/api/admin/roles', [
            'name' => 'editor',
        ])
            ->assertUnauthorized();
    }

    /**
     * Test superadmin can create role with permissions
     */
    public function test_superadmin_can_create_role()
    {
        $response = $this->actingAs($this->superadmin)
            ->postJson('/api/admin/roles', [
                'name' => 'editor',
                'description' => 'Content editor role',
                'is_active' => true,
                'permissions' => [
                    'view-content',
                    'create-content',
                    'edit-content',
                ],
            ]);

        $response->assertCreated()
            ->assertJsonPath('data.name', 'editor')
            ->assertJsonPath('data.is_active', true)
            ->assertJsonPath('data.permissions.0', 'view-content')
            ->assertHeader('Location');

        $this->assertDatabaseHas('roles', [
            'name' => 'editor',
        ]);

        $this->assertDatabaseHas('role_permissions', [
            'permission' => 'view-content',
        ]);
    }

    /**
     * Test role name must be unique
     */
    public function test_role_name_must_be_unique()
    {
        Role::create([
            'name' => 'editor',
        ]);

        $response = $this->actingAs($this->superadmin)
            ->postJson('/api/admin/roles', [
                'name' => 'editor',
                'display_name' => 'Editor 2',
            ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors('name');
    }

    /**
     * Test superadmin can view single role
     */
    public function test_superadmin_can_view_role()
    {
        $role = Role::factory()->create();

        $response = $this->actingAs($this->superadmin)
            ->getJson("/api/admin/roles/{$role->id}");

        $response->assertOk()
            ->assertJsonPath('data.id', $role->id)
            ->assertJsonPath('data.name', $role->name);
    }

    /**
     * Test superadmin can update role
     */
    public function test_superadmin_can_update_role()
    {
        $role = Role::factory()->create(['name' => 'editor']);
        $role->syncPermissions(['view-content', 'create-content']);

        $response = $this->actingAs($this->superadmin)
            ->putJson("/api/admin/roles/{$role->id}", [
                'name' => 'editor',
                'description' => 'Updated description',
                'is_active' => false,
                'permissions' => ['view-content', 'edit-content'],
            ]);

        $response->assertOk()
            ->assertJsonPath('data.is_active', false);

        $this->assertDatabaseHas('roles', [
            'id' => $role->id,
            'is_active' => false,
        ]);

        // Verify permissions were synced
        $updated = $role->fresh();
        $permissions = $updated->getPermissionStrings();
        $this->assertContains('view-content', $permissions);
        $this->assertContains('edit-content', $permissions);
        $this->assertNotContains('create-content', $permissions);
    }

    /**
     * Test superadmin can soft delete role
     */
    public function test_superadmin_can_delete_role()
    {
        $role = Role::factory()->create();

        $response = $this->actingAs($this->superadmin)
            ->deleteJson("/api/admin/roles/{$role->id}");

        $response->assertNoContent();

        $this->assertDatabaseMissing('roles', ['id' => $role->id]);
    }

    /**
     * Test cannot delete superadmin role
     */
    public function test_cannot_delete_superadmin_role()
    {
        $superadminRole = Role::where('name', 'superadmin')->first();

        $response = $this->actingAs($this->superadmin)
            ->deleteJson("/api/admin/roles/{$superadminRole->id}");

        $response->assertForbidden();
    }

    /**
     * Test sync permissions endpoint
     */
    public function test_sync_permissions()
    {
        $role = Role::factory()->create();
        $role->syncPermissions(['old-perm']);

        $response = $this->actingAs($this->superadmin)
            ->postJson("/api/admin/roles/{$role->id}/sync-permissions", [
                'permissions' => ['new-perm-1', 'new-perm-2'],
            ]);

        $response->assertOk();

        $updated = $role->fresh();
        $permissions = $updated->getPermissionStrings();

        $this->assertNotContains('old-perm', $permissions);
        $this->assertContains('new-perm-1', $permissions);
        $this->assertContains('new-perm-2', $permissions);
    }

    /**
     * Test filter roles by search query
     */
    public function test_filter_roles_by_search()
    {
        Role::factory()->create(['name' => 'editor']);
        Role::factory()->create(['name' => 'viewer']);

        $response = $this->actingAs($this->superadmin)
            ->getJson('/api/admin/roles?q=editor');

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'editor');
    }

    /**
     * Test filter roles by active status
     */
    public function test_filter_roles_by_active_status()
    {
        Role::factory()->create(['is_active' => true]);
        Role::factory()->create(['is_active' => false]);

        $response = $this->actingAs($this->superadmin)
            ->getJson('/api/admin/roles?is_active=true');

        $response->assertOk();
        $this->assertTrue(
            collect($response->json('data'))->every(fn ($role) => $role['is_active'] === true)
        );
    }

    /**
     * Test load permissions with include parameter
     */
    public function test_include_permissions_in_listing()
    {
        $role = Role::factory()->create();
        $role->syncPermissions(['view-content', 'create-content']);

        $response = $this->actingAs($this->superadmin)
            ->getJson('/api/admin/roles?include=permissions');

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => ['permissions']
                ]
            ]);
    }
}

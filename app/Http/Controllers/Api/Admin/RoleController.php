<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreRoleRequest;
use App\Http\Requests\Admin\UpdateRoleRequest;
use App\Http\Resources\RoleResource;
use App\Models\Role;
use App\Services\AuditService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\Facades\DB;

class RoleController extends Controller
{
    /**
     * Get all roles with optional filtering
     */
    public function index(): ResourceCollection
    {
        $this->authorize('viewAny', Role::class);

        $query = Role::query();

        // Search by name or display_name
        if (request('q')) {
            $q = request('q');
            $query->where('name', 'like', "%{$q}%")
                ->orWhere('display_name', 'like', "%{$q}%");
        }

        // Filter by status
        if (request('is_active') !== null) {
            $query->where('is_active', request('is_active') == 'true' || request('is_active') == 1);
        }

        // Include permissions if requested
        if (request('include') === 'permissions') {
            $query->with('permissions');
        }

        return RoleResource::collection(
            $query->orderBy('name')
                ->paginate(request('per_page', 15))
        );
    }

    /**
     * Store a newly created role
     */
    public function store(StoreRoleRequest $request): JsonResponse
    {
        $this->authorize('create', Role::class);

        return DB::transaction(function () use ($request) {
            $validated = $request->validated();
            $permissions = $validated['permissions'] ?? [];
            unset($validated['permissions']);

            $role = Role::create($validated);

            if (!empty($permissions)) {
                $role->syncPermissions($permissions);
            }

            AuditService::logCreate('Role', $role->id, $role->toArray());

            $role->load('permissions');

            // Preserve request permission ordering in the returned resource
            if (!empty($permissions)) {
                $ordered = $role->permissions->sortBy(function ($perm) use ($permissions) {
                    return array_search($perm->permission, $permissions);
                })->values();

                $role->setRelation('permissions', $ordered);
            }

            return (new RoleResource($role))
                ->response()
                ->setStatusCode(201)
                ->header('Location', url("/api/admin/roles/{$role->id}"));
        });
    }

    /**
     * Get a specific role
     */
    public function show(Role $role): RoleResource
    {
        $this->authorize('view', $role);

        $role->load('permissions');

        return new RoleResource($role);
    }

    /**
     * Update a role
     */
    public function update(UpdateRoleRequest $request, Role $role): RoleResource
    {
        $this->authorize('update', $role);

        return DB::transaction(function () use ($request, $role) {
            $validated = $request->validated();
            $permissions = $validated['permissions'] ?? [];
            unset($validated['permissions']);

            $before = $role->toArray();
            $role->update($validated);
            $after = $role->toArray();

            if (array_diff_assoc($before, $after)) {
                AuditService::logUpdate('Role', $role->id, $before, $after);
            }

            if (!empty($permissions)) {
                $role->syncPermissions($permissions);
                AuditService::log('sync', 'Role', $role->id, $before, $after);
            }

            $role->load('permissions');

            return new RoleResource($role);
        });
    }

    /**
     * Soft delete a role
     */
    public function destroy(Role $role): JsonResponse
    {
        $this->authorize('delete', $role);

        return DB::transaction(function () use ($role) {
            $before = $role->toArray();
            $id = $role->id;

            $role->delete();

            AuditService::logDelete('Role', $id, $before);

            return response()->json(null, 204);
        });
    }

    /**
     * Sync permissions for a role
     */
    public function syncPermissions(Role $role): RoleResource
    {
        $this->authorize('managePermissions', $role);

        $validated = request()->validate([
            'permissions' => ['required', 'array'],
            'permissions.*' => ['string', 'max:64'],
        ]);

        $permissions = $validated['permissions'];

        return DB::transaction(function () use ($role, $permissions) {
            $before = [
                'permissions' => $role->getPermissionStrings(),
            ];

            $role->syncPermissions($permissions);

            $after = [
                'permissions' => $role->getPermissionStrings(),
            ];

            AuditService::logUpdate('Role', $role->id, $before, $after);

            $role->load('permissions');

            return new RoleResource($role);
        });
    }
}

<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\RoleResource;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleController extends Controller
{
    /**
     * Display a listing of the roles
     */
    public function index()
    {
        $roles = Role::with('permissions')->get();

        return $this->success(RoleResource::collection($roles));
    }

    /**
     * Store a newly created role
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:roles,name',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        $role = Role::create(['name' => $request->name]);

        if ($request->has('permissions')) {
            $permissions = Permission::whereIn('id', $request->permissions)->get();
            $role->syncPermissions($permissions);
        }

        return $this->success(
            new RoleResource($role->load('permissions')),
            'Role created successfully',
            201
        );
    }

    /**
     * Display the specified role
     */
    public function show($id)
    {
        $role = Role::with('permissions')->findOrFail($id);

        return $this->success(new RoleResource($role));
    }

    /**
     * Update the specified role
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:roles,name,' . $id,
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        $role = Role::findOrFail($id);

        // Prevent updating super-admin role
        if ($role->name === 'super-admin' && $request->name !== 'super-admin') {
            return $this->error('Cannot rename super-admin role', 403);
        }

        $role->update(['name' => $request->name]);

        if ($request->has('permissions')) {
            $permissions = Permission::whereIn('id', $request->permissions)->get();
            $role->syncPermissions($permissions);
        }

        return $this->success(
            new RoleResource($role->load('permissions')),
            'Role updated successfully'
        );
    }

    /**
     * Remove the specified role
     */
    public function destroy($id)
    {
        $role = Role::findOrFail($id);

        // Prevent deletion of system roles
        if (in_array($role->name, ['super-admin', 'admin', 'user'])) {
            return $this->error('Cannot delete system role', 403);
        }

        $role->delete();

        return $this->success(null, 'Role deleted successfully');
    }

    /**
     * Assign permissions to a role
     */
    public function assignPermissions(Request $request, $id)
    {
        $request->validate([
            'permissions' => 'required|array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        $role = Role::findOrFail($id);

        // Ensure super-admin always has all permissions
        if ($role->name === 'super-admin') {
            // Get all permissions and merge with requested permissions
            $allPermissionIds = Permission::pluck('id')->toArray();
            $permissions = Permission::whereIn('id', array_unique(array_merge($allPermissionIds, $request->permissions)))->get();
        } else {
            $permissions = Permission::whereIn('id', $request->permissions)->get();
        }

        $role->syncPermissions($permissions);

        return $this->success(
            new RoleResource($role->load('permissions')),
            'Permissions assigned successfully'
        );
    }
}
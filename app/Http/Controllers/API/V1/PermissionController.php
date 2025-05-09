<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\PermissionResource;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;

class PermissionController extends Controller
{
    /**
     * Display a listing of the permissions
     */
    public function index()
    {
        $permissions = Permission::all();

        return $this->success(PermissionResource::collection($permissions));
    }

    /**
     * Store a newly created permission
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:permissions,name',
        ]);

        $permission = Permission::create(['name' => $request->name]);

        return $this->success(
            new PermissionResource($permission),
            'Permission created successfully',
            201
        );
    }

    /**
     * Display the specified permission
     */
    public function show($id)
    {
        $permission = Permission::findOrFail($id);

        return $this->success(new PermissionResource($permission));
    }

    /**
     * Update the specified permission
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:permissions,name,' . $id,
        ]);

        $permission = Permission::findOrFail($id);

        // Prevent updating core permissions
        $corePermissions = [
            'view-users', 'create-users', 'edit-users', 'delete-users', 'assign-user-roles',
            'view-roles', 'create-roles', 'edit-roles', 'delete-roles', 'manage-roles',
            'view-permissions', 'create-permissions', 'edit-permissions', 'delete-permissions',
        ];

        if (in_array($permission->name, $corePermissions)) {
            return $this->error('Cannot modify core permission', 403);
        }

        $permission->update(['name' => $request->name]);

        return $this->success(
            new PermissionResource($permission),
            'Permission updated successfully'
        );
    }

    /**
     * Remove the specified permission
     */
    public function destroy($id)
    {
        $permission = Permission::findOrFail($id);

        // Prevent deletion of core permissions
        $corePermissions = [
            'view-users', 'create-users', 'edit-users', 'delete-users', 'assign-user-roles',
            'view-roles', 'create-roles', 'edit-roles', 'delete-roles', 'manage-roles',
            'view-permissions', 'create-permissions', 'edit-permissions', 'delete-permissions',
        ];

        if (in_array($permission->name, $corePermissions)) {
            return $this->error('Cannot delete core permission', 403);
        }

        $permission->delete();

        return $this->success(null, 'Permission deleted successfully');
    }
}
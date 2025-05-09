<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\UserStoreRequest;
use App\Http\Requests\API\UserUpdateRequest;
use App\Http\Resources\UserResource;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Services\UserService;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * @var UserRepositoryInterface
     */
    protected $userRepository;

    /**
     * @var UserService
     */
    protected $userService;

    /**
     * UserController constructor.
     *
     * @param UserRepositoryInterface $userRepository
     * @param UserService $userService
     */
    public function __construct(
        UserRepositoryInterface $userRepository,
        UserService $userService
    ) {
        $this->userRepository = $userRepository;
        $this->userService = $userService;
    }

    /**
     * Display a listing of the users
     */
    public function index(Request $request)
    {
        $perPage = $request->query('per_page', 15);
        $users = $this->userRepository->paginate($perPage);

        return $this->success(
            UserResource::collection($users)->response()->getData(true)
        );
    }

    /**
     * Store a newly created user
     */
    public function store(UserStoreRequest $request)
    {
        $user = $this->userService->createUser($request->validated());

        return $this->success(
            new UserResource($user),
            'User created successfully',
            201
        );
    }

    /**
     * Display the specified user
     */
    public function show($id)
    {
        $user = $this->userRepository->find($id);

        return $this->success(new UserResource($user->load('roles', 'permissions')));
    }

    /**
     * Update the specified user
     */
    public function update(UserUpdateRequest $request, $id)
    {
        $user = $this->userService->updateUser($id, $request->validated());

        return $this->success(
            new UserResource($user),
            'User updated successfully'
        );
    }

    /**
     * Remove the specified user
     */
    public function destroy($id)
    {
        $user = $this->userRepository->find($id);

        // Prevent deletion of super-admin
        if ($user->hasRole('super-admin') && $user->id !== auth()->id()) {
            return $this->error('Cannot delete super-admin user', 403);
        }

        $this->userRepository->delete($id);

        return $this->success(null, 'User deleted successfully');
    }

    /**
     * Assign roles to a user
     */
    public function assignRoles(Request $request, $id)
    {
        $request->validate([
            'roles' => 'required|array',
            'roles.*' => 'exists:roles,id',
        ]);

        $this->userRepository->assignRoles($id, $request->roles);

        $user = $this->userRepository->find($id);

        return $this->success(
            new UserResource($user->load('roles', 'permissions')),
            'Roles assigned successfully'
        );
    }

    /**
     * Assign direct permissions to a user
     */
    public function assignPermissions(Request $request, $id)
    {
        $request->validate([
            'permissions' => 'required|array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        $this->userRepository->assignPermissions($id, $request->permissions);

        $user = $this->userRepository->find($id);

        return $this->success(
            new UserResource($user->load('roles', 'permissions')),
            'Permissions assigned successfully'
        );
    }

    /**
     * Change user status (active/inactive)
     */
    public function changeStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|boolean',
        ]);

        $user = $this->userRepository->find($id);

        // Prevent deactivating super-admin
        if ($user->hasRole('super-admin') && !$request->status) {
            return $this->error('Cannot deactivate super-admin user', 403);
        }

        $this->userRepository->update($id, ['is_active' => $request->status]);

        return $this->success(
            null,
            'User status updated successfully'
        );
    }
}
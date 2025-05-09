<?php

namespace App\Repositories\Eloquent;

use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class UserRepository extends BaseRepository implements UserRepositoryInterface
{
    /**
     * UserRepository constructor.
     *
     * @param User $model
     */
    public function __construct(User $model)
    {
        parent::__construct($model);
    }

    /**
     * @inheritDoc
     */
    public function assignRoles(int $userId, array $roleIds)
    {
        $user = $this->find($userId);
        $roles = Role::whereIn('id', $roleIds)->get();
        return $user->syncRoles($roles);
    }

    /**
     * @inheritDoc
     */
    public function assignPermissions(int $userId, array $permissionIds)
    {
        $user = $this->find($userId);
        return $user->syncPermissions($permissionIds);
    }
}
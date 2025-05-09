<?php

namespace App\Repositories\Contracts;

interface UserRepositoryInterface extends RepositoryInterface
{
    /**
     * Assign roles to user
     *
     * @param int $userId
     * @param array $roleIds
     * @return mixed
     */
    public function assignRoles(int $userId, array $roleIds);

    /**
     * Assign permissions to user
     *
     * @param int $userId
     * @param array $permissionIds
     * @return mixed
     */
    public function assignPermissions(int $userId, array $permissionIds);
}
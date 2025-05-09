<?php

namespace App\Services;

use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Support\Facades\Hash;

class UserService
{
    /**
     * @var UserRepositoryInterface
     */
    protected $userRepository;

    /**
     * UserService constructor.
     *
     * @param UserRepositoryInterface $userRepository
     */
    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * Create a new user
     *
     * @param array $data
     * @return mixed
     */
    public function createUser(array $data)
    {
        $userData = [
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'is_active' => $data['is_active'] ?? true,
        ];

        $user = $this->userRepository->create($userData);

        if (isset($data['roles'])) {
            $this->userRepository->assignRoles($user->id, $data['roles']);
        } else {
            // Assign default role
            $this->userRepository->assignRoles($user->id, [4]); // assuming 4 is the ID of 'user' role
        }

        return $user->load('roles', 'permissions');
    }

    /**
     * Update a user
     *
     * @param int $id
     * @param array $data
     * @return mixed
     */
    public function updateUser(int $id, array $data)
    {
        $userData = [
            'name' => $data['name'],
            'email' => $data['email'],
        ];

        if (isset($data['is_active'])) {
            $userData['is_active'] = $data['is_active'];
        }

        if (isset($data['password']) && !empty($data['password'])) {
            $userData['password'] = Hash::make($data['password']);
        }

        $this->userRepository->update($id, $userData);

        if (isset($data['roles'])) {
            $this->userRepository->assignRoles($id, $data['roles']);
        }

        return $this->userRepository->find($id)->load('roles', 'permissions');
    }
}
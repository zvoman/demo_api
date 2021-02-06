<?php


namespace App\Interfaces;


use App\Entity\User;

/**
 * Interface UserRepositoryInterface
 * @package App\Interfaces
 */
interface UserRepositoryInterface
{
    /**
     * Fetching all users
     * @return User[]|null
     */
    public function fetchAllUsers();

    /**
     * @param User $user
     * @return mixed
     */
    public function saveUserToDatabase($user);

    /**
     * @param User $user
     * @return mixed
     */
    public function deleteUserFromDatabase($user);

}
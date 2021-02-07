<?php


namespace App\Manager;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\ORMException;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Throwable;

/**
 * Class UserManager
 * @package App\Manager
 */
class UserManager
{
    /**
     * @var UserRepository
     */
    private $userRepository;
    /**
     * @var HttpClientInterface
     */
    private $client;

    /**
     * UserManager constructor.
     * @param HttpClientInterface $client
     * @param UserRepository $userRepository
     */
    public function __construct(HttpClientInterface $client, UserRepository $userRepository)
    {
        $this->client = $client;
        $this->userRepository = $userRepository;
    }

    /**
     * @return User[]
     */
    public function fetchUserDataFromDatabase()
    {
        return $this->userRepository->fetchAllUsers();
    }

    /**
     * @return array
     * @throws Throwable
     */
    public function fetchUserDataFromAPI()
    {
        try {
            $response = $this->client->request(
                'GET',
                'http://jsonplaceholder.typicode.com/users'
            );
            $content = $response->toArray();
        } catch (Throwable $e) {
            throw $e;
        }

        return $content;
    }

    /**
     * @param $apiUsers
     * @param $dbUsers
     * @return int
     */
    public function synchronizeUserData($apiUsers, $dbUsers)
    {
        $newUsers = [];

        foreach ($apiUsers as $apiUser){
            $user = new User();
            $user->setId($apiUser['id']);
            $user->setName($apiUser['name']);
            $user->setEmail($apiUser['email']);
            $user->setUsername($apiUser['username']);

            $newUsers[] = $user;
        }

        $newUsersFromAPI = array_udiff($newUsers, $dbUsers, function ($a, $b) {
                /** @var User $a */
                /** @var User $b */
                return strcmp($a->getId(), $b->getId());
            }
        );

        $failedSavedUsers = 0;
        if ($newUsersFromAPI){
            foreach ($newUsersFromAPI as $newUser){
                try {
                    $this->userRepository->saveUserToDatabase($newUser);
                } catch (ORMException $e) {
                    $failedSavedUsers++;
                    continue;
                }
            }
        }

        $deletedUsersFromAPI = array_udiff($dbUsers, $newUsers, function ($a, $b) {
                /** @var User $a */
                /** @var User $b */
                return strcmp($a->getId(), $b->getId());
            }
        );

        $failedDeletedUsers = 0;
        if ($deletedUsersFromAPI){
            foreach ($deletedUsersFromAPI as $user) {
                try {
                    $this->userRepository->deleteUserFromDatabase($user);
                } catch (ORMException $e) {
                    $failedDeletedUsers++;
                    continue;
                }
            }
        }

        $errors = $failedSavedUsers + $failedDeletedUsers;

        return $errors;
    }
}
<?php


namespace App\Manager;


use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class UserManager
{
    private $userRepository;
    private $client;

    public function __construct(HttpClientInterface $client, UserRepository $userRepository)
    {
        $this->client = $client;
        $this->userRepository = $userRepository;
    }

    public function fetchUserDataFromDatabase()
    {
        return $this->userRepository->fetchAllUsers();
    }

    public function fetchUserDataFromAPI()
    {
        $response = $this->client->request(
            'GET',
            'http://jsonplaceholder.typicode.com/users'
        );

        $statusCode = $response->getStatusCode();

        $content = $response->getContent();

        $content = $response->toArray();

        return $content;
    }

    public function synchronizeUserData()
    {
        $dbUsers = $this->fetchUserDataFromDatabase();
        $apiUsers = $this->fetchUserDataFromAPI();

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
                return strcmp($a->getId(), $b->getId());
            }
        );

        if ($newUsersFromAPI){
            foreach ($newUsersFromAPI as $newUser){
                try {
                    $this->userRepository->saveUserToDatabase($newUser);
                } catch (OptimisticLockException $e) {
                    //TODO Error
                } catch (ORMException $e) {
                    //TODO Error
                }
            }
        }

        $deletedUsersFromAPI = array_udiff($dbUsers, $newUsers, function ($a, $b) {
            return strcmp($a->getId(), $b->getId());
        }
        );

        if ($deletedUsersFromAPI){
            foreach ($deletedUsersFromAPI as $user) {
                try {
                    $this->userRepository->deleteUserFromDatabase($user);
                } catch (OptimisticLockException $e) {
                    //TODO Error
                } catch (ORMException $e) {
                    //TODO Error
                }
            }
        }

        return true;
    }
}
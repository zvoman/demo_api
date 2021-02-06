<?php

namespace App\Repository;

use App\Entity\User;
use App\Interfaces\UserRepositoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;

class UserRepository extends ServiceEntityRepository implements UserRepositoryInterface
{
    /**
     * UserRepository constructor.
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * Fetching all users
     * @return User[]
     */
    public function fetchAllUsers()
    {
        return $this->createQueryBuilder("user")
            ->orderBy("user.id")
            ->getQuery()
            ->getResult();
    }

    /**
     * @param User $user
     * @return mixed
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function saveUserToDatabase($user)
    {
        $entityManager = $this->getEntityManager();

        $entityManager->persist($user);
        $entityManager->flush();
    }

    /**
     * @param User $user
     * @return mixed
     */
    public function deleteUserFromDatabase($user)
    {
        // TODO: Implement deleteUserFromDatabase() method.
    }
}

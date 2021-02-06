<?php

namespace App\Repository;

use App\Entity\Post;
use App\Interfaces\PostRepositoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;

class PostRepository extends ServiceEntityRepository implements PostRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Post::class);
    }

    /**
     * Fetching all posts
     * @return Post[]|null
     */
    public function fetchAllPosts()
    {
        return $this->createQueryBuilder("post")
            ->orderBy("post.id")
            ->getQuery()
            ->getResult();
    }


    /**
     * @param Post $post
     * @return mixed
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function savePostToDatabase($post)
    {
        $entityManager = $this->getEntityManager();

        $entityManager->persist($post);
        $entityManager->flush();
    }

    /**
     * @param Post $post
     * @return mixed
     */
    public function deletePostFromDatabase($post)
    {
        // TODO: Implement deletePostFromDatabase() method.
    }
}

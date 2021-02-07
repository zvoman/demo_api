<?php

namespace App\Repository;

use App\Entity\Post;
use App\Interfaces\PostRepositoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Class PostRepository
 * @package App\Repository
 */
class PostRepository extends ServiceEntityRepository implements PostRepositoryInterface
{
    /**
     * PostRepository constructor.
     * @param ManagerRegistry $registry
     */
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
     * @return void
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
     * @return void
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function deletePostFromDatabase($post)
    {
        $postDB = $this->find($post->getId());

        $this->_em->remove($postDB);
        $this->_em->flush();
    }
}

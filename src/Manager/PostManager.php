<?php

namespace App\Manager;

use App\Entity\Post;
use App\Entity\User;
use App\Repository\PostRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\ORMException;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Throwable;

/**
 * Class PostManager
 * @package App\Manager
 */
class PostManager
{
    /**
     * @var PostRepository
     */
    private $postRepository;
    /**
     * @var UserRepository
     */
    private $userRepository;
    /**
     * @var HttpClientInterface
     */
    private $client;

    /**
     * PostManager constructor.
     * @param HttpClientInterface $client
     * @param PostRepository $postRepository
     * @param UserRepository $userRepository
     */
    public function __construct(HttpClientInterface $client, PostRepository $postRepository, UserRepository $userRepository)
    {
        $this->postRepository = $postRepository;
        $this->userRepository =  $userRepository;
        $this->client = $client;
    }

    /**
     * @return Post[]|null
     */
    public function fetchPostDataFromDatabase()
    {
        return $this->postRepository->fetchAllPosts();
    }

    /**
     * @return array
     * @throws Throwable
     */
    public function fetchPostDataFromAPI()
    {
        try {
            $response = $this->client->request(
                'GET',
                'http://jsonplaceholder.typicode.com/posts'
            );
            $content = $response->toArray();
        } catch (Throwable $e) {
            throw $e;
        }

        return $content;
    }

    /**
     * @param $apiPosts
     * @param $dbPosts
     * @return bool
     */
    public function synchronizePostData($apiPosts, $dbPosts)
    {
        $newPosts = [];
        foreach ($apiPosts as $apiPost){
            /** @var User $user */
            $user = $this->userRepository->findOneBy(array('id' => $apiPost['userId']));

            $post = new Post();
            $post->setId($apiPost['id']);
            $post->setBody($apiPost['body']);
            $post->setTitle($apiPost['title']);
            $post->setUser($user);

            $newPosts[] = $post;
        }

        $newPostsFromAPI = array_udiff($newPosts, $dbPosts, function ($a, $b) {
                /** @var User $a */
                /** @var User $b */
                return strcmp($a->getId(), $b->getId());
            }
        );

        $failedSavedPosts = 0;
        if ($newPostsFromAPI){
            foreach ($newPostsFromAPI as $newPost){
                try {
                    $this->postRepository->savePostToDatabase($newPost);
                } catch (ORMException $e) {
                    $failedSavedPosts++;
                    continue;
                }
            }
        }

        $deletedPostFromAPI = array_udiff($dbPosts, $newPosts, function ($a, $b) {
                /** @var User $a */
                /** @var User $b */
                return strcmp($a->getId(), $b->getId());
            }
        );

        $failedDeletedPosts = 0;
        if ($deletedPostFromAPI){
            foreach ($deletedPostFromAPI as $post) {
                try {
                    $this->postRepository->deletePostFromDatabase($post);
                } catch (ORMException $e) {
                    $failedDeletedPosts++;
                    continue;
                }
            }
        }

        $errors = $failedSavedPosts + $failedDeletedPosts;

        return $errors;
    }
}
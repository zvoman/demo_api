<?php

namespace App\Manager;

use App\Entity\Post;
use App\Entity\User;
use App\Repository\PostRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class PostManager
{
    private $postRepository;
    private $userRepository;
    private $client;

    public function __construct(HttpClientInterface $client, PostRepository $postRepository, UserRepository $userRepository)
    {
        $this->postRepository = $postRepository;
        $this->userRepository =  $userRepository;
        $this->client = $client;
    }

    public function fetchPostDataFromDatabase()
    {
        return $this->postRepository->fetchAllPosts();
    }

    public function fetchPostDataFromAPI()
    {
        $response = $this->client->request(
            'GET',
            'http://jsonplaceholder.typicode.com/posts'
        );

        $statusCode = $response->getStatusCode();

        $content = $response->getContent();

        $content = $response->toArray();

        return $content;
    }

    public function synchronizePostData()
    {
        $dbPosts = $this->fetchPostDataFromDatabase();
        $apiPosts = $this->fetchPostDataFromAPI();

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
                return strcmp($a->getId(), $b->getId());
            }
        );

        if ($newPostsFromAPI){
            foreach ($newPostsFromAPI as $newPost){
                try {
                    $this->postRepository->savePostToDatabase($newPost);
                } catch (OptimisticLockException $e) {
                    //TODO Error
                } catch (ORMException $e) {
                    //TODO Error
                }
            }
        }

        $deletedPostFromAPI = array_udiff($dbPosts, $newPosts, function ($a, $b) {
            return strcmp($a->getId(), $b->getId());
        }
        );

        if ($deletedPostFromAPI){
            foreach ($deletedPostFromAPI as $post) {
                try {
                    $this->postRepository->deletePostFromDatabase($post);
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
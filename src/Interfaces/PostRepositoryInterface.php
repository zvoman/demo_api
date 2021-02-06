<?php


namespace App\Interfaces;


use App\Entity\Post;

interface PostRepositoryInterface
{
    /**
     * Fetching all users
     * @return Post[]|null
     */
    public function fetchAllPosts();

    /**
     * @param Post $post
     * @return mixed
     */
    public function savePostToDatabase($post);

    /**
     * @param Post $post
     * @return mixed
     */
    public function deletePostFromDatabase($post);

}
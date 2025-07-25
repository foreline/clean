<?php
declare(strict_types=1);

namespace App\Presentation\HTTP\Controller;

use App\Domain\Post\UseCase\PostManager;
use App\Domain\Post\UseCase\CreatePost;
use App\Domain\Post\UseCase\GetPost;
use App\Domain\Post\Aggregate\Post;
use App\Infrastructure\Application;

/**
 * Post Controller - HTTP Presentation Layer
 * 
 * This demonstrates how to use the DI container in the presentation layer
 * to resolve domain services and execute use cases.
 */
class PostController
{
    private Application $app;
    private PostManager $postManager;

    public function __construct(Application $app)
    {
        $this->app = $app;
        $this->postManager = $app->get(PostManager::class);
    }

    /**
     * Create a new post
     * 
     * Example usage:
     * $controller = new PostController($app);
     * $response = $controller->createPost(['title' => 'My Post', 'content' => 'Hello World']);
     */
    public function createPost(array $data): array
    {
        try {
            // Create post entity
            $post = new Post();
            $post->setTitle($data['title'] ?? '');
            $post->setContent($data['content'] ?? '');
            $post->setPostDate(new \DateTime());

            // Persist using the injected manager
            $savedPost = $this->postManager->persist($post);

            return [
                'success' => true,
                'data' => [
                    'id' => $savedPost->getId(),
                    'title' => $savedPost->getTitle(),
                    'content' => $savedPost->getContent(),
                    'created_at' => $savedPost->getPostDate()->format('Y-m-d H:i:s'),
                ],
            ];
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get a post by ID
     */
    public function getPost(int $id): array
    {
        try {
            $post = $this->postManager->findById($id);

            if (!$post) {
                return [
                    'success' => false,
                    'error' => 'Post not found',
                ];
            }

            return [
                'success' => true,
                'data' => [
                    'id' => $post->getId(),
                    'title' => $post->getTitle(),
                    'content' => $post->getContent(),
                    'created_at' => $post->getPostDate()->format('Y-m-d H:i:s'),
                ],
            ];
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get all posts
     */
    public function getAllPosts(): array
    {
        try {
            $posts = $this->postManager->findAll();
            $postsData = [];

            if ($posts) {
                foreach ($posts->getItems() as $post) {
                    $postsData[] = [
                        'id' => $post->getId(),
                        'title' => $post->getTitle(),
                        'content' => $post->getContent(),
                        'created_at' => $post->getPostDate()->format('Y-m-d H:i:s'),
                    ];
                }
            }

            return [
                'success' => true,
                'data' => $postsData,
            ];
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Delete a post
     */
    public function deletePost(int $id): array
    {
        try {
            $deleted = $this->postManager->delete($id);

            return [
                'success' => $deleted,
                'message' => $deleted ? 'Post deleted successfully' : 'Post not found',
            ];
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }
}

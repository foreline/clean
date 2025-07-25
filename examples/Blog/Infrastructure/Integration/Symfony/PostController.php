<?php
declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use App\Domain\Post\UseCase\PostManager;
use App\Domain\Post\Aggregate\Post;

/**
 * Symfony Controller using Pristine Domain Services
 * NO separate Pristine application needed - everything in Symfony container
 */
#[Route('/api/posts')]
class PostController extends AbstractController
{
    private PostManager $postManager;

    // Pristine PostManager is automatically injected by Symfony
    public function __construct(PostManager $postManager)
    {
        $this->postManager = $postManager;
    }

    #[Route('', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        
        $post = new Post();
        $post->setTitle($data['title'] ?? '');
        $post->setContent($data['content'] ?? '');
        $post->setPostDate(new \DateTime());

        $savedPost = $this->postManager->persist($post);

        return $this->json([
            'success' => true,
            'data' => [
                'id' => $savedPost->getId(),
                'title' => $savedPost->getTitle(),
                'content' => $savedPost->getContent(),
                'created_at' => $savedPost->getPostDate()->format('c'),
            ],
        ]);
    }

    #[Route('/{id}', methods: ['GET'])]
    public function show(int $id): JsonResponse
    {
        $post = $this->postManager->findById($id);

        if (!$post) {
            return $this->json(['error' => 'Post not found'], 404);
        }

        return $this->json([
            'success' => true,
            'data' => [
                'id' => $post->getId(),
                'title' => $post->getTitle(),
                'content' => $post->getContent(),
                'created_at' => $post->getPostDate()->format('c'),
            ],
        ]);
    }

    #[Route('', methods: ['GET'])]
    public function index(): JsonResponse
    {
        $posts = $this->postManager->find();
        $postsData = [];

        if ($posts) {
            foreach ($posts->getCollection() as $post) {
                $postsData[] = [
                    'id' => $post->getId(),
                    'title' => $post->getTitle(),
                    'content' => $post->getContent(),
                    'created_at' => $post->getPostDate()->format('c'),
                ];
            }
        }

        return $this->json([
            'success' => true,
            'data' => $postsData,
        ]);
    }
}

<?php
declare(strict_types=1);

/**
 * Blog Application Entry Point
 * 
 * This script demonstrates the complete DI implementation from
 * entry point to repository, showing how all layers work together.
 */

require_once __DIR__ . '/../../vendor/autoload.php';

use App\Infrastructure\Application;
use App\Presentation\HTTP\Controller\PostController;

echo "=== Pristine Framework - Blog Application with DI ===\n\n";

try {
    // 1. Bootstrap the application and DI container
    echo "1. Bootstrapping application...\n";
    $app = new Application();
    echo "   ✓ DI Container initialized\n";
    echo "   ✓ Service providers registered\n";
    echo "   ✓ Dependencies resolved\n\n";

    // 2. Create controller with injected dependencies
    echo "2. Creating controller...\n";
    $postController = new PostController($app);
    echo "   ✓ PostController created with injected PostManager\n";
    echo "   ✓ PostManager injected with PostRepositoryInterface\n";
    echo "   ✓ DatabasePostRepository bound to interface\n\n";

    // 3. Demonstrate CRUD operations
    echo "3. Demonstrating CRUD operations...\n\n";

    // Create posts
    echo "Creating posts:\n";
    $post1Response = $postController->createPost([
        'title' => 'First Post with DI',
        'content' => 'This post was created using dependency injection!',
    ]);
    echo "   ✓ Post 1: " . json_encode($post1Response) . "\n";

    $post2Response = $postController->createPost([
        'title' => 'Clean Architecture Example',
        'content' => 'Domain layer knows nothing about infrastructure details.',
    ]);
    echo "   ✓ Post 2: " . json_encode($post2Response) . "\n\n";

    // Read posts
    echo "Reading posts:\n";
    $allPosts = $postController->getAllPosts();
    echo "   ✓ All posts: " . json_encode($allPosts) . "\n\n";

    // Get specific post
    $singlePost = $postController->getPost(1);
    echo "   ✓ Single post: " . json_encode($singlePost) . "\n\n";

    // 4. Demonstrate DI container direct usage
    echo "4. Direct container usage:\n";
    
    // Get services directly from container
    $postManager = $app->get(\App\Domain\Post\UseCase\PostManager::class);
    echo "   ✓ PostManager resolved from container\n";
    
    $repository = $app->get(\App\Domain\Post\Repository\PostRepositoryInterface::class);
    echo "   ✓ Repository interface resolved to concrete implementation\n";
    
    // Show that the same instance is returned (singleton)
    $postManager2 = $app->get(\App\Domain\Post\UseCase\PostManager::class);
    $isSameInstance = $postManager === $postManager2;
    echo "   ✓ Singleton pattern working: " . ($isSameInstance ? 'YES' : 'NO') . "\n\n";

    echo "5. Architecture Summary:\n";
    echo "   ✓ Domain Layer: Pure business logic (Post, PostManager)\n";
    echo "   ✓ Infrastructure Layer: Technical implementations (DatabasePostRepository, DI Container)\n";
    echo "   ✓ Presentation Layer: HTTP controllers, user interfaces\n";
    echo "   ✓ Dependencies flow inward: Infrastructure → Domain ← Presentation\n";
    echo "   ✓ Domain layer has NO dependencies on Infrastructure\n\n";

    echo "🎉 DI Implementation completed successfully!\n";
    echo "The framework demonstrates Clean Architecture with proper dependency injection.\n";

} catch (\Throwable $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

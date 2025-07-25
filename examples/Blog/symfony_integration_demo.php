<?php
declare(strict_types=1);

/**
 * Symfony Integration Demo - SINGLE KERNEL APPROACH
 * 
 * This demonstrates how to integrate Pristine Framework with Symfony
 * WITHOUT creating a separate Pristine Application/Kernel.
 * 
 * Everything is registered in the Symfony container directly.
 */

echo "=== Pristine + Symfony Integration (Single Kernel) ===\n\n";

// Simulate Symfony service container
class SymfonyContainer 
{
    private array $services = [];
    private array $definitions = [];

    public function set(string $id, object $service): void
    {
        $this->services[$id] = $service;
    }

    public function get(string $id): object
    {
        if (isset($this->services[$id])) {
            return $this->services[$id];
        }

        if (isset($this->definitions[$id])) {
            $definition = $this->definitions[$id];
            $service = $definition();
            $this->services[$id] = $service;
            return $service;
        }

        throw new \Exception("Service '{$id}' not found");
    }

    public function register(string $id, callable $factory): void
    {
        $this->definitions[$id] = $factory;
    }
}

try {
    echo "1. Symfony Kernel Boot...\n";
    $container = new SymfonyContainer();
    echo "   ✓ Symfony container initialized\n\n";

    echo "2. Registering Pristine services in Symfony container...\n";
    
    // Register repository (would be done via services.yaml or CompilerPass)
    $container->register(
        'App\Domain\Post\Repository\PostRepositoryInterface',
        function() {
            require_once __DIR__ . '/Infrastructure/Post/Repository/DatabasePostRepository.php';
            return new \App\Infrastructure\Post\Repository\DatabasePostRepository();
        }
    );
    echo "   ✓ PostRepositoryInterface → DatabasePostRepository\n";

    // Register domain manager (would be done via services.yaml or CompilerPass)
    $container->register(
        'App\Domain\Post\UseCase\PostManager',
        function() use ($container) {
            require_once __DIR__ . '/Post/UseCase/PostManager.php';
            return new \App\Domain\Post\UseCase\PostManager(
                $container->get('App\Domain\Post\Repository\PostRepositoryInterface')
            );
        }
    );
    echo "   ✓ PostManager with injected repository\n\n";

    echo "3. Symfony Controller using Pristine services...\n";
    // Simulate Symfony controller receiving injected PostManager
    $postManager = $container->get('App\Domain\Post\UseCase\PostManager');
    echo "   ✓ PostManager resolved from Symfony container\n";
    echo "   ✓ NO separate Pristine kernel needed!\n\n";

    echo "4. Using services (simulated HTTP request)...\n";
    
    // Create post using domain logic
    require_once __DIR__ . '/Post/Aggregate/Post.php';
    $post = new \App\Domain\Post\Aggregate\Post();
    $post->setTitle('Integrated with Symfony');
    $post->setContent('This post was created using Pristine domain services running in Symfony container');
    $post->setPostDate(new DateTime());
    
    $savedPost = $postManager->persist($post);
    echo "   ✓ Post created via Pristine domain service\n";
    echo "   ✓ Title: " . $savedPost->getTitle() . "\n";
    echo "   ✓ ID: " . $savedPost->getId() . "\n\n";

    echo "5. Integration Benefits:\n";
    echo "   ✓ Single container (Symfony)\n";
    echo "   ✓ Single kernel/bootstrap\n";
    echo "   ✓ Unified configuration\n";
    echo "   ✓ Native Symfony features (routing, events, etc.)\n";
    echo "   ✓ Pristine domain logic remains unchanged\n";
    echo "   ✓ Easy testing with Symfony's test framework\n\n";

    echo "6. Real Symfony Configuration:\n";
    echo "   # config/services.yaml\n";
    echo "   services:\n";
    echo "     App\\Domain\\Post\\Repository\\PostRepositoryInterface:\n";
    echo "       alias: App\\Infrastructure\\Post\\Repository\\DatabasePostRepository\n";
    echo "     App\\Domain\\Post\\UseCase\\PostManager:\n";
    echo "       arguments:\n";
    echo "         \$repository: '@App\\Domain\\Post\\Repository\\PostRepositoryInterface'\n\n";

    echo "🎉 Integration successful - NO dual kernels needed!\n";

} catch (\Throwable $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

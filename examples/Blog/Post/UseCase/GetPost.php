<?php
declare(strict_types=1);

namespace App\Domain\Post\UseCase;

use App\Domain\Post\Aggregate\Post;

/**
 * Get Post UseCase
 */
class GetPost
{
    /**
     * @param int $postId
     * @param bool $raiseEvents whether to raise Domain Events
     * @return ?Post
     */
    public function get(int $postId, bool $raiseEvents = false): ?Post
    {
        $post = (new PostManager())->findById($postId);
        
        if ($post) {
            $this->checkPermissions($post);
            // Could raise PostViewedEvent if needed
        }
        
        return $post;
    }

    public function checkPermissions(?Post $post = null): void
    {
        if ($post) {
            (new PostPermissions())->canGet($post);
        }
    }
}

<?php
declare(strict_types=1);

namespace App\Domain\Post\Event;

use App\Domain\Post\Aggregate\Post;
use Domain\Event\Event;

/**
 * Post Created Event
 */
class PostCreatedEvent extends Event
{
    private Post $post;

    public function __construct(Post $post)
    {
        parent::__construct();
        $this->post = $post;
    }

    public function getPost(): Post
    {
        return $this->post;
    }

    public function getName(): string
    {
        return 'post.created';
    }
}

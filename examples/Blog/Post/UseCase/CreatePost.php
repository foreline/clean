<?php
declare(strict_types=1);

namespace App\Domain\Post\UseCase;

use App\Domain\Post\Aggregate\Post;
use App\Domain\Post\Event\PostCreatedEvent;
use App\Domain\Post\Presentation\Form\PostForm;
use Domain\Event\Publisher;

/**
 * Create Post UseCase
 */
class CreatePost
{
    /**
     * @param Post $post
     * @param bool $raiseEvents whether to raise Domain Events
     * @return Post
     */
    public function create(Post $post, bool $raiseEvents = true): Post
    {
        $this->checkPermissions($post);
        (new PostForm())->validate($post);
        $post = (new PostManager())->persist($post);
        if ($raiseEvents) {
            Publisher::getInstance()->publish(new PostCreatedEvent($post));
        }
        return $post;
    }

    public function checkPermissions(?Post $post = null): void
    {
        (new PostPermissions())->canCreate($post);
    }
}

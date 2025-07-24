<?php
declare(strict_types=1);

namespace App\Domain\Post\UseCase;

use App\Domain\Post\Aggregate\Post;
use App\Domain\Post\Event\PostUpdatedEvent;
use App\Domain\Post\Presentation\Form\PostForm;
use Domain\Event\Publisher;

/**
 * Update Post UseCase
 */
class UpdatePost
{
    /**
     * @param Post $post
     * @param bool $raiseEvents whether to raise Domain Events
     * @return Post
     */
    public function update(Post $post, bool $raiseEvents = true): Post
    {
        $this->checkPermissions($post);
        (new PostForm())->validate($post);
        $post = (new PostManager())->persist($post);
        if ($raiseEvents) {
            Publisher::getInstance()->publish(new PostUpdatedEvent($post));
        }
        return $post;
    }

    public function checkPermissions(?Post $post = null): void
    {
        (new PostPermissions())->canUpdate($post);
    }
}

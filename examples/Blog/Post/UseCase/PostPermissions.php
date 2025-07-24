<?php
declare(strict_types=1);

namespace App\Domain\Post\UseCase;

use App\Domain\Post\Aggregate\Post;
use App\Domain\Post\ValueObject\BlogRole;
use Domain\User\Service\GetCurrentUser;
use Domain\Exception\NotAuthorizedException;
use Domain\Exception\NotPermittedException;

/**
 * Post Permissions
 */
class PostPermissions
{
    /**
     * @param ?Post $post
     * @return void
     * @throws NotAuthorizedException
     * @throws NotPermittedException
     * @throws \Exception
     */
    public function canCreate(?Post $post = null): void
    {
        if (!$user = (new GetCurrentUser())->get()) {
            throw new NotAuthorizedException();
        }

        if ($user->in(BlogRole::AUTHOR)) {
            return;
        }

        throw new NotPermittedException();
    }

    /**
     * @param ?Post $post
     * @return bool
     */
    public function checkCanCreate(?Post $post = null): bool
    {
        try {
            $this->canCreate($post);
        } catch (\Exception) {
            return false;
        }
        return true;
    }

    /**
     * @param ?Post $post
     * @return void
     * @throws NotAuthorizedException
     * @throws NotPermittedException
     * @throws \Exception
     */
    public function canUpdate(?Post $post = null): void
    {
        if (!$user = (new GetCurrentUser())->get()) {
            throw new NotAuthorizedException();
        }

        // Authors can only edit their own posts
        if ($user->in(BlogRole::AUTHOR) && $post && $post->getAuthor()?->getId() === $user->getId()) {
            return;
        }

        // Editors can edit any post
        if ($user->in(BlogRole::EDITOR)) {
            return;
        }

        throw new NotPermittedException();
    }

    /**
     * @param ?Post $post
     * @return bool
     */
    public function checkCanUpdate(?Post $post = null): bool
    {
        try {
            $this->canUpdate($post);
        } catch (\Exception) {
            return false;
        }
        return true;
    }

    /**
     * @param ?Post $post
     * @return void
     * @throws NotAuthorizedException
     * @throws NotPermittedException
     * @throws \Exception
     */
    public function canDelete(?Post $post = null): void
    {
        if (!$user = (new GetCurrentUser())->get()) {
            throw new NotAuthorizedException();
        }

        // Only editors and admins can delete
        if ($user->in(BlogRole::EDITOR) || $user->in(BlogRole::ADMIN)) {
            return;
        }

        throw new NotPermittedException();
    }

    /**
     * @param ?Post $post
     * @return bool
     */
    public function checkCanDelete(?Post $post = null): bool
    {
        try {
            $this->canDelete($post);
        } catch (\Exception) {
            return false;
        }
        return true;
    }

    /**
     * @param ?Post $post
     * @return void
     * @throws NotPermittedException
     * @throws \Exception
     */
    public function canGet(?Post $post): void
    {
        $user = (new GetCurrentUser())->get();

        // Check specific $post properties to make decision
        if ($post && $post->isDraft() && !$user?->in(BlogRole::AUTHOR)) {
            throw new NotPermittedException();
        }
        // In this case every user (even unauthorized) can view published posts
        return;
    }

    /**
     * @param ?Post $post
     * @return bool
     */
    public function checkCanGet(?Post $post): bool
    {
        try {
            $this->canGet($post);
        } catch (\Exception) {
            return false;
        }
        return true;
    }
}

<?php
declare(strict_types=1);

namespace App\Domain\Comment\Aggregate;

use App\Domain\Comment\Entity\CommentEntity;
use Domain\Aggregate\AggregateInterface;

/**
 * Comment Aggregate
 */
class Comment extends CommentEntity implements AggregateInterface
{
    public function __construct()
    {
        $this->setDate(new \DateTime());
    }

    /**
     * Business rule: Check if comment can be edited
     */
    public function canEdit(\Domain\User\Entity\UserEntity $user): bool
    {
        return $this->getAuthor()?->getId() === $user->getId();
    }

    /**
     * Business rule: Check if comment is recent (can be edited within 15 minutes)
     */
    public function isRecent(): bool
    {
        if (!$this->getDate()) {
            return false;
        }
        
        $fifteenMinutesAgo = new \DateTime('-15 minutes');
        return $this->getDate() > $fifteenMinutesAgo;
    }
}

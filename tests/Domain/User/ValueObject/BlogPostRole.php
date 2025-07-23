<?php
declare(strict_types=1);

namespace Tests\Domain\User\ValueObject;

use Domain\User\ValueObject\Role;

/**
 *
 */
class BlogPostRole extends Role
{
    public const REVIEWER = 'reviewer';
    public const COMMENTER = 'commenter';
    public const AUTHOR = 'author';
    
    protected function getInheritedRoles(): array
    {
        return [
            self::AUTHOR => [self::COMMENTER, self::REVIEWER],
            self::COMMENTER => [self::REVIEWER]
        ];
    }
}
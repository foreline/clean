<?php
declare(strict_types=1);

namespace App\Domain\Post\ValueObject;

use Domain\User\ValueObject\Role as BaseRole;

/**
 * Blog Post Role - Domain-specific roles
 */
class BlogRole extends BaseRole
{
    public const AUTHOR = 'author';
    public const EDITOR = 'editor';
    public const REVIEWER = 'reviewer';
    public const COMMENTER = 'commenter';

    private array $names = [
        self::ADMIN     => 'Администратор',
        self::EDITOR    => 'Редактор',
        self::AUTHOR    => 'Автор',
        self::REVIEWER  => 'Рецензент',
        self::COMMENTER => 'Комментатор',
    ];

    /**
     * Define role inheritance hierarchy
     * Higher roles inherit permissions from lower roles
     */
    protected function getInheritedRoles(): array
    {
        return [
            self::ADMIN => [self::EDITOR, self::AUTHOR, self::REVIEWER, self::COMMENTER],
            self::EDITOR => [self::AUTHOR, self::REVIEWER, self::COMMENTER],
            self::AUTHOR => [self::REVIEWER, self::COMMENTER],
            self::REVIEWER => [self::COMMENTER],
        ];
    }
}

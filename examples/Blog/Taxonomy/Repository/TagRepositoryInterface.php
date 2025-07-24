<?php
declare(strict_types=1);

namespace App\Domain\Taxonomy\Repository;

use App\Domain\Taxonomy\Aggregate\Tag;
use App\Domain\Taxonomy\Aggregate\TagCollection;

/**
 * Tag Repository Interface
 */
interface TagRepositoryInterface
{
    public const ID = 'id';
    public const NAME = 'name';
    public const COLOR = 'color';

    /**
     * @param Tag $tag
     * @return Tag
     */
    public function persist(Tag $tag): Tag;

    /**
     * @param int $id
     * @return ?Tag
     */
    public function findById(int $id): ?Tag;

    /**
     * @return ?TagCollection
     */
    public function find(): ?TagCollection;

    /**
     * @param string $name
     * @return ?Tag
     */
    public function findByName(string $name): ?Tag;

    /**
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool;
}

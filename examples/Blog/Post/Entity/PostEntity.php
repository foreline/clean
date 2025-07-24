<?php
declare(strict_types=1);

namespace App\Domain\Post\Entity;

use Domain\Entity\EntityInterface;
use Domain\User\Entity\UserEntity;
use DateTime;

/**
 * Post Entity - Simple data container
 */
class PostEntity implements EntityInterface
{
    private ?int $id = null;
    private string $title = '';
    private string $content = '';
    private ?UserEntity $author = null;
    private ?DateTime $postDate = null;
    private bool $isDraft = true;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): void
    {
        $this->id = $id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function setContent(string $content): void
    {
        $this->content = $content;
    }

    public function getAuthor(): ?UserEntity
    {
        return $this->author;
    }

    public function setAuthor(UserEntity $author): void
    {
        $this->author = $author;
    }

    public function getPostDate(): ?DateTime
    {
        return $this->postDate;
    }

    public function setPostDate(DateTime $postDate): void
    {
        $this->postDate = $postDate;
    }

    public function isDraft(): bool
    {
        return $this->isDraft;
    }

    public function setDraft(bool $isDraft): void
    {
        $this->isDraft = $isDraft;
    }
}

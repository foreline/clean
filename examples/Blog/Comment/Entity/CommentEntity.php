<?php
declare(strict_types=1);

namespace App\Domain\Comment\Entity;

use Domain\Entity\EntityInterface;
use Domain\User\Entity\UserEntity;
use DateTime;

/**
 * Comment Entity - Simple data container
 */
class CommentEntity implements EntityInterface
{
    private ?int $id = null;
    private ?UserEntity $author = null;
    private string $comment = '';
    private ?DateTime $date = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): void
    {
        $this->id = $id;
    }

    public function getAuthor(): ?UserEntity
    {
        return $this->author;
    }

    public function setAuthor(UserEntity $author): void
    {
        $this->author = $author;
    }

    public function getComment(): string
    {
        return $this->comment;
    }

    public function setComment(string $comment): void
    {
        $this->comment = $comment;
    }

    public function getDate(): ?DateTime
    {
        return $this->date;
    }

    public function setDate(DateTime $date): void
    {
        $this->date = $date;
    }
}

<?php
declare(strict_types=1);

namespace Domain\User\Entity;

/**
 *
 */
class GroupEntity
{
    private ?int $id = null;
    private bool $active = true;
    private string $name = '';
    private string $description = '';
    private string $code = '';
    private int $sort = 50;
    private bool $anonymous = false;
    private bool $privileged = false;

    /**
     * @param int|null $id
     */
    public function __construct(?int $id = null)
    {
        if ( null !== $id ) {
            $this->setId($id);
        }
    }

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @param int|null $id
     * @return $this
     */
    public function setId(?int $id): static
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->active;
    }

    /**
     * @param bool $active
     * @return $this
     */
    public function setActive(bool $active): static
    {
        $this->active = $active;
        return $this;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return $this
     */
    public function setName(string $name): static
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return int
     */
    public function getSort(): int
    {
        return $this->sort;
    }

    /**
     * @param int $sort
     * @return $this
     */
    public function setSort(int $sort): static
    {
        $this->sort = $sort;
        return $this;
    }

    /**
     * @return string
     */
    public function getCode(): string
    {
        return $this->code;
    }

    /**
     * @param string $code
     * @return $this
     */
    public function setCode(string $code): static
    {
        $this->code = $code;
        return $this;
    }

    /**
     * @return bool
     */
    public function isAnonymous(): bool
    {
        return $this->anonymous;
    }

    /**
     * @param bool $anonymous
     * @return $this
     */
    public function setAnonymous(bool $anonymous): static
    {
        $this->anonymous = $anonymous;
        return $this;
    }

    /**
     * @return bool
     */
    public function isPrivileged(): bool
    {
        return $this->privileged;
    }

    /**
     * @param bool $privileged
     * @return $this
     */
    public function setPrivileged(bool $privileged): static
    {
        $this->privileged = $privileged;
        return $this;
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @param string $description
     * @return $this
     */
    public function setDescription(string $description): static
    {
        $this->description = $description;
        return $this;
    }

}
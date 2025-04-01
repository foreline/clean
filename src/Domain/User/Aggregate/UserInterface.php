<?php
declare(strict_types=1);

namespace Domain\User\Aggregate;

/**
 * User Interface
 */
interface UserInterface
{
    /**
     * @return int|null
     */
    public function getId(): ?int;

    /**
     * @param ?int $id
     * @return $this
     */
    public function setId(?int $id): static;

    /**
     * Returns user email
     * @return string
     */
    public function getEmail(): string;

    /**
     * Checks if user confirmed email address
     * @return bool
     */
    public function isConfirmed(): bool;

    /**
     * Checks if user is authorized
     * @return bool
     */
    public function isAuthorized(): bool;

    /**
     * Checks if user is in roles
     * @param string|string[] ...$role
     * @return bool
     */
    public function in(string|array ...$role): bool;
}
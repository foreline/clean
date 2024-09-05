<?php
declare(strict_types=1);

namespace Domain\User\Presentation\HTTP;

use Domain\User\Aggregate\UserCollection;

/**
 * User Collection HTTP API Data Transfer Object
 */
class UserCollectionDto
{
    /**
     * @param UserCollection|null $users
     * @param array $fields
     * @return array|null
     */
    public static function toArray(?UserCollection $users, array $fields = []): ?array
    {
        if ( !$users ) {
            return null;
        }
        
        $dto = [];
        
        foreach ( $users as $user ) {
            $dto[] = UserDto::toArray($user, $fields);
        }
        
        return $dto;
    }
    
    /**
     * @param ?array $data
     * @return ?UserCollection
     */
    public static function fromArray(?array $data): ?UserCollection
    {
        if ( null === $data ) {
            return null;
        }
        
        $users = new UserCollection();
        
        foreach ( $data as $item ) {
            $users->addItem(UserDto::fromArray($item));
        }
        
        return $users;
    }
}
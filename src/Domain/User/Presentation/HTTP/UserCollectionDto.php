<?php
declare(strict_types=1);

namespace Domain\User\Presentation\HTTP;

use Domain\User\Aggregate\UserCollection;

/**
 *
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
        
        $dto = [
            'entityType'    => 'userCollection',
        ];
        
        foreach ( $users as $user ) {
            $dto[] = UserDto::toArray($user, $fields);
        }
        
        return $dto;
    }
}
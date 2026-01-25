<?php
declare(strict_types=1);

namespace Domain\User\Presentation\HTTP;

use Domain\User\Aggregate\UserCollection;

/**
 * User Collection HTTP API Handler
 */
class UserCollectionHandler
{
    /**
     * @param UserCollection $users
     * @param array $fields
     * @return array
     */
    public static function toArray(UserCollection $users, array $fields = []): array
    {
        $result = [];
        
        foreach ( $users as $user ) {
            $result[] = UserHandler::toArray($user, $fields);
        }
        
        return $result;
    }
    
    /**
     * @param array $data
     * @return UserCollection
     */
    public static function fromArray(array $data): UserCollection
    {
        $users = new UserCollection();
        
        foreach ( $data as $item ) {
            $users->addItem(UserHandler::fromArray($item));
        }
        
        return $users;
    }
}
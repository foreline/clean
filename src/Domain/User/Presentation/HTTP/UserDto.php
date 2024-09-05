<?php
declare(strict_types=1);

namespace Domain\User\Presentation\HTTP;

use Domain\User\Aggregate\User;

/**
 * User HTTP API Data Transfer Object
 */
class UserDto
{
    /**
     * @param User|null $user
     * @param array $fields
     * @return string[]|null
     */
    public static function toArray(?User $user, array $fields = []): ?array
    {
        if ( !$user ) {
            return null;
        }
        
        $dto = [
            'entityType'    => 'user',
        ];
        
        if ( empty($fields) || array_key_exists('id', $fields) ) {
            $dto['id'] = $user->getId();
        }
    
        if ( empty($fields) || array_key_exists('name', $fields) ) {
            $dto['name'] = $user->getName();
        }
    
        if ( empty($fields) || array_key_exists('lastName', $fields) ) {
            $dto['lastName'] = $user->getLastName();
        }
    
        if ( empty($fields) || array_key_exists('email', $fields) ) {
            $dto['email'] = $user->getEmail();
        }
    
        if ( empty($fields) || array_key_exists('login', $fields) ) {
            $dto['login'] = $user->getLogin();
        }
        
        return $dto;
    }
}
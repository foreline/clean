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
        
        if ( empty($fields) || array_key_exists('secondName', $fields) ) {
            $dto['secondName'] = $user->getSecondName();
        }
    
        if ( empty($fields) || array_key_exists('email', $fields) ) {
            $dto['email'] = $user->getEmail();
        }
    
        if ( empty($fields) || array_key_exists('login', $fields) ) {
            $dto['login'] = $user->getLogin();
        }
        
        return $dto;
    }
    
    /**
     * @param ?array $data
     * @return ?User
     */
    public static function fromArray(?array $data): ?User
    {
        if ( null === $data ) {
            return null;
        }
        
        $user = new User();
        
        if ( array_key_exists('id', $data) ) {
            $user->setId((int)$data['id']);
        }
        
        if ( array_key_exists('firstName', $data) ) {
            $user->setFirstName((string)$data['firstName']);
        }
    
        if ( array_key_exists('lastName', $data) ) {
            $user->setLastName((string)$data['lastName']);
        }
        
        if ( array_key_exists('secondName', $data) ) {
            $user->setSecondName((string)$data['secondName']);
        }
    
        if ( array_key_exists('email', $data) ) {
            $user->setEmail((string)$data['email']);
        }
        
        return $user;
    }
}
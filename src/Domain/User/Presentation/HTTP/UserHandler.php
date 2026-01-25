<?php
declare(strict_types=1);

namespace Domain\User\Presentation\HTTP;

use Domain\File\Presentation\HTTP\FileHandler;
use Domain\User\Aggregate\User;

/**
 * User HTTP API Handler
 */
class UserHandler
{
    public const ID = 'id';
    public const NAME = 'name';
    public const FIRST_NAME = 'firstName';
    public const LAST_NAME = 'lastName';
    public const SECOND_NAME = 'secondName';
    public const EMAIL = 'email';
    public const PHONE = 'phone';
    public const LOGIN = 'login';
    public const AVATAR = 'avatar';
    public const POSITION = 'position';
    public const DEPARTMENT = 'department';
    
    
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
        
        $result = [];
        
        if ( empty($fields) || array_key_exists(self::ID, $fields) ) {
            $result[self::ID] = $user->getId();
        }
        
        if ( empty($fields) || array_key_exists(self::NAME, $fields) ) {
            $result[self::NAME] = $user->getName();
        }
        
        if ( empty($fields) || array_key_exists(self::LAST_NAME, $fields) ) {
            $result[self::LAST_NAME] = $user->getLastName();
        }
        
        if ( empty($fields) || array_key_exists(self::SECOND_NAME, $fields) ) {
            $result[self::SECOND_NAME] = $user->getSecondName();
        }
        
        if ( empty($fields) || array_key_exists(self::SECOND_NAME, $fields) ) {
            $result[self::SECOND_NAME] = $user->getSecondName();
        }
        
        if ( empty($fields) || array_key_exists(self::EMAIL, $fields) ) {
            $result[self::EMAIL] = $user->getEmail();
        }
        
        if ( empty($fields) || array_key_exists(self::PHONE, $fields) ) {
            $result[self::PHONE] = $user->getPhone();
        }
        
        if ( empty($fields) || array_key_exists(self::LOGIN, $fields) ) {
            $result[self::LOGIN] = $user->getLogin();
        }
        
        if ( empty($fields) || array_key_exists(self::AVATAR, $fields) ) {
            $result[self::AVATAR] = FileHandler::toArray($user->getAvatar());
        }
        
        if ( empty($fields) || array_key_exists(self::POSITION, $fields) ) {
            $result[self::POSITION] = $user->getPosition();
        }
        
        if ( empty($fields) || array_key_exists(self::DEPARTMENT, $fields) ) {
            $result[self::DEPARTMENT] = $user->getDepartment();
        }
        
        return $result;
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
        
        if ( array_key_exists(self::ID, $data) ) {
            $user->setId((int)$data[self::ID]);
        }
        
        if ( array_key_exists(self::FIRST_NAME, $data) ) {
            $user->setFirstName((string)$data[self::FIRST_NAME]);
        }
        
        if ( array_key_exists(self::LAST_NAME, $data) ) {
            $user->setLastName((string)$data[self::LAST_NAME]);
        }
        
        if ( array_key_exists(self::SECOND_NAME, $data) ) {
            $user->setSecondName((string)$data[self::SECOND_NAME]);
        }
        
        if ( array_key_exists(self::EMAIL, $data) ) {
            $user->setEmail((string)$data[self::EMAIL]);
        }
        
        if ( array_key_exists(self::PHONE, $data) ) {
            $user->setPhone((string)$data[self::PHONE]);
        }
        
        if ( array_key_exists(self::LOGIN, $data) ) {
            $user->setLogin((string)$data[self::LOGIN]);
        }
        
        if ( array_key_exists(self::POSITION, $data) ) {
            $user->setPosition((string)$data[self::POSITION]);
        }
        
        if ( array_key_exists(self::DEPARTMENT, $data) ) {
            $user->setDepartment((string)$data[self::DEPARTMENT]);
        }
        
        return $user;
    }
}
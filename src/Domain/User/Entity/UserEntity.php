<?php
declare(strict_types=1);

namespace Domain\User\Entity;

use Webmozart\Assert\Assert;

/**
 * User Entity
 */
class UserEntity
{
    /** @var int|null ID */
    private ?int $id = null;
    
    /** @var string Имя */
    private string $firstName = '';
    
    /** @var string Фамилия */
    private string $lastName = '';
    
    /** @var string Отчество */
    private string $secondName = '';
    
    /** @var bool Активность */
    private bool $active = true;
    
    /** @var bool Администратор */
    private bool $admin = false;
    
    /** @var string Е-мейл */
    private string $email = '';
    
    /** @var string Логин */
    private string $login = '';
    
    /** @var string Телефон */
    private string $phone = '';
    
    /** @var string Отдел */
    private string $department = '';
    
    /** @var string Должность */
    private string $position = '';
    
    /** @var string Пароль */
    private string $password = '';
    
    /** @var string Подтверждение пароля */
    private string $confirmPassword = '';
    
    /** @var array Пользовательские поля */
    private array $userFields = [];
    
    /** @var string Код подтверждения емейла */
    private string $confirmationCode = '';
    
    /**
     * @param int|null $id
     * @param string $firstName
     * @param string $lastName
     * @param bool $active
     * @param string $email
     * @param string $login
     */
    public function __construct(
        int $id = null,
        string $firstName = '',
        string $lastName = '',
        bool $active = true,
        string $email = '',
        string $login = ''
    )
    {
        $this->setId($id);
        $this->setFirstName($firstName);
        $this->setLastName($lastName);
        $this->setActive($active);
        $this->setEmail($email);
        $this->setLogin($login);
    }
    
    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }
    
    /**
     * @param ?int $id
     * @return $this
     */
    public function setId(?int $id): static
    {
        $this->id = $id;
        return $this;
    }
    
    /**
     * @return string
     */
    public function getFirstName(): string
    {
        return $this->firstName;
    }
    
    /**
     * @param string $firstName
     * @return UserEntity
     */
    public function setFirstName(string $firstName): self
    {
        $this->firstName = $firstName;
        return $this;
    }
    
    /**
     * @return string
     */
    public function getLastName(): string
    {
        return $this->lastName;
    }
    
    /**
     * @param string $lastName
     * @return UserEntity
     */
    public function setLastName(string $lastName): self
    {
        $this->lastName = $lastName;
        return $this;
    }
    
    /**
     * @return string
     */
    public function getSecondName(): string
    {
        return $this->secondName;
    }
    
    /**
     * @param string $secondName
     */
    public function setSecondName(string $secondName): void
    {
        $this->secondName = $secondName;
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
     * @return UserEntity
     */
    public function setActive(bool $active): self
    {
        $this->active = $active;
        return $this;
    }
    
    /**
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email;
    }
    
    /**
     * @param string $email
     * @return UserEntity
     */
    public function setEmail(string $email): self
    {
        if ( '' !== $email ) {
            Assert::email($email, 'Неверный е-мейл');
        }
        $this->email = $email;
        return $this;
    }
    
    /**
     * @return string
     */
    public function getLogin(): string
    {
        return $this->login;
    }
    
    /**
     * @param string $login
     * @return UserEntity
     */
    public function setLogin(string $login): self
    {
        $this->login = $login;
        return $this;
    }
    
    /**
     * @return string
     */
    public function getFullName(): string
    {
        if ( !empty($this->lastName) || !empty($this->firstName) ) {
            return $this->lastName . ' ' . $this->firstName;
        } elseif ( !empty($this->email) ) {
            return $this->email;
        } elseif ( !empty($this->login) ) {
            return $this->login;
        } else {
            return '';
        }
    }

    /**
     * @see getFullName()
     * @return string
     */
    public function getName(): string
    {
        return $this->getFullName();
    }
    
    /**
     * @return bool
     */
    public function isAdmin(): bool
    {
        return $this->admin;
    }
    
    /**
     * @param bool $admin
     * @return UserEntity
     */
    public function setAdmin(bool $admin): self
    {
        $this->admin = $admin;
        return $this;
    }
    
    /**
     * @return string
     */
    public function getPhone(): string
    {
        return $this->phone;
    }
    
    /**
     * @param string $phone
     * @return UserEntity
     */
    public function setPhone(string $phone): self
    {
        $this->phone = $phone;
        return $this;
    }
    
    /**
     * @return string
     */
    public function getPassword(): string
    {
        return $this->password;
    }
    
    /**
     * @param string $password
     * @return UserEntity
     */
    public function setPassword(string $password): self
    {
        $this->password = $password;
        return $this;
    }
    
    /**
     * @return string
     */
    public function getConfirmPassword(): string
    {
        return $this->confirmPassword;
    }
    
    /**
     * @param string $confirmPassword
     * @return UserEntity
     */
    public function setConfirmPassword(string $confirmPassword): self
    {
        $this->confirmPassword = $confirmPassword;
        return $this;
    }
    
    /**
     * @return array
     */
    public function getUserFields(): array
    {
        return $this->userFields;
    }
    
    /**
     * @param array $userFields
     * @return UserEntity
     */
    public function setUserFields(array $userFields): self
    {
        $this->userFields = [];
        foreach ( $userFields as $fieldCode => $fieldValue ) {
            $this->addUserField($fieldCode, $fieldValue);
        }
        return $this;
    }
    
    /**
     * @param string $fieldCode
     * @param mixed $fieldValue
     * @return $this
     */
    public function addUserField(string $fieldCode, mixed $fieldValue): self
    {
        $this->userFields[$fieldCode] = $fieldValue;
        return $this;
    }
    
    /**
     * @param string $fieldCode
     * @return mixed
     */
    public function getUserField(string $fieldCode): mixed
    {
        return $this->userFields[$fieldCode] ?? null;
    }
    
    /**
     * @return string
     */
    public function getDepartment(): string
    {
        return $this->department;
    }
    
    /**
     * @param string $department
     * @return UserEntity
     */
    public function setDepartment(string $department): UserEntity
    {
        $this->department = $department;
        return $this;
    }
    
    /**
     * @return string
     */
    public function getPosition(): string
    {
        return $this->position;
    }
    
    /**
     * @param string $position
     * @return UserEntity
     */
    public function setPosition(string $position): UserEntity
    {
        $this->position = $position;
        return $this;
    }
    
    /**
     * @return string
     */
    public function getConfirmationCode(): string
    {
        return $this->confirmationCode;
    }
    
    /**
     * @param string $confirmationCode
     * @return UserEntity
     */
    public function setConfirmationCode(string $confirmationCode): UserEntity
    {
        $this->confirmationCode = $confirmationCode;
        return $this;
    }
    
    /**
     * Is user email confirmed
     * @return bool
     */
    public function isConfirmed(): bool
    {
        return '' === $this->getConfirmationCode();
    }
    
    /**
     * @return bool
     */
    public function isAuthorized(): bool
    {
        return 0 < $this->getId();
    }
    
    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->getFullName();
    }
    
}
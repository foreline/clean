# Начало работы с Pristine Framework

> Пошаговое руководство для разработчиков, изучающих Clean Architecture

## Что такое Pristine Framework?

**Pristine Framework** - это набор интерфейсов и базовых классов для разработки PHP-приложений по принципам Clean Architecture. В отличие от полнофункциональных фреймворков (Symfony, Laravel), Pristine предоставляет только структурный каркас, позволяя вам:

- ✅ Правильно разделить бизнес-логику и технические детали
- ✅ Создать легко тестируемый и поддерживаемый код  
- ✅ Интегрироваться с любыми существующими системами
- ✅ Соблюдать принципы SOLID и Clean Architecture

## Основные принципы

### 1. Разделение на слои
```
┌─────────────────────────────────────┐
│           Presentation              │ ← HTTP, CLI, API
├─────────────────────────────────────┤
│            Use Cases                │ ← Бизнес-сценарии
├─────────────────────────────────────┤
│             Domain                  │ ← Основная логика
├─────────────────────────────────────┤
│          Infrastructure             │ ← База данных, файлы
└─────────────────────────────────────┘
```

### 2. Зависимости направлены внутрь
- Внешние слои знают о внутренних
- Внутренние слои НЕ знают о внешних
- Используются интерфейсы для обращения к внешним слоям

### 3. Основные компоненты

| Компонент | Назначение | Примеры |
|-----------|------------|---------|
| **Entity** | Простые контейнеры данных | `UserEntity`, `PostEntity` |
| **Aggregate** | Бизнес-логика + управление связями | `User`, `Post` |
| **Value Object** | Неизменяемые значения | `Email`, `Money`, `Status` |
| **Repository** | Интерфейсы доступа к данным | `UserRepositoryInterface` |
| **Use Case** | Бизнес-сценарии | `CreateUser`, `PublishPost` |
| **Service** | Доменные сервисы | `EmailService`, `PaymentService` |

## Установка

### Требования
- PHP >= 8.0
- Composer

### Добавление в проект

**composer.json:**
```json
{
  "require": {
    "php": ">=8.0",
    "foreline/clean": "dev-main"
  },
  "repositories": [
    {
      "type": "git",
      "url": "https://gitlab.foreline.ru/foreline/clean.git"
    }
  ]
}
```

**Установка:**
```bash
composer install
```

## Первые шаги

### 1. Структура проекта
Создайте следующую структуру директорий:
```
your-project/
├── src/
│   └── Blog/            # Исходный код приложения
└── vendor/              # Зависимости Composer
```

### 2. Первая сущность

Создайте простую сущность:
```php
<?php
// src/Blog/Domain/User/Entity/UserEntity.php

namespace App\Domain\User\Entity;

use Domain\Entity\EntityInterface;

class UserEntity implements EntityInterface
{
    private ?int $id = null;
    private string $name;
    private string $email;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): self
    {
        $this->id = $id;
        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;
        return $this;
    }
}
```

### 3. Создание агрегата

Расширьте сущность до агрегата для бизнес-логики:
```php
<?php
// app/Domain/User/Aggregate/User.php

namespace App\Domain\User\Aggregate;

use App\Domain\User\Entity\UserEntity;
use Domain\Aggregate\AggregateInterface;

class User extends UserEntity implements AggregateInterface
{
    /**
     * Бизнес-правило: email должен быть уникальным
     */
    public function changeEmail(string $newEmail): void
    {
        if (!filter_var($newEmail, FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException('Некорректный email');
        }
        
        $this->setEmail($newEmail);
    }

    /**
     * Конвертация в массив для сохранения
     */
    public function toArray(): array
    {
        return [
            'id' => $this->getId(),
            'name' => $this->getName(),
            'email' => $this->getEmail(),
        ];
    }
}
```

### 4. Интерфейс репозитория

Определите интерфейс для работы с данными:
```php
<?php
// app/Domain/User/Repository/UserRepositoryInterface.php

namespace App\Domain\User\Repository;

use App\Domain\User\Aggregate\User;

interface UserRepositoryInterface
{
    public function save(User $user): User;
    public function findById(int $id): ?User;
    public function findByEmail(string $email): ?User;
    public function delete(User $user): void;
}
```

### 5. Use Case

Создайте бизнес-сценарий:
```php
<?php
// app/Domain/User/UseCase/CreateUser.php

namespace App\Domain\User\UseCase;

use App\Domain\User\Aggregate\User;
use App\Domain\User\Repository\UserRepositoryInterface;

class CreateUser
{
    private UserRepositoryInterface $userRepository;

    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function execute(string $name, string $email): User
    {
        // Проверяем уникальность email
        if ($this->userRepository->findByEmail($email) !== null) {
            throw new \DomainException('Пользователь с таким email уже существует');
        }

        // Создаем нового пользователя
        $user = new User();
        $user->setName($name);
        $user->changeEmail($email); // Используем бизнес-метод

        // Сохраняем
        return $this->userRepository->save($user);
    }
}
```

## Что дальше?

1. **[Изучите туториал](./tutorial/index.md)** - Создайте полноценное приложение-блог
2. **[Познакомьтесь с паттернами](./architecture/index.md)** - Углубитесь в архитектурные решения  
3. **[Посмотрите примеры](./examples/index.md)** - Готовые решения для типовых задач
4. **[Изучите лучшие практики](./best-practices.md)** - Избегайте распространенных ошибок

## Нужна помощь?

- **[FAQ](./faq.md)** - Часто задаваемые вопросы
- **[API Reference](./api/index.md)** - Полная документация интерфейсов
- **[Примеры интеграции](./integration.md)** - Подключение к существующим проектам

---

**Следующий шаг:** [Создание приложения-блога →](./tutorial/index.md)

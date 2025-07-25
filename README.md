# Pristine Framework

> 🏗️ PHP фреймворк для разработки приложений по принципам Clean Architecture

[![Tests](https://github.com/foreline/clean/workflows/Tests/badge.svg)](https://github.com/foreline/clean/actions)
[![Coverage](https://codecov.io/gh/foreline/clean/branch/main/graph/badge.svg)](https://codecov.io/gh/foreline/clean)
[![PHPStan](https://img.shields.io/badge/PHPStan-level%208-brightgreen.svg)](https://phpstan.org/)
[![PHP Version](https://img.shields.io/badge/PHP-8.1%2B-blue)](https://php.net)

## ✨ Особенности

- 🏛️ **Clean Architecture** - четкое разделение слоев и зависимостей
- 🎯 **Анемичная модель** - простые сущности, бизнес-логика в Use Cases
- 🔄 **Event-Driven** - слабая связанность через доменные события
- 📝 **Type Safety** - строгая типизация с Value Objects
- 🧪 **Testable** - высокое покрытие тестами из коробки
- 🔧 **Flexible** - легкая интеграция с существующими проектами

## 🚀 Быстрый старт

### Установка

```bash
composer require foreline/pristine-framework
```

### Создание первой сущности

```php
use Domain\Entity\EntityInterface;
use Domain\ValueObject\Email;

class User implements EntityInterface
{
    private ?int $id = null;
    private Email $email;
    private string $name;

    public function getId(): ?int 
    { 
        return $this->id; 
    }

    public function setId(?int $id): self 
    { 
        $this->id = $id; 
        return $this; 
    }

    public function setEmail(Email $email): self
    {
        $this->email = $email;
        return $this;
    }

    public function getEmail(): Email
    {
        return $this->email;
    }
}
```

### Создание Use Case

```php
class CreateUser
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private Publisher $eventPublisher
    ) {}

    public function execute(array $data): User
    {
        // Валидация
        $email = new Email($data['email']);
        
        // Проверка бизнес-правил
        if ($this->userRepository->findByEmail($email)) {
            throw new \DomainException('Пользователь с таким email уже существует');
        }

        // Создание агрегата
        $user = new User();
        $user->setEmail($email);
        $user->setName($data['name']);

        // Сохранение
        $savedUser = $this->userRepository->persist($user);

        // Событие
        $this->eventPublisher->publish(
            new UserCreatedEvent($savedUser->getId(), $email->getValue())
        );

        return $savedUser;
    }
}
```

## 📚 Документация

Полная документация доступна в директории [`docs/`](./docs/index.md).

### 🎯 Основы
- **[Быстрый старт](./docs/getting-started.md)** - Начните здесь
- **[Архитектура](./docs/architecture/index.md)** - Принципы и паттерны  
- **[Туториал](./docs/tutorial/index.md)** - Создание блога пошагово

### 🏗️ Слои архитектуры
- **[Доменный слой](./docs/domain/index.md)** - Entities, Aggregates, Value Objects
- **[Инфраструктура](./docs/infrastructure/index.md)** - Repositories, DI, External services
- **[Презентация](./docs/presentation/index.md)** - Controllers, Views, HTTP

### 📖 Справочники
- **[API Reference](./docs/api/index.md)** - Полная документация интерфейсов
- **[Cookbook](./docs/cookbook.md)** - Готовые рецепты решений
- **[Лучшие практики](./docs/best-practices.md)** - Рекомендации экспертов

### 🔧 Разработка
- **[Тестирование](./docs/testing.md)** - Unit, Integration, E2E тесты
- **[Производительность](./docs/performance.md)** - Оптимизация и профилирование
- **[Миграция](./docs/migration.md)** - Переход с других архитектур

## 🧪 Тестирование

```bash
# Запуск всех тестов
composer test

# Статический анализ  
composer analyse
```

## 📄 Лиценза

Распространяется под [MIT License](LICENSE).

---

**📚 [Полная документация](./docs/index.md) | 🚀 [Быстрый старт](./docs/getting-started.md)**
# API Reference

> Полная документация интерфейсов и классов Pristine Framework

## 📑 Содержание

### Доменный слой
- [EntityInterface](#entityinterface) - Базовый интерфейс сущностей
- [AggregateInterface](#aggregateinterface) - Интерфейс агрегатов
- [CollectionInterface](#collectioninterface) - Интерфейс коллекций
- [ValueObjectInterface](#valueobjectinterface) - Базовый интерфейс объектов-значений
- [Value Object типы](#value-object-типы) - Специализированные интерфейсы

### События
- [DomainEventInterface](#domaineventinterface) - Интерфейс доменных событий
- [EventDispatcherInterface](#eventdispatcherinterface) - Диспетчер событий

### Пользователи и роли
- [UserInterface](#userinterface) - Интерфейс пользователя
- [RoleInterface](#roleinterface) - Интерфейс ролей

---

## Доменный слой

### EntityInterface

Базовый интерфейс для всех сущностей в системе.

```php
namespace Domain\Entity;

interface EntityInterface
{
    /**
     * Получение уникального идентификатора сущности
     * 
     * @return int|string|null Идентификатор или null для новых сущностей
     */
    public function getId(): null|int|string;
}
```

#### Реализация
```php
class UserEntity implements EntityInterface
{
    private ?int $id = null;
    
    public function getId(): ?int
    {
        return $this->id;
    }
    
    public function setId(?int $id): self
    {
        $this->id = $id;
        return $this;
    }
}
```

#### Использование
- ✅ Простые контейнеры данных
- ✅ DTO между слоями
- ✅ Базовые классы для Aggregates
- ❌ Сложная бизнес-логика

---

### AggregateInterface

Интерфейс для агрегатов - объектов с бизнес-логикой.

```php
namespace Domain\Aggregate;

use Domain\Entity\EntityInterface;

interface AggregateInterface extends EntityInterface, ConvertableInterface
{
    // Наследует getId() от EntityInterface
    // Наследует toArray() от ConvertableInterface
}
```

#### ConvertableInterface
```php
namespace Domain\Aggregate;

interface ConvertableInterface
{
    /**
     * Конвертация агрегата в массив для сериализации
     * 
     * @return array Ассоциативный массив с данными агрегата
     */
    public function toArray(): array;
}
```

#### Реализация
```php
class User extends UserEntity implements AggregateInterface
{
    private UserStatus $status;
    private array $domainEvents = [];
    
    public function activate(): void
    {
        $this->status = UserStatus::active();
        $this->raiseEvent(new UserActivatedEvent($this->getId()));
    }
    
    public function toArray(): array
    {
        return [
            'id' => $this->getId(),
            'name' => $this->getName(),
            'email' => $this->getEmail(),
            'status' => $this->status->getValue(),
        ];
    }
    
    private function raiseEvent(DomainEventInterface $event): void
    {
        $this->domainEvents[] = $event;
    }
}
```

#### Использование
- ✅ Объекты с бизнес-правилами
- ✅ Управление связанными сущностями
- ✅ Генерация доменных событий
- ✅ Инкапсуляция сложных операций

---

### CollectionInterface

Интерфейс для типизированных коллекций доменных объектов.

```php
namespace Domain\Aggregate;

use Iterator;

interface CollectionInterface extends Iterator
{
    /**
     * Получение всех элементов коллекции
     * 
     * @return array Массив элементов коллекции
     */
    public function getCollection(): array;
    
    /**
     * Добавление элемента в коллекцию
     * 
     * @param mixed $item Элемент для добавления
     * @return self Fluent interface
     */
    public function addItem($item): self;
    
    /**
     * Установка элементов из итератора
     * 
     * @param Iterator|null $items Итератор с элементами
     * @return self Fluent interface
     */
    public function setItems(Iterator|null $items): self;
    
    /**
     * Проверка наличия элемента в коллекции
     * 
     * @param mixed $item Искомый элемент
     * @return bool true если элемент найден
     */
    public function contains($item): bool;
    
    /**
     * Количество элементов в коллекции
     * 
     * @return int Количество элементов
     */
    public function count(): int;
}
```

#### IteratorTrait
Вспомогательный трейт для реализации Iterator:

```php
namespace Domain\Aggregate;

trait IteratorTrait
{
    protected int $position = 0;
    
    public function rewind(): void
    {
        $this->position = 0;
    }
    
    public function key(): int
    {
        return $this->position;
    }
    
    public function next(): void
    {
        ++$this->position;
    }
    
    public function valid(): bool
    {
        return isset($this->items[$this->position]);
    }
}
```

#### Реализация
```php
class UserCollection implements CollectionInterface
{
    use IteratorTrait;
    
    /** @var User[] */
    private array $items = [];
    
    public function current(): ?User
    {
        return $this->valid() ? $this->items[$this->position] : null;
    }
    
    public function getCollection(): array
    {
        return $this->items;
    }
    
    public function addItem($item): self
    {
        if (!$item instanceof User) {
            throw new \InvalidArgumentException('Item must be instance of User');
        }
        
        if (!$this->contains($item)) {
            $this->items[] = $item;
        }
        
        return $this;
    }
    
    public function contains($item): bool
    {
        if (!$item instanceof User || !$item->getId()) {
            return false;
        }
        
        foreach ($this->items as $existing) {
            if ($existing->getId() === $item->getId()) {
                return true;
            }
        }
        
        return false;
    }
    
    public function count(): int
    {
        return count($this->items);
    }
}
```

---

### ValueObjectInterface

Базовый интерфейс для всех объектов-значений.

```php
namespace Domain\ValueObject;

interface ValueObjectInterface
{
    /**
     * Сравнение с другим объектом-значением
     * 
     * @param ValueObjectInterface $other Объект для сравнения
     * @return bool true если объекты равны
     */
    public function equals(ValueObjectInterface $other): bool;
}
```

#### Использование
- ✅ Неизменяемые значения
- ✅ Типизация примитивов
- ✅ Валидация данных
- ✅ Бизнес-правила для значений

---

### Value Object типы

#### StringValueObjectInterface
Для объектов-значений с строковым значением:

```php
namespace Domain\ValueObject;

interface StringValueObjectInterface extends ValueObjectInterface
{
    /**
     * Получение строкового значения
     * 
     * @return string Значение объекта
     */
    public function getValue(): string;
}
```

**Пример реализации:**
```php
class Email implements StringValueObjectInterface
{
    private string $value;
    
    public function __construct(string $value)
    {
        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException('Invalid email format');
        }
        $this->value = $value;
    }
    
    public function getValue(): string
    {
        return $this->value;
    }
    
    public function equals(ValueObjectInterface $other): bool
    {
        return $other instanceof self && $this->value === $other->value;
    }
}
```

#### IntValueObjectInterface
Для объектов-значений с целочисленным значением:

```php
namespace Domain\ValueObject;

interface IntValueObjectInterface extends ValueObjectInterface
{
    /**
     * Получение целочисленного значения
     * 
     * @return int Значение объекта
     */
    public function getValue(): int;
}
```

**Пример реализации:**
```php
class UserId implements IntValueObjectInterface
{
    private int $value;
    
    public function __construct(int $value)
    {
        if ($value <= 0) {
            throw new \InvalidArgumentException('User ID must be positive');
        }
        $this->value = $value;
    }
    
    public function getValue(): int
    {
        return $this->value;
    }
    
    public function equals(ValueObjectInterface $other): bool
    {
        return $other instanceof self && $this->value === $other->value;
    }
}
```

#### FloatValueObjectInterface
Для объектов-значений с дробным значением:

```php
namespace Domain\ValueObject;

interface FloatValueObjectInterface extends ValueObjectInterface
{
    /**
     * Получение дробного значения
     * 
     * @return float Значение объекта
     */
    public function getValue(): float;
}
```

**Пример реализации:**
```php
class Price implements FloatValueObjectInterface
{
    private float $value;
    
    public function __construct(float $value)
    {
        if ($value < 0) {
            throw new \InvalidArgumentException('Price cannot be negative');
        }
        $this->value = $value;
    }
    
    public function getValue(): float
    {
        return $this->value;
    }
    
    public function equals(ValueObjectInterface $other): bool
    {
        return $other instanceof self && abs($this->value - $other->value) < 0.01;
    }
}
```

#### EnumValueObjectInterface
Для объектов-значений с ограниченным набором значений:

```php
namespace Domain\ValueObject;

interface EnumValueObjectInterface extends ValueObjectInterface
{
    /**
     * Получение значения перечисления
     * 
     * @return string Значение перечисления
     */
    public function getValue(): string;
    
    /**
     * Получение всех возможных значений
     * 
     * @return array Массив допустимых значений
     */
    public static function getValues(): array;
}
```

**Пример реализации:**
```php
class UserStatus implements EnumValueObjectInterface
{
    public const ACTIVE = 'active';
    public const INACTIVE = 'inactive';
    public const BLOCKED = 'blocked';
    
    private string $value;
    
    public function __construct(string $value)
    {
        if (!in_array($value, self::getValues())) {
            throw new \InvalidArgumentException('Invalid user status');
        }
        $this->value = $value;
    }
    
    public function getValue(): string
    {
        return $this->value;
    }
    
    public static function getValues(): array
    {
        return [self::ACTIVE, self::INACTIVE, self::BLOCKED];
    }
    
    public static function active(): self
    {
        return new self(self::ACTIVE);
    }
    
    public static function inactive(): self
    {
        return new self(self::INACTIVE);
    }
    
    public static function blocked(): self
    {
        return new self(self::BLOCKED);
    }
    
    public function equals(ValueObjectInterface $other): bool
    {
        return $other instanceof self && $this->value === $other->value;
    }
}
```

---

## События

### DomainEventInterface

Интерфейс для доменных событий.

```php
namespace Domain\Event;

interface DomainEventInterface
{
    /**
     * Получение времени возникновения события
     * 
     * @return \DateTimeInterface Время события
     */
    public function getOccurredOn(): \DateTimeInterface;
    
    /**
     * Получение имени события
     * 
     * @return string Имя события
     */
    public function getEventName(): string;
    
    /**
     * Получение данных события
     * 
     * @return array Массив с данными события
     */
    public function toArray(): array;
}
```

#### Реализация
```php
class UserRegisteredEvent implements DomainEventInterface
{
    private \DateTimeInterface $occurredOn;
    private int $userId;
    private string $email;
    
    public function __construct(int $userId, string $email)
    {
        $this->userId = $userId;
        $this->email = $email;
        $this->occurredOn = new \DateTime();
    }
    
    public function getOccurredOn(): \DateTimeInterface
    {
        return $this->occurredOn;
    }
    
    public function getEventName(): string
    {
        return 'user.registered';
    }
    
    public function getUserId(): int
    {
        return $this->userId;
    }
    
    public function getEmail(): string
    {
        return $this->email;
    }
    
    public function toArray(): array
    {
        return [
            'event_name' => $this->getEventName(),
            'occurred_on' => $this->occurredOn->format('c'),
            'user_id' => $this->userId,
            'email' => $this->email,
        ];
    }
}
```

### EventDispatcherInterface

Интерфейс диспетчера событий.

```php
namespace Domain\Event;

interface EventDispatcherInterface
{
    /**
     * Отправка события подписчикам
     * 
     * @param DomainEventInterface $event Событие для отправки
     * @return void
     */
    public function dispatch(DomainEventInterface $event): void;
    
    /**
     * Подписка на событие
     * 
     * @param string $eventName Имя события
     * @param callable $listener Обработчик события
     * @return void
     */
    public function subscribe(string $eventName, callable $listener): void;
    
    /**
     * Отписка от события
     * 
     * @param string $eventName Имя события
     * @param callable $listener Обработчик события
     * @return void
     */
    public function unsubscribe(string $eventName, callable $listener): void;
}
```

---

## Пользователи и роли

### UserInterface

Расширенный интерфейс пользователя с поддержкой ролей.

```php
namespace Domain\User;

use Domain\Aggregate\AggregateInterface;
use Domain\User\ValueObject\Role;

interface UserInterface extends AggregateInterface
{
    /**
     * Получение имени пользователя
     * 
     * @return string Имя пользователя
     */
    public function getName(): string;
    
    /**
     * Получение email пользователя
     * 
     * @return string Email адрес
     */
    public function getEmail(): string;
    
    /**
     * Проверка наличия роли у пользователя
     * 
     * @param Role|string $role Роль для проверки
     * @return bool true если роль есть у пользователя
     */
    public function in(Role|string $role): bool;
    
    /**
     * Назначение роли пользователю
     * 
     * @param Role|string $role Роль для назначения
     * @return self Fluent interface
     */
    public function assignRole(Role|string $role): self;
    
    /**
     * Удаление роли у пользователя
     * 
     * @param Role|string $role Роль для удаления
     * @return self Fluent interface
     */
    public function removeRole(Role|string $role): self;
    
    /**
     * Получение всех ролей пользователя
     * 
     * @return array Массив ролей
     */
    public function getRoles(): array;
}
```

### RoleInterface

Интерфейс для системы ролей.

```php
namespace Domain\User\ValueObject;

use Domain\ValueObject\StringValueObjectInterface;

interface RoleInterface extends StringValueObjectInterface
{
    /**
     * Проверка наследования от другой роли
     * 
     * @param RoleInterface $role Роль для проверки
     * @return bool true если роль наследуется
     */
    public function inheritsFrom(RoleInterface $role): bool;
    
    /**
     * Получение родительских ролей
     * 
     * @return array Массив родительских ролей
     */
    public function getParentRoles(): array;
    
    /**
     * Получение всех унаследованных ролей
     * 
     * @return array Массив всех доступных ролей
     */
    public function getAllInheritedRoles(): array;
}
```

#### Реализация
```php
use Domain\User\ValueObject\Role;

class BlogRole extends Role
{
    public const READER = 'reader';
    public const COMMENTER = 'commenter';
    public const AUTHOR = 'author';
    public const EDITOR = 'editor';
    public const ADMIN = 'admin';
    
    protected static array $hierarchy = [
        self::ADMIN => [self::EDITOR],
        self::EDITOR => [self::AUTHOR],
        self::AUTHOR => [self::COMMENTER],
        self::COMMENTER => [self::READER],
        self::READER => [],
    ];
    
    public static function reader(): self
    {
        return new self(self::READER);
    }
    
    public static function author(): self
    {
        return new self(self::AUTHOR);
    }
    
    public static function admin(): self
    {
        return new self(self::ADMIN);
    }
}
```

---

## Примеры использования

### Создание сущности с агрегатом

```php
// 1. Создание Entity
$userEntity = new UserEntity();
$userEntity->setName('John Doe');
$userEntity->setEmail('john@example.com');

// 2. Расширение до Aggregate
$user = new User();
$user->setName('John Doe');
$user->changeEmail('john@example.com'); // Бизнес-метод с валидацией
$user->assignRole(BlogRole::author());

// 3. Работа с бизнес-логикой
if ($user->in(BlogRole::author())) {
    $user->activate();
}
```

### Работа с коллекциями

```php
$users = new UserCollection();

$user1 = new User();
$user1->setId(1);
$user1->setName('John');

$user2 = new User();
$user2->setId(2);
$user2->setName('Jane');

$users->addItem($user1);
$users->addItem($user2);

// Итерация
foreach ($users as $user) {
    echo $user->getName() . "\n";
}

// Проверка наличия
if ($users->contains($user1)) {
    echo "User found in collection\n";
}
```

### Работа с событиями

```php
// Создание и отправка события
$event = new UserRegisteredEvent($user->getId(), $user->getEmail());
$eventDispatcher->dispatch($event);

// Подписка на событие
$eventDispatcher->subscribe('user.registered', function(UserRegisteredEvent $event) {
    $emailService->sendWelcomeEmail($event->getEmail());
});
```

---

## 🔗 Связанные разделы

- **[Руководство по началу работы](../getting-started.md)** - Первые шаги с фреймворком
- **[Лучшие практики](../best-practices.md)** - Рекомендации по использованию
- **[Примеры кода](../examples/index.md)** - Готовые решения и шаблоны

# Доменный слой

> Сердце приложения: бизнес-логика, правила и основные сущности

Доменный слой содержит **самую важную часть вашего приложения** - бизнес-логику и правила, которые не зависят от технических деталей реализации (база данных, веб-фреймворк, UI).

## 🎯 Основные принципы

### 1. Независимость от внешних слоев
Доменный слой **НЕ знает** (и не должен знать) о:
- Базах данных и способах хранения
- HTTP-запросах и веб-фреймворках  
- UI и способах отображения
- Внешних API и сервисах

### 2. Анемичная доменная модель
Фреймворк не накладывает строгих ограничений на выбор модели, но **рекомендует анемичную модель** с минимальной бизнес-логикой согласно принципу GRASP Information Expert.

Объекты содержат **данные и простую логику над собственными свойствами**, а сложное поведение выносится в UseCases и сервисы:
```php
// ✅ Анемичная модель
class User {
    private ?Email $email;

    public function setEmail(Email $email): self
    { 
        $this->email = $email;
        return $this;
    }

    public function getEmail(): ?Email
    {
        return $this->email;
    }
}
```

Такой подход позволяет сузить круг ответственности сущности и не превращать сущность в класс, отвечающий за валидацию, доменные события, взаимодействие с другими сервисами или даже с репозитоориями. Сущность отвечает только за состояние своих данных.

---

## 🏗️ Основные компоненты

### [Сущности (Entities)](./entities.md)

**Сущности** - простые контейнеры данных с уникальной идентичностью.

#### Характеристики:
- ✅ Имеют уникальный идентификатор (ID)
- ✅ Содержат простые геттеры и сеттеры
- ✅ Фокусируются на структуре данных
- ✅ Минимальная бизнес-логика

#### Пример сущности:
```php
namespace App\Blog\Post\Entity;

use Domain\Entity\EntityInterface;

class PostEntity implements EntityInterface
{
    private ?int $id = null;
    private string $title;
    private string $content;
    private int $authorId;
    private \DateTime $createdAt;

    public function getId(): ?int { return $this->id; }
    public function setId(?int $id): self { $this->id = $id; return $this; }
    
    public function getTitle(): string { return $this->title; }
    public function setTitle(string $title): self { $this->title = $title; return $this; }
    
    // ... остальные геттеры и сеттеры
}
```

#### Когда использовать:
- Простые объекты без сложной бизнес-логики
- Базовые классы для расширения в Aggregates

---

### [Агрегаты (Aggregates)](./aggregates.md)

**Агрегаты** - корневые сущности, объединяющие связанные доменные объекты и поддерживающие их консистентность.

#### Характеристики:
- ✅ Могут наследоваться от Entity (опционально)
- ✅ Содержат минимальную бизнес-логику согласно принципу Information Expert
- ✅ Объединяют связанные сущности и коллекции
- ✅ Поддерживают инвариантность данных
- ❌ Не генерируют доменные события напрямую

#### Архитектурные решения:
- **С наследованием от Entity**: больше абстракций, но меньший размер агрегата
- **Без наследования**: меньше абстракций, но больший размер агрегата

#### Пример агрегата:
```php
use Domain\Aggregate\AggregateInterface;

class Post extends PostEntity implements AggregateInterface
{
    private CommentCollection $comments;
    private CategoryCollection $categories;
    private PostStatus $status;

    /**
     * Добавление комментария с проверками
     */
    public function addComment(Comment $comment): void
    {
        if (!$this->status->equals(PostStatus::PUBLISHED)) {
            throw new \DomainException('Нельзя комментировать неопубликованный пост');
        }

        $this->comments->addItem($comment);
    }
}
```

#### Когда использовать:
- Объекты, связанные с другими сущностями

---

### [Объекты-значения (Value Objects)](./valueobject/index.md)

**Value Objects** - неизменяемые объекты, определяемые своими значениями.

#### Характеристики:
- ✅ Не имеют идентификатора
- ✅ Неизменяемые (immutable)
- ✅ Определяются значением, не личностью
- ✅ Содержат валидацию собственных данных
- ✅ Можно безопасно копировать
- ❌ Не взаимодействуют с сервисами или репозиториями

#### Доступные интерфейсы:
- `StringValueObjectInterface` - для строковых значений
- `IntValueObjectInterface` - для целых чисел  
- `FloatValueObjectInterface` - для дробных чисел
- `EnumValueObjectInterface` - для перечислений
- `MixedValueObjectInterface` - для составных значений и комплексных объектов

#### Примеры Value Objects:
```php
// Email с валидацией
class Email implements StringValueObjectInterface
{
    private string $value;

    public function __construct(string $value)
    {
        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException('Некорректный email адрес');
        }
        $this->value = $value;
    }

    public function getValue(): string { return $this->value; }
    
    public function equals(ValueObjectInterface $other): bool
    {
        return $other instanceof self && $this->value === $other->value;
    }
}

// Статус как enum
class PostStatus implements EnumValueObjectInterface
{
    public const DRAFT = 'draft';
    public const PUBLISHED = 'published';
    public const ARCHIVED = 'archived';

    private string $value;

    public function __construct(string $value)
    {
        if (!in_array($value, [self::DRAFT, self::PUBLISHED, self::ARCHIVED])) {
            throw new \InvalidArgumentException('Недопустимый статус поста');
        }
        $this->value = $value;
    }

    public function getValue(): string { return $this->value; }
    
    public static function draft(): self { return new self(self::DRAFT); }
    public static function published(): self { return new self(self::PUBLISHED); }
    public static function archived(): self { return new self(self::ARCHIVED); }
}
```

#### Когда использовать:
- Типизированные примитивы (Email, Money, Phone)
- Статусы и перечисления
- Составные значения (Address, FullName)
- Значения с валидацией

---

### [Коллекции (Collections)](./collections.md)

**Коллекции** - типизированные наборы доменных объектов.

#### Интерфейс CollectionInterface:
```php
class PostCollection implements CollectionInterface
{
    use \Domain\Aggregate\IteratorTrait;

    /** @var Post[] */
    private array $items = [];

    public function addItem(Post $item): self
    {
        if (!$this->contains($item)) {
            $this->items[] = $item;
        }
        return $this;
    }

    public function removeItem(Post $item): self
    {
        $this->items = array_filter(
            $this->items, 
            fn($existing) => !$existing->equals($item)
        );
        return $this;
    }

    public function count(): int
    {
        return count($this->items);
    }

    public function getPublished(): self
    {
        $published = new self();
        foreach ($this->items as $post) {
            if ($post->isPublished()) {
                $published->addItem($post);
            }
        }
        return $published;
    }
}
```

#### Примечание:
Фильтрация в коллекциях (как `getPublished()`) - достаточно редкий сценарий, так как обычно сервисы получают уже отфильтрованные данные через репозитории. Более распространенные сценарии - сортировка и базовые операции с коллекцией.

---

### [Репозитории (Repositories)](./repositories.md)

**Интерфейсы** для доступа к данным без привязки к конкретной реализации.

#### Принципы проектирования:
- ✅ Только интерфейсы в доменном слое
- ✅ Работа с агрегатами, не с Entity  
- ✅ Выразительные методы поиска
- ✅ Абстракция над технологией хранения

#### Пример интерфейса:
```php
interface PostRepositoryInterface
{
    public function persist(Post $post): Post;
    public function findById(int $id): ?Post;
    public function findByAuthor(int $authorId): PostCollection;
    public function findPublished(int $limit = 10): PostCollection;
    public function findByCategory(int $categoryId): PostCollection;
    public function delete(Post $post): void;
    public function count(): int;
}
```

---

### [Use Cases (Сценарии использования)](./usecases.md)

**Use Cases** - реализация конкретных бизнес-сценариев.

#### Характеристики:
- ✅ Один сценарий = один класс
- ✅ Координация между доменными объектами
- ✅ Проверка бизнес-правил и разрешений
- ✅ Генерация событий

#### Пример Use Case:
```php
class PublishPost
{
    private PostRepositoryInterface $postRepository;
    private UserRepositoryInterface $userRepository;
    private Publisher $publisher;

    public function execute(int $postId, int $userId): Post
    {
        // Получение объектов
        $post = $this->postRepository->findById($postId);
        if (!$post) {
            throw new \DomainException('Пост не найден');
        }

        $user = $this->userRepository->findById($userId);
        if (!$user) {
            throw new \DomainException('Пользователь не найден');
        }

        // Проверка разрешений
        if (!$user->canPublishPost($post)) {
            throw new \DomainException('Недостаточно прав для публикации');
        }

        // Выполнение бизнес-операции
        $post->setStatus(PostStatus::published());
        $post->setPublishedAt(new \DateTime());

        // Сохранение
        $publishedPost = $this->postRepository->persist($post);

        // Генерация событий (ответственность Use Case)
        $this->publisher->publish(new PostPublishedEvent($post->getId(), $user->getId()));

        return $publishedPost;
    }
}
```

---

### [Доменные сервисы (Domain Services)](./services.md)

**Сервисы** для сложной логики, затрагивающей несколько агрегатов.

#### Когда использовать:
- Логика не принадлежит одному агрегату
- Координация между несколькими агрегатами
- Сложные вычисления или алгоритмы

#### Пример:
```php
class BlogStatisticsService
{
    public function calculatePopularityScore(Post $post, CommentCollection $comments, ViewCollection $views): float
    {
        $commentsWeight = $comments->count() * 2;
        $viewsWeight = $views->count() * 1;
        $ageWeight = $this->calculateAgeWeight($post->getCreatedAt());
        
        return ($commentsWeight + $viewsWeight) * $ageWeight;
    }
}
```

---

### [Доменные события (Domain Events)](./events.md)

**События** для слабой связанности и реактивности.

#### Пример события:
```php
class PostPublishedEvent implements DomainEventInterface
{
    private int $postId;
    private int $authorId;
    private \DateTime $publishedAt;

    public function __construct(int $postId, int $authorId)
    {
        $this->postId = $postId;
        $this->authorId = $authorId;
        $this->publishedAt = new \DateTime();
    }

    // Геттеры...
}
```

---

### [Система пользователей и ролей](./user/index.md)

Расширенная система управления пользователями с **иерархическими ролями** и гибкими разрешениями.

#### Ключевые возможности:
- ✅ **Наследование ролей** - автоматическое получение разрешений
- ✅ **Доменно-специфичные роли** - каждый модуль может определять свои роли
- ✅ **Интеграция с группами** - совместимость с существующими системами
- ✅ **Гибкая проверка разрешений** - простая проверка прав доступа

---

## 📖 Дополнительные материалы

- **[Лучшие практики доменного слоя](../best-practices.md#доменный-слой)**
- **[Примеры кода](../examples/index.md#доменный-слой)**
- **[Частые ошибки и как их избежать](../faq.md#архитектурные-вопросы)**

Ключевые особенности:
- Иерархическое наследование ролей
- Интеграция с существующими системами групп (Bitrix CMS)
- Доменно-специфичные роли
- Автоматическое наследование разрешений

### Задания / Task Scheduler

Регулярные задания [выполняемые планировщиком](./scheduler/index.md).

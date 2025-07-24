# Доменный слой

> Сердце приложения: бизнес-логика, правила и основные сущности

Доменный слой содержит **самую важную часть вашего приложения** - бизнес-логику и правила, которые не зависят от технических деталей реализации (база данных, веб-фреймворк, UI).

## 🎯 Основные принципы

### 1. Независимость от внешних слоев
Доменный слой **НЕ знает** о:
- Базах данных и способах хранения
- HTTP-запросах и веб-фреймворках  
- UI и способах отображения
- Внешних API и сервисах

### 2. Выражение бизнес-логики
Код должен **читаться как спецификация**:
```php
// ✅ Хорошо: код выражает бизнес-правила
$user->changeEmail($newEmail);
$post->publish();
$order->calculateDiscount();

// ❌ Плохо: технические детали вместо бизнес-логики  
$user->setEmail($newEmail);
$post->setStatus('published');
$order->setPrice($price * 0.9);
```

### 3. Богатая доменная модель
Объекты содержат **поведение, а не только данные**:
```php
// ❌ Анемичная модель
class User {
    public function setEmail(string $email) { $this->email = $email; }
}

// ✅ Богатая модель
class User {
    public function changeEmail(string $email): void {
        $this->validateEmailFormat($email);
        $this->ensureEmailIsUnique($email);
        $this->email = $email;
        $this->raiseEvent(new EmailChangedEvent($this->id, $email));
    }
}
```

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
- DTO для передачи данных между слоями
- Базовые классы для расширения в Aggregates

---

### [Агрегаты (Aggregates)](./aggregates.md)

**Агрегаты** - расширение сущностей с бизнес-логикой и управлением связями.

#### Характеристики:
- ✅ Наследуют от Entity
- ✅ Содержат бизнес-правила и валидацию
- ✅ Управляют связанными объектами
- ✅ Инкапсулируют сложные операции
- ✅ Генерируют доменные события

#### Пример агрегата:
```php
use Domain\Aggregate\AggregateInterface;

class Post extends PostEntity implements AggregateInterface
{
    private CommentCollection $comments;
    private CategoryCollection $categories;
    private PostStatus $status;

    /**
     * Бизнес-правило: публикация поста
     */
    public function publish(): void
    {
        if (empty($this->title) || empty($this->content)) {
            throw new \DomainException('Пост должен иметь заголовок и содержимое');
        }

        if ($this->status->equals(PostStatus::PUBLISHED)) {
            throw new \DomainException('Пост уже опубликован');
        }

        $this->status = PostStatus::PUBLISHED;
        $this->raiseEvent(new PostPublishedEvent($this->id));
    }

    /**
     * Добавление комментария с проверками
     */
    public function addComment(Comment $comment): void
    {
        if (!$this->status->equals(PostStatus::PUBLISHED)) {
            throw new \DomainException('Нельзя комментировать неопубликованный пост');
        }

        $this->comments->addItem($comment);
        $this->raiseEvent(new CommentAddedEvent($this->id, $comment->getId()));
    }

    public function toArray(): array
    {
        return [
            'id' => $this->getId(),
            'title' => $this->getTitle(),
            'content' => $this->getContent(),
            'status' => $this->status->getValue(),
            'comments_count' => $this->comments->count(),
        ];
    }
}
```

#### Когда использовать:
- Объекты со сложной бизнес-логикой
- Координация между связанными сущностями
- Инкапсуляция доменных правил

---

### [Объекты-значения (Value Objects)](./valueobject/index.md)

**Value Objects** - неизменяемые объекты, определяемые своими значениями.

#### Характеристики:
- ✅ Не имеют идентификатора
- ✅ Неизменяемые (immutable)
- ✅ Определяются значением, не личностью
- ✅ Содержат валидацию и бизнес-правила
- ✅ Можно безопасно копировать

#### Доступные интерфейсы:
- `StringValueObjectInterface` - для строковых значений
- `IntValueObjectInterface` - для целых чисел  
- `FloatValueObjectInterface` - для дробных чисел
- `EnumValueObjectInterface` - для перечислений

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
    private EventDispatcherInterface $eventDispatcher;

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
        $post->publish();

        // Сохранение
        $publishedPost = $this->postRepository->persist($post);

        // Обработка событий
        foreach ($post->getEvents() as $event) {
            $this->eventDispatcher->dispatch($event);
        }

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

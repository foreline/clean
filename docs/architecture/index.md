# Архитектурные паттерны

> Основные принципы и шаблоны проектирования в Pristine Framework

## 📋 Содержание

1. [Entity vs Aggregate](#entity-vs-aggregate)
2. [Коллекции агрегатов](#коллекции-агрегатов)
3. [Use Cases (сценарии использования)](#use-cases-сценарии-использования)
4. [Система разрешений](#система-разрешений)
5. [Менеджеры и сервисы](#менеджеры-и-сервисы)
6. [Доменные события](#доменные-события)
7. [Формы и валидация](#формы-и-валидация)

---

## Entity vs Aggregate

**Основное правило:** Используйте Entity в качество простого контейнера для данных, Aggregate для связи с другими сущностями.

### 🔹 Когда использовать Entity

**Используйте `EntityInterface`** для:
- ✅ Простых контейнеров данных
- ✅ DTO между слоями
- ✅ Базовых классов для Aggregates
- ✅ Объектов без сложной бизнес-логики

### 🔸 Когда использовать Aggregate  

**Используйте `AggregateInterface`** для:
- ✅ Объектов с бизнес-правилами
- ✅ Управления связанными сущностями
- ✅ Инкапсуляции сложных операций

### Паттерн: Entity → Aggregate

**Шаг 1:** Создайте простую Entity
```php
use Domain\Entity\EntityInterface;

class PostEntity implements EntityInterface 
{
    private ?int $id = null;
    private string $title;
    private string $content;
    private \DateTimeImmutable $createdAt;

    // Только простые геттеры и сеттеры
    public function getId(): ?int { return $this->id; }
    public function setId(?int $id): self { $this->id = $id; return $this; }
    
    public function getTitle(): string { return $this->title; }
    public function setTitle(string $title): self { $this->title = $title; return $this; }
    
    public function getContent(): string { return $this->content; }
    public function setContent(string $content): self { $this->content = $content; return $this; }

    public function getCreatedAt(): \DateTimeImmutable { return $this->createdAt; }
    public function setCreatedAt(\DateTimeImmutable $createdAt): self { $this->createdAt = $createdAt; return $this; }
}
```

**Шаг 2:** Расширьте простую сущность до агрегата добавляя связи с другими агрегатами
```php
use Domain\Aggregate\AggregateInterface;

class Post extends PostEntity implements AggregateInterface 
{
    private UserInterface $author;
    private CommentCollection $comments;
    private CategoryCollection $categories;
    private PostStatus $status;

    public function __construct()
    {
        $this->comments = new CommentCollection();
        $this->categories = new CategoryCollection();
        $this->status = PostStatus::draft();
        $this->setCreatedAt(new \DateTimeImmutable());
    }

    /**
     * Добавление комментария с проверками
     */
    public function addComment(Comment $comment): void
    {
        if (!$this->isPublished()) {
            throw new \DomainException('Нельзя комментировать неопубликованный пост');
        }

        $this->comments->addItem($comment);
    }

    /**
     * Назначение категорий
     */
    public function setCategories(CategoryCollection $categories): self
    {
        $this->categories = new CategoryCollection();
        foreach ( $categories as $category ) {
            $this->addCategory($category);
        }
        return $this;
    }

    public function addCategory(Category $category): self
    {
        $this->categories->addItem($category);
        return $this;
    }

    public function getCategories(): CategoryCollection
    {
        return $this->categories;
    }

    public function isPublished(): bool
    {
        return $this->status->equals(PostStatus::published());
    }

    // ...
}
```

---

## Коллекции агрегатов

**Коллекции** обеспечивают типизированную работу с наборами доменных объектов.

### Реализация коллекции

Используйте `Domain\Aggregate\CollectionInterface` и трейт `CollectionTrait`:

```php
use Domain\Aggregate\CollectionInterface;

class PostCollection implements CollectionInterface
{
    use \Domain\Aggregate\CollectionTrait;

    /** @var Post[] */
    private array $items = [];

    /**
     * Получение текущего элемента
     */
    public function current(): ?Post
    {
        return $this->valid() ? $this->items[$this->position] : null;
    }

    /**
     * Получение предыдущего элемента  
     */
    public function previous(): ?Post
    {
        return $this->items[$this->position - 1] ?? null;
    }

    /**
     * Получение всей коллекции
     */
    public function getCollection(): array
    {
        return $this->items;
    }

    /**
     * Добавление элемента (без дублирования)
     */
    public function addItem(Post $item): self
    {
        if (!$this->contains($item)) {
            $this->items[] = $item;
        }
        return $this;
    }

    /**
     * Добавление нескольких элементов
     */
    public function addItems(PostCollection $items): self
    {
        foreach ($items as $item) {
            $this->addItem($item);
        }
        return $this;
    }

    /**
     * Установка элементов из итератора
     */
    public function setItems(Iterator|null $items): self
    {
        $this->items = [];
        if ($items) {
            foreach ($items as $item) {
                $this->addItem($item);
            }
        }
        return $this;
    }

    /**
     * Удаление элемента
     */
    public function removeItem(Post $item): self
    {
        $this->items = array_filter(
            $this->items,
            fn($existing) => $existing->getId() !== $item->getId()
        );
        return $this;
    }

    /**
     * Проверка наличия элемента
     */
    public function contains(Post $item): bool
    {
        if (!$item->getId()) {
            return false;
        }
        
        foreach ($this->items as $existing) {
            if ($existing->getId() === $item->getId()) {
                return true;
            }
        }
        return false;
    }

    /**
     * Количество элементов
     */
    public function count(): int
    {
        return count($this->items);
    }

    /**
     * Фильтрация опубликованных постов
     */
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

    /**
     * Поиск по автору
     */
    public function getByAuthor(int $authorId): self
    {
        $byAuthor = new self();
        foreach ($this->items as $post) {
            if ($post->getAuthorId() === $authorId) {
                $byAuthor->addItem($post);
            }
        }
        return $byAuthor;
    }
}
```

### Лучшие практики коллекций

#### ✅ Добавляйте методы фильтрации
```php
public function getByCategory(int $categoryId): self
{
    $filtered = new self();
    foreach ($this->items as $post) {
        if ($post->hasCategory($categoryId)) {
            $filtered->addItem($post);
        }
    }
    return $filtered;
}
```

#### ✅ Реализуйте методы сортировки
```php
public function sortByDate(): self
{
    $sorted = clone $this;
    usort($sorted->items, fn($a, $b) => $b->getCreatedAt() <=> $a->getCreatedAt());
    return $sorted;
}
```

#### ✅ Обеспечьте типобезопасность
```php
public function addItem(Post $item): self  // Строгая типизация
{
    // Валидация типа на уровне PHP
}
```

---

## Структура директорий

**Рекомендуемая** организация кода с ограниченными контекстами:

```
App/Blog/
├── Post/                              # Контекст управления постами
│   ├── Entity/PostEntity.php          # Простая сущность
│   ├── Aggregate/Post.php             # Агрегат с бизнес-логикой
│   ├── Aggregate/PostCollection.php   # Типизированная коллекция
│   ├── UseCase/                       # Бизнес-сценарии
│   │   ├── CreatePost.php
│   │   ├── UpdatePost.php
│   │   ├── DeletePost.php
│   │   └── GetPost.php
│   ├── Service/PostManager.php        # CRUD операции
│   ├── Event/                         # Доменные события
│   │   ├── PostCreatedEvent.php
│   │   └── PostPublishedEvent.php
│   ├── Subscriber/                    # Обработчики событий
│   │   └── PostEventSubscriber.php
│   ├── Repository/                    # Интерфейсы доступа к данным
│   │   └── PostRepositoryInterface.php
│   ├── ValueObject/                   # Объекты-значения
│   │   ├── PostStatus.php
│   │   └── BlogRole.php
│   └── Exception/                     # Доменные исключения
│       └── PostNotFoundException.php
├── Comment/                           # Контекст управления комментариями
│   ├── Entity/CommentEntity.php
│   ├── Aggregate/Comment.php
│   ├── UseCase/...
│   └── ...
└── Taxonomy/                          # Контекст таксономии (категории/теги)
    ├── Entity/
    │   ├── CategoryEntity.php
    │   └── TagEntity.php
    ├── Aggregate/
    │   ├── Category.php
    │   └── Tag.php
    └── ...
```

### Принципы организации

#### ✅ Разделение по контекстам
- Каждый контекст содержит связанную функциональность
- Минимальные зависимости между контекстами
- Четкие границы ответственности

#### ✅ Слоистая структура
- **Entity** - данные
- **Aggregate** - бизнес-логика  
- **UseCase** - сценарии использования
- **Service** - вспомогательные сервисы
- **Repository** - доступ к данным

---

## EntityManager vs UseCase

**EntityManager** отвечает за базовые CRUD операции с сущностью, **UseCase** - за бизнес-сценарии.

### EntityManager (Менеджер сущностей)

**Назначение:**
- ✅ Базовые CRUD операции
- ✅ Единственная точка взаимодействия с репозиторием (Repository)
- ✅ Простая логика без бизнес-правил
- ✅ Техническая абстракция над хранилищем

#### Пример PostManager:
```php
class PostManager 
{
    private PostRepositoryInterface $repository;

    public function __construct(PostRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }
    
    /**
     * Сохранение поста (создание/обновление)
     */
    public function persist(Post $post): Post
    {
        return $this->repository->save($post);
    }
    
    /**
     * Поиск по ID
     */
    public function findById(int $id): ?Post
    {
        return $this->repository->findById($id);
    }

    /**
     * Получение коллекции постов
     */
    public function find(array $criteria = []): PostCollection
    {
        return $this->repository->findByCriteria($criteria);
    }

    /**
     * Удаление поста
     */
    public function delete(Post $post): void
    {
        $this->repository->delete($post);
    }

    /**
     * Подсчет количества
     */
    public function count(array $criteria = []): int
    {
        return $this->repository->count($criteria);
    }
}
```

### UseCase (Сценарии использования)

**Назначение:**
- ✅ Реализация простых бизнес-сценариев
- ✅ Проверка разрешений и валидация
- ✅ Координация между агрегатами
- ✅ Генерация доменных событий
- ✅ Обработка сложной логики

#### Базовые UseCase для каждой сущности:

1. **CreateEntity** - Создание с проверками
2. **UpdateEntity** - Обновление с валидацией  
3. **DeleteEntity** - Удаление с проверкой прав
4. **GetEntity** - Получение с разрешениями
5. **GetEntityCollection** - Поиск с фильтрацией
6. **EntityPermissions** - Проверка разрешений

#### Пример CreatePost UseCase:
```php
class CreatePost
{
    private PostManager $postManager;
    private UserRepositoryInterface $userRepository;
    private EventDispatcherInterface $eventDispatcher;

    public function __construct(
        PostManager $postManager,
        UserRepositoryInterface $userRepository,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->postManager = $postManager;
        $this->userRepository = $userRepository;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * Создание поста с полной валидацией
     */
    public function execute(
        string $title,
        string $content,
        int $authorId,
        array $categoryIds = []
    ): Post {
        // 1. Проверка разрешений
        $this->checkPermissions($authorId);
        
        // 2. Валидация данных
        $this->validateData($title, $content);
        
        // 3. Создание агрегата
        $post = new Post();
        $post->setTitle($title);
        $post->setContent($content);
        $post->setAuthorId($authorId);
        
        // 4. Назначение категорий
        if (!empty($categoryIds)) {
            $categories = $this->getCategoriesByIds($categoryIds);
            $post->assignCategories($categories);
        }
        
        // 5. Сохранение через менеджер
        $savedPost = $this->postManager->persist($post);
        
        // 6. Обработка событий
        $this->eventDispatcher->dispatch(
            new PostCreatedEvent($savedPost->getId(), $authorId)
        );
        
        return $savedPost;
    }

    private function checkPermissions(int $authorId): void
    {
        $user = $this->userRepository->findById($authorId);
        if (!$user) {
            throw new \DomainException('Пользователь не найден');
        }

        if (!$user->canCreatePosts()) {
            throw new \DomainException('Недостаточно прав для создания постов');
        }
    }

    private function validateData(string $title, string $content): void
    {
        if (empty(trim($title))) {
            throw new \InvalidArgumentException('Заголовок не может быть пустым');
        }

        if (empty(trim($content))) {
            throw new \InvalidArgumentException('Содержимое не может быть пустым');
        }

        if (strlen($title) > 255) {
            throw new \InvalidArgumentException('Заголовок слишком длинный');
        }
    }
}
```

### Взаимодействие Manager ↔ UseCase

```php
// ❌ Плохо: UseCase напрямую обращается к Repository
class CreatePost {
    public function execute($data): Post {
        return $this->repository->save($post); // Плохо!
    }
}

// ✅ Хорошо: UseCase использует Manager
class CreatePost {
    public function execute($data): Post {
        $post = new Post($data);
        return $this->postManager->persist($post); // Хорошо!
    }
}
```

Пример сценария создания поста `CreatePost`:
```php
class CreatePost
{
    /**
     * @param Post $post
     * @param bool $raiseEvents where to raise Domain Events
     * @return Post
     */
    public function create(Post $post, bool $raiseEvents = true): Post
    {
        $this->checkPermissions($post);
		(new PostForm())->validate($post);
		$post = ( new PostManager() )->persist($post);
		if ( $raiseEvents ) {
		    Publisher::getInstance()->publish(new PostCreatedEvent($post));
		}
		return $post;
    }

    public function checkPermissions(?Post $post = null): void
	{
		( new PostPermissions() )->canCreate($post);
	}
}
```

Пример класса отвечающего за права доступа `PostPermissions` class:

```php
namespace App\Blog\Post\UseCase;

class PostPermissions
{
	/**
	 * @param ?Post $post
	 * @return void
	 * @throws NotAuthorizedException|NotPermittedException
	 */
	public function canCreate(?Post $post = null): void
	{
		if ( !$user = (new GetCurrentUser())->get() ) {
			throw new NotAuthorizedException();
		}
        
        if ( $user->in(Role::AUTHOR) ) {
            return;
        }
        
        throw new NotPermittedException();
	}

	/**
	 * @param ?Post $post
	 * @return bool
	 */
	public function checkCanCreate(?Post $post = null): bool
	{
		try {
			$this->canCreate($post);
		} catch (Exception) {
			return false;
		}
		return true;
	}

	/**
	 * @param ?Post $post
	 * @return void
	 * @throws NotAuthorizedException|NotPermittedException
	 */
	public function canUpdate(?Post $post = null): void
	{
		if ( !$user = (new GetCurrentUser())->get() ) {
			throw new NotAuthorizedException();
		}
        
        // Authors can only edit their own posts
        if ( $user->in(Role::AUTHOR) && $post->getAuthor()->is($user->getId()) ) {
            return;
        }
        
        // Editors can edit any post
        if ( $user->in(Role::EDITOR) ) {
            return;
        }
        
        throw new NotPermittedException();
	}

	/**
	 * @param ?Post $post
	 * @return bool
	 */
	public function checkCanUpdate(?Post $post = null): bool
	{
		try {
			$this->canUpdate($post);
		} catch (Exception) {
			return false;
		}
		return true;
	}

	/**
	 * @param ?Post $post
	 * @return void
	 * @throws NotAuthorizedException|NotPermittedException
	 */
	public function canDelete(?Post $post = null): void
	{
		if ( !$user = (new GetCurrentUser())->get() ) {
			throw new NotAuthorizedException();
		}
        
        // Only editors and admins can delete
        if ($user->in(Role::EDITOR) || $user->in(Role::ADMIN)) {
            return;
        }
        
        throw new NotPermittedException();
	}

	/**
	 * @param ?Post $post
	 * @return bool
	 */
	public function checkCanDelete(?Post $post = null): bool
	{
		try {
			$this->canDelete($post);
		} catch (Exception) {
			return false;
		}
		return true;
	}

	/**
	 * @param ?Post $post
	 * @return void
	 * @throws NotAuthorizedException|NotPermittedException
	 */
	public function canGet(?Post $post): void
	{
        $user = ( new GetCurrentUser() )->get();

        // you can check specific $post properties value to make decision
        if ( $post->isDraft() && !$user?->in(Role::AUTHOR) ) {
            throw new NotPermittedExeption();
        }
        // in this case every user (even unauthorizated) 
		return;
	}

	/**
	 * @param ?Post $post
	 * @return bool
	 */
	public function checkCanGet(?Post $post): bool
	{
		try {
			$this->canGet($post);
		} catch (Exception) {
			return false;
		}
		return true;
	}

	/**
	 * @param ?GetPostCollection $service
	 * @return void
	 * @throws NotAuthorizedException|NotPermittedException
	 */
	public function canGetCollection(?GetPostCollection $service = null): void
	{
		if ( !$user = ( new GetCurrentUser() )->get() ) {
			throw new NotAuthorizedException();
		}
        
        if ( !$user?->in(Role::AUTHOR) ) {
            // unauthorizated users can only access published blog posts
            $service->filterByDraft(false);
        }
        
        return;
	}

	/**
	 * @param ?GetPostCollection $service
	 * @return bool
	 */
	public function checkCanGetCollection(?GetPostCollection $service = null): bool
	{
		try {
			$this->canGetCollection($service);
		} catch (Exception) {
			return false;
		}
		return true;
	}
}
```

## Сервисы
Непосредственно за бизнес-логику отвечают сервисы (Services). В то время как UseCases отвечают за простейшие типовые операции (создание, обновление, удаление, получение сущностей).

## Dealing with forms
A suggested way for dealing with forms is to have a separate class i.e. `PostForm` which is responsible for restoring and validating an Aggregate from a form.

### Entity Form class example
Notice that class constants are recomended for usign in form inputs name attribute (so use snake_case notation for constants values):
```html
<input 
    type="hidden" 
    name="<?=htmlspecialchars(PostForm::ID)?>" 
    value="<?=htmlspecialchars((string)$post?->getId())?>" 
/>
```

A form example:
```php
namespace App\Blog\Post\Presentation;

/**
 * PostForm DTO and Validation
 */
class PostForm
{
	public const ID = 'id';
	public const TITLE = 'title';
    public const CONTENT = 'content';
    public const AUTHOR = 'author';
    public const POST_DATE = 'post_date';
    public const CATEGORY = 'category';
    public const TAGS = 'tags';
    public const COMMENTS = 'comments';

	/**
	 * @param array $data
	 * @param ?Post $post
	 * @return Post
	 * @throws Exception
	 */
	public function convertToEntity(array $data, ?Post $post = null): Post
	{
		$post = $post ?? new Post();

		if ( array_key_exists(self::ID , $data) && 0 < (int)$data[self::ID] ) {
			$post->setId((int)$data[self::ID]);
		}

		if ( array_key_exists(self::TITLE, $data) ) {
			$post->setTitle((string)$data[self::TITLE]);
		}

		if ( array_key_exists(self::AUTHOR, $data) ) {
		    $author = ( new GetUser() )->get((int)$data[self::AUTHOR]);
			$post->setAuthor($author);
		}

        // ...

		return $post;
	}

	/**
	 * @throws InvalidArgumentException
	 */
	public function validate(Post $post): void
	{
        // You can use a separate class for a complex validation logic
		//( new ValidatePostForm() )->validate($post);
        // Or implement validation here
        if ( empty($post->getTitle()) ) {
            throw new InvalidArgumentException('Post title should not be empty');
        }
	}

	/**
	 * Returns class constants
	 * @return array<string, string>
	 */
	public static function getMap(): array
	{
		$reflection = new ReflectionClass(__CLASS__);
		return $reflection->getConstants();
	}
}
```

## Repository design

It is suggested to break Entity Repository into to classes: `EntityRepositoryProxy` and `EntityRepository`. Where a proxy is only responsible for data transfer operations and the repository is responsible for the rest.

### Examples

A `PostProxy` example:
```php
class PostProxy extends Repository implements ProxyInterface
{
    public function entityToOrmObject(Post $post)
    {
        // implementation depends on the used ORM
    }

    public function objectToEntity(mixed $object): Post
    {
        $post = new Post();

        if ( null !== $id = $object->get(PostRepositoryInterface::ID) ) {
            $post->setId((int)$id);
        }

        // ...

        if ( (new CategoryPermission())->checkCanGetCollection() ) {
            if ( null !== $categories = $object->get(PostRepositoryInterface::CATEGORIES) ) {
                $post->setCategories( (new CategoryProxy())->objectToEntity($categories)  )
            }
        }

        // ...

        return $post;
    }
}
```

A `PostRepository` example:

```php
class PostRepository extends PostProxy implements RepositoryInterface
{
    public FilterInterface|PostFilter|null $filter = null;
	public SortInterface|PostSort|null $sort = null;
    public LimitInterface|PostLimit|null $limit = null;
	public FieldsInterface|PostFields|null $fields;
	public GroupInterface|PostGroup|null $group;

	public function __construct(?ServiceInterface $service = null)
	{
		$this->filter   = new PostFilter($service);
		$this->sort     = new PostSort($service);
		$this->limit    = new PostLimit($service);
		$this->fields   = new PostFields($service);
		$this->group    = new PostGroup($service);
	}

    public function findById(int $id): ?Post
    {
        // ...
    }

    public function find(): ?PostCollection
    {
        // ...
    }

    public function persist(Post $post): Post
    {
        // ...
    }

    // ...
}
```

## Cross-boundary communication

### Restoring aggregates from persistence layer
As an aggregate may consist of another aggregates we need somehow to restore and hydrate it from persistance layer i.e. a database. This should be done in Repository by calling another aggregates managers. But this depends heavy on repository type or choosen ORM.

### Event based communication
Boundaries should communicate with each other by Domain Events. One boundary (UseCase or Service) raises events. Another boundaries may subscribe to this events implementing Subscribers.
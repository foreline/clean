# Часто задаваемые вопросы (FAQ)

> Ответы на популярные вопросы о Pristine Framework и Clean Architecture

## 🤔 Общие вопросы

### В чем отличие Pristine от Symfony/Laravel?

**Pristine** - это не полнофункциональный фреймворк, а набор интерфейсов и базовых классов для структурирования кода по принципам Clean Architecture.

| Аспект | Symfony/Laravel | Pristine Framework |
|--------|-----------------|-------------------|
| **Назначение** | Полнофункциональные веб-фреймворки | Архитектурный каркас |
| **Размер** | Большие, много компонентов | Минимальный, только интерфейсы |
| **Гибкость** | Определенная структура проекта | Полная свобода архитектуры |
| **Интеграция** | Замена текущего фреймворка | Дополнение к существующему коду |
| **Обучение** | Изучение фреймворка | Изучение принципов архитектуры |

### Можно ли использовать Pristine с существующими проектами?

**Да!** Pristine специально разработан для интеграции с любыми существующими системами:

```php
// Пример интеграции с Laravel
class LaravelUserRepository implements UserRepositoryInterface
{
    public function save(User $user): User
    {
        // Использование Eloquent ORM
        $model = EloquentUser::updateOrCreate(
            ['id' => $user->getId()],
            $user->toArray()
        );
        
        return $this->mapToAggregate($model);
    }
}
```

### Зачем разделять Entity и Aggregate?

**Entity** - простой контейнер данных, **Aggregate** - бизнес-логика:

```php
// ❌ Плохо: все в одном классе
class User {
    private string $email;
    
    // Простые геттеры/сеттеры + бизнес-логика
    public function setEmail(string $email) { ... }
    public function sendWelcomeEmail() { ... }
    public function calculatePermissions() { ... }
}

// ✅ Хорошо: разделение ответственности  
class UserEntity {
    private string $email;
    // Только геттеры/сеттеры
}

class User extends UserEntity {
    // Только бизнес-логика
    public function changeEmail(string $email) { ... }
    public function sendWelcomeEmail() { ... }
}
```

## 🏗️ Архитектурные вопросы

### Где размещать валидацию данных?

**Принцип:** Валидация размещается в том слое, который отвечает за конкретную ответственность:

```php
// ✅ Value Object - валидация формата
class Email {
    public function __construct(string $value) {
        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException('Неверный формат email');
        }
    }
}

// ✅ Use Case - бизнес-валидация
class CreateUser {
    public function execute(string $email, string $name): User {
        // Проверка уникальности email - это бизнес-правило
        if ($this->userRepository->findByEmail($email)) {
            throw new \DomainException('Email уже используется');
        }
    }
}

// ✅ Presentation - валидация ввода
class UserController {
    public function create(Request $request) {
        // Проверка обязательных полей
        if (empty($request->email) || empty($request->name)) {
            throw new \InvalidArgumentException('Заполните все поля');
        }
    }
}
```

### Как организовать связи между Aggregates?

**Правило:** Aggregates связываются только через ID, а не через объекты:

```php
// ❌ Плохо: прямые ссылки на объекты
class Post {
    private User $author;           // Плохо!
    private CategoryCollection $categories; // Плохо!
}

// ✅ Хорошо: ссылки через ID
class Post {
    private int $authorId;
    private array $categoryIds;
    
    // Получение связанных объектов через репозитории
    public function getAuthor(UserRepositoryInterface $repo): User {
        return $repo->findById($this->authorId);
    }
}
```

### Где размещать сложную бизнес-логику?

**Варианты размещения** в порядке предпочтения:

1. **В методах Aggregate** - если логика касается одного агрегата
2. **В Domain Service** - если логика затрагивает несколько агрегатов
3. **В Use Case** - если логика специфична для конкретного сценария

```php
// 1. В Aggregate - простая логика
class Post {
    public function publish(): void {
        if (empty($this->title) || empty($this->content)) {
            throw new \DomainException('Пост должен иметь заголовок и содержимое');
        }
        $this->status = PostStatus::PUBLISHED;
    }
}

// 2. В Domain Service - сложная логика нескольких агрегатов
class BlogPostService {
    public function transferPost(Post $post, User $newAuthor): void {
        // Сложная логика с несколькими агрегатами
    }
}

// 3. В Use Case - сценарий-специфичная логика
class PublishPost {
    public function execute(int $postId, int $userId): void {
        // Логика публикации с проверками разрешений
    }
}
```

## 🔧 Практические вопросы

### Как обрабатывать ошибки?

**Стратегия обработки ошибок** по слоям:

```php
// Domain слой - доменные исключения
class User {
    public function changeEmail(string $email): void {
        if ($this->isEmailTaken($email)) {
            throw new \DomainException('Email уже занят');
        }
    }
}

// Use Case - бизнес исключения
class CreateUser {
    public function execute(string $email): User {
        try {
            return $this->userRepository->save($user);
        } catch (RepositoryException $e) {
            throw new \DomainException('Не удалось создать пользователя', 0, $e);
        }
    }
}

// Presentation - HTTP исключения
class UserController {
    public function create(Request $request) {
        try {
            $user = $this->createUser->execute($request->email);
            return new JsonResponse($user->toArray());
        } catch (\DomainException $e) {
            return new JsonResponse(['error' => $e->getMessage()], 400);
        }
    }
}
```

### Как тестировать Use Cases?

**Используйте мок-объекты** для репозиториев:

```php
class CreateUserTest extends TestCase {
    public function testCreateUser(): void {
        // Arrange
        $repository = $this->createMock(UserRepositoryInterface::class);
        $repository->expects($this->once())
                  ->method('save')
                  ->willReturnArgument(0);
                  
        $useCase = new CreateUser($repository);
        
        // Act
        $user = $useCase->execute('test@example.com', 'Test User');
        
        // Assert
        $this->assertEquals('test@example.com', $user->getEmail());
        $this->assertEquals('Test User', $user->getName());
    }
}
```

### Как интегрироваться с ORM?

**Реализуйте Repository** через адаптер:

```php
// Интерфейс остается чистым
interface UserRepositoryInterface {
    public function save(User $user): User;
    public function findById(int $id): ?User;
}

// Реализация через Doctrine ORM
class DoctrineUserRepository implements UserRepositoryInterface {
    private EntityManagerInterface $em;
    
    public function save(User $user): User {
        $entity = $this->mapToDoctrineEntity($user);
        $this->em->persist($entity);
        $this->em->flush();
        
        return $this->mapToAggregate($entity);
    }
    
    private function mapToAggregate(DoctrineUser $entity): User {
        $user = new User();
        $user->setId($entity->getId());
        $user->setEmail($entity->getEmail());
        return $user;
    }
}
```

## 🚀 Продвинутые вопросы

### Как масштабировать приложение?

**Стратегии масштабирования:**

1. **Разделение на ограниченные контексты** - каждый модуль независим
2. **Event-driven архитектура** - слабая связанность через события
3. **CQRS** - разделение команд и запросов
4. **Микросервисы** - выделение контекстов в отдельные сервисы

### Как работать с транзакциями?

**Используйте Unit of Work** паттерн:

```php
class CreatePostWithCategories {
    public function execute(array $postData, array $categoryIds): Post {
        $this->unitOfWork->begin();
        
        try {
            $post = $this->postRepository->save(new Post($postData));
            $categories = $this->categoryRepository->findByIds($categoryIds);
            $post->assignCategories($categories);
            
            $this->unitOfWork->commit();
            return $post;
        } catch (\Exception $e) {
            $this->unitOfWork->rollback();
            throw $e;
        }
    }
}
```

### Как реализовать кэширование?

**Декоратор** для Repository:

```php
class CachedUserRepository implements UserRepositoryInterface {
    private UserRepositoryInterface $repository;
    private CacheInterface $cache;
    
    public function findById(int $id): ?User {
        $key = "user_{$id}";
        
        if ($cached = $this->cache->get($key)) {
            return $cached;
        }
        
        $user = $this->repository->findById($id);
        $this->cache->set($key, $user, 3600);
        
        return $user;
    }
}
```

---

## Не нашли ответ?

- 📖 **[Изучите документацию](./index.md)** - возможно, ответ есть в других разделах
- 🛠️ **[Посмотрите примеры](./examples/index.md)** - готовые решения типовых задач  
- 🎓 **[Пройдите туториал](./tutorial/index.md)** - пошаговое изучение на практике
- 📝 **[Создайте issue](https://gitlab.foreline.ru/foreline/clean/-/issues)** - задайте вопрос разработчикам

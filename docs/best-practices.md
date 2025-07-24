# Лучшие практики

> Проверенные временем рекомендации для разработки с Pristine Framework

## 🎯 Общие принципы

### 1. Следуйте принципу единственной ответственности
Каждый класс должен иметь только одну причину для изменения:

```php
// ❌ Плохо: класс делает слишком много
class User {
    public function save() { /* работа с БД */ }
    public function sendEmail() { /* отправка писем */ }
    public function validateData() { /* валидация */ }
    public function generateReport() { /* отчеты */ }
}

// ✅ Хорошо: разделение ответственности
class User { /* только данные и бизнес-логика */ }
class UserRepository { /* только работа с БД */ }
class EmailService { /* только отправка писем */ }
class UserValidator { /* только валидация */ }
```

### 2. Программируйте на интерфейсы, не на реализации
```php
// ❌ Плохо: зависимость от конкретного класса
class CreateUser {
    private DoctrineUserRepository $repository;
}

// ✅ Хорошо: зависимость от интерфейса
class CreateUser {
    private UserRepositoryInterface $repository;
}
```

### 3. Используйте инъекцию зависимостей
```php
// ❌ Плохо: создание зависимостей внутри класса
class CreateUser {
    public function execute(): User {
        $repository = new UserRepository(); // Плохо!
        return $repository->save($user);
    }
}

// ✅ Хорошо: инъекция через конструктор
class CreateUser {
    public function __construct(
        private UserRepositoryInterface $repository
    ) {}
    
    public function execute(): User {
        return $this->repository->save($user);
    }
}
```

---

## 🏗️ Доменный слой

### Entity vs Aggregate: Четкое разделение

#### ✅ Entity - только данные
```php
class PostEntity implements EntityInterface
{
    private ?int $id = null;
    private string $title;
    private string $content;
    
    // Только геттеры и сеттеры
    public function getTitle(): string { return $this->title; }
    public function setTitle(string $title): self { $this->title = $title; return $this; }
}
```

#### ✅ Aggregate - бизнес-логика
```php
class Post extends PostEntity implements AggregateInterface
{
    private PostStatus $status;
    private CommentCollection $comments;
    
    // Бизнес-методы
    public function publish(): void {
        $this->validateCanPublish();
        $this->status = PostStatus::published();
        $this->raiseEvent(new PostPublishedEvent($this->getId()));
    }
    
    public function addComment(Comment $comment): void {
        if (!$this->isPublished()) {
            throw new \DomainException('Нельзя комментировать неопубликованный пост');
        }
        $this->comments->addItem($comment);
    }
}
```

### Value Objects: Типизируйте примитивы

#### ❌ Избегайте primitive obsession
```php
// Плохо: везде строки и числа
class User {
    private string $email;        // А что если невалидный?
    private string $status;       // А какие статусы бывают?
    private float $balance;       // А в какой валюте?
}
```

#### ✅ Используйте Value Objects
```php
class User {
    private Email $email;         // Гарантированно валидный
    private UserStatus $status;   // Ограниченный набор значений
    private Money $balance;       // С указанием валюты
}

class Email implements StringValueObjectInterface {
    public function __construct(private string $value) {
        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException('Некорректный email');
        }
    }
    
    public function getValue(): string { return $this->value; }
}
```

### Repository: Правильное проектирование

#### ✅ Выразительные методы
```php
interface PostRepositoryInterface
{
    // Четкие имена методов
    public function findPublishedByAuthor(int $authorId): PostCollection;
    public function findByTagAndCategory(string $tag, int $categoryId): PostCollection;
    public function findMostPopular(int $limit = 10): PostCollection;
    
    // Не слишком общие методы
    public function findByCriteria(array $criteria): PostCollection; // ❌ Слишком общий
}
```

#### ✅ Работа с агрегатами, не с Entity
```php
interface PostRepositoryInterface
{
    public function save(Post $post): Post;           // ✅ Агрегат
    public function findById(int $id): ?Post;         // ✅ Агрегат
    
    // Не так:
    public function save(PostEntity $entity): PostEntity; // ❌ Entity
}
```

### Use Cases: Один сценарий = один класс

#### ✅ Focused Use Cases
```php
// Хорошо: конкретный сценарий
class PublishPost {
    public function execute(int $postId, int $userId): Post {
        // Логика публикации поста
    }
}

class UnpublishPost {
    public function execute(int $postId, int $userId): Post {
        // Логика отмены публикации
    }
}
```

#### ❌ Избегайте универсальных Use Cases
```php
// Плохо: слишком общий Use Case
class ManagePost {
    public function execute(string $action, int $postId, array $data): Post {
        switch ($action) {
            case 'publish': /* ... */
            case 'unpublish': /* ... */
            case 'update': /* ... */
            // Нарушает принцип единственной ответственности
        }
    }
}
```

### Доменные события: Слабая связанность

#### ✅ Используйте события для развязки
```php
class Post {
    public function publish(): void {
        $this->status = PostStatus::published();
        
        // Событие вместо прямого вызова
        $this->raiseEvent(new PostPublishedEvent($this->getId()));
        
        // НЕ вызывайте напрямую:
        // $this->emailService->sendNotification(); // ❌
        // $this->cacheService->invalidate();       // ❌
    }
}

// Обработчик события
class PostPublishedEventSubscriber {
    public function handle(PostPublishedEvent $event): void {
        $this->emailService->sendNotification($event->getPostId());
        $this->cacheService->invalidatePost($event->getPostId());
    }
}
```

---

## 🔧 Инфраструктурный слой

### Repository реализации: Правильное маппирование

#### ✅ Четкое разделение Entity и Aggregate
```php
class DoctrinePostRepository implements PostRepositoryInterface
{
    public function save(Post $post): Post {
        // 1. Маппинг Aggregate → Entity для Doctrine
        $entity = $this->mapToDoctrineEntity($post);
        
        // 2. Сохранение через ORM
        $this->em->persist($entity);
        $this->em->flush();
        
        // 3. Маппинг Entity → Aggregate для возврата
        return $this->mapToAggregate($entity);
    }
    
    private function mapToDoctrineEntity(Post $post): PostEntity {
        // Конверсия агрегата в Doctrine Entity
    }
    
    private function mapToAggregate(PostEntity $entity): Post {
        // Конверсия Doctrine Entity в агрегат
        // + загрузка связанных данных
    }
}
```

#### ✅ Используйте Data Mapper, не Active Record
```php
// ✅ Хорошо: Data Mapper pattern
class Post extends PostEntity {
    // НЕТ методов для работы с БД
    public function publish(): void { /* только бизнес-логика */ }
}

class PostRepository {
    public function save(Post $post): Post { /* работа с БД */ }
}

// ❌ Плохо: Active Record pattern
class Post {
    public function save(): void { /* БД логика в доменной модели */ }
    public function delete(): void { /* нарушает принципы */ }
}
```

### Интеграция с ORM: Лучшие практики

#### ✅ Используйте отдельные Entity для ORM
```php
// Доменная модель
namespace App\Domain\Post;
class Post extends PostEntity implements AggregateInterface { }

// ORM модель  
namespace App\Infrastructure\Doctrine\Entity;
/**
 * @Entity
 * @Table(name="posts")
 */
class PostEntity {
    /** @Id @GeneratedValue @Column(type="integer") */
    private int $id;
    
    /** @Column(type="string") */
    private string $title;
}
```

#### ✅ Изолируйте маппинг в методах
```php
class DoctrinePostRepository {
    private function mapToAggregate(DoctrinePostEntity $entity): Post {
        $post = new Post();
        $post->setId($entity->getId());
        $post->setTitle($entity->getTitle());
        
        // Ленивая загрузка связанных данных
        $this->loadComments($post);
        $this->loadCategories($post);
        
        return $post;
    }
}
```

---

## 🌐 Слой представления

### HTTP Controllers: Тонкий слой

#### ✅ Минимальная логика в контроллерах
```php
class PostController {
    public function create(Request $request): Response {
        try {
            // 1. Извлечение данных
            $data = $this->extractCreateData($request);
            
            // 2. Валидация (базовая)
            $this->validateCreateData($data);
            
            // 3. Вызов Use Case
            $post = $this->createPost->execute(
                $data['title'],
                $data['content'], 
                $request->getUser()->getId()
            );
            
            // 4. Формирование ответа
            return ResponseFactory::success($post->toArray(), 201);
            
        } catch (\DomainException $e) {
            return ResponseFactory::error($e->getMessage(), 422);
        }
    }
    
    // НЕТ бизнес-логики в контроллере!
}
```

#### ❌ Избегайте толстых контроллеров
```php
class PostController {
    public function create(Request $request): Response {
        // ❌ Много логики в контроллере
        $title = $request->get('title');
        if (empty($title)) { /* валидация */ }
        
        $author = $this->userRepository->findById($request->getUserId());
        if (!$author) { /* проверки */ }
        
        if (!$author->canCreatePosts()) { /* авторизация */ }
        
        $post = new Post();
        $post->setTitle($title);
        // ... много кода ...
        
        // Вся эта логика должна быть в Use Case!
    }
}
```

### Response форматирование: Стандартизация

#### ✅ Единообразные форматы ответов
```php
class ResponseFactory {
    public static function success(array $data, int $status = 200): JsonResponse {
        return new JsonResponse([
            'success' => true,
            'data' => $data,
            'timestamp' => time()
        ], $status);
    }
    
    public static function error(string $message, int $status = 400): JsonResponse {
        return new JsonResponse([
            'success' => false,
            'error' => $message,
            'timestamp' => time()
        ], $status);
    }
}
```

### Валидация: Разделение по слоям

#### ✅ Разные типы валидации в разных местах
```php
// Presentation: валидация ввода
class PostController {
    private function validateInput(array $data): void {
        if (empty($data['title'])) {
            throw new \InvalidArgumentException('Заголовок обязателен');
        }
    }
}

// Domain: бизнес-валидация
class Post {
    public function publish(): void {
        if (empty($this->title) || empty($this->content)) {
            throw new \DomainException('Пост должен иметь заголовок и содержимое');
        }
    }
}

// Infrastructure: техническая валидация
class PostRepository {
    public function save(Post $post): Post {
        if ($post->getId() && !$this->exists($post->getId())) {
            throw new \RuntimeException('Пост не найден в БД');
        }
    }
}
```

---

## 🚀 Архитектурные паттерны

### CQRS: Разделение команд и запросов

#### ✅ Разные модели для чтения и записи
```php
// Команды (изменение состояния)
class CreatePostCommand {
    public function execute(CreatePostData $data): Post {
        // Полная доменная модель с валидацией
    }
}

// Запросы (только чтение)
class GetPostsQuery {
    public function execute(PostFilter $filter): PostReadModel[] {
        // Упрощенная модель для чтения
        return $this->readRepository->findByFilter($filter);
    }
}

// Разные модели
class Post extends PostEntity { /* полная модель */ }
class PostReadModel { /* упрощенная модель для чтения */ }
```

### Event Sourcing: Сохранение истории изменений

#### ✅ Восстановление состояния из событий
```php
class Post {
    private array $events = [];
    
    public static function fromEvents(array $events): self {
        $post = new self();
        foreach ($events as $event) {
            $post->applyEvent($event);
        }
        return $post;
    }
    
    private function applyEvent(DomainEventInterface $event): void {
        match($event::class) {
            PostCreatedEvent::class => $this->applyPostCreated($event),
            PostPublishedEvent::class => $this->applyPostPublished($event),
            // ...
        };
    }
}
```

---

## 🧪 Тестирование

### Unit тесты: Изоляция зависимостей

#### ✅ Мокирование внешних зависимостей
```php
class CreatePostTest extends TestCase {
    public function testCreatePost(): void {
        // Arrange
        $repository = $this->createMock(PostRepositoryInterface::class);
        $repository->expects($this->once())
                  ->method('save')
                  ->willReturnArgument(0);
        
        $useCase = new CreatePost($repository);
        
        // Act
        $post = $useCase->execute('Title', 'Content', 1);
        
        // Assert
        $this->assertEquals('Title', $post->getTitle());
    }
}
```

### Интеграционные тесты: Реальные компоненты

#### ✅ Тестирование реальной инфраструктуры
```php
class DoctrinePostRepositoryTest extends TestCase {
    private EntityManagerInterface $em;
    private PostRepositoryInterface $repository;
    
    protected function setUp(): void {
        $this->em = $this->createTestEntityManager();
        $this->repository = new DoctrinePostRepository($this->em);
    }
    
    public function testSaveAndFind(): void {
        $post = new Post();
        $post->setTitle('Test Post');
        
        $savedPost = $this->repository->save($post);
        $foundPost = $this->repository->findById($savedPost->getId());
        
        $this->assertEquals('Test Post', $foundPost->getTitle());
    }
}
```

---

## ⚠️ Частые ошибки

### 1. Смешивание слоев
```php
// ❌ Плохо: HTTP в доменном слое
class Post {
    public function notify(): void {
        $response = file_get_contents('http://api.example.com/notify');
    }
}

// ✅ Хорошо: через интерфейс
class Post {
    public function notify(NotificationServiceInterface $service): void {
        $service->send(new PostNotification($this));
    }
}
```

### 2. Толстые Use Cases
```php
// ❌ Плохо: слишком много логики
class CreatePost {
    public function execute($data): Post {
        // 100+ строк кода
        // Валидация + создание + связи + события + уведомления
    }
}

// ✅ Хорошо: декомпозиция
class CreatePost {
    public function execute($data): Post {
        $this->validator->validate($data);
        $post = $this->factory->create($data);
        $this->manager->save($post);
        $this->eventDispatcher->dispatch(new PostCreatedEvent($post));
        return $post;
    }
}
```

### 3. Анемичная доменная модель
```php
// ❌ Плохо: только геттеры/сеттеры
class Post {
    public function setStatus(string $status): void { $this->status = $status; }
}

// ✅ Хорошо: выразительные бизнес-методы
class Post {
    public function publish(): void {
        $this->validateCanPublish();
        $this->status = PostStatus::published();
        $this->raiseEvent(new PostPublishedEvent($this->id));
    }
}
```

---

## 📊 Метрики и мониторинг

### Отслеживайте качество архитектуры

#### ✅ Метрики для контроля
- **Связанность**: количество зависимостей между модулями
- **Сплоченность**: насколько тесно связаны элементы внутри модуля
- **Цикломатическая сложность**: количество путей выполнения в методе
- **Покрытие тестами**: процент протестированного кода

#### ✅ Используйте инструменты анализа
```bash
# PHPStan для статического анализа
./vendor/bin/phpstan analyse src --level=8

# PHPUnit для покрытия тестами
./vendor/bin/phpunit --coverage-html coverage/

# PHP_CodeSniffer для стиля кода
./vendor/bin/phpcs src --standard=PSR12
```

---

## 🎓 Эволюция архитектуры

### Постепенное улучшение

#### ✅ Рефакторинг по мере роста
1. **Начните с простого** - Entity + Repository + Use Case
2. **Добавляйте сложность постепенно** - Events, CQRS, DDD patterns
3. **Измеряйте эффект** - покрытие тестами, время разработки
4. **Адаптируйтесь** - не все паттерны нужны всегда

#### ✅ Признаки необходимости рефакторинга
- Сложно добавлять новые фичи
- Высокая связанность между модулями  
- Дублирование логики в разных местах
- Сложно тестировать компоненты изолированно

---

**Помните:** Clean Architecture - это средство, а не цель. Цель - создавать поддерживаемые и расширяемые приложения!

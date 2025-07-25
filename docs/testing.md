# Руководство по тестированию

> Полное руководство по тестированию приложений на Pristine Framework

## 📋 Содержание

- [Стратегия тестирования](#стратегия-тестирования)
- [Модульные тесты](#модульные-тесты)
- [Интеграционные тесты](#интеграционные-тесты)
- [Тестирование доменного слоя](#тестирование-доменного-слоя)
- [Тестирование Use Cases](#тестирование-use-cases)
- [Тестирование событий](#тестирование-событий)
- [Моки и заглушки](#моки-и-заглушки)
- [Тестовые данные](#тестовые-данные)
- [CI/CD](#cicd)

---

## 🎯 Стратегия тестирования

### Пирамида тестирования

```
    /\
   /  \  E2E (UI/API) - 10%
  /____\
 /      \
/        \ Integration - 20%
/__________\
|          |
|   Unit   | Unit Tests - 70%
|__________|
```

### Покрытие по слоям

1. **Доменный слой**: 90-95% покрытие
   - Value Objects: 100%
   - Aggregates: 90%
   - Collections: 85%

2. **Use Cases**: 85-90% покрытие
   - Happy path: 100%
   - Error cases: 80%

3. **Infrastructure**: 70-80% покрытие
   - Repositories: 80%
   - External integrations: 70%

## 🧪 Модульные тесты

### Тестирование Value Objects

```php
use PHPUnit\Framework\TestCase;

class EmailTest extends TestCase
{
    public function testValidEmailCreation(): void
    {
        $email = new Email('user@example.com');
        
        $this->assertEquals('user@example.com', $email->getValue());
    }

    public function testInvalidEmailThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Некорректный email адрес');
        
        new Email('invalid-email');
    }

    public function testEmailEquality(): void
    {
        $email1 = new Email('user@example.com');
        $email2 = new Email('user@example.com');
        $email3 = new Email('other@example.com');

        $this->assertTrue($email1->equals($email2));
        $this->assertFalse($email1->equals($email3));
    }

    /**
     * @dataProvider emailProvider
     */
    public function testEmailValidation(string $email, bool $isValid): void
    {
        if (!$isValid) {
            $this->expectException(\InvalidArgumentException::class);
        }

        $emailVO = new Email($email);
        
        if ($isValid) {
            $this->assertEquals($email, $emailVO->getValue());
        }
    }

    public function emailProvider(): array
    {
        return [
            ['valid@example.com', true],
            ['user.name@example.com', true],
            ['user+tag@example.com', true],
            ['invalid.email', false],
            ['@example.com', false],
            ['user@', false],
            ['', false],
        ];
    }
}
```

### Тестирование Aggregates

```php
class PostTest extends TestCase
{
    private Post $post;
    private CommentCollection $comments;

    protected function setUp(): void
    {
        $this->comments = new CommentCollection();
        $this->post = new Post();
        $this->post->setStatus(PostStatus::published());
        $this->post->setComments($this->comments);
    }

    public function testAddCommentToPublishedPost(): void
    {
        $comment = $this->createComment('Great post!');
        
        $this->post->addComment($comment);
        
        $this->assertCount(1, $this->post->getComments());
        $this->assertTrue($this->post->getComments()->contains($comment));
    }

    public function testCannotAddCommentToDraftPost(): void
    {
        $this->post->setStatus(PostStatus::draft());
        $comment = $this->createComment('Comment');

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Нельзя комментировать неопубликованный пост');

        $this->post->addComment($comment);
    }

    public function testPostInitialState(): void
    {
        $post = new Post();
        
        $this->assertNull($post->getId());
        $this->assertCount(0, $post->getComments());
        $this->assertNull($post->getPublishedAt());
    }

    private function createComment(string $text): Comment
    {
        $comment = new Comment();
        $comment->setText($text);
        $comment->setAuthor($this->createUser());
        $comment->setCreatedAt(new \DateTimeImmutable());
        
        return $comment;
    }

    private function createUser(): User
    {
        $user = new User();
        $user->setId(1);
        $user->setEmail(new Email('author@example.com'));
        
        return $user;
    }
}
```

### Тестирование коллекций

```php
class ProductCollectionTest extends TestCase
{
    private ProductCollection $collection;

    protected function setUp(): void
    {
        $this->collection = new ProductCollection();
    }

    public function testAddItem(): void
    {
        $product = $this->createProduct('Test Product', 1000);
        
        $result = $this->collection->addItem($product);
        
        $this->assertSame($this->collection, $result);
        $this->assertCount(1, $this->collection);
        $this->assertTrue($this->collection->contains($product));
    }

    public function testAddDuplicateItem(): void
    {
        $product = $this->createProduct('Test Product', 1000);
        
        $this->collection->addItem($product);
        $this->collection->addItem($product); // Дубликат
        
        $this->assertCount(1, $this->collection);
    }

    public function testRemoveItem(): void
    {
        $product1 = $this->createProduct('Product 1', 1000);
        $product2 = $this->createProduct('Product 2', 2000);
        
        $this->collection->addItem($product1);
        $this->collection->addItem($product2);
        
        $this->collection->removeItem($product1);
        
        $this->assertCount(1, $this->collection);
        $this->assertFalse($this->collection->contains($product1));
        $this->assertTrue($this->collection->contains($product2));
    }

    public function testFilterByCategory(): void
    {
        $category1 = new Category('Electronics');
        $category2 = new Category('Books');
        
        $product1 = $this->createProduct('Phone', 50000);
        $product1->setCategory($category1);
        
        $product2 = $this->createProduct('Book', 1000);
        $product2->setCategory($category2);
        
        $this->collection->addItem($product1);
        $this->collection->addItem($product2);
        
        $filtered = $this->collection->filterByCategory($category1);
        
        $this->assertCount(1, $filtered);
        $this->assertTrue($filtered->contains($product1));
        $this->assertFalse($filtered->contains($product2));
    }

    public function testSortByPrice(): void
    {
        $product1 = $this->createProduct('Expensive', 5000);
        $product2 = $this->createProduct('Cheap', 1000);
        $product3 = $this->createProduct('Medium', 3000);
        
        $this->collection->addItem($product1);
        $this->collection->addItem($product2);
        $this->collection->addItem($product3);
        
        $sorted = $this->collection->sortByPrice();
        $items = $sorted->toArray();
        
        $this->assertEquals(1000, $items[0]->getPrice()->getValue());
        $this->assertEquals(3000, $items[1]->getPrice()->getValue());
        $this->assertEquals(5000, $items[2]->getPrice()->getValue());
    }

    private function createProduct(string $name, int $price): Product
    {
        $product = new Product();
        $product->setName(new ProductName($name));
        $product->setPrice(new Money($price));
        
        return $product;
    }
}
```

## 📝 Тестирование Use Cases

### Основы тестирования Use Cases

```php
class CreateUserTest extends TestCase
{
    private CreateUser $useCase;
    private UserRepositoryInterface $userRepository;
    private EmailService $emailService;
    private Publisher $eventPublisher;

    protected function setUp(): void
    {
        $this->userRepository = $this->createMock(UserRepositoryInterface::class);
        $this->emailService = $this->createMock(EmailService::class);
        $this->eventPublisher = $this->createMock(Publisher::class);
        
        $this->useCase = new CreateUser(
            $this->userRepository,
            $this->emailService,
            $this->eventPublisher
        );
    }

    public function testSuccessfulUserCreation(): void
    {
        // Arrange
        $userData = [
            'email' => 'new@example.com',
            'name' => 'John Doe',
            'password' => 'secure123'
        ];

        $this->userRepository
            ->expects($this->once())
            ->method('findByEmail')
            ->with(new Email('new@example.com'))
            ->willReturn(null);

        $this->userRepository
            ->expects($this->once())
            ->method('persist')
            ->willReturnCallback(function(User $user) {
                $user->setId(123);
                return $user;
            });

        $this->eventPublisher
            ->expects($this->once())
            ->method('publish')
            ->with($this->isInstanceOf(UserCreatedEvent::class));

        // Act
        $user = $this->useCase->execute($userData);

        // Assert
        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals(123, $user->getId());
        $this->assertEquals('new@example.com', $user->getEmail()->getValue());
        $this->assertEquals('John Doe', $user->getName());
    }

    public function testCannotCreateUserWithExistingEmail(): void
    {
        // Arrange
        $existingUser = new User();
        $existingUser->setEmail(new Email('existing@example.com'));

        $this->userRepository
            ->expects($this->once())
            ->method('findByEmail')
            ->willReturn($existingUser);

        $this->userRepository
            ->expects($this->never())
            ->method('persist');

        // Act & Assert
        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Пользователь с таким email уже существует');

        $this->useCase->execute([
            'email' => 'existing@example.com',
            'name' => 'John Doe',
            'password' => 'secure123'
        ]);
    }

    public function testInvalidEmailThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->useCase->execute([
            'email' => 'invalid-email',
            'name' => 'John Doe',
            'password' => 'secure123'
        ]);
    }
}
```

### Тестирование сложных Use Cases

```php
class ProcessOrderTest extends TestCase
{
    private ProcessOrder $useCase;
    private OrderRepositoryInterface $orderRepository;
    private ProductRepositoryInterface $productRepository;
    private UserRepositoryInterface $userRepository;
    private InventoryService $inventoryService;

    protected function setUp(): void
    {
        $this->orderRepository = $this->createMock(OrderRepositoryInterface::class);
        $this->productRepository = $this->createMock(ProductRepositoryInterface::class);
        $this->userRepository = $this->createMock(UserRepositoryInterface::class);
        $this->inventoryService = $this->createMock(InventoryService::class);
        
        $this->useCase = new ProcessOrder(
            $this->orderRepository,
            $this->productRepository,
            $this->userRepository,
            $this->inventoryService,
            $this->createMock(Publisher::class)
        );
    }

    public function testSuccessfulOrderProcessing(): void
    {
        // Arrange
        $user = $this->createUser();
        $product = $this->createProduct();
        
        $orderData = [
            'user_id' => 1,
            'items' => [
                ['product_id' => 1, 'quantity' => 2]
            ]
        ];

        $this->userRepository
            ->method('findById')
            ->with(1)
            ->willReturn($user);

        $this->productRepository
            ->method('findById')
            ->with(1)
            ->willReturn($product);

        $this->inventoryService
            ->method('isAvailable')
            ->willReturn(true);

        $this->inventoryService
            ->expects($this->once())
            ->method('reserve');

        $this->orderRepository
            ->expects($this->once())
            ->method('beginTransaction');

        $this->orderRepository
            ->expects($this->once())
            ->method('persist')
            ->willReturnCallback(function(Order $order) {
                $order->setId(100);
                return $order;
            });

        $this->orderRepository
            ->expects($this->once())
            ->method('commit');

        // Act
        $order = $this->useCase->execute($orderData);

        // Assert
        $this->assertInstanceOf(Order::class, $order);
        $this->assertEquals(100, $order->getId());
        $this->assertCount(1, $order->getItems());
    }

    public function testInsufficientStockRollsBackTransaction(): void
    {
        // Arrange
        $user = $this->createUser();
        $product = $this->createProduct();

        $this->userRepository
            ->method('findById')
            ->willReturn($user);

        $this->productRepository
            ->method('findById')
            ->willReturn($product);

        $this->inventoryService
            ->method('isAvailable')
            ->willReturn(false);

        $this->orderRepository
            ->expects($this->once())
            ->method('beginTransaction');

        $this->orderRepository
            ->expects($this->once())
            ->method('rollback');

        $this->orderRepository
            ->expects($this->never())
            ->method('commit');

        // Act & Assert
        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Недостаточно товара');

        $this->useCase->execute([
            'user_id' => 1,
            'items' => [['product_id' => 1, 'quantity' => 10]]
        ]);
    }

    private function createUser(): User
    {
        $user = new User();
        $user->setId(1);
        $user->setEmail(new Email('user@example.com'));
        return $user;
    }

    private function createProduct(): Product
    {
        $product = new Product();
        $product->setId(1);
        $product->setName(new ProductName('Test Product'));
        $product->setPrice(new Money(1000));
        return $product;
    }
}
```

## 🔔 Тестирование событий

### Тестирование генерации событий

```php
class EventPublisherTest extends TestCase
{
    private Publisher $publisher;
    private array $publishedEvents = [];

    protected function setUp(): void
    {
        $this->publisher = new Publisher();
        $this->publishedEvents = [];
        
        // Подписываемся на все события для тестирования
        $this->publisher->subscribe('*', function($event) {
            $this->publishedEvents[] = $event;
        });
    }

    public function testEventIsPublished(): void
    {
        $event = new UserCreatedEvent(123, 'test@example.com');
        
        $this->publisher->publish($event);
        
        $this->assertCount(1, $this->publishedEvents);
        $this->assertInstanceOf(UserCreatedEvent::class, $this->publishedEvents[0]);
        $this->assertEquals(123, $this->publishedEvents[0]->getUserId());
    }

    public function testMultipleEventsArePublished(): void
    {
        $event1 = new UserCreatedEvent(1, 'user1@example.com');
        $event2 = new UserCreatedEvent(2, 'user2@example.com');
        
        $this->publisher->publish($event1);
        $this->publisher->publish($event2);
        
        $this->assertCount(2, $this->publishedEvents);
    }
}
```

### Тестирование обработчиков событий

```php
class EmailNotificationHandlerTest extends TestCase
{
    private EmailNotificationHandler $handler;
    private EmailService $emailService;
    private UserRepositoryInterface $userRepository;

    protected function setUp(): void
    {
        $this->emailService = $this->createMock(EmailService::class);
        $this->userRepository = $this->createMock(UserRepositoryInterface::class);
        
        $this->handler = new EmailNotificationHandler(
            $this->emailService,
            $this->userRepository
        );
    }

    public function testHandleUserCreatedEvent(): void
    {
        // Arrange
        $user = new User();
        $user->setId(123);
        $user->setEmail(new Email('user@example.com'));
        $user->setName('John Doe');

        $this->userRepository
            ->expects($this->once())
            ->method('findById')
            ->with(123)
            ->willReturn($user);

        $this->emailService
            ->expects($this->once())
            ->method('send')
            ->with(
                $this->equalTo(new Email('user@example.com')),
                $this->equalTo('Добро пожаловать!'),
                $this->stringContains('John Doe')
            );

        $event = new UserCreatedEvent(123, 'user@example.com');

        // Act
        $this->handler->handleUserCreated($event);
    }

    public function testHandleUserCreatedEventWithNonExistentUser(): void
    {
        $this->userRepository
            ->method('findById')
            ->willReturn(null);

        $this->emailService
            ->expects($this->never())
            ->method('send');

        $event = new UserCreatedEvent(999, 'nonexistent@example.com');

        // Не должно выбрасывать исключение, просто игнорировать
        $this->handler->handleUserCreated($event);
    }
}
```

## 🎭 Моки и заглушки

### Создание моков репозиториев

```php
class TestUserRepository implements UserRepositoryInterface
{
    private array $users = [];
    private int $nextId = 1;

    public function persist(User $user): User
    {
        if (!$user->getId()) {
            $user->setId($this->nextId++);
        }
        
        $this->users[$user->getId()] = clone $user;
        return $user;
    }

    public function findById(int $id): ?User
    {
        return $this->users[$id] ?? null;
    }

    public function findByEmail(Email $email): ?User
    {
        foreach ($this->users as $user) {
            if ($user->getEmail()->equals($email)) {
                return clone $user;
            }
        }
        return null;
    }

    public function delete(User $user): void
    {
        unset($this->users[$user->getId()]);
    }

    // Утилиты для тестов
    public function addUser(User $user): void
    {
        $this->users[$user->getId()] = clone $user;
    }

    public function clear(): void
    {
        $this->users = [];
        $this->nextId = 1;
    }

    public function count(): int
    {
        return count($this->users);
    }
}
```

### Фабрики тестовых данных

```php
class UserFactory
{
    private static int $sequence = 1;

    public static function create(array $attributes = []): User
    {
        $defaults = [
            'id' => self::$sequence++,
            'email' => 'user' . self::$sequence . '@example.com',
            'name' => 'User ' . self::$sequence,
            'created_at' => new \DateTimeImmutable(),
        ];

        $data = array_merge($defaults, $attributes);

        $user = new User();
        $user->setId($data['id']);
        $user->setEmail(new Email($data['email']));
        $user->setName($data['name']);
        $user->setCreatedAt($data['created_at']);

        return $user;
    }

    public static function createMany(int $count, array $attributes = []): array
    {
        $users = [];
        for ($i = 0; $i < $count; $i++) {
            $users[] = self::create($attributes);
        }
        return $users;
    }

    public static function reset(): void
    {
        self::$sequence = 1;
    }
}

class ProductFactory
{
    public static function create(array $attributes = []): Product
    {
        $defaults = [
            'name' => 'Test Product',
            'price' => 1000,
            'category' => 'General',
        ];

        $data = array_merge($defaults, $attributes);

        $product = new Product();
        $product->setName(new ProductName($data['name']));
        $product->setPrice(new Money($data['price']));
        $product->setCategory(new Category($data['category']));

        return $product;
    }
}
```

## 📊 Интеграционные тесты

### Тестирование репозиториев

```php
class ProductRepositoryIntegrationTest extends TestCase
{
    private ProductRepository $repository;
    private \PDO $database;

    protected function setUp(): void
    {
        // Создаем in-memory SQLite для тестов
        $this->database = new \PDO('sqlite::memory:');
        $this->database->exec('
            CREATE TABLE products (
                id INTEGER PRIMARY KEY,
                name TEXT NOT NULL,
                price INTEGER NOT NULL,
                category_id INTEGER,
                is_active BOOLEAN DEFAULT 1,
                created_at TEXT
            )
        ');

        $this->repository = new ProductRepository($this->database);
    }

    public function testPersistAndFind(): void
    {
        // Arrange
        $product = ProductFactory::create([
            'name' => 'Integration Test Product',
            'price' => 5000
        ]);

        // Act
        $savedProduct = $this->repository->persist($product);
        $foundProduct = $this->repository->findById($savedProduct->getId());

        // Assert
        $this->assertNotNull($foundProduct);
        $this->assertEquals('Integration Test Product', $foundProduct->getName()->getValue());
        $this->assertEquals(5000, $foundProduct->getPrice()->getValue());
    }

    public function testFindByCategory(): void
    {
        // Arrange
        $category = new Category('Electronics');
        
        $product1 = ProductFactory::create(['category' => 'Electronics']);
        $product2 = ProductFactory::create(['category' => 'Books']);
        $product3 = ProductFactory::create(['category' => 'Electronics']);

        $this->repository->persist($product1);
        $this->repository->persist($product2);
        $this->repository->persist($product3);

        // Act
        $products = $this->repository->findByCategory($category);

        // Assert
        $this->assertCount(2, $products);
        foreach ($products as $product) {
            $this->assertTrue($product->getCategory()->equals($category));
        }
    }
}
```

## 🚀 CI/CD

### GitHub Actions конфигурация

```yaml
# .github/workflows/tests.yml
name: Tests

on:
  push:
    branches: [ main, develop ]
  pull_request:
    branches: [ main ]

jobs:
  test:
    runs-on: ubuntu-latest

    strategy:
      matrix:
        php-version: [8.1, 8.2, 8.3]

    steps:
    - uses: actions/checkout@v3

    - name: Setup PHP
      uses: shivammathur/setup-php@v2
      with:
        php-version: ${{ matrix.php-version }}
        extensions: mbstring, xml, ctype, iconv, intl, pdo_sqlite
        coverage: xdebug

    - name: Cache Composer packages
      id: composer-cache
      uses: actions/cache@v3
      with:
        path: vendor
        key: ${{ runner.os }}-php-${{ hashFiles('**/composer.lock') }}
        restore-keys: |
          ${{ runner.os }}-php-

    - name: Install dependencies
      run: composer install --prefer-dist --no-progress

    - name: Run tests
      run: vendor/bin/phpunit --coverage-clover coverage.xml

    - name: Upload coverage to Codecov
      uses: codecov/codecov-action@v3
      with:
        file: ./coverage.xml
        fail_ci_if_error: true

  static-analysis:
    runs-on: ubuntu-latest

    steps:
    - uses: actions/checkout@v3

    - name: Setup PHP
      uses: shivammathur/setup-php@v2
      with:
        php-version: 8.2

    - name: Install dependencies
      run: composer install --prefer-dist --no-progress

    - name: Run PHPStan
      run: vendor/bin/phpstan analyse src/ --level=8

    - name: Run PHP CS Fixer
      run: vendor/bin/php-cs-fixer fix --dry-run --diff
```

### PHPUnit конфигурация

```xml
<!-- phpunit.xml -->
<?xml version="1.0" encoding="UTF-8"?>
<phpunit xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
         xsi:noNamespaceSchemaLocation="https://schema.phpunit.de/10.0/phpunit.xsd"
         bootstrap="vendor/autoload.php"
         colors="true"
         testdox="true">
    
    <testsuites>
        <testsuite name="Unit">
            <directory>tests/Unit</directory>
        </testsuite>
        <testsuite name="Integration">
            <directory>tests/Integration</directory>
        </testsuite>
        <testsuite name="Feature">
            <directory>tests/Feature</directory>
        </testsuite>
    </testsuites>

    <source>
        <include>
            <directory>src</directory>
        </include>
        <exclude>
            <directory>src/Infrastructure/Migration</directory>
        </exclude>
    </source>

    <coverage>
        <report>
            <html outputDirectory="coverage-html"/>
            <clover outputFile="coverage.xml"/>
        </report>
    </coverage>

    <php>
        <env name="APP_ENV" value="testing"/>
        <env name="DB_CONNECTION" value="sqlite"/>
        <env name="DB_DATABASE" value=":memory:"/>
    </php>
</phpunit>
```

## ✅ Чек-лист тестирования

### Доменный слой
- [ ] Все Value Objects покрыты тестами (100%)
- [ ] Тестируется валидация в конструкторах
- [ ] Тестируется метод equals()
- [ ] Aggregates тестируются на бизнес-правила
- [ ] Коллекции тестируются на все операции

### Use Cases
- [ ] Happy path сценарии
- [ ] Обработка ошибок
- [ ] Валидация входных данных
- [ ] Взаимодействие с репозиториями
- [ ] Генерация событий

### События
- [ ] Генерация событий
- [ ] Обработка событий
- [ ] Подписчики событий

### Интеграционные тесты
- [ ] Тестирование репозиториев с БД
- [ ] Тестирование внешних интеграций
- [ ] End-to-end сценарии

### CI/CD
- [ ] Автоматический запуск тестов
- [ ] Проверка покрытия кода
- [ ] Статический анализ
- [ ] Линтинг кода

# Миграция на Clean Architecture

> Пошаговое руководство по переходу с других архитектур

## 📑 Содержание

- [Анализ текущего состояния](#анализ-текущего-состояния)
- [Стратегии миграции](#стратегии-миграции)
- [Миграция с MVC](#миграция-с-mvc)
- [Миграция с Monolith](#миграция-с-monolith)
- [Миграция legacy кода](#миграция-legacy-кода)
- [Поэтапный план](#поэтапный-план)
- [Типичные проблемы](#типичные-проблемы)
- [Метрики успеха](#метрики-успеха)

---

## Анализ текущего состояния

### Аудит архитектуры

Перед началом миграции необходимо провести анализ:

#### 1. Карта зависимостей

```bash
# Анализ связей между классами
composer require --dev phpstan/phpstan
vendor/bin/phpstan analyse src/ --level=5 --error-format=json > dependencies.json

# Поиск циклических зависимостей
composer require --dev deptrac/deptrac
```

```yaml
# deptrac.yaml
paths:
  - ./src
layers:
  - name: Controller
    collectors:
      - type: className
        regex: .*Controller$
  - name: Service
    collectors:
      - type: className
        regex: .*Service$
  - name: Repository
    collectors:
      - type: className
        regex: .*Repository$
  - name: Model
    collectors:
      - type: className
        regex: .*Model$

ruleset:
  Controller:
    - Service
  Service:
    - Repository
    - Model
  Repository:
    - Model
  Model: []
```

#### 2. Инвентаризация компонентов

```php
<?php
// Скрипт анализа кодовой базы
class ArchitectureAnalyzer
{
    public function analyzeCodebase(string $path): array
    {
        $result = [
            'controllers' => [],
            'models' => [],
            'services' => [],
            'repositories' => [],
            'utilities' => [],
            'violations' => []
        ];

        $files = $this->getPhpFiles($path);
        
        foreach ($files as $file) {
            $analysis = $this->analyzeFile($file);
            $result = array_merge_recursive($result, $analysis);
        }

        return $result;
    }

    private function analyzeFile(string $file): array
    {
        $content = file_get_contents($file);
        $tokens = token_get_all($content);
        
        return [
            'database_calls' => $this->findDatabaseCalls($content),
            'static_calls' => $this->findStaticCalls($content),
            'global_variables' => $this->findGlobalVariables($content),
            'mixed_responsibilities' => $this->findMixedResponsibilities($content),
        ];
    }

    private function findDatabaseCalls(string $content): array
    {
        $patterns = [
            '/DB::|mysqli_|mysql_|PDO/',
            '/SELECT|INSERT|UPDATE|DELETE/',
            '/\$wpdb->/',
            '/ActiveRecord::'
        ];
        
        $violations = [];
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $content)) {
                $violations[] = $pattern;
            }
        }
        
        return $violations;
    }
}
```

#### 3. Оценка сложности

```php
class ComplexityCalculator
{
    public function calculateMetrics(string $codebase): array
    {
        return [
            'cyclomatic_complexity' => $this->calculateCyclomaticComplexity($codebase),
            'coupling_factor' => $this->calculateCouplingFactor($codebase),
            'cohesion_score' => $this->calculateCohesionScore($codebase),
            'test_coverage' => $this->getTestCoverage($codebase),
        ];
    }

    private function calculateCyclomaticComplexity(string $code): float
    {
        // Подсчет условных операторов, циклов, switch-case
        $patterns = ['/\bif\b/', '/\bwhile\b/', '/\bfor\b/', '/\bcase\b/', '/\bcatch\b/'];
        $complexity = 1; // базовая сложность
        
        foreach ($patterns as $pattern) {
            $complexity += preg_match_all($pattern, $code);
        }
        
        return $complexity;
    }
}
```

---

## Стратегии миграции

### 1. Strangler Fig Pattern

Постепенное замещение старых компонентов новыми:

```php
<?php
// Старый контроллер
class OldProductController
{
    public function index()
    {
        // Legacy код с SQL запросами
        $products = DB::query('SELECT * FROM products WHERE active = 1');
        return view('products.index', compact('products'));
    }
}

// Промежуточный адаптер
class ProductControllerAdapter extends OldProductController
{
    private GetProducts $getProducts;
    private bool $useNewArchitecture = false;

    public function __construct(GetProducts $getProducts = null)
    {
        $this->getProducts = $getProducts;
        $this->useNewArchitecture = $getProducts !== null;
    }

    public function index()
    {
        if ($this->useNewArchitecture) {
            return $this->indexCleanArchitecture();
        }
        
        return parent::index();
    }

    private function indexCleanArchitecture()
    {
        $filter = new ProductFilter();
        $filter->setPublishedOnly(true);
        
        $products = $this->getProducts->execute($filter);
        
        return new JsonResponse([
            'success' => true,
            'data' => $products->toArray()
        ]);
    }
}
```

### 2. Branch by Abstraction

Создание абстракций для изоляции изменений:

```php
<?php
// Абстракция для данных
interface ProductDataSourceInterface
{
    public function getProducts(array $criteria): array;
    public function getProduct(int $id): ?array;
    public function saveProduct(array $data): int;
}

// Legacy реализация
class LegacyProductDataSource implements ProductDataSourceInterface
{
    public function getProducts(array $criteria): array
    {
        $sql = "SELECT * FROM products WHERE 1=1";
        
        if (isset($criteria['category_id'])) {
            $sql .= " AND category_id = " . (int)$criteria['category_id'];
        }
        
        return DB::query($sql);
    }
    
    public function getProduct(int $id): ?array
    {
        $result = DB::query("SELECT * FROM products WHERE id = $id");
        return $result[0] ?? null;
    }
    
    public function saveProduct(array $data): int
    {
        return DB::insert('products', $data);
    }
}

// Clean Architecture реализация
class CleanProductDataSource implements ProductDataSourceInterface
{
    private ProductRepositoryInterface $repository;

    public function __construct(ProductRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function getProducts(array $criteria): array
    {
        $filter = $this->buildFilter($criteria);
        $products = $this->repository->findByFilter($filter);
        
        return $products->toArray();
    }

    public function getProduct(int $id): ?array
    {
        $product = $this->repository->findById($id);
        return $product ? $product->toArray() : null;
    }

    public function saveProduct(array $data): int
    {
        $request = CreateProductRequest::fromArray($data);
        $product = new Product();
        // ... маппинг данных
        
        $saved = $this->repository->save($product);
        return $saved->getId();
    }

    private function buildFilter(array $criteria): ProductFilter
    {
        $filter = new ProductFilter();
        
        if (isset($criteria['category_id'])) {
            $filter->setCategoryId($criteria['category_id']);
        }
        
        return $filter;
    }
}

// Использование
class ProductService
{
    private ProductDataSourceInterface $dataSource;

    public function __construct(ProductDataSourceInterface $dataSource)
    {
        $this->dataSource = $dataSource;
    }

    public function getProductList(array $criteria): array
    {
        return $this->dataSource->getProducts($criteria);
    }
}
```

### 3. Event Sourcing для синхронизации

```php
<?php
// Событие для синхронизации данных
class DataMigrationEvent implements DomainEventInterface
{
    private string $entityType;
    private int $entityId;
    private array $legacyData;
    private array $cleanData;

    public function __construct(string $entityType, int $entityId, array $legacyData, array $cleanData)
    {
        $this->entityType = $entityType;
        $this->entityId = $entityId;
        $this->legacyData = $legacyData;
        $this->cleanData = $cleanData;
        $this->occurredOn = new \DateTime();
    }

    // ... геттеры
}

// Синхронизатор данных
class DataSynchronizer
{
    private EventDispatcherInterface $eventDispatcher;
    private array $migrationHandlers = [];

    public function __construct(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    public function registerHandler(string $entityType, callable $handler): void
    {
        $this->migrationHandlers[$entityType] = $handler;
    }

    public function synchronize(string $entityType, int $entityId): void
    {
        if (!isset($this->migrationHandlers[$entityType])) {
            throw new \InvalidArgumentException("No handler for entity type: $entityType");
        }

        $handler = $this->migrationHandlers[$entityType];
        $result = $handler($entityId);

        $event = new DataMigrationEvent(
            $entityType, 
            $entityId, 
            $result['legacy'], 
            $result['clean']
        );

        $this->eventDispatcher->dispatch($event);
    }
}
```

---

## Миграция с MVC

### Поэтапная трансформация MVC → Clean Architecture

#### Этап 1: Выделение Use Cases из контроллеров

```php
<?php
// Было: толстые контроллеры
class ProductController extends Controller
{
    public function store(Request $request)
    {
        // Валидация
        $this->validate($request, [
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'category_id' => 'required|exists:categories,id'
        ]);

        // Бизнес-логика в контроллере
        $product = new Product();
        $product->name = $request->name;
        $product->description = $request->description;
        $product->price = $request->price;
        $product->category_id = $request->category_id;
        $product->slug = Str::slug($request->name);
        
        // Проверки
        if (Product::where('slug', $product->slug)->exists()) {
            $product->slug .= '-' . time();
        }

        $product->save();

        // Отправка уведомлений
        Mail::to('admin@example.com')->send(new ProductCreated($product));

        // Очистка кэша
        Cache::forget('products');

        return response()->json($product, 201);
    }
}

// Стало: тонкие контроллеры + Use Cases
class ProductController extends Controller
{
    private CreateProduct $createProduct;

    public function __construct(CreateProduct $createProduct)
    {
        $this->createProduct = $createProduct;
    }

    public function store(ProductCreateRequest $request)
    {
        try {
            $createRequest = CreateProductRequest::fromArray($request->validated());
            $product = $this->createProduct->execute($createRequest);

            return response()->json([
                'success' => true,
                'data' => $product->toArray()
            ], 201);

        } catch (\DomainException $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 422);
        }
    }
}

// Use Case инкапсулирует бизнес-логику
class CreateProduct
{
    private ProductRepositoryInterface $productRepository;
    private SlugGeneratorInterface $slugGenerator;
    private EventDispatcherInterface $eventDispatcher;

    public function execute(CreateProductRequest $request): Product
    {
        $product = new Product();
        $product->setName($request->getName());
        $product->setDescription($request->getDescription());
        $product->setPrice($request->getPrice());
        $product->setCategoryId($request->getCategoryId());

        // Генерация уникального slug
        $slug = $this->slugGenerator->generateUniqueSlug($request->getName());
        $product->setSlug($slug);

        $savedProduct = $this->productRepository->save($product);

        // Обработка событий
        foreach ($savedProduct->getEvents() as $event) {
            $this->eventDispatcher->dispatch($event);
        }
        $savedProduct->clearEvents();

        return $savedProduct;
    }
}
```

#### Этап 2: Трансформация Eloquent Models в Entities/Aggregates

```php
<?php
// Было: Eloquent Model с логикой
class Product extends Model
{
    protected $fillable = ['name', 'description', 'price', 'category_id'];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function getAverageRatingAttribute()
    {
        return $this->reviews->avg('rating');
    }

    public function publish()
    {
        if (empty($this->name) || empty($this->description)) {
            throw new \Exception('Product cannot be published without name and description');
        }

        $this->status = 'published';
        $this->published_at = now();
        $this->save();

        // Уведомления
        event(new ProductPublished($this));
    }

    public function applyDiscount($percentage)
    {
        if ($percentage < 0 || $percentage > 100) {
            throw new \Exception('Invalid discount percentage');
        }

        $this->price = $this->price * (1 - $percentage / 100);
        $this->save();
    }
}

// Стало: разделение на Entity + Aggregate + Repository
class ProductEntity implements EntityInterface
{
    private ?int $id = null;
    private string $name;
    private string $description;
    private float $price;
    private int $categoryId;
    private string $status;
    private ?\DateTime $publishedAt = null;

    // ... геттеры и сеттеры
}

class Product extends ProductEntity implements AggregateInterface
{
    private ReviewCollection $reviews;
    private array $domainEvents = [];

    public function publish(): void
    {
        $this->validateCanPublish();
        
        $this->status = 'published';
        $this->publishedAt = new \DateTime();
        
        $this->raiseEvent(new ProductPublishedEvent($this->getId()));
    }

    public function applyDiscount(DiscountPercentage $discount): void
    {
        if ($this->status !== 'published') {
            throw new \DomainException('Only published products can have discounts');
        }

        $this->price *= (1 - $discount->getDecimal());
        
        $this->raiseEvent(new ProductDiscountAppliedEvent($this->getId(), $discount));
    }

    public function getAverageRating(): float
    {
        if ($this->reviews->count() === 0) {
            return 0.0;
        }

        $total = 0;
        foreach ($this->reviews as $review) {
            $total += $review->getRating();
        }

        return $total / $this->reviews->count();
    }

    private function validateCanPublish(): void
    {
        if (empty($this->name)) {
            throw new \DomainException('Product name is required');
        }

        if (empty($this->description)) {
            throw new \DomainException('Product description is required');
        }

        if ($this->status === 'published') {
            throw new \DomainException('Product is already published');
        }
    }
}

// Laravel Eloquent как Repository реализация
class LaravelProductRepository implements ProductRepositoryInterface
{
    public function save(Product $product): Product
    {
        $model = $this->findEloquentModel($product->getId()) ?? new ProductModel();
        
        $model->fill([
            'name' => $product->getName(),
            'description' => $product->getDescription(),
            'price' => $product->getPrice(),
            'category_id' => $product->getCategoryId(),
            'status' => $product->getStatus(),
            'published_at' => $product->getPublishedAt(),
        ]);

        $model->save();

        return $this->mapToAggregate($model);
    }

    public function findById(int $id): ?Product
    {
        $model = ProductModel::find($id);
        return $model ? $this->mapToAggregate($model) : null;
    }

    private function mapToAggregate(ProductModel $model): Product
    {
        $product = new Product();
        $product->setId($model->id);
        $product->setName($model->name);
        $product->setDescription($model->description);
        $product->setPrice($model->price);
        $product->setCategoryId($model->category_id);
        $product->setStatus($model->status);
        $product->setPublishedAt($model->published_at);

        // Загрузка связанных данных
        $this->loadReviews($product, $model);

        return $product;
    }
}
```

#### Этап 3: Миграция сервисов в Use Cases

```php
<?php
// Было: Service слой
class ProductService
{
    public function createProduct(array $data): Product
    {
        DB::beginTransaction();
        
        try {
            $product = Product::create($data);
            
            // Создание SEO записи
            SEO::create([
                'product_id' => $product->id,
                'meta_title' => $data['name'],
                'meta_description' => Str::limit($data['description'], 160)
            ]);
            
            // Индексация в поиске
            SearchIndex::index($product);
            
            // Уведомления
            Notification::send(User::admins(), new ProductCreatedNotification($product));
            
            DB::commit();
            
            return $product;
            
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }
}

// Стало: Use Case с явными зависимостями
class CreateProduct
{
    private ProductRepositoryInterface $productRepository;
    private SEOServiceInterface $seoService;
    private SearchIndexInterface $searchIndex;
    private NotificationServiceInterface $notificationService;
    private DatabaseTransactionInterface $transaction;

    public function execute(CreateProductRequest $request): Product
    {
        return $this->transaction->execute(function() use ($request) {
            // Создание продукта
            $product = new Product();
            $product->setName($request->getName());
            $product->setDescription($request->getDescription());
            $product->setPrice($request->getPrice());
            $product->setCategoryId($request->getCategoryId());

            $savedProduct = $this->productRepository->save($product);

            // SEO метаданные
            $this->seoService->createForProduct($savedProduct);

            // Индексация
            $this->searchIndex->index($savedProduct);

            // Уведомления через события
            $savedProduct->raiseEvent(new ProductCreatedEvent($savedProduct->getId()));

            return $savedProduct;
        });
    }
}
```

---

## Миграция с Monolith

### Разделение монолита на домены

#### 1. Идентификация bounded contexts

```php
<?php
// Анализ связей между таблицами
class DomainBoundaryAnalyzer
{
    public function analyzeDatabaseSchema(array $tables): array
    {
        $domains = [];
        
        foreach ($tables as $table) {
            $domain = $this->inferDomainFromTable($table);
            $domains[$domain][] = $table;
        }
        
        return $domains;
    }

    private function inferDomainFromTable(string $table): string
    {
        $patterns = [
            'user' => ['users', 'user_profiles', 'user_settings', 'roles', 'permissions'],
            'product' => ['products', 'categories', 'brands', 'product_images'],
            'order' => ['orders', 'order_items', 'payments', 'invoices'],
            'inventory' => ['inventory', 'warehouses', 'stock_movements'],
            'cms' => ['pages', 'posts', 'comments', 'media'],
        ];

        foreach ($patterns as $domain => $tableNames) {
            foreach ($tableNames as $tableName) {
                if (strpos($table, $tableName) !== false) {
                    return $domain;
                }
            }
        }

        return 'shared';
    }
}

// Результат анализа
$domains = [
    'user' => [
        'tables' => ['users', 'user_profiles', 'roles', 'permissions'],
        'entities' => ['User', 'UserProfile', 'Role', 'Permission'],
        'aggregates' => ['User'],
        'repositories' => ['UserRepository', 'RoleRepository']
    ],
    'product' => [
        'tables' => ['products', 'categories', 'reviews'],
        'entities' => ['Product', 'Category', 'Review'],
        'aggregates' => ['Product', 'Category'],
        'repositories' => ['ProductRepository', 'CategoryRepository']
    ]
];
```

#### 2. Создание доменных модулей

```php
<?php
// Структура каталогов по доменам
/*
src/
├── User/
│   ├── Domain/
│   │   ├── Entity/
│   │   │   ├── User.php
│   │   │   └── UserProfile.php
│   │   ├── Repository/
│   │   │   └── UserRepositoryInterface.php
│   │   ├── UseCase/
│   │   │   ├── CreateUser.php
│   │   │   └── UpdateUserProfile.php
│   │   └── Event/
│   │       └── UserRegistered.php
│   ├── Infrastructure/
│   │   └── Repository/
│   │       └── DoctrineUserRepository.php
│   └── Presentation/
│       └── Controller/
│           └── UserController.php
├── Product/
│   ├── Domain/
│   └── Infrastructure/
└── Shared/
    ├── Domain/
    │   ├── ValueObject/
    │   └── Event/
    └── Infrastructure/
*/

// Namespace mapping
// User Domain
namespace App\User\Domain\Entity;
namespace App\User\Domain\Repository;
namespace App\User\Domain\UseCase;

// Product Domain  
namespace App\Product\Domain\Entity;
namespace App\Product\Domain\Repository;
namespace App\Product\Domain\UseCase;

// Shared Kernel
namespace App\Shared\Domain\ValueObject;
namespace App\Shared\Domain\Event;
```

#### 3. Межмодульное взаимодействие

```php
<?php
// Shared события для связи между доменами
namespace App\Shared\Domain\Event;

class UserRegisteredEvent implements DomainEventInterface
{
    private int $userId;
    private string $email;
    private \DateTimeInterface $registeredAt;

    public function __construct(int $userId, string $email)
    {
        $this->userId = $userId;
        $this->email = $email;
        $this->registeredAt = new \DateTime();
    }

    // ... геттеры
}

// Product Domain подписывается на события User Domain
namespace App\Product\Domain\EventHandler;

class UserEventHandler
{
    private CustomerRepositoryInterface $customerRepository;

    public function onUserRegistered(UserRegisteredEvent $event): void
    {
        // Создаем Customer в Product домене
        $customer = new Customer();
        $customer->setUserId($event->getUserId());
        $customer->setEmail($event->getEmail());
        
        $this->customerRepository->save($customer);
    }
}

// Application Service для координации
namespace App\Application\Service;

class UserRegistrationService
{
    private CreateUser $createUser;
    private EventDispatcherInterface $eventDispatcher;

    public function registerUser(UserRegistrationRequest $request): User
    {
        // Создаем пользователя в User домене
        $user = $this->createUser->execute($request);

        // Публикуем событие для других доменов
        $event = new UserRegisteredEvent($user->getId(), $user->getEmail());
        $this->eventDispatcher->dispatch($event);

        return $user;
    }
}
```

---

## Миграция legacy кода

### Работа с устаревшими системами

#### 1. Anti-Corruption Layer

```php
<?php
// Адаптер для legacy базы данных
class LegacyDatabaseAdapter implements ProductRepositoryInterface
{
    private \PDO $pdo;
    private ProductMapper $mapper;

    public function __construct(\PDO $pdo, ProductMapper $mapper)
    {
        $this->pdo = $pdo;
        $this->mapper = $mapper;
    }

    public function findById(int $id): ?Product
    {
        // Legacy база с другой схемой
        $stmt = $this->pdo->prepare('
            SELECT p.*, c.name as category_name 
            FROM legacy_products p 
            LEFT JOIN legacy_categories c ON p.cat_id = c.id 
            WHERE p.product_id = ?
        ');
        
        $stmt->execute([$id]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$row) {
            return null;
        }

        // Маппинг legacy структуры в доменную модель
        return $this->mapper->mapFromLegacyData($row);
    }

    public function save(Product $product): Product
    {
        $data = $this->mapper->mapToLegacyData($product);

        if ($product->getId()) {
            // Update
            $stmt = $this->pdo->prepare('
                UPDATE legacy_products 
                SET product_name = ?, product_desc = ?, product_price = ?, cat_id = ?
                WHERE product_id = ?
            ');
            
            $stmt->execute([
                $data['product_name'],
                $data['product_desc'], 
                $data['product_price'],
                $data['cat_id'],
                $product->getId()
            ]);
        } else {
            // Insert
            $stmt = $this->pdo->prepare('
                INSERT INTO legacy_products (product_name, product_desc, product_price, cat_id)
                VALUES (?, ?, ?, ?)
            ');
            
            $stmt->execute([
                $data['product_name'],
                $data['product_desc'],
                $data['product_price'], 
                $data['cat_id']
            ]);

            $product->setId($this->pdo->lastInsertId());
        }

        return $product;
    }
}

// Маппер для преобразования данных
class ProductMapper
{
    public function mapFromLegacyData(array $data): Product
    {
        $product = new Product();
        
        // Маппинг полей с другими названиями
        $product->setId($data['product_id']);
        $product->setName($data['product_name']);
        $product->setDescription($data['product_desc']);
        $product->setPrice((float) $data['product_price']);
        $product->setCategoryId($data['cat_id']);
        
        // Обработка статуса
        $status = $this->mapLegacyStatus($data['status'] ?? 1);
        $product->setStatus($status);

        return $product;
    }

    public function mapToLegacyData(Product $product): array
    {
        return [
            'product_id' => $product->getId(),
            'product_name' => $product->getName(),
            'product_desc' => $product->getDescription(),
            'product_price' => $product->getPrice(),
            'cat_id' => $product->getCategoryId(),
            'status' => $this->mapToLegacyStatus($product->getStatus()),
        ];
    }

    private function mapLegacyStatus(int $legacyStatus): string
    {
        return match($legacyStatus) {
            1 => 'published',
            0 => 'draft',
            2 => 'archived',
            default => 'draft'
        };
    }

    private function mapToLegacyStatus(string $status): int
    {
        return match($status) {
            'published' => 1,
            'draft' => 0,
            'archived' => 2,
            default => 0
        };
    }
}
```

#### 2. Facade для legacy API

```php
<?php
// Facade для legacy SOAP API
class LegacyInventoryFacade implements InventoryServiceInterface
{
    private \SoapClient $soapClient;
    private LoggerInterface $logger;

    public function __construct(\SoapClient $soapClient, LoggerInterface $logger)
    {
        $this->soapClient = $soapClient;
        $this->logger = $logger;
    }

    public function getStock(int $productId): int
    {
        try {
            // Legacy SOAP вызов
            $response = $this->soapClient->GetProductStock([
                'ProductID' => $productId,
                'WarehouseCode' => 'MAIN'
            ]);

            $stock = $response->GetProductStockResult->Stock ?? 0;
            
            $this->logger->info('Retrieved stock from legacy system', [
                'product_id' => $productId,
                'stock' => $stock
            ]);

            return (int) $stock;

        } catch (\SoapFault $e) {
            $this->logger->error('Failed to retrieve stock from legacy system', [
                'product_id' => $productId,
                'error' => $e->getMessage()
            ]);

            throw new \DomainException('Unable to retrieve stock information');
        }
    }

    public function reserveStock(int $productId, int $quantity): bool
    {
        try {
            $response = $this->soapClient->ReserveStock([
                'ProductID' => $productId,
                'Quantity' => $quantity,
                'ReservationCode' => $this->generateReservationCode()
            ]);

            return $response->ReserveStockResult->Success === true;

        } catch (\SoapFault $e) {
            $this->logger->error('Failed to reserve stock', [
                'product_id' => $productId,
                'quantity' => $quantity,
                'error' => $e->getMessage()
            ]);

            return false;
        }
    }

    private function generateReservationCode(): string
    {
        return 'RSV_' . time() . '_' . uniqid();
    }
}
```

#### 3. Постепенная замена компонентов

```php
<?php
// Feature Toggle для постепенного перехода
class FeatureToggle
{
    private array $features;

    public function __construct(array $features = [])
    {
        $this->features = $features;
    }

    public function isEnabled(string $feature): bool
    {
        return $this->features[$feature] ?? false;
    }

    public function setFeature(string $feature, bool $enabled): void
    {
        $this->features[$feature] = $enabled;
    }
}

// Hybrid Repository с переключением
class HybridProductRepository implements ProductRepositoryInterface
{
    private ProductRepositoryInterface $legacyRepository;
    private ProductRepositoryInterface $cleanRepository;
    private FeatureToggle $featureToggle;

    public function __construct(
        ProductRepositoryInterface $legacyRepository,
        ProductRepositoryInterface $cleanRepository,
        FeatureToggle $featureToggle
    ) {
        $this->legacyRepository = $legacyRepository;
        $this->cleanRepository = $cleanRepository;
        $this->featureToggle = $featureToggle;
    }

    public function findById(int $id): ?Product
    {
        if ($this->featureToggle->isEnabled('clean_architecture_read')) {
            try {
                return $this->cleanRepository->findById($id);
            } catch (\Exception $e) {
                // Fallback to legacy
                return $this->legacyRepository->findById($id);
            }
        }

        return $this->legacyRepository->findById($id);
    }

    public function save(Product $product): Product
    {
        if ($this->featureToggle->isEnabled('clean_architecture_write')) {
            $saved = $this->cleanRepository->save($product);
            
            // Dual write для синхронизации
            try {
                $this->legacyRepository->save($saved);
            } catch (\Exception $e) {
                // Log error but don't fail
                error_log("Failed to sync to legacy: " . $e->getMessage());
            }
            
            return $saved;
        }

        return $this->legacyRepository->save($product);
    }
}
```

---

## Поэтапный план

### Фаза 1: Подготовка (2-4 недели)

1. **Анализ архитектуры**
   - Аудит зависимостей
   - Карта legacy компонентов
   - Оценка рисков

2. **Настройка инфраструктуры**
   - CI/CD для новой архитектуры
   - Мониторинг и метрики
   - Feature toggles

3. **Обучение команды**
   - Workshops по Clean Architecture
   - Code review guidelines
   - Документация стандартов

### Фаза 2: Фундамент (4-6 недель)

1. **Доменные модели**
   - Выделение Entity и Value Objects
   - Создание Aggregates
   - Определение границ доменов

2. **Repository слой**
   - Интерфейсы репозиториев
   - Адаптеры для legacy систем
   - Тестирование слоя данных

3. **Use Cases**
   - Выделение бизнес-логики
   - Создание команд и запросов
   - Валидация и обработка ошибок

### Фаза 3: Миграция (8-12 недель)

1. **Модуль за модулем**
   - Приоритизация по бизнес-ценности
   - Strangler Fig pattern
   - A/B тестирование

2. **Интеграция**
   - Event-driven коммуникация
   - API Gateway
   - Data synchronization

3. **Тестирование**
   - Unit тесты для домена
   - Integration тесты
   - End-to-end тесты

### Фаза 4: Оптимизация (4-6 недель)

1. **Производительность**
   - Профилирование
   - Кэширование
   - Оптимизация запросов

2. **Мониторинг**
   - Метрики бизнес-логики
   - Алерты и dashboards
   - Performance tracking

3. **Документация**
   - Архитектурные решения
   - Руководства по развертыванию
   - Troubleshooting guides

---

## Типичные проблемы

### 1. Циклические зависимости

```php
<?php
// Проблема: User зависит от Order, Order зависит от User
class User
{
    private OrderCollection $orders; // ❌ Прямая зависимость
    
    public function getTotalSpent(): float
    {
        return $this->orders->getTotalAmount();
    }
}

class Order  
{
    private User $user; // ❌ Обратная зависимость
}

// Решение: Dependency Inversion + Domain Services
interface OrderTotalCalculatorInterface
{
    public function calculateUserTotal(int $userId): float;
}

class User
{
    private int $id;
    
    public function getId(): int
    {
        return $this->id;
    }
    
    // Убираем прямую зависимость от Order
}

class UserTotalService
{
    private OrderRepositoryInterface $orderRepository;

    public function calculateTotal(User $user): float
    {
        $orders = $this->orderRepository->findByUserId($user->getId());
        
        $total = 0;
        foreach ($orders as $order) {
            $total += $order->getAmount();
        }
        
        return $total;
    }
}
```

### 2. Анемичная доменная модель

```php
<?php
// Проблема: Entity без поведения
class Product
{
    private float $price;
    private string $status;
    
    public function getPrice(): float { return $this->price; }
    public function setPrice(float $price): void { $this->price = $price; }
    
    public function getStatus(): string { return $this->status; }
    public function setStatus(string $status): void { $this->status = $status; }
}

// Бизнес-логика в сервисах ❌
class ProductService 
{
    public function applyDiscount(Product $product, float $discount): void
    {
        if ($product->getStatus() !== 'published') {
            throw new \Exception('Cannot apply discount to unpublished product');
        }
        
        $newPrice = $product->getPrice() * (1 - $discount / 100);
        $product->setPrice($newPrice);
    }
}

// Решение: Богатая доменная модель
class Product
{
    private Money $price;
    private ProductStatus $status;
    private array $domainEvents = [];
    
    public function applyDiscount(DiscountPercentage $discount): void
    {
        // Бизнес-правила внутри домена ✅
        if (!$this->status->isPublished()) {
            throw new \DomainException('Cannot apply discount to unpublished product');
        }
        
        $this->price = $this->price->multiply(1 - $discount->getDecimal());
        $this->raiseEvent(new ProductDiscountAppliedEvent($this->id, $discount));
    }
    
    public function publish(): void
    {
        $this->validateCanPublish();
        $this->status = ProductStatus::published();
        $this->raiseEvent(new ProductPublishedEvent($this->id));
    }
}
```

### 3. Утечка инфраструктуры в домен

```php
<?php
// Проблема: Doctrine аннотации в домене ❌
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="products")
 */
class Product
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private int $id;
    
    /**
     * @ORM\Column(type="string", length=255)
     */
    private string $name;
}

// Решение: Разделение concerns
// Доменная модель без инфраструктурных зависимостей ✅
class Product implements AggregateInterface
{
    private ?int $id = null;
    private string $name;
    private ProductStatus $status;
    
    // Чистая бизнес-логика
    public function publish(): void
    {
        $this->validateCanPublish();
        $this->status = ProductStatus::published();
    }
}

// Инфраструктурная Entity для ORM ✅
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="products")
 */
class ProductEntity
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    public int $id;
    
    /**
     * @ORM\Column(type="string", length=255)
     */
    public string $name;
    
    /**
     * @ORM\Column(type="string", length=50)
     */
    public string $status;
}

// Маппер между доменом и инфраструктурой ✅
class ProductMapper
{
    public function mapToEntity(Product $product): ProductEntity
    {
        $entity = new ProductEntity();
        $entity->id = $product->getId();
        $entity->name = $product->getName();
        $entity->status = $product->getStatus()->getValue();
        
        return $entity;
    }
    
    public function mapToDomain(ProductEntity $entity): Product
    {
        $product = new Product();
        $product->setId($entity->id);
        $product->setName($entity->name);
        $product->setStatus(ProductStatus::fromString($entity->status));
        
        return $product;
    }
}
```

---

## Метрики успеха

### Технические метрики

```php
<?php
class ArchitectureMetrics
{
    public function calculateMetrics(string $codebase): array
    {
        return [
            // Качество кода
            'cyclomatic_complexity' => $this->calculateCyclomaticComplexity($codebase),
            'code_coverage' => $this->getCodeCoverage($codebase),
            'technical_debt_ratio' => $this->calculateTechnicalDebt($codebase),
            
            // Архитектурные метрики
            'coupling_factor' => $this->calculateCoupling($codebase),
            'cohesion_score' => $this->calculateCohesion($codebase),
            'dependency_violations' => $this->findDependencyViolations($codebase),
            
            // Clean Architecture специфичные
            'domain_purity' => $this->calculateDomainPurity($codebase),
            'infrastructure_isolation' => $this->calculateInfrastructureIsolation($codebase),
            'use_case_coverage' => $this->calculateUseCaseCoverage($codebase),
        ];
    }

    private function calculateDomainPurity(string $codebase): float
    {
        // Процент доменных классов без внешних зависимостей
        $domainClasses = $this->findDomainClasses($codebase);
        $pureClasses = 0;
        
        foreach ($domainClasses as $class) {
            if ($this->isPureDomainClass($class)) {
                $pureClasses++;
            }
        }
        
        return count($domainClasses) > 0 ? $pureClasses / count($domainClasses) : 0;
    }

    private function isPureDomainClass(string $class): bool
    {
        $dependencies = $this->getClassDependencies($class);
        
        foreach ($dependencies as $dependency) {
            if ($this->isInfrastructureDependency($dependency)) {
                return false;
            }
        }
        
        return true;
    }
}
```

### Бизнес метрики

1. **Скорость разработки**
   - Time to market для новых фичей
   - Velocity команды
   - Lead time для изменений

2. **Качество**
   - Количество багов в продакшене
   - Время на исправление багов
   - Customer satisfaction

3. **Поддерживаемость**
   - Время на onboarding новых разработчиков
   - Количество архитектурных решений
   - Frequency of refactoring

### Dashboard для мониторинга

```php
<?php
class MigrationDashboard
{
    private MetricsCollector $metrics;

    public function getProgress(): array
    {
        return [
            'migration_percentage' => $this->calculateMigrationPercentage(),
            'domains_migrated' => $this->getCompletedDomains(),
            'legacy_code_remaining' => $this->getLegacyCodePercentage(),
            'test_coverage_new_code' => $this->getNewCodeCoverage(),
            'performance_impact' => $this->getPerformanceMetrics(),
            'team_confidence' => $this->getTeamConfidenceScore(),
        ];
    }

    private function calculateMigrationPercentage(): float
    {
        $totalComponents = $this->getTotalComponents();
        $migratedComponents = $this->getMigratedComponents();
        
        return $totalComponents > 0 ? $migratedComponents / $totalComponents * 100 : 0;
    }
}
```

---

Миграция на Clean Architecture — это долгосрочный процесс, требующий планирования, терпения и постоянного обучения команды. Главное — начать с малого, измерять прогресс и корректировать подход по мере накопления опыта.

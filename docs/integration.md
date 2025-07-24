# Интеграция с существующими системами

> Как встроить Pristine Framework в действующие проекты без полного переписывания

## 🎯 Стратегии интеграции

### 1. Постепенная миграция (Strangler Fig Pattern)
Заменяйте части системы постепенно, не затрагивая работающий функционал:

```php
// Этап 1: Добавляем новый функционал через Clean Architecture
class NewPostController {
    public function create(Request $request): Response {
        // Новая логика через Use Cases
        return $this->createPost->execute($data);
    }
}

// Этап 2: Постепенно мигрируем существующий функционал
class LegacyPostController {
    public function update(Request $request): Response {
        // Пока работаем по-старому
        $post = Post::find($id);
        $post->update($data);
        
        // Но уже используем события из новой архитектуры
        $this->eventDispatcher->dispatch(new PostUpdatedEvent($post->id));
    }
}

// Этап 3: Полная миграция на новую архитектуру
class PostController {
    public function update(Request $request): Response {
        // Полностью через новую архитектуру
        return $this->updatePost->execute($id, $data);
    }
}
```

### 2. Адаптер Pattern
Используйте адаптеры для интеграции с существующими компонентами:

```php
// Адаптер для существующей системы пользователей
class LegacyUserRepositoryAdapter implements UserRepositoryInterface
{
    private LegacyUserManager $legacyManager;

    public function findById(int $id): ?User
    {
        $legacyUser = $this->legacyManager->getUserById($id);
        
        return $legacyUser ? $this->adaptToCleanArchitecture($legacyUser) : null;
    }

    public function save(User $user): User
    {
        $legacyData = $this->adaptToLegacyFormat($user);
        $savedLegacyUser = $this->legacyManager->saveUser($legacyData);
        
        return $this->adaptToCleanArchitecture($savedLegacyUser);
    }

    private function adaptToCleanArchitecture(LegacyUser $legacyUser): User
    {
        $user = new User();
        $user->setId($legacyUser->getId());
        $user->setName($legacyUser->getFullName());
        $user->setEmail($legacyUser->getEmailAddress());
        
        return $user;
    }

    private function adaptToLegacyFormat(User $user): array
    {
        return [
            'id' => $user->getId(),
            'full_name' => $user->getName(),
            'email_address' => $user->getEmail(),
        ];
    }
}
```

### 3. Anti-Corruption Layer
Защитите новую архитектуру от "загрязнения" старым кодом:

```php
// Слой защиты от коррупции
class PaymentAntiCorruptionLayer
{
    private LegacyPaymentGateway $legacyGateway;

    public function processPayment(PaymentRequest $request): PaymentResult
    {
        try {
            // Конверсия в формат legacy системы
            $legacyRequest = $this->convertToLegacyFormat($request);
            
            // Вызов legacy API
            $legacyResponse = $this->legacyGateway->processPayment($legacyRequest);
            
            // Конверсия обратно в чистый формат
            return $this->convertToCleanFormat($legacyResponse);
            
        } catch (LegacyException $e) {
            // Преобразование legacy исключений
            throw new PaymentException($e->getMessage(), $e->getCode(), $e);
        }
    }

    private function convertToLegacyFormat(PaymentRequest $request): array
    {
        return [
            'amount_cents' => $request->getAmount()->getCents(),
            'currency_code' => $request->getAmount()->getCurrency()->getCode(),
            'card_number' => $request->getCard()->getNumber(),
            // Маппинг полей...
        ];
    }

    private function convertToCleanFormat(array $legacyResponse): PaymentResult
    {
        return new PaymentResult(
            new TransactionId($legacyResponse['transaction_id']),
            PaymentStatus::fromString($legacyResponse['status']),
            new Money($legacyResponse['amount_cents'], Currency::fromCode($legacyResponse['currency']))
        );
    }
}
```

---

## 🔧 Интеграция с популярными фреймворками

### Laravel Integration

#### Использование Pristine в Laravel проекте

**1. Структура директорий:**
```
app/
├── Http/Controllers/          # Laravel контроллеры (адаптеры)
├── Domain/                    # Pristine Domain слой
│   ├── User/
│   ├── Post/
│   └── ...
├── Infrastructure/            # Pristine Infrastructure
│   ├── Repository/
│   └── Services/
└── Providers/                 # Service Providers для DI
    └── DomainServiceProvider.php
```

**2. Service Provider для регистрации зависимостей:**
```php
// app/Providers/DomainServiceProvider.php
class DomainServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Регистрация Repository
        $this->app->bind(
            UserRepositoryInterface::class,
            EloquentUserRepository::class
        );

        // Регистрация Use Cases
        $this->app->bind(CreateUser::class, function ($app) {
            return new CreateUser(
                $app->make(UserRepositoryInterface::class),
                $app->make(EventDispatcherInterface::class)
            );
        });
    }
}
```

**3. Eloquent Repository адаптер:**
```php
class EloquentUserRepository implements UserRepositoryInterface
{
    public function save(User $user): User
    {
        $eloquentUser = EloquentUser::updateOrCreate(
            ['id' => $user->getId()],
            [
                'name' => $user->getName(),
                'email' => $user->getEmail(),
            ]
        );

        return $this->mapToAggregate($eloquentUser);
    }

    public function findById(int $id): ?User
    {
        $eloquentUser = EloquentUser::find($id);
        
        return $eloquentUser ? $this->mapToAggregate($eloquentUser) : null;
    }

    private function mapToAggregate(EloquentUser $eloquentUser): User
    {
        $user = new User();
        $user->setId($eloquentUser->id);
        $user->setName($eloquentUser->name);
        $user->setEmail($eloquentUser->email);
        
        return $user;
    }
}
```

**4. Laravel контроллер как адаптер:**
```php
class UserController extends Controller
{
    public function __construct(
        private CreateUser $createUser,
        private GetUser $getUser
    ) {}

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
        ]);

        try {
            $user = $this->createUser->execute(
                $request->input('name'),
                $request->input('email')
            );

            return response()->json([
                'data' => $user->toArray()
            ], 201);

        } catch (\DomainException $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 422);
        }
    }
}
```

### Symfony Integration

**1. Конфигурация сервисов:**
```yaml
# config/services.yaml
services:
    # Auto-wire Use Cases
    App\Domain\User\UseCase\:
        resource: '../src/Domain/User/UseCase'
        autowire: true

    # Repository bindings
    App\Domain\User\Repository\UserRepositoryInterface:
        class: App\Infrastructure\Repository\DoctrineUserRepository
        autowire: true

    # Event Dispatcher
    App\Domain\Event\EventDispatcherInterface:
        class: App\Infrastructure\Event\SymfonyEventDispatcher
        autowire: true
```

**2. Doctrine Repository адаптер:**
```php
class DoctrineUserRepository implements UserRepositoryInterface
{
    public function __construct(
        private EntityManagerInterface $em,
        private EntityRepository $repository
    ) {
        $this->repository = $em->getRepository(UserEntity::class);
    }

    public function save(User $user): User
    {
        $entity = $this->mapToDoctrineEntity($user);
        
        $this->em->persist($entity);
        $this->em->flush();
        
        return $this->mapToAggregate($entity);
    }

    public function findById(int $id): ?User
    {
        $entity = $this->repository->find($id);
        
        return $entity ? $this->mapToAggregate($entity) : null;
    }
}
```

**3. Symfony контроллер:**
```php
class UserController extends AbstractController
{
    public function __construct(
        private CreateUser $createUser
    ) {}

    #[Route('/api/users', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        try {
            $user = $this->createUser->execute(
                $data['name'] ?? '',
                $data['email'] ?? ''
            );

            return $this->json([
                'success' => true,
                'data' => $user->toArray()
            ], 201);

        } catch (\DomainException $e) {
            return $this->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 422);
        }
    }
}
```

### CodeIgniter Integration

**1. Структура и загрузка:**
```php
// application/libraries/DomainBootstrap.php
class DomainBootstrap
{
    private $CI;
    
    public function __construct()
    {
        $this->CI =& get_instance();
        $this->registerDependencies();
    }

    private function registerDependencies(): void
    {
        // Регистрация зависимостей через CI container
        $this->CI->config->set_item('user_repository', 
            new CodeIgniterUserRepository($this->CI->db));
        
        $this->CI->config->set_item('create_user',
            new CreateUser($this->CI->config->item('user_repository')));
    }
}
```

**2. CodeIgniter Repository:**
```php
class CodeIgniterUserRepository implements UserRepositoryInterface
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function save(User $user): User
    {
        $data = [
            'name' => $user->getName(),
            'email' => $user->getEmail(),
        ];

        if ($user->getId()) {
            $this->db->where('id', $user->getId());
            $this->db->update('users', $data);
        } else {
            $this->db->insert('users', $data);
            $user->setId($this->db->insert_id());
        }

        return $user;
    }

    public function findById(int $id): ?User
    {
        $query = $this->db->get_where('users', ['id' => $id]);
        $row = $query->row_array();

        if (!$row) {
            return null;
        }

        $user = new User();
        $user->setId($row['id']);
        $user->setName($row['name']);
        $user->setEmail($row['email']);

        return $user;
    }
}
```

---

## 🗄️ Интеграция с базами данных

### Работа с существующими схемами БД

#### Адаптация к legacy схемам
```php
class LegacyPostRepository implements PostRepositoryInterface
{
    public function save(Post $post): Post
    {
        // Адаптация к существующей схеме БД
        $data = [
            'post_title' => $post->getTitle(),           // title -> post_title
            'post_content' => $post->getContent(),       // content -> post_content
            'author_user_id' => $post->getAuthorId(),    // authorId -> author_user_id
            'post_status' => $post->getStatus()->getValue(), // status -> post_status
            'created_date' => $post->getCreatedAt()->format('Y-m-d H:i:s'), // createdAt -> created_date
        ];

        if ($post->getId()) {
            $this->db->update('legacy_posts', $data, ['post_id' => $post->getId()]);
        } else {
            $this->db->insert('legacy_posts', $data);
            $post->setId($this->db->lastInsertId());
        }

        return $post;
    }

    public function findById(int $id): ?Post
    {
        $row = $this->db->select('*')
                       ->from('legacy_posts')
                       ->where('post_id', $id)
                       ->get()
                       ->row_array();

        if (!$row) {
            return null;
        }

        return $this->mapRowToAggregate($row);
    }

    private function mapRowToAggregate(array $row): Post
    {
        $post = new Post();
        $post->setId($row['post_id']);
        $post->setTitle($row['post_title']);
        $post->setContent($row['post_content']);
        $post->setAuthorId($row['author_user_id']);
        $post->setStatus(PostStatus::fromString($row['post_status']));
        $post->setCreatedAt(new \DateTime($row['created_date']));

        // Загрузка связанных данных
        $this->loadComments($post);
        $this->loadCategories($post);

        return $post;
    }
}
```

#### Работа с несколькими источниками данных
```php
class HybridUserRepository implements UserRepositoryInterface
{
    private UserRepositoryInterface $primaryRepo;
    private UserRepositoryInterface $legacyRepo;

    public function __construct(
        UserRepositoryInterface $primaryRepo,
        UserRepositoryInterface $legacyRepo
    ) {
        $this->primaryRepo = $primaryRepo;
        $this->legacyRepo = $legacyRepo;
    }

    public function findById(int $id): ?User
    {
        // Сначала ищем в новой системе
        $user = $this->primaryRepo->findById($id);

        // Если не найден, ищем в legacy системе
        if (!$user) {
            $user = $this->legacyRepo->findById($id);
            
            // Если найден в legacy, мигрируем в новую систему
            if ($user) {
                $this->primaryRepo->save($user);
            }
        }

        return $user;
    }

    public function save(User $user): User
    {
        // Сохраняем в обеих системах для переходного периода
        $savedUser = $this->primaryRepo->save($user);
        
        try {
            $this->legacyRepo->save($savedUser);
        } catch (\Exception $e) {
            // Логируем ошибку, но не падаем
            error_log("Failed to sync user to legacy system: " . $e->getMessage());
        }

        return $savedUser;
    }
}
```

---

## 🎨 Интеграция с CMS системами

### Bitrix Integration

**1. Адаптер для пользователей Bitrix:**
```php
use CUser;

class BitrixUserRepository implements UserRepositoryInterface
{
    private CUser $bitrixUser;

    public function __construct()
    {
        $this->bitrixUser = new CUser();
    }

    public function findById(int $id): ?User
    {
        $bitrixData = $this->bitrixUser->GetByID($id)->Fetch();
        
        if (!$bitrixData) {
            return null;
        }

        return $this->mapFromBitrix($bitrixData);
    }

    public function save(User $user): User
    {
        $fields = $this->mapToBitrix($user);

        if ($user->getId()) {
            $result = $this->bitrixUser->Update($user->getId(), $fields);
        } else {
            $result = $this->bitrixUser->Add($fields);
            if ($result) {
                $user->setId($result);
            }
        }

        if (!$result) {
            throw new \RuntimeException('Ошибка сохранения пользователя: ' . $this->bitrixUser->LAST_ERROR);
        }

        return $user;
    }

    private function mapFromBitrix(array $bitrixData): User
    {
        $user = new User();
        $user->setId((int) $bitrixData['ID']);
        $user->setName(trim($bitrixData['NAME'] . ' ' . $bitrixData['LAST_NAME']));
        $user->setEmail($bitrixData['EMAIL']);

        return $user;
    }

    private function mapToBitrix(User $user): array
    {
        $nameParts = explode(' ', $user->getName(), 2);
        
        return [
            'NAME' => $nameParts[0] ?? '',
            'LAST_NAME' => $nameParts[1] ?? '',
            'EMAIL' => $user->getEmail(),
            'ACTIVE' => 'Y',
        ];
    }
}
```

**2. Интеграция с инфоблоками Bitrix:**
```php
class BitrixPostRepository implements PostRepositoryInterface
{
    private int $iblockId;

    public function __construct(int $iblockId = 1)
    {
        $this->iblockId = $iblockId;
    }

    public function save(Post $post): Post
    {
        $element = new \CIBlockElement();
        
        $fields = [
            'IBLOCK_ID' => $this->iblockId,
            'NAME' => $post->getTitle(),
            'DETAIL_TEXT' => $post->getContent(),
            'CREATED_BY' => $post->getAuthorId(),
            'ACTIVE' => $post->isPublished() ? 'Y' : 'N',
        ];

        if ($post->getId()) {
            $result = $element->Update($post->getId(), $fields);
        } else {
            $result = $element->Add($fields);
            if ($result) {
                $post->setId($result);
            }
        }

        if (!$result) {
            throw new \RuntimeException('Ошибка сохранения поста: ' . $element->LAST_ERROR);
        }

        return $post;
    }

    public function findById(int $id): ?Post
    {
        $element = \CIBlockElement::GetByID($id)->GetNext();
        
        if (!$element) {
            return null;
        }

        return $this->mapFromBitrix($element);
    }

    private function mapFromBitrix(array $element): Post
    {
        $post = new Post();
        $post->setId((int) $element['ID']);
        $post->setTitle($element['NAME']);
        $post->setContent($element['DETAIL_TEXT']);
        $post->setAuthorId((int) $element['CREATED_BY']);
        $post->setCreatedAt(new \DateTime($element['DATE_CREATE']));

        if ($element['ACTIVE'] === 'Y') {
            $post->publish();
        }

        return $post;
    }
}
```

### WordPress Integration

**1. WordPress адаптер для постов:**
```php
class WordPressPostRepository implements PostRepositoryInterface
{
    public function save(Post $post): Post
    {
        $postData = [
            'post_title' => $post->getTitle(),
            'post_content' => $post->getContent(),
            'post_author' => $post->getAuthorId(),
            'post_status' => $post->isPublished() ? 'publish' : 'draft',
        ];

        if ($post->getId()) {
            $postData['ID'] = $post->getId();
            $result = wp_update_post($postData);
        } else {
            $result = wp_insert_post($postData);
            if ($result && !is_wp_error($result)) {
                $post->setId($result);
            }
        }

        if (is_wp_error($result)) {
            throw new \RuntimeException('Ошибка сохранения поста: ' . $result->get_error_message());
        }

        return $post;
    }

    public function findById(int $id): ?Post
    {
        $wpPost = get_post($id);
        
        if (!$wpPost) {
            return null;
        }

        return $this->mapFromWordPress($wpPost);
    }

    private function mapFromWordPress(\WP_Post $wpPost): Post
    {
        $post = new Post();
        $post->setId($wpPost->ID);
        $post->setTitle($wpPost->post_title);
        $post->setContent($wpPost->post_content);
        $post->setAuthorId($wpPost->post_author);
        $post->setCreatedAt(new \DateTime($wpPost->post_date));

        if ($wpPost->post_status === 'publish') {
            $post->publish();
        }

        return $post;
    }
}
```

---

## 📊 Мониторинг интеграции

### Отслеживание миграции

**1. Метрики для контроля процесса:**
```php
class MigrationMetrics
{
    private MetricsCollectorInterface $metrics;

    public function trackLegacyUsage(string $component): void
    {
        $this->metrics->increment('legacy.usage', [
            'component' => $component,
            'timestamp' => time()
        ]);
    }

    public function trackCleanArchitectureUsage(string $useCase): void
    {
        $this->metrics->increment('clean_architecture.usage', [
            'use_case' => $useCase,
            'timestamp' => time()
        ]);
    }

    public function trackMigrationProgress(string $module, float $percentage): void
    {
        $this->metrics->gauge('migration.progress', $percentage, [
            'module' => $module
        ]);
    }
}
```

**2. Логирование для отладки:**
```php
class IntegrationLogger
{
    private LoggerInterface $logger;

    public function logAdapterCall(string $adapter, string $method, array $context = []): void
    {
        $this->logger->info("Adapter call: {$adapter}::{$method}", [
            'adapter' => $adapter,
            'method' => $method,
            'context' => $context,
            'timestamp' => time()
        ]);
    }

    public function logLegacyFallback(string $reason, array $context = []): void
    {
        $this->logger->warning("Legacy fallback triggered: {$reason}", [
            'reason' => $reason,
            'context' => $context,
            'timestamp' => time()
        ]);
    }
}
```

---

## 🚀 План поэтапной миграции

### Рекомендуемая последовательность

**Этап 1: Подготовка**
- [ ] Анализ существующей архитектуры
- [ ] Выбор стратегии интеграции
- [ ] Настройка системы метрик
- [ ] Создание адаптеров для критичных компонентов

**Этап 2: Пилотный модуль**
- [ ] Выбор простого модуля для миграции
- [ ] Реализация через Clean Architecture
- [ ] A/B тестирование старой и новой реализации
- [ ] Анализ результатов

**Этап 3: Основные модули**
- [ ] Миграция ключевых бизнес-процессов
- [ ] Интеграция с существующими данными
- [ ] Постепенное отключение legacy кода
- [ ] Рефакторинг связанных компонентов

**Этап 4: Завершение**
- [ ] Миграция оставшихся модулей
- [ ] Удаление legacy кода
- [ ] Оптимизация производительности
- [ ] Документирование новой архитектуры

---

**Помните:** Миграция к Clean Architecture - это марафон, не спринт. Планируйте постепенные изменения и всегда имейте план отката!

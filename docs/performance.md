# Performance Guidelines

> Рекомендации по оптимизации производительности в Pristine Framework

## 📊 Содержание

- [Общие принципы](#общие-принципы)
- [Доменный слой](#доменный-слой)
- [Репозитории и базы данных](#репозитории-и-базы-данных)
- [Коллекции](#коллекции)
- [События](#события)
- [Кэширование](#кэширование)
- [Профилирование](#профилирование)
- [Метрики](#метрики)

---

## 🎯 Общие принципы

### 1. Ленивая загрузка
Используйте ленивую загрузку для связанных сущностей:

```php
class Post implements AggregateInterface
{
    private ?CommentCollection $comments = null;
    private CommentRepositoryInterface $commentRepository;

    public function getComments(): CommentCollection
    {
        if ($this->comments === null) {
            $this->comments = $this->commentRepository->findByPostId($this->getId());
        }
        return $this->comments;
    }
}
```

### 2. Пакетная обработка
Загружайте связанные данные батчами:

```php
class PostService
{
    public function getPostsWithAuthors(array $postIds): array
    {
        // ❌ N+1 проблема
        // foreach ($posts as $post) {
        //     $post->setAuthor($this->userRepository->findById($post->getAuthorId()));
        // }

        // ✅ Пакетная загрузка
        $posts = $this->postRepository->findByIds($postIds);
        $authorIds = array_map(fn($post) => $post->getAuthorId(), $posts);
        $authors = $this->userRepository->findByIds($authorIds);
        
        $authorsMap = [];
        foreach ($authors as $author) {
            $authorsMap[$author->getId()] = $author;
        }
        
        foreach ($posts as $post) {
            $post->setAuthor($authorsMap[$post->getAuthorId()]);
        }
        
        return $posts;
    }
}
```

## 🏗️ Доменный слой

### Value Objects - кэширование

Кэшируйте часто создаваемые Value Objects:

```php
class Email implements StringValueObjectInterface
{
    private static array $cache = [];
    private string $value;

    public function __construct(string $value)
    {
        if (isset(self::$cache[$value])) {
            $this->value = self::$cache[$value]->value;
            return;
        }

        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException('Некорректный email');
        }

        $this->value = $value;
        self::$cache[$value] = $this;
    }

    public static function clearCache(): void
    {
        self::$cache = [];
    }
}
```

### Агрегаты - оптимизация загрузки

```php
class Order implements AggregateInterface
{
    private bool $itemsLoaded = false;
    private OrderItemCollection $items;

    public function getItems(): OrderItemCollection
    {
        if (!$this->itemsLoaded) {
            $this->loadItems();
        }
        return $this->items;
    }

    public function getTotalAmount(): Money
    {
        // Можем вычислить без загрузки items, если есть кэшированное значение
        if ($this->cachedTotalAmount !== null) {
            return $this->cachedTotalAmount;
        }

        $total = Money::zero();
        foreach ($this->getItems() as $item) {
            $total = $total->add($item->getAmount());
        }

        $this->cachedTotalAmount = $total;
        return $total;
    }
}
```

## 🗄️ Репозитории и базы данных

### Оптимизация запросов

```php
interface ProductRepositoryInterface
{
    // ✅ Эффективные методы поиска
    public function findByIds(array $ids): ProductCollection;
    public function findByCategory(int $categoryId, int $limit = 100): ProductCollection;
    public function findAvailableInStock(): ProductCollection;
    
    // ✅ Поиск с пагинацией
    public function findPaginated(int $page, int $perPage): PaginatedResult;
    
    // ✅ Агрегированные данные
    public function getStatistics(): ProductStatistics;
}

class ProductRepository implements ProductRepositoryInterface
{
    public function findByCategory(int $categoryId, int $limit = 100): ProductCollection
    {
        // Оптимизированный запрос с LIMIT
        $sql = "
            SELECT p.*, c.name as category_name 
            FROM products p 
            JOIN categories c ON p.category_id = c.id 
            WHERE p.category_id = :categoryId 
            AND p.is_active = 1
            ORDER BY p.created_at DESC
            LIMIT :limit
        ";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['categoryId' => $categoryId, 'limit' => $limit]);
        
        return $this->hydrateCollection($stmt->fetchAll());
    }

    public function getStatistics(): ProductStatistics
    {
        // Один запрос для всей статистики
        $sql = "
            SELECT 
                COUNT(*) as total_products,
                COUNT(CASE WHEN is_active = 1 THEN 1 END) as active_products,
                AVG(price) as average_price,
                SUM(stock_quantity) as total_stock
            FROM products
        ";
        
        $result = $this->db->query($sql)->fetch();
        return new ProductStatistics($result);
    }
}
```

### Использование индексов

```sql
-- Индексы для частых запросов
CREATE INDEX idx_products_category_active ON products(category_id, is_active);
CREATE INDEX idx_products_created_at ON products(created_at);
CREATE INDEX idx_orders_user_status ON orders(user_id, status);
CREATE INDEX idx_order_items_order_id ON order_items(order_id);

-- Композитные индексы для сложных фильтров
CREATE INDEX idx_products_search ON products(category_id, price, is_active, created_at);
```

## 📋 Коллекции

### Оптимизация операций с коллекциями

```php
class ProductCollection implements CollectionInterface
{
    private array $items = [];
    private array $indexById = []; // Индекс для быстрого поиска

    public function addItem(object $product): self
    {
        if (!$product instanceof Product) {
            throw new \InvalidArgumentException('Expected Product');
        }

        $id = $product->getId();
        if ($id && isset($this->indexById[$id])) {
            return $this; // Уже есть
        }

        $this->items[] = $product;
        
        if ($id) {
            $this->indexById[$id] = $product;
        }

        return $this;
    }

    public function findById(int $id): ?Product
    {
        return $this->indexById[$id] ?? null;
    }

    public function filterByCategory(Category $category): self
    {
        $filtered = new self();
        
        foreach ($this->items as $product) {
            if ($product->getCategory()->equals($category)) {
                $filtered->addItem($product);
            }
        }
        
        return $filtered;
    }

    // Оптимизированная сортировка
    public function sortByPrice(bool $ascending = true): self
    {
        $sorted = clone $this;
        
        usort($sorted->items, function(Product $a, Product $b) use ($ascending) {
            $priceA = $a->getPrice()->getValue();
            $priceB = $b->getPrice()->getValue();
            
            $result = $priceA <=> $priceB;
            return $ascending ? $result : -$result;
        });
        
        // Пересобираем индекс
        $sorted->rebuildIndex();
        
        return $sorted;
    }

    private function rebuildIndex(): void
    {
        $this->indexById = [];
        foreach ($this->items as $product) {
            if ($id = $product->getId()) {
                $this->indexById[$id] = $product;
            }
        }
    }
}
```

## 🔔 События

### Асинхронная обработка событий

```php
use Infrastructure\Event\AsyncEventDispatcher;

class OrderService
{
    public function __construct(
        private AsyncEventDispatcher $eventDispatcher
    ) {}

    public function processOrder(Order $order): void
    {
        // Синхронные критически важные события
        $this->eventDispatcher->dispatchSync(
            new OrderValidatedEvent($order->getId())
        );

        // Асинхронные некритичные события
        $this->eventDispatcher->dispatchAsync([
            new OrderCreatedEvent($order->getId()),
            new InventoryUpdatedEvent($order->getItems()),
            new EmailNotificationEvent($order->getUser()->getEmail())
        ]);
    }
}

class AsyncEventDispatcher
{
    public function dispatchAsync(array $events): void
    {
        // Отправляем в очередь (Redis, RabbitMQ, etc.)
        foreach ($events as $event) {
            $this->queueManager->push('events', serialize($event));
        }
    }

    public function dispatchSync(DomainEventInterface $event): void
    {
        // Обрабатываем сразу
        $this->handleEvent($event);
    }
}
```

### Пакетная обработка событий

```php
class EventBatchProcessor
{
    private array $eventBatch = [];
    private int $batchSize = 100;

    public function addEvent(DomainEventInterface $event): void
    {
        $this->eventBatch[] = $event;
        
        if (count($this->eventBatch) >= $this->batchSize) {
            $this->processBatch();
        }
    }

    public function processBatch(): void
    {
        if (empty($this->eventBatch)) {
            return;
        }

        // Группируем события по типу
        $eventsByType = [];
        foreach ($this->eventBatch as $event) {
            $type = get_class($event);
            $eventsByType[$type][] = $event;
        }

        // Обрабатываем пакетами
        foreach ($eventsByType as $type => $events) {
            $this->processEventType($type, $events);
        }

        $this->eventBatch = [];
    }

    private function processEventType(string $type, array $events): void
    {
        $handler = $this->getHandlerForEventType($type);
        
        if ($handler instanceof BatchEventHandler) {
            $handler->handleBatch($events);
        } else {
            foreach ($events as $event) {
                $handler->handle($event);
            }
        }
    }
}
```

## 🚀 Кэширование

### Кэширование на уровне репозитория

```php
class CachedProductRepository implements ProductRepositoryInterface
{
    public function __construct(
        private ProductRepositoryInterface $repository,
        private CacheInterface $cache
    ) {}

    public function findById(int $id): ?Product
    {
        $cacheKey = "product:{$id}";
        
        $cached = $this->cache->get($cacheKey);
        if ($cached !== null) {
            return $this->deserialize($cached);
        }

        $product = $this->repository->findById($id);
        
        if ($product) {
            $this->cache->set($cacheKey, $this->serialize($product), 3600);
        }

        return $product;
    }

    public function persist(Product $product): Product
    {
        $saved = $this->repository->persist($product);
        
        // Инвалидируем кэш
        if ($saved->getId()) {
            $this->cache->delete("product:{$saved->getId()}");
        }

        return $saved;
    }
}
```

### Кэш запросов

```php
class QueryCache
{
    private array $cache = [];

    public function remember(string $key, callable $callback, int $ttl = 3600): mixed
    {
        if (isset($this->cache[$key])) {
            $item = $this->cache[$key];
            if ($item['expires'] > time()) {
                return $item['value'];
            }
        }

        $value = $callback();
        $this->cache[$key] = [
            'value' => $value,
            'expires' => time() + $ttl
        ];

        return $value;
    }

    public function invalidate(string $pattern): void
    {
        foreach ($this->cache as $key => $item) {
            if (fnmatch($pattern, $key)) {
                unset($this->cache[$key]);
            }
        }
    }
}

// Использование
class ProductService
{
    public function getPopularProducts(): ProductCollection
    {
        return $this->queryCache->remember(
            'popular_products',
            fn() => $this->productRepository->findPopular(),
            1800 // 30 минут
        );
    }
}
```

## 📊 Профилирование

### Измерение производительности

```php
class PerformanceProfiler
{
    private array $timers = [];
    private array $memorySnapshots = [];

    public function startTimer(string $name): void
    {
        $this->timers[$name] = [
            'start' => hrtime(true),
            'memory_start' => memory_get_usage(true)
        ];
    }

    public function endTimer(string $name): array
    {
        if (!isset($this->timers[$name])) {
            throw new \InvalidArgumentException("Timer {$name} not started");
        }

        $timer = $this->timers[$name];
        $duration = (hrtime(true) - $timer['start']) / 1e6; // milliseconds
        $memoryUsed = memory_get_usage(true) - $timer['memory_start'];

        unset($this->timers[$name]);

        return [
            'duration_ms' => $duration,
            'memory_used_bytes' => $memoryUsed,
            'memory_used_mb' => round($memoryUsed / 1024 / 1024, 2)
        ];
    }

    public function profile(string $name, callable $callback): mixed
    {
        $this->startTimer($name);
        
        try {
            $result = $callback();
            $stats = $this->endTimer($name);
            
            $this->logPerformance($name, $stats);
            
            return $result;
        } catch (\Throwable $e) {
            $this->endTimer($name);
            throw $e;
        }
    }

    private function logPerformance(string $name, array $stats): void
    {
        error_log(sprintf(
            "Performance [%s]: %.2fms, %.2fMB",
            $name,
            $stats['duration_ms'],
            $stats['memory_used_mb']
        ));
    }
}

// Использование в Use Case
class ProcessOrder
{
    public function execute(int $userId, array $items): Order
    {
        return $this->profiler->profile('process_order', function() use ($userId, $items) {
            // Основная логика Use Case
            return $this->doExecute($userId, $items);
        });
    }
}
```

## 📈 Метрики

### Сбор метрик производительности

```php
class MetricsCollector
{
    private array $counters = [];
    private array $timers = [];
    private array $gauges = [];

    public function incrementCounter(string $name, int $value = 1, array $tags = []): void
    {
        $key = $this->buildKey($name, $tags);
        $this->counters[$key] = ($this->counters[$key] ?? 0) + $value;
    }

    public function recordTimer(string $name, float $duration, array $tags = []): void
    {
        $key = $this->buildKey($name, $tags);
        $this->timers[$key][] = $duration;
    }

    public function setGauge(string $name, float $value, array $tags = []): void
    {
        $key = $this->buildKey($name, $tags);
        $this->gauges[$key] = $value;
    }

    public function getReport(): array
    {
        return [
            'counters' => $this->counters,
            'timers' => array_map(fn($times) => [
                'count' => count($times),
                'avg' => array_sum($times) / count($times),
                'min' => min($times),
                'max' => max($times)
            ], $this->timers),
            'gauges' => $this->gauges
        ];
    }

    private function buildKey(string $name, array $tags): string
    {
        if (empty($tags)) {
            return $name;
        }

        $tagString = implode(',', array_map(
            fn($key, $value) => "{$key}={$value}",
            array_keys($tags),
            array_values($tags)
        ));

        return "{$name}|{$tagString}";
    }
}

// Интеграция с Use Cases
class OrderUseCase
{
    public function execute(int $userId, array $items): Order
    {
        $startTime = hrtime(true);
        
        try {
            $order = $this->processOrder($userId, $items);
            
            $this->metrics->incrementCounter('orders.created', 1, [
                'user_type' => $this->getUserType($userId)
            ]);
            
            return $order;
            
        } catch (\Exception $e) {
            $this->metrics->incrementCounter('orders.failed', 1, [
                'error_type' => get_class($e)
            ]);
            throw $e;
            
        } finally {
            $duration = (hrtime(true) - $startTime) / 1e6;
            $this->metrics->recordTimer('orders.processing_time', $duration);
        }
    }
}
```

## ⚡ Чек-лист оптимизации

### ✅ Доменный слой
- [ ] Value Objects кэшируются для часто используемых значений
- [ ] Коллекции используют индексы для быстрого поиска
- [ ] Агрегаты загружают связанные данные по требованию
- [ ] Вычисления кэшируются в агрегатах

### ✅ Репозитории
- [ ] Используются оптимизированные SQL запросы
- [ ] Настроены индексы базы данных
- [ ] Реализована пакетная загрузка
- [ ] Настроено кэширование результатов

### ✅ События
- [ ] Некритичные события обрабатываются асинхронно
- [ ] Используется пакетная обработка событий
- [ ] Настроены очереди для тяжелых операций

### ✅ Общее
- [ ] Настроено профилирование критичных операций
- [ ] Собираются метрики производительности
- [ ] Используется кэширование на разных уровнях
- [ ] Проводится регулярный мониторинг производительности

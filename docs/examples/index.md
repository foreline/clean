# Примеры кода

> Готовые решения и шаблоны для типовых задач

## 📑 Содержание

### Доменный слой
- [Создание сущностей и агрегатов](#создание-сущностей-и-агрегатов)
- [Value Objects для типовых задач](#value-objects-для-типовых-задач)
- [Коллекции с бизнес-логикой](#коллекции-с-бизнес-логикой)
- [Use Cases для CRUD операций](#use-cases-для-crud-операций)
- [Доменные события](#доменные-события)

### Инфраструктурный слой
- [Repository реализации](#repository-реализации)
- [DTO и маппинг данных](#dto-и-маппинг-данных)
- [Интеграция с ORM](#интеграция-с-orm)

### Слой представления
- [HTTP контроллеры](#http-контроллеры)
- [CLI команды](#cli-команды)
- [Middleware и фильтры](#middleware-и-фильтры)

### Паттерны интеграции
- [Адаптеры для legacy систем](#адаптеры-для-legacy-систем)
- [Event-driven архитектура](#event-driven-архитектура)

---

## Доменный слой

### Создание сущностей и агрегатов

#### Базовая структура Entity + Aggregate

```php
<?php
// Сущность для данных
class ProductEntity implements EntityInterface
{
    private ?int $id = null;
    private string $name;
    private string $description;
    private float $price;
    private int $categoryId;
    private \DateTime $createdAt;

    public function getId(): ?int { return $this->id; }
    public function setId(?int $id): self { $this->id = $id; return $this; }
    
    public function getName(): string { return $this->name; }
    public function setName(string $name): self { $this->name = $name; return $this; }
    
    public function getDescription(): string { return $this->description; }
    public function setDescription(string $description): self { $this->description = $description; return $this; }
    
    public function getPrice(): float { return $this->price; }
    public function setPrice(float $price): self { $this->price = $price; return $this; }
    
    public function getCategoryId(): int { return $this->categoryId; }
    public function setCategoryId(int $categoryId): self { $this->categoryId = $categoryId; return $this; }
    
    public function getCreatedAt(): \DateTime { return $this->createdAt; }
    public function setCreatedAt(\DateTime $createdAt): self { $this->createdAt = $createdAt; return $this; }
}

// Агрегат для бизнес-логики
class Product extends ProductEntity implements AggregateInterface
{
    private ProductStatus $status;
    private ReviewCollection $reviews;
    private array $domainEvents = [];

    public function __construct()
    {
        $this->status = ProductStatus::draft();
        $this->reviews = new ReviewCollection();
        $this->setCreatedAt(new \DateTime());
    }

    /**
     * Публикация товара с проверками
     */
    public function publish(): void
    {
        $this->validateCanPublish();
        $this->status = ProductStatus::published();
        $this->raiseEvent(new ProductPublishedEvent($this->getId()));
    }

    /**
     * Применение скидки
     */
    public function applyDiscount(DiscountPercentage $discount): void
    {
        if (!$this->status->equals(ProductStatus::published())) {
            throw new \DomainException('Скидку можно применить только к опубликованным товарам');
        }

        $newPrice = $this->price * (1 - $discount->getValue() / 100);
        $this->setPrice($newPrice);
        
        $this->raiseEvent(new ProductDiscountAppliedEvent($this->getId(), $discount->getValue()));
    }

    /**
     * Добавление отзыва
     */
    public function addReview(Review $review): void
    {
        if (!$this->status->equals(ProductStatus::published())) {
            throw new \DomainException('Нельзя оставлять отзывы на неопубликованные товары');
        }

        $this->reviews->addItem($review);
        $this->raiseEvent(new ProductReviewAddedEvent($this->getId(), $review->getId()));
    }

    /**
     * Получение средней оценки
     */
    public function getAverageRating(): float
    {
        if ($this->reviews->count() === 0) {
            return 0.0;
        }

        $totalRating = 0;
        foreach ($this->reviews as $review) {
            $totalRating += $review->getRating();
        }

        return round($totalRating / $this->reviews->count(), 2);
    }

    public function isPublished(): bool
    {
        return $this->status->equals(ProductStatus::published());
    }

    private function validateCanPublish(): void
    {
        if (empty($this->name)) {
            throw new \DomainException('Товар должен иметь название');
        }

        if (empty($this->description)) {
            throw new \DomainException('Товар должен иметь описание');
        }

        if ($this->price <= 0) {
            throw new \DomainException('Цена товара должна быть положительной');
        }

        if ($this->isPublished()) {
            throw new \DomainException('Товар уже опубликован');
        }
    }

    private function raiseEvent(DomainEventInterface $event): void
    {
        $this->domainEvents[] = $event;
    }

    public function getEvents(): array
    {
        return $this->domainEvents;
    }

    public function clearEvents(): void
    {
        $this->domainEvents = [];
    }

    public function toArray(): array
    {
        return [
            'id' => $this->getId(),
            'name' => $this->getName(),
            'description' => $this->getDescription(),
            'price' => $this->getPrice(),
            'category_id' => $this->getCategoryId(),
            'status' => $this->status->getValue(),
            'average_rating' => $this->getAverageRating(),
            'reviews_count' => $this->reviews->count(),
            'created_at' => $this->getCreatedAt()->format('Y-m-d H:i:s'),
        ];
    }
}
```

### Value Objects для типовых задач

#### Email с валидацией

```php
class Email implements StringValueObjectInterface
{
    private string $value;

    public function __construct(string $value)
    {
        $this->validate($value);
        $this->value = strtolower(trim($value));
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function getDomain(): string
    {
        return substr($this->value, strpos($this->value, '@') + 1);
    }

    public function getLocalPart(): string
    {
        return substr($this->value, 0, strpos($this->value, '@'));
    }

    public function equals(ValueObjectInterface $other): bool
    {
        return $other instanceof self && $this->value === $other->value;
    }

    private function validate(string $email): void
    {
        if (empty($email)) {
            throw new \InvalidArgumentException('Email не может быть пустым');
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException('Некорректный формат email');
        }

        if (strlen($email) > 254) {
            throw new \InvalidArgumentException('Email слишком длинный');
        }
    }
}
```

#### Money с валютой

```php
class Money implements ValueObjectInterface
{
    private int $cents;
    private Currency $currency;

    public function __construct(float $amount, Currency $currency)
    {
        if ($amount < 0) {
            throw new \InvalidArgumentException('Сумма не может быть отрицательной');
        }

        $this->cents = (int) round($amount * 100);
        $this->currency = $currency;
    }

    public function getAmount(): float
    {
        return $this->cents / 100;
    }

    public function getCents(): int
    {
        return $this->cents;
    }

    public function getCurrency(): Currency
    {
        return $this->currency;
    }

    public function add(Money $other): self
    {
        $this->ensureSameCurrency($other);
        return new self(($this->cents + $other->cents) / 100, $this->currency);
    }

    public function subtract(Money $other): self
    {
        $this->ensureSameCurrency($other);
        $newCents = $this->cents - $other->cents;
        
        if ($newCents < 0) {
            throw new \DomainException('Результат не может быть отрицательным');
        }
        
        return new self($newCents / 100, $this->currency);
    }

    public function multiply(float $multiplier): self
    {
        if ($multiplier < 0) {
            throw new \InvalidArgumentException('Множитель не может быть отрицательным');
        }
        
        return new self(($this->cents * $multiplier) / 100, $this->currency);
    }

    public function isGreaterThan(Money $other): bool
    {
        $this->ensureSameCurrency($other);
        return $this->cents > $other->cents;
    }

    public function equals(ValueObjectInterface $other): bool
    {
        return $other instanceof self 
            && $this->cents === $other->cents 
            && $this->currency->equals($other->currency);
    }

    public function format(): string
    {
        return number_format($this->getAmount(), 2) . ' ' . $this->currency->getCode();
    }

    private function ensureSameCurrency(Money $other): void
    {
        if (!$this->currency->equals($other->currency)) {
            throw new \DomainException('Нельзя выполнять операции с разными валютами');
        }
    }
}

class Currency implements StringValueObjectInterface
{
    private string $code;

    public function __construct(string $code)
    {
        $code = strtoupper($code);
        
        if (!in_array($code, ['USD', 'EUR', 'RUB', 'GBP'])) {
            throw new \InvalidArgumentException('Неподдерживаемая валюта');
        }
        
        $this->code = $code;
    }

    public function getValue(): string
    {
        return $this->code;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function equals(ValueObjectInterface $other): bool
    {
        return $other instanceof self && $this->code === $other->code;
    }

    public static function usd(): self { return new self('USD'); }
    public static function eur(): self { return new self('EUR'); }
    public static function rub(): self { return new self('RUB'); }
}
```

#### Процентная скидка

```php
class DiscountPercentage implements FloatValueObjectInterface
{
    private float $value;

    public function __construct(float $value)
    {
        if ($value < 0 || $value > 100) {
            throw new \InvalidArgumentException('Скидка должна быть от 0 до 100%');
        }

        $this->value = $value;
    }

    public function getValue(): float
    {
        return $this->value;
    }

    public function getDecimal(): float
    {
        return $this->value / 100;
    }

    public function equals(ValueObjectInterface $other): bool
    {
        return $other instanceof self && abs($this->value - $other->value) < 0.01;
    }

    public static function fromString(string $percentage): self
    {
        $value = (float) str_replace('%', '', $percentage);
        return new self($value);
    }

    public function format(): string
    {
        return number_format($this->value, 1) . '%';
    }
}
```

### Коллекции с бизнес-логикой

```php
class ProductCollection implements CollectionInterface
{
    use IteratorTrait;

    /** @var Product[] */
    private array $items = [];

    public function current(): ?Product
    {
        return $this->valid() ? $this->items[$this->position] : null;
    }

    public function getCollection(): array
    {
        return $this->items;
    }

    public function addItem($item): self
    {
        if (!$item instanceof Product) {
            throw new \InvalidArgumentException('Item must be instance of Product');
        }

        if (!$this->contains($item)) {
            $this->items[] = $item;
        }

        return $this;
    }

    public function contains($item): bool
    {
        if (!$item instanceof Product || !$item->getId()) {
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

    /**
     * Фильтрация опубликованных товаров
     */
    public function getPublished(): self
    {
        $published = new self();
        foreach ($this->items as $product) {
            if ($product->isPublished()) {
                $published->addItem($product);
            }
        }
        return $published;
    }

    /**
     * Поиск по категории
     */
    public function getByCategory(int $categoryId): self
    {
        $filtered = new self();
        foreach ($this->items as $product) {
            if ($product->getCategoryId() === $categoryId) {
                $filtered->addItem($product);
            }
        }
        return $filtered;
    }

    /**
     * Фильтрация по ценовому диапазону
     */
    public function getByPriceRange(Money $minPrice, Money $maxPrice): self
    {
        $filtered = new self();
        foreach ($this->items as $product) {
            $productPrice = new Money($product->getPrice(), $minPrice->getCurrency());
            
            if ($productPrice->isGreaterThan($minPrice) && $maxPrice->isGreaterThan($productPrice)) {
                $filtered->addItem($product);
            }
        }
        return $filtered;
    }

    /**
     * Сортировка по цене
     */
    public function sortByPrice(bool $ascending = true): self
    {
        $sorted = clone $this;
        
        usort($sorted->items, function(Product $a, Product $b) use ($ascending) {
            $result = $a->getPrice() <=> $b->getPrice();
            return $ascending ? $result : -$result;
        });
        
        return $sorted;
    }

    /**
     * Сортировка по рейтингу
     */
    public function sortByRating(bool $ascending = false): self
    {
        $sorted = clone $this;
        
        usort($sorted->items, function(Product $a, Product $b) use ($ascending) {
            $result = $a->getAverageRating() <=> $b->getAverageRating();
            return $ascending ? $result : -$result;
        });
        
        return $sorted;
    }

    /**
     * Получение общей стоимости
     */
    public function getTotalValue(Currency $currency): Money
    {
        $total = new Money(0, $currency);
        
        foreach ($this->items as $product) {
            $productPrice = new Money($product->getPrice(), $currency);
            $total = $total->add($productPrice);
        }
        
        return $total;
    }

    /**
     * Конверсия в массив
     */
    public function toArray(): array
    {
        return array_map(fn($product) => $product->toArray(), $this->items);
    }
}
```

### Use Cases для CRUD операций

#### CreateProduct Use Case

```php
class CreateProduct
{
    private ProductRepositoryInterface $productRepository;
    private CategoryRepositoryInterface $categoryRepository;
    private EventDispatcherInterface $eventDispatcher;

    public function __construct(
        ProductRepositoryInterface $productRepository,
        CategoryRepositoryInterface $categoryRepository,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->productRepository = $productRepository;
        $this->categoryRepository = $categoryRepository;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function execute(CreateProductRequest $request): Product
    {
        // Валидация входных данных
        $this->validateRequest($request);

        // Проверка существования категории
        $category = $this->categoryRepository->findById($request->getCategoryId());
        if (!$category) {
            throw new \DomainException('Категория не найдена');
        }

        // Создание продукта
        $product = new Product();
        $product->setName($request->getName());
        $product->setDescription($request->getDescription());
        $product->setPrice($request->getPrice());
        $product->setCategoryId($request->getCategoryId());

        // Сохранение
        $savedProduct = $this->productRepository->save($product);

        // Обработка событий
        foreach ($savedProduct->getEvents() as $event) {
            $this->eventDispatcher->dispatch($event);
        }
        $savedProduct->clearEvents();

        return $savedProduct;
    }

    private function validateRequest(CreateProductRequest $request): void
    {
        if (empty($request->getName())) {
            throw new \InvalidArgumentException('Название товара обязательно');
        }

        if (strlen($request->getName()) > 255) {
            throw new \InvalidArgumentException('Название товара слишком длинное');
        }

        if (empty($request->getDescription())) {
            throw new \InvalidArgumentException('Описание товара обязательно');
        }

        if ($request->getPrice() <= 0) {
            throw new \InvalidArgumentException('Цена должна быть положительной');
        }

        if ($request->getCategoryId() <= 0) {
            throw new \InvalidArgumentException('Некорректная категория');
        }
    }
}

// DTO для запроса
class CreateProductRequest
{
    private string $name;
    private string $description;
    private float $price;
    private int $categoryId;

    public function __construct(string $name, string $description, float $price, int $categoryId)
    {
        $this->name = $name;
        $this->description = $description;
        $this->price = $price;
        $this->categoryId = $categoryId;
    }

    public function getName(): string { return $this->name; }
    public function getDescription(): string { return $this->description; }
    public function getPrice(): float { return $this->price; }
    public function getCategoryId(): int { return $this->categoryId; }

    public static function fromArray(array $data): self
    {
        return new self(
            $data['name'] ?? '',
            $data['description'] ?? '',
            (float) ($data['price'] ?? 0),
            (int) ($data['category_id'] ?? 0)
        );
    }
}
```

#### GetProducts Use Case с фильтрацией

```php
class GetProducts
{
    private ProductRepositoryInterface $productRepository;

    public function __construct(ProductRepositoryInterface $productRepository)
    {
        $this->productRepository = $productRepository;
    }

    public function execute(ProductFilter $filter): ProductCollection
    {
        $products = $this->productRepository->findByFilter($filter);

        // Применение дополнительных фильтров на уровне домена
        if ($filter->getMinPrice() && $filter->getMaxPrice()) {
            $minPrice = new Money($filter->getMinPrice(), Currency::rub());
            $maxPrice = new Money($filter->getMaxPrice(), Currency::rub());
            $products = $products->getByPriceRange($minPrice, $maxPrice);
        }

        // Сортировка
        switch ($filter->getSortBy()) {
            case 'price':
                $products = $products->sortByPrice($filter->getSortDirection() === 'asc');
                break;
            case 'rating':
                $products = $products->sortByRating($filter->getSortDirection() === 'asc');
                break;
        }

        return $products;
    }
}

// Фильтр для поиска
class ProductFilter
{
    private ?int $categoryId = null;
    private ?float $minPrice = null;
    private ?float $maxPrice = null;
    private bool $publishedOnly = true;
    private string $sortBy = 'created_at';
    private string $sortDirection = 'desc';
    private int $limit = 20;
    private int $offset = 0;

    public function getCategoryId(): ?int { return $this->categoryId; }
    public function setCategoryId(?int $categoryId): self { $this->categoryId = $categoryId; return $this; }

    public function getMinPrice(): ?float { return $this->minPrice; }
    public function setMinPrice(?float $minPrice): self { $this->minPrice = $minPrice; return $this; }

    public function getMaxPrice(): ?float { return $this->maxPrice; }
    public function setMaxPrice(?float $maxPrice): self { $this->maxPrice = $maxPrice; return $this; }

    public function isPublishedOnly(): bool { return $this->publishedOnly; }
    public function setPublishedOnly(bool $publishedOnly): self { $this->publishedOnly = $publishedOnly; return $this; }

    public function getSortBy(): string { return $this->sortBy; }
    public function setSortBy(string $sortBy): self { $this->sortBy = $sortBy; return $this; }

    public function getSortDirection(): string { return $this->sortDirection; }
    public function setSortDirection(string $sortDirection): self { $this->sortDirection = $sortDirection; return $this; }

    public function getLimit(): int { return $this->limit; }
    public function setLimit(int $limit): self { $this->limit = $limit; return $this; }

    public function getOffset(): int { return $this->offset; }
    public function setOffset(int $offset): self { $this->offset = $offset; return $this; }
}
```

### Доменные события

```php
// Базовое событие
abstract class DomainEvent implements DomainEventInterface
{
    protected \DateTimeInterface $occurredOn;

    public function __construct()
    {
        $this->occurredOn = new \DateTime();
    }

    public function getOccurredOn(): \DateTimeInterface
    {
        return $this->occurredOn;
    }

    abstract public function getEventName(): string;
    abstract public function toArray(): array;
}

// Событие создания продукта
class ProductCreatedEvent extends DomainEvent
{
    private int $productId;
    private string $productName;
    private int $categoryId;

    public function __construct(int $productId, string $productName, int $categoryId)
    {
        parent::__construct();
        $this->productId = $productId;
        $this->productName = $productName;
        $this->categoryId = $categoryId;
    }

    public function getEventName(): string
    {
        return 'product.created';
    }

    public function getProductId(): int
    {
        return $this->productId;
    }

    public function getProductName(): string
    {
        return $this->productName;
    }

    public function getCategoryId(): int
    {
        return $this->categoryId;
    }

    public function toArray(): array
    {
        return [
            'event_name' => $this->getEventName(),
            'occurred_on' => $this->occurredOn->format('c'),
            'product_id' => $this->productId,
            'product_name' => $this->productName,
            'category_id' => $this->categoryId,
        ];
    }
}

// Обработчик событий
class ProductEventSubscriber
{
    private EmailServiceInterface $emailService;
    private CacheServiceInterface $cacheService;
    private LoggerInterface $logger;

    public function __construct(
        EmailServiceInterface $emailService,
        CacheServiceInterface $cacheService,
        LoggerInterface $logger
    ) {
        $this->emailService = $emailService;
        $this->cacheService = $cacheService;
        $this->logger = $logger;
    }

    public function onProductCreated(ProductCreatedEvent $event): void
    {
        // Логирование
        $this->logger->info('Product created', [
            'product_id' => $event->getProductId(),
            'product_name' => $event->getProductName(),
        ]);

        // Очистка кэша
        $this->cacheService->invalidateTag('products');

        // Уведомление администраторов
        $this->emailService->sendNotification(
            'admin@example.com',
            'Новый товар создан',
            "Создан новый товар: {$event->getProductName()}"
        );
    }

    public function onProductPublished(ProductPublishedEvent $event): void
    {
        // Индексация в поисковике
        $this->searchIndexer->index($event->getProductId());

        // Уведомление подписчиков
        $this->notificationService->notifySubscribers($event->getProductId());
    }
}
```

---

## Инфраструктурный слой

### Repository реализации

#### Doctrine ORM Repository

```php
class DoctrineProductRepository implements ProductRepositoryInterface
{
    private EntityManagerInterface $em;
    private EntityRepository $repository;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
        $this->repository = $em->getRepository(ProductEntity::class);
    }

    public function save(Product $product): Product
    {
        $entity = $this->mapToDoctrineEntity($product);
        
        $this->em->persist($entity);
        $this->em->flush();
        
        return $this->mapToAggregate($entity);
    }

    public function findById(int $id): ?Product
    {
        $entity = $this->repository->find($id);
        
        return $entity ? $this->mapToAggregate($entity) : null;
    }

    public function findByFilter(ProductFilter $filter): ProductCollection
    {
        $qb = $this->repository->createQueryBuilder('p');

        if ($filter->getCategoryId()) {
            $qb->andWhere('p.categoryId = :categoryId')
               ->setParameter('categoryId', $filter->getCategoryId());
        }

        if ($filter->isPublishedOnly()) {
            $qb->andWhere('p.status = :status')
               ->setParameter('status', 'published');
        }

        if ($filter->getMinPrice()) {
            $qb->andWhere('p.price >= :minPrice')
               ->setParameter('minPrice', $filter->getMinPrice());
        }

        if ($filter->getMaxPrice()) {
            $qb->andWhere('p.price <= :maxPrice')
               ->setParameter('maxPrice', $filter->getMaxPrice());
        }

        $qb->orderBy('p.' . $filter->getSortBy(), $filter->getSortDirection())
           ->setMaxResults($filter->getLimit())
           ->setFirstResult($filter->getOffset());

        $entities = $qb->getQuery()->getResult();
        
        return $this->mapToCollection($entities);
    }

    public function delete(Product $product): void
    {
        $entity = $this->repository->find($product->getId());
        if ($entity) {
            $this->em->remove($entity);
            $this->em->flush();
        }
    }

    private function mapToDoctrineEntity(Product $product): ProductEntity
    {
        $entity = new ProductEntity();
        $entity->setId($product->getId());
        $entity->setName($product->getName());
        $entity->setDescription($product->getDescription());
        $entity->setPrice($product->getPrice());
        $entity->setCategoryId($product->getCategoryId());
        $entity->setCreatedAt($product->getCreatedAt());
        
        return $entity;
    }

    private function mapToAggregate(ProductEntity $entity): Product
    {
        $product = new Product();
        $product->setId($entity->getId());
        $product->setName($entity->getName());
        $product->setDescription($entity->getDescription());
        $product->setPrice($entity->getPrice());
        $product->setCategoryId($entity->getCategoryId());
        $product->setCreatedAt($entity->getCreatedAt());
        
        // Загрузка связанных данных
        $this->loadReviews($product);
        
        return $product;
    }

    private function mapToCollection(array $entities): ProductCollection
    {
        $collection = new ProductCollection();
        foreach ($entities as $entity) {
            $collection->addItem($this->mapToAggregate($entity));
        }
        return $collection;
    }

    private function loadReviews(Product $product): void
    {
        // Ленивая загрузка отзывов
        $reviews = $this->em->getRepository(ReviewEntity::class)
                           ->findBy(['productId' => $product->getId()]);
        
        foreach ($reviews as $reviewEntity) {
            $review = $this->mapReviewToAggregate($reviewEntity);
            $product->getReviews()->addItem($review);
        }
    }
}
```

#### Redis Cache Repository Decorator

```php
class CachedProductRepository implements ProductRepositoryInterface
{
    private ProductRepositoryInterface $repository;
    private CacheInterface $cache;
    private int $ttl;

    public function __construct(
        ProductRepositoryInterface $repository, 
        CacheInterface $cache, 
        int $ttl = 3600
    ) {
        $this->repository = $repository;
        $this->cache = $cache;
        $this->ttl = $ttl;
    }

    public function save(Product $product): Product
    {
        $savedProduct = $this->repository->save($product);
        
        // Обновляем кэш
        $this->cache->set(
            $this->getCacheKey($savedProduct->getId()),
            serialize($savedProduct),
            $this->ttl
        );
        
        // Инвалидируем связанные кэши
        $this->cache->delete('products:category:' . $savedProduct->getCategoryId());
        
        return $savedProduct;
    }

    public function findById(int $id): ?Product
    {
        $cacheKey = $this->getCacheKey($id);
        $cached = $this->cache->get($cacheKey);
        
        if ($cached !== null) {
            return unserialize($cached);
        }
        
        $product = $this->repository->findById($id);
        
        if ($product) {
            $this->cache->set($cacheKey, serialize($product), $this->ttl);
        }
        
        return $product;
    }

    public function findByFilter(ProductFilter $filter): ProductCollection
    {
        $cacheKey = $this->getFilterCacheKey($filter);
        $cached = $this->cache->get($cacheKey);
        
        if ($cached !== null) {
            return unserialize($cached);
        }
        
        $products = $this->repository->findByFilter($filter);
        
        $this->cache->set($cacheKey, serialize($products), $this->ttl);
        
        return $products;
    }

    public function delete(Product $product): void
    {
        $this->repository->delete($product);
        
        // Очищаем кэш
        $this->cache->delete($this->getCacheKey($product->getId()));
        $this->cache->delete('products:category:' . $product->getCategoryId());
    }

    private function getCacheKey(int $id): string
    {
        return "product:{$id}";
    }

    private function getFilterCacheKey(ProductFilter $filter): string
    {
        $params = [
            'category' => $filter->getCategoryId(),
            'min_price' => $filter->getMinPrice(),
            'max_price' => $filter->getMaxPrice(),
            'published' => $filter->isPublishedOnly(),
            'sort' => $filter->getSortBy(),
            'direction' => $filter->getSortDirection(),
            'limit' => $filter->getLimit(),
            'offset' => $filter->getOffset(),
        ];
        
        return 'products:filter:' . md5(serialize($params));
    }
}
```

---

## Слой представления

### HTTP контроллеры

#### REST API контроллер

```php
class ProductController
{
    private CreateProduct $createProduct;
    private GetProducts $getProducts;
    private GetProduct $getProduct;
    private UpdateProduct $updateProduct;
    private DeleteProduct $deleteProduct;

    public function __construct(
        CreateProduct $createProduct,
        GetProducts $getProducts,
        GetProduct $getProduct,
        UpdateProduct $updateProduct,
        DeleteProduct $deleteProduct
    ) {
        $this->createProduct = $createProduct;
        $this->getProducts = $getProducts;
        $this->getProduct = $getProduct;
        $this->updateProduct = $updateProduct;
        $this->deleteProduct = $deleteProduct;
    }

    /**
     * GET /api/products
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $filter = $this->buildFilterFromRequest($request);
            $products = $this->getProducts->execute($filter);
            
            return new JsonResponse([
                'success' => true,
                'data' => $products->toArray(),
                'meta' => [
                    'total' => $products->count(),
                    'limit' => $filter->getLimit(),
                    'offset' => $filter->getOffset(),
                ]
            ]);
            
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'error' => 'Ошибка получения продуктов',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * GET /api/products/{id}
     */
    public function show(Request $request, int $id): JsonResponse
    {
        try {
            $product = $this->getProduct->execute($id);
            
            if (!$product) {
                return new JsonResponse([
                    'success' => false,
                    'error' => 'Продукт не найден'
                ], 404);
            }
            
            return new JsonResponse([
                'success' => true,
                'data' => $product->toArray()
            ]);
            
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'error' => 'Ошибка получения продукта',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * POST /api/products
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $this->validateCreateRequest($request);
            
            $createRequest = CreateProductRequest::fromArray($request->getData());
            $product = $this->createProduct->execute($createRequest);
            
            return new JsonResponse([
                'success' => true,
                'data' => $product->toArray(),
                'message' => 'Продукт успешно создан'
            ], 201);
            
        } catch (\InvalidArgumentException $e) {
            return new JsonResponse([
                'success' => false,
                'error' => 'Ошибка валидации',
                'message' => $e->getMessage()
            ], 400);
            
        } catch (\DomainException $e) {
            return new JsonResponse([
                'success' => false,
                'error' => 'Бизнес-ошибка',
                'message' => $e->getMessage()
            ], 422);
            
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'error' => 'Внутренняя ошибка сервера'
            ], 500);
        }
    }

    /**
     * PUT /api/products/{id}
     */
    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $this->validateUpdateRequest($request);
            
            $updateRequest = UpdateProductRequest::fromArray($id, $request->getData());
            $product = $this->updateProduct->execute($updateRequest);
            
            return new JsonResponse([
                'success' => true,
                'data' => $product->toArray(),
                'message' => 'Продукт успешно обновлен'
            ]);
            
        } catch (\InvalidArgumentException $e) {
            return new JsonResponse([
                'success' => false,
                'error' => 'Ошибка валидации',
                'message' => $e->getMessage()
            ], 400);
            
        } catch (\DomainException $e) {
            return new JsonResponse([
                'success' => false,
                'error' => 'Бизнес-ошибка',
                'message' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * DELETE /api/products/{id}
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        try {
            $this->deleteProduct->execute($id);
            
            return new JsonResponse([
                'success' => true,
                'message' => 'Продукт успешно удален'
            ]);
            
        } catch (\DomainException $e) {
            return new JsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], 422);
        }
    }

    private function buildFilterFromRequest(Request $request): ProductFilter
    {
        $filter = new ProductFilter();
        
        if ($request->has('category_id')) {
            $filter->setCategoryId((int) $request->get('category_id'));
        }
        
        if ($request->has('min_price')) {
            $filter->setMinPrice((float) $request->get('min_price'));
        }
        
        if ($request->has('max_price')) {
            $filter->setMaxPrice((float) $request->get('max_price'));
        }
        
        if ($request->has('published_only')) {
            $filter->setPublishedOnly((bool) $request->get('published_only'));
        }
        
        if ($request->has('sort_by')) {
            $filter->setSortBy($request->get('sort_by'));
        }
        
        if ($request->has('sort_direction')) {
            $filter->setSortDirection($request->get('sort_direction'));
        }
        
        if ($request->has('limit')) {
            $filter->setLimit((int) $request->get('limit'));
        }
        
        if ($request->has('offset')) {
            $filter->setOffset((int) $request->get('offset'));
        }
        
        return $filter;
    }

    private function validateCreateRequest(Request $request): void
    {
        $required = ['name', 'description', 'price', 'category_id'];
        
        foreach ($required as $field) {
            if (!$request->has($field) || empty($request->get($field))) {
                throw new \InvalidArgumentException("Поле '{$field}' обязательно");
            }
        }
    }

    private function validateUpdateRequest(Request $request): void
    {
        $this->validateCreateRequest($request);
    }
}
```

---

**Это была первая часть примеров кода. Продолжить с CLI командами и остальными примерами?**

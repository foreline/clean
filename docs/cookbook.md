# Cookbook - Готовые решения

> Коллекция типовых задач и их решений в Pristine Framework

## 📚 Содержание

### Доменный слой
- [Создание Value Object с валидацией](#создание-value-object-с-валидацией)
- [Работа с коллекциями](#работа-с-коллекциями)
- [Пользовательские роли и права](#пользовательские-роли-и-права)
- [Агрегаты с бизнес-правилами](#агрегаты-с-бизнес-правилами)

### Use Cases
- [CRUD операции](#crud-операции)
- [Сложные бизнес-сценарии](#сложные-бизнес-сценарии)
- [Обработка ошибок](#обработка-ошибок)

### События
- [Система уведомлений](#система-уведомлений)
- [Интеграция с внешними системами](#интеграция-с-внешними-системами)

### Инфраструктура
- [Настройка репозиториев](#настройка-репозиториев)
- [Конфигурация DI](#конфигурация-di)
- [Тестирование](#тестирование)

---

## 🏗️ Доменный слой

### Создание Value Object с валидацией

#### Задача
Создать Value Object для номера телефона с валидацией российских номеров.

#### Решение

```php
use Domain\ValueObject\StringValueObjectInterface;
use Domain\ValueObject\ValueObjectInterface;

class PhoneNumber implements StringValueObjectInterface
{
    private string $value;

    public function __construct(string $value)
    {
        $cleaned = $this->cleanPhoneNumber($value);
        
        if (!$this->isValidRussianPhone($cleaned)) {
            throw new \InvalidArgumentException(
                "Некорректный номер телефона: {$value}"
            );
        }
        
        $this->value = $cleaned;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function equals(ValueObjectInterface $other): bool
    {
        return $other instanceof self && $this->value === $other->value;
    }

    public function getFormatted(): string
    {
        // +7 (999) 123-45-67
        return sprintf(
            '+7 (%s) %s-%s-%s',
            substr($this->value, 1, 3),
            substr($this->value, 4, 3),
            substr($this->value, 7, 2),
            substr($this->value, 9, 2)
        );
    }

    private function cleanPhoneNumber(string $phone): string
    {
        // Удаляем всё кроме цифр
        $digits = preg_replace('/\D/', '', $phone);
        
        // Конвертируем в российский формат
        if (strlen($digits) === 10) {
            return '7' . $digits; // 7XXXXXXXXXX
        }
        
        if (strlen($digits) === 11 && $digits[0] === '8') {
            return '7' . substr($digits, 1); // 8 -> 7
        }
        
        return $digits;
    }

    private function isValidRussianPhone(string $phone): bool
    {
        // Проверяем длину и что начинается с 7
        return strlen($phone) === 11 && $phone[0] === '7';
    }
}
```

#### Использование

```php
// ✅ Корректные номера
$phone1 = new PhoneNumber('+7 (999) 123-45-67');
$phone2 = new PhoneNumber('8 999 123 45 67');
$phone3 = new PhoneNumber('9991234567');

echo $phone1->getFormatted(); // +7 (999) 123-45-67

// ❌ Некорректный номер
try {
    $phone = new PhoneNumber('123');
} catch (\InvalidArgumentException $e) {
    echo $e->getMessage(); // Некорректный номер телефона: 123
}
```

### Работа с коллекциями

#### Задача
Создать типизированную коллекцию товаров с методами фильтрации и сортировки.

#### Решение

```php
use Domain\Aggregate\CollectionInterface;
use Domain\Aggregate\IteratorTrait;

class ProductCollection implements CollectionInterface
{
    use IteratorTrait;

    /** @var Product[] */
    private array $items = [];

    public function addItem(object $product): self
    {
        if (!$product instanceof Product) {
            throw new \InvalidArgumentException('Ожидается объект Product');
        }

        if (!$this->contains($product)) {
            $this->items[] = $product;
        }
        
        return $this;
    }

    public function removeItem(object $product): self
    {
        $this->items = array_filter(
            $this->items,
            fn($item) => !$item->equals($product)
        );
        
        return $this;
    }

    public function contains(object $product): bool
    {
        foreach ($this->items as $item) {
            if ($item->equals($product)) {
                return true;
            }
        }
        return false;
    }

    public function count(): int
    {
        return count($this->items);
    }

    public function toArray(): array
    {
        return $this->items;
    }

    // Бизнес-методы для работы с коллекцией
    
    public function getAvailable(): self
    {
        $available = new self();
        foreach ($this->items as $product) {
            if ($product->isAvailable()) {
                $available->addItem($product);
            }
        }
        return $available;
    }

    public function sortByPrice(bool $ascending = true): self
    {
        $sorted = clone $this;
        usort($sorted->items, function(Product $a, Product $b) use ($ascending) {
            $result = $a->getPrice()->getValue() <=> $b->getPrice()->getValue();
            return $ascending ? $result : -$result;
        });
        
        return $sorted;
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

    public function getTotalValue(): Money
    {
        $total = Money::zero();
        foreach ($this->items as $product) {
            $total = $total->add($product->getPrice());
        }
        return $total;
    }
}
```

### Пользовательские роли и права

#### Задача
Реализовать гибкую систему ролей с проверкой разрешений.

#### Решение

```php
use Domain\User\Aggregate\User;
use Domain\User\ValueObject\Role;
use Domain\User\ValueObject\Permission;

class UserRoleManager
{
    private array $roleHierarchy = [
        'admin' => ['moderator', 'user'],
        'moderator' => ['user'],
        'user' => []
    ];

    public function hasPermission(User $user, Permission $permission): bool
    {
        $userRoles = $user->getRoles();
        
        foreach ($userRoles as $role) {
            if ($this->roleHasPermission($role, $permission)) {
                return true;
            }
        }
        
        return false;
    }

    public function assignRole(User $user, Role $role): User
    {
        if (!$user->hasRole($role)) {
            $user->addRole($role);
        }
        
        return $user;
    }

    public function getEffectiveRoles(User $user): array
    {
        $roles = [];
        
        foreach ($user->getRoles() as $role) {
            $roles[] = $role;
            $roles = array_merge($roles, $this->getInheritedRoles($role));
        }
        
        return array_unique($roles);
    }

    private function roleHasPermission(Role $role, Permission $permission): bool
    {
        // Проверяем прямые разрешения роли
        if ($role->hasPermission($permission)) {
            return true;
        }
        
        // Проверяем унаследованные роли
        $inheritedRoles = $this->getInheritedRoles($role);
        foreach ($inheritedRoles as $inheritedRole) {
            if ($inheritedRole->hasPermission($permission)) {
                return true;
            }
        }
        
        return false;
    }

    private function getInheritedRoles(Role $role): array
    {
        $roleName = $role->getValue();
        if (!isset($this->roleHierarchy[$roleName])) {
            return [];
        }
        
        $inherited = [];
        foreach ($this->roleHierarchy[$roleName] as $inheritedRoleName) {
            $inherited[] = new Role($inheritedRoleName);
        }
        
        return $inherited;
    }
}
```

## 📝 Use Cases

### CRUD операции

#### Задача
Реализовать стандартные CRUD операции для управления товарами.

#### Решение

```php
use Domain\Product\Repository\ProductRepositoryInterface;
use Domain\Product\Aggregate\Product;
use Infrastructure\Event\Publisher;

class ManageProduct
{
    public function __construct(
        private ProductRepositoryInterface $productRepository,
        private Publisher $eventPublisher
    ) {}

    public function createProduct(array $data): Product
    {
        // Валидация и создание Value Objects
        $name = new ProductName($data['name']);
        $price = new Money($data['price']);
        $category = new Category($data['category']);
        
        // Создание агрегата
        $product = new Product();
        $product->setName($name);
        $product->setPrice($price);
        $product->setCategory($category);
        $product->setCreatedAt(new \DateTimeImmutable());
        
        // Сохранение
        $savedProduct = $this->productRepository->persist($product);
        
        // Публикация события
        $this->eventPublisher->publish(
            new ProductCreatedEvent($savedProduct->getId(), $data)
        );
        
        return $savedProduct;
    }

    public function updateProduct(int $id, array $data): Product
    {
        $product = $this->productRepository->findById($id);
        if (!$product) {
            throw new \DomainException("Товар с ID {$id} не найден");
        }
        
        // Обновление полей
        if (isset($data['name'])) {
            $product->setName(new ProductName($data['name']));
        }
        
        if (isset($data['price'])) {
            $product->setPrice(new Money($data['price']));
        }
        
        $product->setUpdatedAt(new \DateTimeImmutable());
        
        // Сохранение
        $updatedProduct = $this->productRepository->persist($product);
        
        // Событие
        $this->eventPublisher->publish(
            new ProductUpdatedEvent($product->getId(), $data)
        );
        
        return $updatedProduct;
    }

    public function deleteProduct(int $id): void
    {
        $product = $this->productRepository->findById($id);
        if (!$product) {
            throw new \DomainException("Товар с ID {$id} не найден");
        }
        
        // Проверка бизнес-правил
        if ($product->hasActiveOrders()) {
            throw new \DomainException("Нельзя удалить товар с активными заказами");
        }
        
        // Удаление
        $this->productRepository->delete($product);
        
        // Событие
        $this->eventPublisher->publish(
            new ProductDeletedEvent($id)
        );
    }
}
```

### Сложные бизнес-сценарии

#### Задача
Реализовать процесс оформления заказа с проверкой наличия товаров и применением скидок.

#### Решение

```php
class ProcessOrder
{
    public function __construct(
        private ProductRepositoryInterface $productRepository,
        private UserRepositoryInterface $userRepository,
        private OrderRepositoryInterface $orderRepository,
        private DiscountService $discountService,
        private InventoryService $inventoryService,
        private Publisher $eventPublisher
    ) {}

    public function execute(int $userId, array $items): Order
    {
        // Начинаем транзакцию
        $this->orderRepository->beginTransaction();
        
        try {
            // Получаем пользователя
            $user = $this->userRepository->findById($userId);
            if (!$user) {
                throw new \DomainException("Пользователь не найден");
            }
            
            // Создаем заказ
            $order = new Order();
            $order->setUser($user);
            $order->setStatus(OrderStatus::pending());
            $order->setCreatedAt(new \DateTimeImmutable());
            
            $totalAmount = Money::zero();
            
            // Обрабатываем каждый товар
            foreach ($items as $itemData) {
                $product = $this->productRepository->findById($itemData['product_id']);
                if (!$product) {
                    throw new \DomainException("Товар #{$itemData['product_id']} не найден");
                }
                
                $quantity = new Quantity($itemData['quantity']);
                
                // Проверяем наличие
                if (!$this->inventoryService->isAvailable($product, $quantity)) {
                    throw new \DomainException("Недостаточно товара: {$product->getName()}");
                }
                
                // Резервируем товар
                $this->inventoryService->reserve($product, $quantity);
                
                // Создаем элемент заказа
                $orderItem = new OrderItem();
                $orderItem->setProduct($product);
                $orderItem->setQuantity($quantity);
                $orderItem->setPrice($product->getPrice());
                
                $order->addItem($orderItem);
                $totalAmount = $totalAmount->add(
                    $product->getPrice()->multiply($quantity->getValue())
                );
            }
            
            // Применяем скидки
            $discount = $this->discountService->calculateDiscount($user, $order);
            $finalAmount = $totalAmount->subtract($discount);
            
            $order->setTotalAmount($finalAmount);
            $order->setDiscount($discount);
            
            // Сохраняем заказ
            $savedOrder = $this->orderRepository->persist($order);
            
            // Фиксируем транзакцию
            $this->orderRepository->commit();
            
            // Публикуем события
            $this->eventPublisher->publish(
                new OrderCreatedEvent($savedOrder->getId(), $userId)
            );
            
            return $savedOrder;
            
        } catch (\Exception $e) {
            // Откатываем транзакцию
            $this->orderRepository->rollback();
            throw $e;
        }
    }
}
```

## 🔔 События

### Система уведомлений

#### Задача
Создать систему уведомлений, которая реагирует на доменные события.

#### Решение

```php
use Infrastructure\Event\EventSubscriber;

class NotificationSubscriber implements EventSubscriber
{
    public function __construct(
        private EmailService $emailService,
        private SmsService $smsService,
        private UserRepositoryInterface $userRepository
    ) {}

    public function getSubscribedEvents(): array
    {
        return [
            OrderCreatedEvent::class => 'onOrderCreated',
            OrderShippedEvent::class => 'onOrderShipped',
            UserRegisteredEvent::class => 'onUserRegistered',
        ];
    }

    public function onOrderCreated(OrderCreatedEvent $event): void
    {
        $user = $this->userRepository->findById($event->getUserId());
        
        // Email пользователю
        $this->emailService->send(
            $user->getEmail(),
            'Заказ оформлен',
            $this->buildOrderConfirmationEmail($event->getOrderId())
        );
        
        // SMS если включено
        if ($user->getPreferences()->smsEnabled()) {
            $this->smsService->send(
                $user->getPhone(),
                "Ваш заказ #{$event->getOrderId()} принят в обработку"
            );
        }
    }

    public function onOrderShipped(OrderShippedEvent $event): void
    {
        $user = $this->userRepository->findById($event->getUserId());
        
        $this->emailService->send(
            $user->getEmail(),
            'Заказ отправлен',
            $this->buildShippingNotificationEmail(
                $event->getOrderId(),
                $event->getTrackingNumber()
            )
        );
    }

    public function onUserRegistered(UserRegisteredEvent $event): void
    {
        $user = $this->userRepository->findById($event->getUserId());
        
        // Приветственное письмо
        $this->emailService->send(
            $user->getEmail(),
            'Добро пожаловать!',
            $this->buildWelcomeEmail($user)
        );
    }

    private function buildOrderConfirmationEmail(int $orderId): string
    {
        return "Ваш заказ #{$orderId} успешно оформлен и принят в обработку.";
    }

    private function buildShippingNotificationEmail(int $orderId, string $trackingNumber): string
    {
        return "Ваш заказ #{$orderId} отправлен. Трек-номер: {$trackingNumber}";
    }

    private function buildWelcomeEmail(User $user): string
    {
        return "Добро пожаловать, {$user->getName()}! Спасибо за регистрацию.";
    }
}
```

## 🧪 Тестирование

### Тестирование Use Cases

#### Решение

```php
use PHPUnit\Framework\TestCase;

class ProcessOrderTest extends TestCase
{
    private ProcessOrder $useCase;
    private ProductRepositoryInterface $productRepository;
    private UserRepositoryInterface $userRepository;
    private OrderRepositoryInterface $orderRepository;

    protected function setUp(): void
    {
        $this->productRepository = $this->createMock(ProductRepositoryInterface::class);
        $this->userRepository = $this->createMock(UserRepositoryInterface::class);
        $this->orderRepository = $this->createMock(OrderRepositoryInterface::class);
        
        $this->useCase = new ProcessOrder(
            $this->productRepository,
            $this->userRepository,
            $this->orderRepository,
            $this->createMock(DiscountService::class),
            $this->createMock(InventoryService::class),
            $this->createMock(Publisher::class)
        );
    }

    public function testSuccessfulOrderCreation(): void
    {
        // Arrange
        $user = $this->createUser();
        $product = $this->createProduct();
        
        $this->userRepository
            ->expects($this->once())
            ->method('findById')
            ->with(1)
            ->willReturn($user);
            
        $this->productRepository
            ->expects($this->once())
            ->method('findById')
            ->with(1)
            ->willReturn($product);

        // Act
        $order = $this->useCase->execute(1, [
            ['product_id' => 1, 'quantity' => 2]
        ]);

        // Assert
        $this->assertInstanceOf(Order::class, $order);
        $this->assertEquals(1, $order->getUser()->getId());
        $this->assertCount(1, $order->getItems());
    }

    public function testUserNotFoundThrowsException(): void
    {
        // Arrange
        $this->userRepository
            ->expects($this->once())
            ->method('findById')
            ->with(999)
            ->willReturn(null);

        // Act & Assert
        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Пользователь не найден');
        
        $this->useCase->execute(999, []);
    }

    private function createUser(): User
    {
        $user = new User();
        $user->setId(1);
        $user->setEmail(new Email('test@example.com'));
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

---

## 🎯 Полезные советы

### 1. Валидация в Value Objects
Всегда валидируйте данные в конструкторе Value Object и выбрасывайте `InvalidArgumentException` при ошибке.

### 2. Неизменяемость
Делайте Value Objects неизменяемыми. Если нужно изменить значение - создавайте новый объект.

### 3. Equals метод
Обязательно реализуйте метод `equals()` для корректного сравнения Value Objects.

### 4. Коллекции
Используйте типизированные коллекции вместо обычных массивов для лучшей читаемости кода.

### 5. События
Генерируйте события в Use Cases, а не в агрегатах, чтобы соблюдать принцип анемичной модели.

### 6. Транзакции
Управляйте транзакциями в Use Cases для обеспечения консистентности данных.

### 7. Тестирование
Покрывайте тестами в первую очередь Use Cases и Value Objects, так как они содержат основную логику.

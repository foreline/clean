# Инфраструктурный слой

> Технические детали реализации: базы данных, файловые системы, внешние API

Инфраструктурный слой **изолирует доменную логику** от технических деталей реализации. Он содержит конкретные реализации интерфейсов, определенных в доменном слое.

## 🎯 Основные принципы

### 1. Реализация доменных интерфейсов
Инфраструктурный слой **реализует интерфейсы** из доменного слоя:
```php
// Domain слой - только интерфейс
interface UserRepositoryInterface {
    public function save(User $user): User;
    public function findById(int $id): ?User;
}

// Infrastructure слой - конкретная реализация
class DoctrineUserRepository implements UserRepositoryInterface {
    // Реализация через Doctrine ORM
}
```

### 2. Адаптация внешних зависимостей
Инфраструктурный слой **адаптирует** внешние библиотеки и сервисы:
```php
// Адаптер для отправки email через SwiftMailer
class SwiftMailerEmailService implements EmailServiceInterface {
    private Swift_Mailer $mailer;
    
    public function send(EmailMessage $message): bool {
        $swiftMessage = $this->adaptToSwiftMessage($message);
        return $this->mailer->send($swiftMessage) > 0;
    }
}
```

### 3. Сокрытие технических деталей
Доменный слой **не знает** о технических деталях:
```php
// ✅ Хорошо: домен работает через интерфейс
class CreateUser {
    public function execute(string $email): User {
        // Домен не знает, что используется MySQL
        return $this->userRepository->save($user);
    }
}

// ❌ Плохо: домен зависит от технических деталей
class CreateUser {
    public function execute(string $email): User {
        // Прямая работа с базой данных
        $pdo = new PDO('mysql:...');
        // ...
    }
}
```

---

## 🗂️ Основные компоненты

### [Repository (Репозитории)](./repositories.md)

**Конкретные реализации** доменных Repository интерфейсов.

#### Пример реализации через Doctrine ORM:
```php
use Doctrine\ORM\EntityManagerInterface;

class DoctrinePostRepository implements PostRepositoryInterface
{
    private EntityManagerInterface $em;
    private EntityRepository $repository;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
        $this->repository = $em->getRepository(PostEntity::class);
    }

    public function save(Post $post): Post
    {
        // Маппинг агрегата в Doctrine Entity
        $entity = $this->mapToDoctrineEntity($post);
        
        $this->em->persist($entity);
        $this->em->flush();
        
        // Маппинг обратно в агрегат
        return $this->mapToAggregate($entity);
    }

    public function findById(int $id): ?Post
    {
        $entity = $this->repository->find($id);
        
        return $entity ? $this->mapToAggregate($entity) : null;
    }

    public function findByAuthor(int $authorId): PostCollection
    {
        $entities = $this->repository->findBy(['authorId' => $authorId]);
        
        return $this->mapToCollection($entities);
    }

    public function findPublished(int $limit = 10): PostCollection
    {
        $qb = $this->repository->createQueryBuilder('p')
            ->where('p.status = :status')
            ->setParameter('status', 'published')
            ->orderBy('p.createdAt', 'DESC')
            ->setMaxResults($limit);

        $entities = $qb->getQuery()->getResult();
        
        return $this->mapToCollection($entities);
    }

    public function delete(Post $post): void
    {
        $entity = $this->repository->find($post->getId());
        if ($entity) {
            $this->em->remove($entity);
            $this->em->flush();
        }
    }

    /**
     * Маппинг агрегата в Doctrine Entity
     */
    private function mapToDoctrineEntity(Post $post): PostEntity
    {
        $entity = new PostEntity();
        $entity->setId($post->getId());
        $entity->setTitle($post->getTitle());
        $entity->setContent($post->getContent());
        $entity->setAuthorId($post->getAuthorId());
        $entity->setCreatedAt($post->getCreatedAt());
        
        return $entity;
    }

    /**
     * Маппинг Doctrine Entity в агрегат
     */
    private function mapToAggregate(PostEntity $entity): Post
    {
        $post = new Post();
        $post->setId($entity->getId());
        $post->setTitle($entity->getTitle());
        $post->setContent($entity->getContent());
        $post->setAuthorId($entity->getAuthorId());
        $post->setCreatedAt($entity->getCreatedAt());
        
        // Загрузка связанных данных
        $this->loadComments($post);
        $this->loadCategories($post);
        
        return $post;
    }

    private function mapToCollection(array $entities): PostCollection
    {
        $collection = new PostCollection();
        foreach ($entities as $entity) {
            $collection->addItem($this->mapToAggregate($entity));
        }
        return $collection;
    }
}
```

#### Пример реализации через PDO:
```php
class PdoUserRepository implements UserRepositoryInterface
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function save(User $user): User
    {
        if ($user->getId()) {
            return $this->update($user);
        } else {
            return $this->insert($user);
        }
    }

    private function insert(User $user): User
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO users (name, email, created_at) VALUES (?, ?, ?)'
        );
        
        $stmt->execute([
            $user->getName(),
            $user->getEmail(),
            $user->getCreatedAt()->format('Y-m-d H:i:s')
        ]);

        $user->setId((int) $this->pdo->lastInsertId());
        
        return $user;
    }

    private function update(User $user): User
    {
        $stmt = $this->pdo->prepare(
            'UPDATE users SET name = ?, email = ? WHERE id = ?'
        );
        
        $stmt->execute([
            $user->getName(),
            $user->getEmail(),
            $user->getId()
        ]);

        return $user;
    }

    public function findById(int $id): ?User
    {
        $stmt = $this->pdo->prepare('SELECT * FROM users WHERE id = ?');
        $stmt->execute([$id]);
        
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $data ? $this->mapToUser($data) : null;
    }

    private function mapToUser(array $data): User
    {
        $user = new User();
        $user->setId((int) $data['id']);
        $user->setName($data['name']);
        $user->setEmail($data['email']);
        $user->setCreatedAt(new \DateTime($data['created_at']));
        
        return $user;
    }
}
```

---

### [DTO (Data Transfer Objects)](./dto.md)

**Объекты передачи данных** между слоями приложения.

#### Характеристики DTO:
- ✅ Простые контейнеры данных
- ✅ Сериализация/десериализация
- ✅ Валидация входных данных
- ✅ Трансформация форматов

#### Пример DTO для API:
```php
class PostDto
{
    public int $id;
    public string $title;
    public string $content;
    public int $authorId;
    public string $authorName;
    public string $status;
    public string $createdAt;
    public array $categories;
    public int $commentsCount;

    public static function fromAggregate(Post $post, User $author): self
    {
        $dto = new self();
        $dto->id = $post->getId();
        $dto->title = $post->getTitle();
        $dto->content = $post->getContent();
        $dto->authorId = $post->getAuthorId();
        $dto->authorName = $author->getName();
        $dto->status = $post->getStatus()->getValue();
        $dto->createdAt = $post->getCreatedAt()->format('Y-m-d H:i:s');
        $dto->categories = $post->getCategories()->toArray();
        $dto->commentsCount = $post->getComments()->count();
        
        return $dto;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'content' => $this->content,
            'author' => [
                'id' => $this->authorId,
                'name' => $this->authorName,
            ],
            'status' => $this->status,
            'created_at' => $this->createdAt,
            'categories' => $this->categories,
            'comments_count' => $this->commentsCount,
        ];
    }

    public function toJson(): string
    {
        return json_encode($this->toArray());
    }
}
```

#### DTO для форм (входные данные):
```php
class CreatePostDto
{
    public string $title;
    public string $content;
    public int $authorId;
    public array $categoryIds;
    public array $tagIds;

    public static function fromArray(array $data): self
    {
        $dto = new self();
        $dto->title = $data['title'] ?? '';
        $dto->content = $data['content'] ?? '';
        $dto->authorId = (int) ($data['author_id'] ?? 0);
        $dto->categoryIds = $data['category_ids'] ?? [];
        $dto->tagIds = $data['tag_ids'] ?? [];
        
        return $dto;
    }

    public function validate(): array
    {
        $errors = [];

        if (empty(trim($this->title))) {
            $errors['title'] = 'Заголовок обязателен';
        }

        if (empty(trim($this->content))) {
            $errors['content'] = 'Содержимое обязательно';
        }

        if ($this->authorId <= 0) {
            $errors['author_id'] = 'Некорректный автор';
        }

        return $errors;
    }
}
```

---

### [Mailer (Почтовые сервисы)](./mailer.md)

**Адаптеры** для отправки электронной почты.

#### Интерфейс в доменном слое:
```php
// Domain/Service/EmailServiceInterface.php
interface EmailServiceInterface
{
    public function send(EmailMessage $message): bool;
    public function sendBatch(array $messages): int;
}
```

#### Реализация через SwiftMailer:
```php
use Swift_Mailer;
use Swift_Message;

class SwiftMailerEmailService implements EmailServiceInterface
{
    private Swift_Mailer $mailer;

    public function __construct(Swift_Mailer $mailer)
    {
        $this->mailer = $mailer;
    }

    public function send(EmailMessage $message): bool
    {
        $swiftMessage = $this->createSwiftMessage($message);
        
        try {
            return $this->mailer->send($swiftMessage) > 0;
        } catch (\Exception $e) {
            // Логирование ошибки
            return false;
        }
    }

    public function sendBatch(array $messages): int
    {
        $sent = 0;
        foreach ($messages as $message) {
            if ($this->send($message)) {
                $sent++;
            }
        }
        return $sent;
    }

    private function createSwiftMessage(EmailMessage $message): Swift_Message
    {
        $swiftMessage = new Swift_Message();
        $swiftMessage->setSubject($message->getSubject());
        $swiftMessage->setFrom($message->getFrom());
        $swiftMessage->setTo($message->getTo());
        $swiftMessage->setBody($message->getBody(), $message->getContentType());

        if ($message->hasAttachments()) {
            foreach ($message->getAttachments() as $attachment) {
                $swiftMessage->attach($attachment);
            }
        }

        return $swiftMessage;
    }
}
```

---

### [Migration (Миграции базы данных)](./migration.md)

**Управление схемой базы данных** для эволюции структуры.

#### Базовый интерфейс миграции:
```php
interface MigrationInterface
{
    public function up(): void;
    public function down(): void;
    public function getVersion(): string;
}
```

#### Пример миграции:
```php
class CreatePostsTable implements MigrationInterface
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function up(): void
    {
        $sql = "
            CREATE TABLE posts (
                id INT AUTO_INCREMENT PRIMARY KEY,
                title VARCHAR(255) NOT NULL,
                content TEXT NOT NULL,
                author_id INT NOT NULL,
                status ENUM('draft', 'published', 'archived') DEFAULT 'draft',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_author (author_id),
                INDEX idx_status (status),
                INDEX idx_created (created_at)
            )
        ";
        
        $this->pdo->exec($sql);
    }

    public function down(): void
    {
        $this->pdo->exec("DROP TABLE posts");
    }

    public function getVersion(): string
    {
        return '2024_01_01_000001';
    }
}
```

---

### [Helpers (Вспомогательные утилиты)](./helpers.md)

**Утилиты** для общих технических задач.

#### Пример утилит для работы с файлами:
```php
class FileHelper
{
    public static function ensureDirectoryExists(string $path): void
    {
        if (!is_dir($path)) {
            mkdir($path, 0755, true);
        }
    }

    public static function generateUniqueFileName(string $originalName): string
    {
        $extension = pathinfo($originalName, PATHINFO_EXTENSION);
        $name = pathinfo($originalName, PATHINFO_FILENAME);
        $timestamp = time();
        $random = bin2hex(random_bytes(8));
        
        return "{$name}_{$timestamp}_{$random}.{$extension}";
    }

    public static function getFileSize(string $path): int
    {
        return file_exists($path) ? filesize($path) : 0;
    }

    public static function getMimeType(string $path): string
    {
        return mime_content_type($path) ?: 'application/octet-stream';
    }
}
```

---

## 🔧 Интеграция с существующими системами

### Принципы интеграции

#### ✅ Используйте адаптеры
```php
// Адаптер для работы с Bitrix CMS
class BitrixUserRepository implements UserRepositoryInterface
{
    public function findById(int $id): ?User
    {
        $bitrixUser = CUser::GetByID($id)->Fetch();
        return $bitrixUser ? $this->mapFromBitrix($bitrixUser) : null;
    }

    private function mapFromBitrix(array $bitrixData): User
    {
        $user = new User();
        $user->setId((int) $bitrixData['ID']);
        $user->setName($bitrixData['NAME'] . ' ' . $bitrixData['LAST_NAME']);
        $user->setEmail($bitrixData['EMAIL']);
        
        return $user;
    }
}
```

#### ✅ Изолируйте зависимости
```php
// Конфигурация зависимостей
$container->bind(UserRepositoryInterface::class, function() {
    if (defined('BITRIX_INSTALLED')) {
        return new BitrixUserRepository();
    } else {
        return new DoctrineUserRepository($entityManager);
    }
});
```

#### ✅ Сохраняйте обратную совместимость
```php
// Гибридная реализация
class HybridUserRepository implements UserRepositoryInterface
{
    private UserRepositoryInterface $primaryRepo;
    private UserRepositoryInterface $fallbackRepo;

    public function findById(int $id): ?User
    {
        $user = $this->primaryRepo->findById($id);
        
        if (!$user) {
            $user = $this->fallbackRepo->findById($id);
        }
        
        return $user;
    }
}
```

---

## 📖 Дополнительные материалы

- **[Лучшие практики инфраструктурного слоя](../best-practices.md#инфраструктурный-слой)**
- **[Примеры интеграции](../examples/index.md#инфраструктурный-слой)**
- **[Руководство по миграции](../migration.md)**



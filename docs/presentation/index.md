# Слой представления

> Интерфейс взаимодействия с внешним миром: HTTP, CLI, API

Слой представления отвечает за **ввод и вывод данных**, обработку пользовательского ввода и представление результатов работы приложения. Он служит мостом между внешними интерфейсами и доменной логикой.

## 🎯 Основные принципы

### 1. Тонкий слой
Presentation слой должен быть **минимальным**:
- ✅ Получение и валидация входных данных
- ✅ Вызов соответствующих Use Cases
- ✅ Форматирование и возврат результатов
- ❌ Никакой бизнес-логики

### 2. Независимость от технологий
Доменный слой **не знает** о способах представления:
```php
// ✅ Хорошо: Use Case не знает об HTTP
class CreatePost {
    public function execute(string $title, string $content): Post {
        // Чистая бизнес-логика
    }
}

// Presentation слой адаптирует к HTTP
class PostController {
    public function create(Request $request): Response {
        $post = $this->createPost->execute(
            $request->get('title'),
            $request->get('content')
        );
        return new JsonResponse($post->toArray());
    }
}
```

### 3. Единообразные интерфейсы
Разные способы представления **используют одни и те же Use Cases**:
```php
// HTTP контроллер
class HttpPostController {
    public function create(Request $request): Response {
        return $this->createPost->execute($data);
    }
}

// CLI команда  
class CreatePostCommand {
    public function handle(array $arguments): void {
        return $this->createPost->execute($data);
    }
}

// GraphQL resolver
class PostResolver {
    public function createPost(array $args): array {
        return $this->createPost->execute($data);
    }
}
```

---

## 🌐 Компоненты слоя представления

### [HTTP Controllers](./http.md)

**Обработчики HTTP-запросов** для веб-приложений и API.

#### Базовая структура контроллера:
```php
use Presentation\HTTP\ControllerInterface;
use Presentation\Response\ResponseInterface;

class PostController implements ControllerInterface
{
    private CreatePost $createPost;
    private GetPost $getPost;
    private UpdatePost $updatePost;
    private DeletePost $deletePost;

    public function __construct(
        CreatePost $createPost,
        GetPost $getPost,
        UpdatePost $updatePost,
        DeletePost $deletePost
    ) {
        $this->createPost = $createPost;
        $this->getPost = $getPost;
        $this->updatePost = $updatePost;
        $this->deletePost = $deletePost;
    }

    /**
     * Создание нового поста
     */
    public function create(Request $request): ResponseInterface
    {
        try {
            // 1. Валидация входных данных
            $this->validateCreateRequest($request);
            
            // 2. Извлечение данных
            $title = $request->get('title');
            $content = $request->get('content');
            $authorId = $request->getUser()->getId();
            $categoryIds = $request->get('category_ids', []);
            
            // 3. Выполнение Use Case
            $post = $this->createPost->execute($title, $content, $authorId, $categoryIds);
            
            // 4. Формирование ответа
            return new JsonResponse([
                'success' => true,
                'data' => PostDto::fromAggregate($post)->toArray()
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
     * Получение поста по ID
     */
    public function show(Request $request, int $id): ResponseInterface
    {
        try {
            $post = $this->getPost->execute($id, $request->getUser()->getId());
            
            if (!$post) {
                return new JsonResponse([
                    'success' => false,
                    'error' => 'Пост не найден'
                ], 404);
            }
            
            return new JsonResponse([
                'success' => true,
                'data' => PostDto::fromAggregate($post)->toArray()
            ]);
            
        } catch (\DomainException $e) {
            return new JsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], 403);
        }
    }

    /**
     * Обновление поста
     */
    public function update(Request $request, int $id): ResponseInterface
    {
        try {
            $this->validateUpdateRequest($request);
            
            $updatedPost = $this->updatePost->execute(
                $id,
                $request->get('title'),
                $request->get('content'),
                $request->getUser()->getId()
            );
            
            return new JsonResponse([
                'success' => true,
                'data' => PostDto::fromAggregate($updatedPost)->toArray()
            ]);
            
        } catch (\InvalidArgumentException $e) {
            return new JsonResponse([
                'success' => false,
                'error' => 'Ошибка валидации',
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Удаление поста
     */
    public function delete(Request $request, int $id): ResponseInterface
    {
        try {
            $this->deletePost->execute($id, $request->getUser()->getId());
            
            return new JsonResponse([
                'success' => true,
                'message' => 'Пост успешно удален'
            ]);
            
        } catch (\DomainException $e) {
            return new JsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], 403);
        }
    }

    private function validateCreateRequest(Request $request): void
    {
        if (empty($request->get('title'))) {
            throw new \InvalidArgumentException('Заголовок обязателен');
        }
        
        if (empty($request->get('content'))) {
            throw new \InvalidArgumentException('Содержимое обязательно');
        }
    }

    private function validateUpdateRequest(Request $request): void
    {
        $this->validateCreateRequest($request);
    }
}
```

#### REST API структура:
```php
// Маршруты для REST API
$routes = [
    'GET /api/posts' => [PostController::class, 'index'],
    'GET /api/posts/{id}' => [PostController::class, 'show'],
    'POST /api/posts' => [PostController::class, 'create'],
    'PUT /api/posts/{id}' => [PostController::class, 'update'],
    'DELETE /api/posts/{id}' => [PostController::class, 'delete'],
];
```

---

### [Response Builders](./response.md)

**Стандартизированные ответы** для различных типов представления.

#### Базовые типы ответов:
```php
// JSON API ответ
class JsonResponse implements ResponseInterface
{
    private array $data;
    private int $statusCode;
    private array $headers;

    public function __construct(array $data, int $statusCode = 200, array $headers = [])
    {
        $this->data = $data;
        $this->statusCode = $statusCode;
        $this->headers = array_merge(['Content-Type' => 'application/json'], $headers);
    }

    public function getContent(): string
    {
        return json_encode($this->data, JSON_UNESCAPED_UNICODE);
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }
}

// XML ответ
class XmlResponse implements ResponseInterface
{
    private string $xml;
    private int $statusCode;

    public function __construct(string $xml, int $statusCode = 200)
    {
        $this->xml = $xml;
        $this->statusCode = $statusCode;
    }

    public function getContent(): string
    {
        return $this->xml;
    }

    public function getHeaders(): array
    {
        return ['Content-Type' => 'application/xml'];
    }
}

// HTML ответ
class HtmlResponse implements ResponseInterface
{
    private string $html;
    private int $statusCode;

    public function __construct(string $html, int $statusCode = 200)
    {
        $this->html = $html;
        $this->statusCode = $statusCode;
    }

    public function getContent(): string
    {
        return $this->html;
    }

    public function getHeaders(): array
    {
        return ['Content-Type' => 'text/html; charset=utf-8'];
    }
}
```

#### Response Factory для удобства:
```php
class ResponseFactory
{
    public static function success(array $data, int $statusCode = 200): JsonResponse
    {
        return new JsonResponse([
            'success' => true,
            'data' => $data
        ], $statusCode);
    }

    public static function error(string $message, int $statusCode = 400, array $details = []): JsonResponse
    {
        $response = [
            'success' => false,
            'error' => $message
        ];

        if (!empty($details)) {
            $response['details'] = $details;
        }

        return new JsonResponse($response, $statusCode);
    }

    public static function validationError(array $errors): JsonResponse
    {
        return new JsonResponse([
            'success' => false,
            'error' => 'Ошибка валидации',
            'validation_errors' => $errors
        ], 422);
    }

    public static function notFound(string $message = 'Ресурс не найден'): JsonResponse
    {
        return new JsonResponse([
            'success' => false,
            'error' => $message
        ], 404);
    }

    public static function unauthorized(string $message = 'Необходима авторизация'): JsonResponse
    {
        return new JsonResponse([
            'success' => false,
            'error' => $message
        ], 401);
    }

    public static function forbidden(string $message = 'Доступ запрещен'): JsonResponse
    {
        return new JsonResponse([
            'success' => false,
            'error' => $message
        ], 403);
    }
}
```

---

### [CLI Commands](./cli.md)

**Консольные команды** для административных задач и автоматизации.

#### Базовая структура CLI команды:
```php
use Presentation\CLI\CommandInterface;

class CreatePostCommand implements CommandInterface
{
    private CreatePost $createPost;
    private UserRepositoryInterface $userRepository;

    public function __construct(
        CreatePost $createPost,
        UserRepositoryInterface $userRepository
    ) {
        $this->createPost = $createPost;
        $this->userRepository = $userRepository;
    }

    public function getName(): string
    {
        return 'post:create';
    }

    public function getDescription(): string
    {
        return 'Создание нового поста';
    }

    public function getArguments(): array
    {
        return [
            'title' => 'Заголовок поста',
            'content' => 'Содержимое поста',
            'author-email' => 'Email автора'
        ];
    }

    public function getOptions(): array
    {
        return [
            'publish' => 'Опубликовать пост сразу после создания',
            'categories' => 'ID категорий через запятую'
        ];
    }

    public function execute(array $arguments, array $options): int
    {
        try {
            // Валидация аргументов
            $this->validateArguments($arguments);
            
            // Поиск автора
            $author = $this->userRepository->findByEmail($arguments['author-email']);
            if (!$author) {
                $this->output("Ошибка: Пользователь с email {$arguments['author-email']} не найден");
                return 1;
            }

            // Создание поста
            $post = $this->createPost->execute(
                $arguments['title'],
                $arguments['content'],
                $author->getId(),
                $this->parseCategoryIds($options['categories'] ?? '')
            );

            $this->output("✅ Пост создан с ID: {$post->getId()}");

            // Публикация если указана опция
            if (isset($options['publish'])) {
                $post->publish();
                $this->output("✅ Пост опубликован");
            }

            return 0;

        } catch (\InvalidArgumentException $e) {
            $this->output("❌ Ошибка валидации: {$e->getMessage()}");
            return 1;
            
        } catch (\DomainException $e) {
            $this->output("❌ Бизнес-ошибка: {$e->getMessage()}");
            return 1;
            
        } catch (\Exception $e) {
            $this->output("❌ Внутренняя ошибка: {$e->getMessage()}");
            return 1;
        }
    }

    private function validateArguments(array $arguments): void
    {
        if (empty($arguments['title'])) {
            throw new \InvalidArgumentException('Заголовок не может быть пустым');
        }

        if (empty($arguments['content'])) {
            throw new \InvalidArgumentException('Содержимое не может быть пустым');
        }

        if (empty($arguments['author-email'])) {
            throw new \InvalidArgumentException('Email автора обязателен');
        }
    }

    private function parseCategoryIds(string $categories): array
    {
        if (empty($categories)) {
            return [];
        }

        return array_map(
            fn($id) => (int) trim($id),
            explode(',', $categories)
        );
    }

    private function output(string $message): void
    {
        echo $message . PHP_EOL;
    }
}
```

#### Команды для типовых задач:
```php
// Команда для миграций
class RunMigrationsCommand implements CommandInterface
{
    public function execute(array $arguments, array $options): int
    {
        $migrator = new DatabaseMigrator();
        $migrations = $migrator->getPendingMigrations();
        
        foreach ($migrations as $migration) {
            $this->output("Выполнение миграции: {$migration->getVersion()}");
            $migration->up();
        }
        
        $this->output("✅ Все миграции выполнены");
        return 0;
    }
}

// Команда для очистки кэша
class ClearCacheCommand implements CommandInterface
{
    public function execute(array $arguments, array $options): int
    {
        $cacheDir = $arguments['cache-dir'] ?? '/tmp/app-cache';
        
        if (is_dir($cacheDir)) {
            $this->deleteDirectory($cacheDir);
            $this->output("✅ Кэш очищен");
        } else {
            $this->output("ℹ️ Директория кэша не найдена");
        }
        
        return 0;
    }
}
```

---

### [Middleware & Helpers](./helpers.md)

**Вспомогательные компоненты** для обработки запросов.

#### Middleware для аутентификации:
```php
class AuthenticationMiddleware implements MiddlewareInterface
{
    private UserRepositoryInterface $userRepository;

    public function process(Request $request, callable $next): ResponseInterface
    {
        $token = $request->getHeader('Authorization');
        
        if (!$token) {
            return ResponseFactory::unauthorized('Токен аутентификации отсутствует');
        }

        $user = $this->validateToken($token);
        if (!$user) {
            return ResponseFactory::unauthorized('Недействительный токен');
        }

        // Добавляем пользователя в запрос
        $request->setUser($user);
        
        return $next($request);
    }

    private function validateToken(string $token): ?User
    {
        // Валидация JWT токена или другой логики
        $payload = $this->decodeToken($token);
        
        return $payload ? $this->userRepository->findById($payload['user_id']) : null;
    }
}
```

#### Middleware для валидации:
```php
class ValidationMiddleware implements MiddlewareInterface
{
    private array $rules;

    public function __construct(array $rules)
    {
        $this->rules = $rules;
    }

    public function process(Request $request, callable $next): ResponseInterface
    {
        $errors = $this->validate($request->getData(), $this->rules);
        
        if (!empty($errors)) {
            return ResponseFactory::validationError($errors);
        }
        
        return $next($request);
    }

    private function validate(array $data, array $rules): array
    {
        $errors = [];
        
        foreach ($rules as $field => $rule) {
            if (empty($data[$field]) && str_contains($rule, 'required')) {
                $errors[$field] = "Поле {$field} обязательно";
            }
        }
        
        return $errors;
    }
}
```

#### Helper для форматирования данных:
```php
class DataFormatter
{
    public static function formatPostForApi(Post $post, User $author): array
    {
        return [
            'id' => $post->getId(),
            'title' => $post->getTitle(),
            'content' => $post->getContent(),
            'excerpt' => self::createExcerpt($post->getContent()),
            'author' => [
                'id' => $author->getId(),
                'name' => $author->getName(),
                'email' => $author->getEmail(),
            ],
            'status' => $post->getStatus()->getValue(),
            'created_at' => $post->getCreatedAt()->format('c'),
            'categories' => $post->getCategories()->toArray(),
            'comments_count' => $post->getComments()->count(),
        ];
    }

    public static function formatPostCollection(PostCollection $posts): array
    {
        $result = [];
        foreach ($posts as $post) {
            $result[] = self::formatPostForApi($post);
        }
        return $result;
    }

    private static function createExcerpt(string $content, int $length = 200): string
    {
        $content = strip_tags($content);
        
        if (strlen($content) <= $length) {
            return $content;
        }
        
        return substr($content, 0, $length) . '...';
    }
}
```

---

## 🔧 Интеграция с фреймворками

### Принципы интеграции

#### ✅ Используйте адаптеры
```php
// Адаптер для Symfony Request
class SymfonyRequestAdapter implements RequestInterface
{
    private SymfonyRequest $request;

    public function __construct(SymfonyRequest $request)
    {
        $this->request = $request;
    }

    public function get(string $key, $default = null)
    {
        return $this->request->get($key, $default);
    }

    public function getHeader(string $name): ?string
    {
        return $this->request->headers->get($name);
    }
}
```

#### ✅ Сохраняйте независимость
```php
// Контроллер остается независимым от Symfony
class PostController
{
    public function create(RequestInterface $request): ResponseInterface
    {
        // Логика не зависит от конкретного фреймворка
    }
}

// Symfony роутинг
class SymfonyPostController extends Controller
{
    public function create(SymfonyRequest $request): SymfonyResponse
    {
        $adaptedRequest = new SymfonyRequestAdapter($request);
        $response = $this->postController->create($adaptedRequest);
        
        return new SymfonyJsonResponse(
            json_decode($response->getContent(), true),
            $response->getStatusCode()
        );
    }
}
```

---

## 📖 Дополнительные материалы

- **[Лучшие практики слоя представления](../best-practices.md#слой-представления)**
- **[Примеры API контроллеров](../examples/index.md#слой-представления)**
- **[Интеграция с популярными фреймворками](../integration.md)**
